package es.favala.wttc

import android.annotation.SuppressLint
import android.bluetooth.BluetoothAdapter
import android.bluetooth.BluetoothDevice
import android.bluetooth.BluetoothGatt
import android.bluetooth.BluetoothGattCallback
import android.bluetooth.BluetoothGattCharacteristic
import android.bluetooth.BluetoothGattDescriptor
import android.bluetooth.BluetoothManager
import android.bluetooth.BluetoothProfile
import android.bluetooth.BluetoothStatusCodes
import android.bluetooth.le.ScanCallback
import android.bluetooth.le.ScanFilter
import android.bluetooth.le.ScanResult
import android.bluetooth.le.ScanSettings
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.os.Build
import android.os.Handler
import android.os.Looper
import android.os.ParcelUuid
import org.json.JSONObject
import java.util.UUID

/**
 * Enlace Bluetooth LE con el ESP32 de WTTC.
 *
 * Código generado íntegramente con Claude (Anthropic).
 *
 * Flujo normal:
 *  1. scan(): busca anuncios con el UUID del servicio WTTC (solo aparecen placas WTTC).
 *  2. choose(): guarda la placa elegida y la empareja; Android muestra su diálogo y pide el PIN de 6 cifras.
 *  3. connect(): conecta por GATT, pide un MTU grande, descubre el servicio y activa las notificaciones.
 *  4. Conectado: llegan el estado (cada 2 s) y las respuestas; send() manda órdenes.
 *  5. Si se pierde la conexión, se vuelve a conectar con autoConnect: Android lo hace solo, gastando poco,
 *     en cuanto la placa vuelve a estar al alcance.
 *
 * Servicio con tres características (los mismos UUID que el firmware):
 *  - STATE: estado en JSON (lectura y notificación cada 2 s)
 *  - CMD:   órdenes en texto ("on 30", "off", "cfg", "set clave=valor"...)
 *  - RESP:  respuesta "orden:datos" (lectura y notificación)
 * Todas exigen emparejamiento con el PIN de 6 cifras de la placa.
 *
 * Android solo admite una operación GATT a la vez: todas pasan por una cola en el hilo principal.
 */
@SuppressLint("MissingPermission")   // los permisos se piden en MainActivity antes de usar esta clase
class BleLink(private val ctx: Context, private val listener: Listener) {

    interface Listener {
        fun onLink(state: State, detail: String)
        fun onDeviceState(j: JSONObject)
        fun onResponse(cmd: String, data: String)
    }

    enum class State { NO_BLUETOOTH, NOT_PAIRED, SCANNING, PAIRING, CONNECTING, CONNECTED, OUT_OF_RANGE }

    companion object {
        val SVC: UUID = UUID.fromString("6e0a0001-7c1d-4b9a-9f3e-5a2c8d7e4b10")
        val CH_STATE: UUID = UUID.fromString("6e0a0002-7c1d-4b9a-9f3e-5a2c8d7e4b10")
        val CH_CMD: UUID = UUID.fromString("6e0a0003-7c1d-4b9a-9f3e-5a2c8d7e4b10")
        val CH_RESP: UUID = UUID.fromString("6e0a0004-7c1d-4b9a-9f3e-5a2c8d7e4b10")
        val CCCD: UUID = UUID.fromString("00002902-0000-1000-8000-00805f9b34fb")
        private const val OP_TIMEOUT_MS = 6000L
        private const val SCAN_MS = 15000L
    }

    // Todo el estado se toca solo desde el hilo principal: los callbacks de Bluetooth llegan en otros hilos
    // y se reenvían aquí con main.post { … }
    private val main = Handler(Looper.getMainLooper())
    private val adapter: BluetoothAdapter? = ctx.getSystemService(BluetoothManager::class.java)?.adapter
    private val prefs = ctx.getSharedPreferences("wttc", Context.MODE_PRIVATE)

    private var gatt: BluetoothGatt? = null      // conexión GATT activa (null = sin conexión)
    private var mtu = 23                         // tamaño de paquete negociado (23 es el mínimo de Bluetooth LE)
    private var wantConnected = false            // ¿queremos estar conectados? (si se cae, reconectar)
    private var scanning = false                 // ¿hay una búsqueda en marcha?
    private var receiverOn = false               // ¿está registrado el receptor de cambios de emparejamiento?
    var state = State.NOT_PAIRED
        private set

    // ---------- cola de operaciones GATT ----------
    // Cada operación es una función que la lanza y devuelve false si no se pudo lanzar.
    // Se espera a su callback (opDone) antes de lanzar la siguiente; si no llega en 6 s, se sigue igualmente.
    private val ops = ArrayDeque<() -> Boolean>()
    private var opBusy = false
    private val opTimeout = Runnable { opBusy = false; next() }

    private fun enqueue(op: () -> Boolean) = main.post { ops.addLast(op); next() }

    private fun next() {
        if (opBusy) return
        val op = ops.removeFirstOrNull() ?: return
        opBusy = true
        main.postDelayed(opTimeout, OP_TIMEOUT_MS)
        if (!op()) opDone()          // no se pudo lanzar: pasa a la siguiente
    }

    private fun opDone() = main.post {
        main.removeCallbacks(opTimeout)
        opBusy = false
        next()
    }

    // ---------- estado del enlace ----------
    private fun setState(s: State, detail: String = "") = main.post {
        state = s
        listener.onLink(s, detail)
    }

    val savedAddress: String? get() = prefs.getString("addr", null)
    val savedName: String? get() = prefs.getString("name", null)

    /** ¿Está el WTTC guardado emparejado en Android? */
    fun isPaired(): Boolean {
        val a = savedAddress ?: return false
        return adapter?.bondedDevices?.any { it.address == a } == true
    }

    fun bluetoothOn(): Boolean = adapter?.isEnabled == true

    /** Arranca: si hay un WTTC emparejado se conecta (y se reconecta solo cuando vuelva a estar cerca). */
    fun start() {
        if (!bluetoothOn()) { setState(State.NO_BLUETOOTH); return }
        val a = savedAddress
        if (a == null || !isPaired()) { setState(State.NOT_PAIRED); return }
        wantConnected = true
        connect(adapter!!.getRemoteDevice(a), auto = false)
    }

    fun stop() {
        wantConnected = false
        stopScan()
        unregisterBond()
        main.removeCallbacksAndMessages(null)
        ops.clear(); opBusy = false
        gatt?.close(); gatt = null
    }

    /** Olvida el WTTC en la app (el emparejamiento de Android se quita en sus Ajustes de Bluetooth). */
    fun forget() {
        stop()
        prefs.edit().remove("addr").remove("name").apply()
        setState(State.NOT_PAIRED)
    }

    // ---------- búsqueda ----------
    private var onFound: ((BluetoothDevice, String) -> Unit)? = null
    private val scanCb = object : ScanCallback() {
        override fun onScanResult(callbackType: Int, result: ScanResult) {
            val name = result.scanRecord?.deviceName ?: result.device.name ?: "WTTC"
            main.post { onFound?.invoke(result.device, name) }
        }
        override fun onScanFailed(errorCode: Int) { scanning = false; setState(State.NOT_PAIRED, "No se pudo buscar ($errorCode)") }
    }

    /** Busca placas WTTC (por el UUID de su servicio) durante 15 s. */
    fun scan(found: (BluetoothDevice, String) -> Unit) {
        if (!bluetoothOn()) { setState(State.NO_BLUETOOTH); return }
        val sc = adapter?.bluetoothLeScanner ?: return
        stopScan()
        onFound = found
        val filter = ScanFilter.Builder().setServiceUuid(ParcelUuid(SVC)).build()
        val settings = ScanSettings.Builder().setScanMode(ScanSettings.SCAN_MODE_LOW_LATENCY).build()
        sc.startScan(listOf(filter), settings, scanCb)
        scanning = true
        setState(State.SCANNING)
        main.postDelayed({ if (scanning) { stopScan(); if (state == State.SCANNING) setState(State.NOT_PAIRED, "Búsqueda terminada") } }, SCAN_MS)
    }

    fun stopScan() {
        if (scanning) runCatching { adapter?.bluetoothLeScanner?.stopScan(scanCb) }
        scanning = false
        onFound = null
    }

    // ---------- emparejamiento ----------
    private val bondReceiver = object : BroadcastReceiver() {
        override fun onReceive(c: Context, i: Intent) {
            if (i.action != BluetoothDevice.ACTION_BOND_STATE_CHANGED) return
            val dev: BluetoothDevice? = if (Build.VERSION.SDK_INT >= 33)
                i.getParcelableExtra(BluetoothDevice.EXTRA_DEVICE, BluetoothDevice::class.java)
            else @Suppress("DEPRECATION") i.getParcelableExtra(BluetoothDevice.EXTRA_DEVICE)
            if (dev?.address != savedAddress) return
            when (i.getIntExtra(BluetoothDevice.EXTRA_BOND_STATE, BluetoothDevice.ERROR)) {
                BluetoothDevice.BOND_BONDED -> { unregisterBond(); wantConnected = true; connect(dev!!, auto = false) }
                BluetoothDevice.BOND_NONE -> { unregisterBond(); setState(State.NOT_PAIRED, "Emparejamiento cancelado o PIN incorrecto") }
            }
        }
    }

    private fun registerBond() {
        if (receiverOn) return
        val f = IntentFilter(BluetoothDevice.ACTION_BOND_STATE_CHANGED)
        if (Build.VERSION.SDK_INT >= 33) ctx.registerReceiver(bondReceiver, f, Context.RECEIVER_EXPORTED)
        else ctx.registerReceiver(bondReceiver, f)
        receiverOn = true
    }

    private fun unregisterBond() {
        if (receiverOn) runCatching { ctx.unregisterReceiver(bondReceiver) }
        receiverOn = false
    }

    /** Elige una placa encontrada: la guarda, la empareja (Android pide el PIN) y se conecta. */
    fun choose(dev: BluetoothDevice, name: String) {
        stopScan()
        prefs.edit().putString("addr", dev.address).putString("name", name).apply()
        if (dev.bondState == BluetoothDevice.BOND_BONDED) { wantConnected = true; connect(dev, auto = false); return }
        registerBond()
        setState(State.PAIRING)
        if (!dev.createBond()) { unregisterBond(); setState(State.NOT_PAIRED, "Android no ha podido iniciar el emparejamiento") }
    }

    // ---------- conexión ----------
    private fun connect(dev: BluetoothDevice, auto: Boolean) {
        gatt?.close()
        mtu = 23
        setState(if (auto) State.OUT_OF_RANGE else State.CONNECTING)
        // autoConnect = true: Android se conecta él solo, gastando poco, cuando la placa vuelva a estar al alcance
        gatt = dev.connectGatt(ctx, auto, gattCb, BluetoothDevice.TRANSPORT_LE)
    }

    // Callbacks de la conexión GATT (llegan en un hilo de Bluetooth, no en el principal)
    private val gattCb = object : BluetoothGattCallback() {
        override fun onConnectionStateChange(g: BluetoothGatt, status: Int, newState: Int) {
            if (newState == BluetoothProfile.STATE_CONNECTED) {
                setState(State.CONNECTING, "Preparando…")
                main.post { if (!g.requestMtu(517)) g.discoverServices() }
            } else if (newState == BluetoothProfile.STATE_DISCONNECTED) {
                main.post {
                    g.close()
                    if (gatt == g) gatt = null
                    ops.clear(); opBusy = false; main.removeCallbacks(opTimeout)
                    if (wantConnected) {
                        val a = savedAddress
                        if (a != null && isPaired()) connect(adapter!!.getRemoteDevice(a), auto = true)
                        else setState(State.NOT_PAIRED)
                    }
                }
            }
        }

        override fun onMtuChanged(g: BluetoothGatt, newMtu: Int, status: Int) {
            if (status == BluetoothGatt.GATT_SUCCESS) mtu = newMtu
            main.post { g.discoverServices() }
        }

        override fun onServicesDiscovered(g: BluetoothGatt, status: Int) {
            val svc = g.getService(SVC)
            if (status != BluetoothGatt.GATT_SUCCESS || svc == null) {
                setState(State.OUT_OF_RANGE, "Esta placa no tiene el servicio WTTC")
                return
            }
            enqueue { enableNotify(g, svc.getCharacteristic(CH_STATE)) }
            enqueue { enableNotify(g, svc.getCharacteristic(CH_RESP)) }
            main.post {
                setState(State.CONNECTED)
                // La placa no tiene reloj con pila: se le da la hora del móvil al conectar
                send("time ${System.currentTimeMillis() / 1000}")
                send("cfg")
                send("sched")
            }
        }

        override fun onDescriptorWrite(g: BluetoothGatt, d: BluetoothGattDescriptor, status: Int) {
            opDone()
        }

        override fun onCharacteristicWrite(g: BluetoothGatt, c: BluetoothGattCharacteristic, status: Int) {
            opDone()
        }

        // Android 13+
        override fun onCharacteristicRead(g: BluetoothGatt, c: BluetoothGattCharacteristic, value: ByteArray, status: Int) {
            if (status == BluetoothGatt.GATT_SUCCESS) dispatch(c.uuid, value)
            opDone()
        }

        @Deprecated("Android 12 y anteriores")
        override fun onCharacteristicRead(g: BluetoothGatt, c: BluetoothGattCharacteristic, status: Int) {
            if (Build.VERSION.SDK_INT >= 33) return
            @Suppress("DEPRECATION") val v = c.value ?: ByteArray(0)
            if (status == BluetoothGatt.GATT_SUCCESS) dispatch(c.uuid, v)
            opDone()
        }

        // Android 13+
        override fun onCharacteristicChanged(g: BluetoothGatt, c: BluetoothGattCharacteristic, value: ByteArray) {
            changed(g, c, value)
        }

        @Deprecated("Android 12 y anteriores")
        override fun onCharacteristicChanged(g: BluetoothGatt, c: BluetoothGattCharacteristic) {
            if (Build.VERSION.SDK_INT >= 33) return
            @Suppress("DEPRECATION") changed(g, c, c.value ?: ByteArray(0))
        }
    }

    // Con un MTU pequeño (algunas radios) la notificación llega cortada: entonces se lee el valor completo
    private fun changed(g: BluetoothGatt, c: BluetoothGattCharacteristic, value: ByteArray) {
        if (mtu >= 200) dispatch(c.uuid, value)
        else enqueue { readChar(g, c) }
    }

    private fun dispatch(uuid: UUID, value: ByteArray) {
        val s = String(value, Charsets.UTF_8)
        main.post {
            when (uuid) {
                CH_STATE -> runCatching { JSONObject(s) }.getOrNull()?.let { listener.onDeviceState(it) }
                CH_RESP -> {
                    val i = s.indexOf(':')
                    if (i > 0) listener.onResponse(s.substring(0, i), s.substring(i + 1))
                }
            }
        }
    }

    // ---------- operaciones ----------
    private fun enableNotify(g: BluetoothGatt, c: BluetoothGattCharacteristic?): Boolean {
        if (c == null) return false
        g.setCharacteristicNotification(c, true)
        val d = c.getDescriptor(CCCD) ?: return false
        val v = BluetoothGattDescriptor.ENABLE_NOTIFICATION_VALUE
        return if (Build.VERSION.SDK_INT >= 33) g.writeDescriptor(d, v) == BluetoothStatusCodes.SUCCESS
        else {
            @Suppress("DEPRECATION") d.value = v
            @Suppress("DEPRECATION") g.writeDescriptor(d)
        }
    }

    private fun readChar(g: BluetoothGatt, c: BluetoothGattCharacteristic): Boolean = g.readCharacteristic(c)

    private fun writeChar(g: BluetoothGatt, c: BluetoothGattCharacteristic, data: ByteArray): Boolean =
        if (Build.VERSION.SDK_INT >= 33)
            g.writeCharacteristic(c, data, BluetoothGattCharacteristic.WRITE_TYPE_DEFAULT) == BluetoothStatusCodes.SUCCESS
        else {
            c.writeType = BluetoothGattCharacteristic.WRITE_TYPE_DEFAULT
            @Suppress("DEPRECATION") c.value = data
            @Suppress("DEPRECATION") g.writeCharacteristic(c)
        }

    /** Envía una orden al ESP32; la respuesta llega por Listener.onResponse. */
    fun send(cmd: String) {
        enqueue {
            val g = gatt ?: return@enqueue false
            val c = g.getService(SVC)?.getCharacteristic(CH_CMD) ?: return@enqueue false
            writeChar(g, c, cmd.toByteArray(Charsets.UTF_8))
        }
    }

    /** Pide el estado completo (lectura de STATE). */
    fun refresh() {
        enqueue {
            val g = gatt ?: return@enqueue false
            val c = g.getService(SVC)?.getCharacteristic(CH_STATE) ?: return@enqueue false
            readChar(g, c)
        }
    }
}
