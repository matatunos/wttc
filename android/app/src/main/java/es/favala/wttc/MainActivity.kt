package es.favala.wttc

import android.Manifest
import android.annotation.SuppressLint
import android.app.Activity
import android.app.AlertDialog
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.TimePickerDialog
import android.bluetooth.BluetoothAdapter
import android.bluetooth.BluetoothDevice
import android.content.Intent
import android.content.pm.PackageManager
import android.graphics.Color
import android.graphics.Typeface
import android.graphics.drawable.GradientDrawable
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.provider.Settings
import android.text.InputType
import android.view.Gravity
import android.view.View
import android.view.ViewGroup
import android.widget.ArrayAdapter
import android.widget.Button
import android.widget.CompoundButton
import android.widget.EditText
import android.widget.FrameLayout
import android.widget.LinearLayout
import android.widget.ScrollView
import android.widget.Spinner
import android.widget.Switch
import android.widget.TextView
import android.widget.Toast
import org.json.JSONObject
import kotlin.math.ceil

@SuppressLint("MissingPermission", "SetTextI18n")
/**
 * Pantalla única de la app WTTC.
 *
 * Código generado íntegramente con Claude (Anthropic).
 *
 * La interfaz se construye por código (sin XML ni AndroidX) para que la app sea pequeña y no dependa de
 * librerías: una columna con desplazamiento que, en pantallas anchas (radios de coche, tablets), no pasa de
 * 640 dp y queda centrada. De arriba abajo: cabecera con el estado del enlace, caja para emparejar (solo si
 * no hay placa emparejada), estado de la calefacción (con la temperatura de dentro si la placa tiene termómetro),
 * duración, objetivo de temperatura y botón grande, hora de salida, programas, diagnóstico, configuración de la placa
 * y ajustes de la app (estadísticas y acceso rápido, ver QuickActivity).
 *
 * Toda la comunicación con la placa va por [BleLink]; esta clase solo pinta y reacciona:
 *  - onLink(): cambia el estado del enlace (buscando, emparejando, conectado, fuera de alcance…)
 *  - onDeviceState(): llega el estado de la placa (cada 2 s mientras está conectada)
 *  - onResponse(): llega la respuesta a una orden ("orden:datos")
 */
class MainActivity : Activity(), BleLink.Listener {
    companion object {
        // Última versión del firmware y su actualización firmada (lo genera wttc-publicar.sh desde el repo)
        private const val OTA_MANIFEST = "https://wttc.favala.es/descargas/ota.json"
    }

    // Colores (los mismos que la web del ESP32)
    private val cBg = Color.parseColor("#0F1A2A")
    private val cSf = Color.parseColor("#172437")
    private val cSf2 = Color.parseColor("#22344D")
    private val cInk = Color.parseColor("#E8EEF6")
    private val cMut = Color.parseColor("#8C9BB0")
    private val cFl = Color.parseColor("#FF9F1C")
    private val cIce = Color.parseColor("#5BC0EB")
    private val cOk = Color.parseColor("#3ECF8E")
    private val cBad = Color.parseColor("#FF6B6B")

    // Duraciones que se ofrecen (el firmware no admite más de 60 min) y letras de los días (lunes primero). Con objetivo
    // de temperatura (termostato) la duración es la ventana máxima, hasta 4 h. Objetivos (el firmware admite 5–25 °C)
    private val durations = intArrayOf(15, 30, 45, 60)
    private val tDurations = intArrayOf(60, 120, 180, 240)
    private val targets = intArrayOf(10, 12, 14, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25)
    private var tgt = 0                // objetivo elegido (0 = encendido normal)
    private var depMin = 8 * 60        // hora de salida elegida (minutos del día)
    private val dayLetters by lazy { resources.getStringArray(R.array.day_letters) }
    // Nombres del estado real que manda el firmware en "ph" (0 apagada … 4 sin respuesta)
    private val phases by lazy { resources.getStringArray(R.array.phases) }
    // Idioma de la interfaz (es, en o de: el del móvil, o inglés si no es ninguno de los tres). Los números van
    // con la coma o el punto de ese idioma, aunque el móvil tenga otra región
    private val lang by lazy { getString(R.string.lang_code) }
    private val numLocale by lazy { java.util.Locale(lang) }

    // Enlace Bluetooth, último estado recibido de la placa y duración elegida en los botones
    private lateinit var link: BleLink
    private var dev: JSONObject? = null
    private var dur = 30
    // Para detectar cambios entre un estado y el siguiente (avisos del sistema y estadísticas)
    private var lastOn = false
    private var lastNote = ""
    private var haveState = false      // hasta el primer estado no se cuentan transiciones
    private var lastWa = false         // aviso de «ya está caliente» ya mostrado en este encendido
    // Modo demostración: placa simulada. Se activa con «Probar sin placa» o al abrir la app con el extra
    // "demo" (lo usan las capturas: adb shell am start … --ez demo true --ez demo_on true --ez capturas true)
    private var demoMode = false
    private var demoHeating = false     // empezar ya encendida (para las capturas)
    private var capturas = false        // sin ventana de estadísticas ni permisos (capturas automáticas)

    // Programas: [activo, días (bit0 = lunes), inicio en minutos, duración, opciones]. Opciones (firmware 0.2.0+):
    // objetivo en °C en los bits 0–5 (0 = sin termostato) y bit 7 = la hora es la de salida
    private data class Prog(var en: Boolean, var days: Int, var start: Int, var dur: Int, var x: Int = 0)
    // Programas en edición, interruptor general y si hay cambios sin guardar en la placa
    private val progs = mutableListOf<Prog>()
    private var progsAuto = true
    private var progsDirty = false

    // Vistas
    // Cabecera: texto y punto de color del estado del enlace
    private lateinit var tLink: TextView
    private lateinit var dot: View
    // Caja de emparejamiento y su texto de ayuda
    private lateinit var pairBox: LinearLayout
    private lateinit var demoBar: LinearLayout   // franja que avisa del modo demostración, con «Salir»
    private lateinit var tPair: TextView
    // Todo lo que manda órdenes a la placa: se desactiva (y se atenúa) mientras no hay conexión
    private lateinit var controls: LinearLayout
    // Pantallas anchas (tablets, radios, televisores): dos columnas dentro de «controls». splitAt = primera vista de la
    // columna derecha (el título de «Programas»); colL/colR solo se usan en modo ancho
    private lateinit var rootCol: LinearLayout
    private var splitAt = 0
    private var colL: LinearLayout? = null
    private var colR: LinearLayout? = null
    // Estado de la calefacción
    private lateinit var tTemp: TextView
    private lateinit var tPhase: TextView
    private lateinit var tRem: TextView
    private lateinit var tStats: TextView
    private lateinit var tGas: TextView
    private lateinit var tNote: TextView
    private lateinit var tCabin: TextView              // temperatura y humedad de dentro (con termómetro)
    private lateinit var tgtRow: LinearLayout          // «Hasta X °C» (con termómetro)
    private lateinit var spTgt: Spinner
    private lateinit var bDepTime: Button              // hora de salida y botón de programarla o cancelarla
    private lateinit var bDep: Button
    private lateinit var tDep: TextView
    // Botones de duración y botón grande de encender/apagar
    private lateinit var segRow: LinearLayout
    private lateinit var bigBtn: Button
    // Programas
    private lateinit var swAuto: Switch
    private lateinit var progList: LinearLayout
    private lateinit var bSaveProgs: Button
    // Diagnóstico (averías y registro)
    private lateinit var tDiag: TextView
    // Campos de la configuración de la placa
    private lateinit var eName: EditText
    private lateinit var ePin: EditText
    private lateinit var eAp: EditText
    private lateinit var spWm: Spinner
    private lateinit var eSsid: EditText
    private lateinit var ePass: EditText
    private lateinit var eTok: EditText
    private lateinit var eChat: EditText
    private lateinit var eMinV: EditText
    private lateinit var eWarm: EditText
    private lateinit var hwBox: LinearLayout           // pantalla, LED y termómetro (firmware 0.2.0+)
    private lateinit var tHw: TextView
    private lateinit var spOled: Spinner
    private lateinit var spDisp: Spinner
    private lateinit var spLed: Spinner
    private lateinit var eToff: EditText
    private lateinit var tCfg: TextView
    private lateinit var tUpd: TextView                // estado de «Buscar actualizaciones»
    private lateinit var pUpd: android.widget.ProgressBar   // y su barra de progreso (mientras la placa descarga)
    private var fwVer = ""                             // versión del firmware de la placa (de «cfg»)
    private var fwOta = false                          // ¿sabe actualizarse sola por internet? (firmware 0.1.5+)
    private var fwTh = false                           // ¿sabe termostato, hora de salida y pantalla? (firmware 0.2.0+)
    private lateinit var spOtaAuto: Spinner            // actualizaciones automáticas de la placa (firmware 0.2.15+)
    private var hasOtaAuto = false
    private var nvAsked = ""                           // versión nueva de la placa por la que ya se ha preguntado
    private var hasSensor = false                      // ¿tiene la placa termómetro? (sin él no se ofrece «Hasta X °C»)
    // Interruptor de las estadísticas anónimas (en «Ajustes de la app»)
    private lateinit var swStats: Switch

    // ---------- ciclo de vida ----------
    // Al crear la pantalla: enlace, interfaz, canal de notificaciones y, la primera vez, la pregunta de las estadísticas
    // Al girar la pantalla o cambiar de tamaño (la actividad no se recrea): se recolocan las columnas
    override fun onConfigurationChanged(newConfig: android.content.res.Configuration) {
        super.onConfigurationChanged(newConfig)
        arrange()
    }

    // Una columna (móvil) o dos (840 dp de ancho o más: tablets, radios de coche, televisores). Mueve las vistas de
    // «controls» entre las dos columnas sin crearlas de nuevo, así que no se pierde nada de lo que hay escrito
    private fun arrange() {
        val wDp = resources.configuration.screenWidthDp
        val wide = wDp >= 840
        val views = mutableListOf<View>()
        val l = colL; val r = colR
        if (l != null && r != null) {
            for (c in listOf(l, r)) { for (i in 0 until c.childCount) views += c.getChildAt(i); c.removeAllViews() }
            controls.removeAllViews(); colL = null; colR = null
        } else { for (i in 0 until controls.childCount) views += controls.getChildAt(i); controls.removeAllViews() }
        if (wide) {
            controls.orientation = LinearLayout.HORIZONTAL
            val nl = LinearLayout(this).apply { orientation = LinearLayout.VERTICAL }
            val nr = LinearLayout(this).apply { orientation = LinearLayout.VERTICAL }
            views.forEachIndexed { i, v -> (if (i < splitAt) nl else nr).addView(v) }
            controls.addView(nl, LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f))
            controls.addView(nr, LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f).apply { leftMargin = dp(28) })
            colL = nl; colR = nr
        } else {
            controls.orientation = LinearLayout.VERTICAL
            views.forEach { controls.addView(it) }
        }
        rootCol.layoutParams = (rootCol.layoutParams as FrameLayout.LayoutParams).apply {
            width = minOf(resources.displayMetrics.widthPixels, dp(if (wide) 1320 else 640))
        }
        rootCol.requestLayout()
        setEnabledDeep(controls, link.state == BleLink.State.CONNECTED || link.demo)
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        link = BleLink(this, this)
        demoMode = intent.getBooleanExtra("demo", false)
        demoHeating = intent.getBooleanExtra("demo_on", false)
        capturas = intent.getBooleanExtra("capturas", false)
        buildUi()
        createChannel()
        if (Stats.consent(this) == null && !capturas) askStats()
    }

    // Al volver a primer plano: permisos y conexión; y el informe de estadísticas del día si toca (y hay permiso)
    override fun onStart() {
        super.onStart()
        if (demoMode) startLink()                     // la placa simulada no necesita permisos de Bluetooth
        else if (askPermissions()) startLink()
        if (!demoMode) Stats.maybeSend(this)
        if (!capturas) checkAppUpdate(silent = true)   // ¿hay app nueva? (una vez al día como mucho)
    }

    // Al salir de primer plano se cierra la conexión: la placa vuelve a anunciarse y gasta menos
    override fun onStop() {
        super.onStop()
        link.stop()
    }

    // Conecta con la placa emparejada (o muestra que el Bluetooth está apagado)
    private fun startLink() {
        if (demoMode) { link.startDemo(demoHeating); return }
        if (!link.bluetoothOn()) {
            onLink(BleLink.State.NO_BLUETOOTH, "")
            return
        }
        link.start()
    }

    // ---------- permisos ----------
    // Permisos que hacen falta según la versión de Android:
    //  - Android 12+: BLUETOOTH_SCAN y BLUETOOTH_CONNECT («dispositivos cercanos»)
    //  - Android 11 y anteriores: ubicación precisa (Android la exige para buscar dispositivos Bluetooth LE)
    //  - Android 13+: notificaciones (para avisar si la calefacción se apaga sola)
    private fun neededPermissions(): Array<String> {
        val l = mutableListOf<String>()
        if (Build.VERSION.SDK_INT >= 31) {
            l += Manifest.permission.BLUETOOTH_SCAN
            l += Manifest.permission.BLUETOOTH_CONNECT
        } else l += Manifest.permission.ACCESS_FINE_LOCATION
        if (Build.VERSION.SDK_INT >= 33) l += Manifest.permission.POST_NOTIFICATIONS
        return l.toTypedArray()
    }

    /** Devuelve true si ya están todos concedidos; si no, los pide. */
    private fun askPermissions(): Boolean {
        val missing = neededPermissions().filter { checkSelfPermission(it) != PackageManager.PERMISSION_GRANTED }
        if (missing.isEmpty()) return true
        requestPermissions(missing.toTypedArray(), 1)
        return false
    }

    // Respuesta a la petición de permisos: el de notificaciones es opcional; los de Bluetooth, imprescindibles
    override fun onRequestPermissionsResult(requestCode: Int, permissions: Array<out String>, grantResults: IntArray) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)
        val bt = permissions.indices.all { i ->
            permissions[i] == Manifest.permission.POST_NOTIFICATIONS || grantResults[i] == PackageManager.PERMISSION_GRANTED
        }
        if (bt) startLink()
        else onLink(BleLink.State.NO_BLUETOOTH, getString(R.string.no_bt_permission))
    }

    // ---------- BleLink.Listener ----------
    // El enlace ha cambiado de estado: texto y color de la cabecera, caja de emparejar y controles activos o no
    override fun onLink(state: BleLink.State, detail: String) {
        val (txt, col) = when (state) {
            BleLink.State.NO_BLUETOOTH -> getString(R.string.link_no_bt) to cBad
            BleLink.State.NOT_PAIRED -> getString(R.string.link_not_paired) to cMut
            BleLink.State.SCANNING -> getString(R.string.link_scanning) to cIce
            BleLink.State.PAIRING -> getString(R.string.link_pairing) to cIce
            BleLink.State.CONNECTING -> getString(R.string.link_connecting) to cIce
            BleLink.State.CONNECTED -> getString(R.string.link_connected, link.savedName ?: "WTTC") to cOk
            BleLink.State.OUT_OF_RANGE -> getString(R.string.link_out_of_range) to cMut
        }
        tLink.text = if (link.demo) getString(R.string.link_demo) else if (detail.isNotEmpty()) "$txt · $detail" else txt
        demoBar.visibility = if (link.demo) View.VISIBLE else View.GONE
        (dot.background as GradientDrawable).setColor(col)

        val paired = link.savedAddress != null && link.isPaired()
        pairBox.visibility = if ((paired || link.demo) && state != BleLink.State.NO_BLUETOOTH) View.GONE else View.VISIBLE
        tPair.text = when (state) {
            BleLink.State.NO_BLUETOOTH -> getString(R.string.pair_no_bt)
            BleLink.State.PAIRING -> getString(R.string.pair_pairing)
            else -> getString(R.string.pair_help)
        }
        setEnabledDeep(controls, state == BleLink.State.CONNECTED)
        if (state != BleLink.State.CONNECTED) bigBtn.text = getString(if (state == BleLink.State.OUT_OF_RANGE) R.string.btn_out_of_range else R.string.btn_no_link)
    }

    // Llega el estado de la placa (JSON con las claves de stateJson() del firmware)
    override fun onDeviceState(j: JSONObject) {
        dev = j
        render()
    }

    // Llega la respuesta a una orden: "orden:datos" o "orden:err mensaje". Cada orden se trata a su manera.
    override fun onResponse(cmd: String, data: String) {
        val err = data.startsWith("err")
        val msg = if (err) data.removePrefix("err").trim() else data
        when (cmd) {
            "on", "off" -> {
                if (err) toast(msg) else if (cmd == "on" && !link.demo) Stats.count(this, "starts_app")
                link.refresh()
            }
            "cfg" -> runCatching {
                val c = JSONObject(data); fillCfg(c); Stats.setFirmware(this, c.optString("ver"))
                // La placa habla el idioma de la app (registro, avisos y web). Solo si su firmware lo admite (trae "lang")
                if (c.has("lang") && c.optString("lang") != lang) link.send("set lang=$lang")
                // Primer uso: la Wi-Fi de la placa sigue con la clave de fábrica (pública). Se pide una vez por sesión
                if (c.optBoolean("apdef") && !apAsked && !link.demo) { apAsked = true; askApPass() }
            }
            "sched" -> parseSched(data)
            "setsched" -> { if (err) toast(msg) else { progsDirty = false; bSaveProgs.text = getString(R.string.saved); refreshSaveBtn() } }
            "errors" -> tDiag.text = formatErrors(data)
            "log" -> tDiag.text = if (data.isBlank()) getString(R.string.log_empty) else data
            "set" -> when {
                err -> toast(msg)
                data.startsWith("restart") -> needRestart = true
            }
            "wifi" -> toast(getString(R.string.wifi_on))
            "scan" -> scanResult(data)
            "dep" -> { if (err) toast(msg); link.refresh() }
            "tgtest" -> toast(if (err) msg else getString(R.string.tg_sent))
            "gasreset" -> { toast(getString(R.string.gas_zeroed)); link.refresh() }
            "forget" -> toast(getString(R.string.forgot_bonds))
            "reboot" -> toast(if (err) msg else getString(R.string.rebooting))
            "time" -> {}
            // Actualización por internet: «busy» al empezar; luego llega el resultado (ok o err) cuando termina
            "update" -> when {
                err -> { tUpd.text = msg; toast(msg) }
                data == "busy" -> { tUpd.visibility = View.VISIBLE; tUpd.text = getString(R.string.upd_started) }
                else -> { tUpd.text = data; toast(data) }
            }
            else -> if (err) toast(msg)
        }
    }

    // ---------- pintado del estado ----------
    // Pinta el estado: temperatura, estado real, tiempo restante, datos, gasoil, avisos y botón grande.
    // También detecta cambios para los avisos del sistema y para los contadores de las estadísticas.
    private fun render() {
        val j = dev ?: return
        val on = j.optInt("on") == 1
        val ph = j.optInt("ph")
        val t = j.optInt("t", -999)
        tTemp.text = if (t > -100) "$t°" else "--°"
        val tg = j.optInt("tg")                        // termostato en marcha (objetivo; 0 = no)
        val wa = j.optInt("wa") == 1                   // el agua ya llegó a la temperatura del aviso
        tPhase.text = if (on) phases.getOrElse(ph) { phases[2] } else if (tg > 0) getString(R.string.waiting) else phases[0]
        tPhase.setTextColor(if (on) cFl else cInk)
        val rem = j.optInt("rem")
        val warm = if (wa) getString(R.string.warm_ok) else ""
        tRem.text = if (tg > 0) getString(R.string.tgt_status, "$tg °C", fmtRem(j.optInt("tu"))) + warm
            else if (on) (if (ph == 3) getString(R.string.hot_water) + " " else "") + getString(R.string.remaining, fmtRem(rem)) +
            (if (j.optString("src") == "programa") " " + getString(R.string.by_schedule) else "") + warm else ""
        // Temperatura de dentro (solo con termómetro: "ct" en décimas de grado, -999 = sin él)
        val ct = j.optInt("ct", -999)
        val hasT = ct > -900
        if (hasT != hasSensor) { hasSensor = hasT; drawProgs() }
        tCabin.visibility = if (hasT) View.VISIBLE else View.GONE
        tgtRow.visibility = if (hasT) View.VISIBLE else View.GONE
        if (hasT) {
            val ch = j.optInt("ch", -1)
            tCabin.text = getString(R.string.cabin, String.format(numLocale, "%.1f °C", ct / 10.0)) + (if (ch >= 0) getString(R.string.cabin_hum, ch) else "")
        } else if (tgt != 0) { tgt = 0; spTgt.setSelection(0); drawSeg() }
        // Salida suelta programada
        val dp = j.optLong("dp")
        tDep.text = if (dp > 0) depText(dp, j.optInt("dt"), j.optLong("time")) else getString(R.string.dep_help)
        bDep.text = getString(if (dp > 0) R.string.dep_cancel else R.string.dep_set)
        val v = j.optDouble("v", -1.0)
        val fl = j.optInt("fl", -1)
        val pw = j.optInt("pw", -1)
        tStats.text = getString(R.string.battery) + " ${if (v > 0) String.format(numLocale, "%.1f V", v) else "--"}   ·   " +
            getString(R.string.flame) + " ${if (fl < 0) "--" else getString(if (fl > 0) R.string.yes else R.string.no)}   ·   " +
            getString(R.string.power) + " ${if (pw < 0) "--" else "$pw W"}"
        j.optJSONArray("gas")?.let { g ->
            fun l(i: Int): String { val v = g.optDouble(i, 0.0); return (if (v < 10) String.format(numLocale, "%.2f", v) else String.format(numLocale, "%.1f", v)) + " l" }
            tGas.text = if (on) getString(R.string.gas_on, l(0), l(2), l(3)) else getString(R.string.gas_off, l(1), l(2), l(3))
        }
        val op = j.optInt("op", -1)
        if (op >= 0) { tUpd.visibility = View.VISIBLE; tUpd.text = getString(R.string.upd_progress, op); pUpd.visibility = View.VISIBLE; pUpd.progress = op }
        else pUpd.visibility = View.GONE
        val note = j.optString("note")
        val warn = mutableListOf<String>()
        if (!on && note.isNotEmpty()) warn += note
        if (j.optInt("bus") == 0) warn += getString(R.string.warn_bus)
        if (j.optInt("tv") == 0) warn += getString(R.string.warn_clock)
        tNote.text = warn.joinToString("\n\n")
        tNote.visibility = if (warn.isEmpty()) View.GONE else View.VISIBLE
        val busy = on || tg > 0                       // con termostato en espera, el botón también lo termina
        bigBtn.text = if (busy) getString(R.string.btn_turn_off)
            else if (tgt > 0) getString(R.string.btn_turn_on_tgt, "$tgt °C", fmtDur(dur)) else getString(R.string.btn_turn_on, fmtDur(dur))
        bigBtn.background = rounded(if (busy) cSf2 else cFl, 16)
        bigBtn.setTextColor(if (busy) cInk else Color.parseColor("#1A1000"))

        // Avisos del sistema (con la app abierta o en segundo plano reciente) y contadores de las estadísticas
        if (haveState && !link.demo) {                 // en modo demostración no hay avisos ni estadísticas
            if (lastOn && !on && note.isNotEmpty() && note != lastNote) {
                notifyUser(getString(R.string.notif_self_off), note)
                Stats.count(this, "self_stops")
                Stats.countErrors(this, Regex("0x([0-9A-F]{2})").findAll(note).map { it.groupValues[1] }.toSet())
            }
            if (!lastOn && on && j.optString("src") == "programa") Stats.count(this, "starts_prog")
            if (on && ph == 4 && lastOn) notifyUser(getString(R.string.notif_no_answer), getString(R.string.notif_no_answer_body))
            if (wa && !lastWa) notifyUser(getString(R.string.notif_warm), getString(R.string.notif_warm_body, j.optInt("t")))
        }
        lastWa = wa
        // La placa ha visto una versión nueva (búsqueda automática): se pregunta una vez por versión
        val nv = j.optString("nv")
        if (nv.isNotEmpty() && nv != nvAsked && !link.demo && j.optInt("op", -1) < 0) {
            nvAsked = nv
            AlertDialog.Builder(this)
                .setTitle(getString(R.string.upd_new_title, nv))
                .setMessage(getString(R.string.board_nv_msg, nv, fwVer.ifEmpty { "?" }))
                .setPositiveButton(getString(R.string.upd_yes)) { _, _ -> link.send("update") }
                .setNegativeButton(getString(R.string.later), null)
                .show()
        }
        haveState = true
        lastOn = on
        lastNote = note
    }

    // ---------- interfaz ----------
    // ---- ayudantes para construir la interfaz por código ----
    // dp -> píxeles de esta pantalla
    private fun dp(v: Int) = (v * resources.displayMetrics.density).toInt()

    // Fondo de color con esquinas redondeadas
    private fun rounded(color: Int, radiusDp: Int) = GradientDrawable().apply {
        setColor(color); cornerRadius = dp(radiusDp).toFloat()
    }

    // Texto con tamaño, color y negrita opcionales
    private fun text(s: String = "", size: Float = 15f, color: Int = cInk, bold: Boolean = false) = TextView(this).apply {
        text = s; textSize = size; setTextColor(color)
        if (bold) setTypeface(typeface, Typeface.BOLD)
    }

    // Botón: principal (azul) o normal (gris), con su acción
    private fun button(s: String, primary: Boolean = false, onClick: () -> Unit) = Button(this).apply {
        text = s; isAllCaps = false; textSize = 15f
        setTextColor(if (primary) Color.parseColor("#04121C") else cInk)
        background = rounded(if (primary) cIce else cSf2, 12)
        setPadding(dp(14), dp(12), dp(14), dp(12))
        setOnClickListener { onClick() }
    }

    // Parámetros de colocación en una columna: ancho, margen superior y peso
    private fun lp(w: Int = ViewGroup.LayoutParams.MATCH_PARENT, top: Int = 0, weight: Float = 0f) =
        LinearLayout.LayoutParams(w, ViewGroup.LayoutParams.WRAP_CONTENT, weight).apply { topMargin = dp(top) }

    // Fila de vistas que se reparten el ancho a partes iguales
    private fun row(vararg views: View) = LinearLayout(this).apply {
        orientation = LinearLayout.HORIZONTAL
        views.forEachIndexed { i, v -> addView(v, LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f).apply { if (i > 0) leftMargin = dp(10) }) }
    }

    // Tarjeta: caja con fondo y esquinas redondeadas
    private fun card(): LinearLayout = LinearLayout(this).apply {
        orientation = LinearLayout.VERTICAL
        background = rounded(cSf, 14)
        setPadding(dp(14), dp(14), dp(14), dp(14))
    }

    // Campo de texto con su etiqueta encima; devuelve (caja, campo)
    private fun field(label: String, type: Int = InputType.TYPE_CLASS_TEXT, hint: String = ""): Pair<View, EditText> {
        val box = LinearLayout(this).apply { orientation = LinearLayout.VERTICAL }
        box.addView(text(label, 13f, cMut), lp(top = 10))
        val e = EditText(this).apply {
            inputType = type; setTextColor(cInk); setHintTextColor(cMut); this.hint = hint; textSize = 16f
            background = rounded(cSf2, 10); setPadding(dp(10), dp(10), dp(10), dp(10)); isSingleLine = true
        }
        box.addView(e, lp(top = 4))
        return box to e
    }

    // Título de sección (y subtítulo opcional)
    private fun section(parent: LinearLayout, title: String, sub: String = "") {
        parent.addView(text(title, 20f, cInk, true), lp(top = 30))
        if (sub.isNotEmpty()) parent.addView(text(sub, 14f, cMut), lp(top = 2))
    }

    // Activa o desactiva una vista y todas las de dentro (atenuadas si están desactivadas)
    private fun setEnabledDeep(v: View, en: Boolean) {
        v.isEnabled = en
        v.alpha = if (en) 1f else 0.55f
        if (v is ViewGroup) for (i in 0 until v.childCount) { val c = v.getChildAt(i); c.isEnabled = en; if (c is ViewGroup) setEnabledDeep(c, en) }
    }

    // Construye toda la interfaz (una vez, al crear la pantalla)
    private fun buildUi() {
        val scroll = ScrollView(this).apply { setBackgroundColor(cBg); isFillViewport = true }
        val frame = FrameLayout(this)
        val root = LinearLayout(this).apply { orientation = LinearLayout.VERTICAL; setPadding(dp(16), dp(14), dp(16), dp(28)) }
        rootCol = root
        // Una columna de 640 dp como mucho; en pantallas anchas, dos (ver arrange())
        frame.addView(root, FrameLayout.LayoutParams(
            minOf(resources.displayMetrics.widthPixels, dp(640)), ViewGroup.LayoutParams.WRAP_CONTENT, Gravity.CENTER_HORIZONTAL))
        scroll.addView(frame)
        setContentView(scroll)

        // Cabecera
        dot = View(this).apply { background = GradientDrawable().apply { shape = GradientDrawable.OVAL; setColor(cMut) } }
        tLink = text("", 14f, cMut)
        val head = LinearLayout(this).apply {
            orientation = LinearLayout.HORIZONTAL; gravity = Gravity.CENTER_VERTICAL
            addView(text("WTTC", 20f, cInk, true))
            addView(View(this@MainActivity), LinearLayout.LayoutParams(dp(12), 1))
            addView(dot, LinearLayout.LayoutParams(dp(9), dp(9)).apply { rightMargin = dp(7) })
            addView(tLink, LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f))
        }
        root.addView(head)

        // Emparejar
        pairBox = card()
        tPair = text("", 14f, cMut)
        pairBox.addView(tPair)
        pairBox.addView(row(button(getString(R.string.btn_scan), true) { scanDialog() }, button(getString(R.string.btn_enable_bt)) { enableBluetooth() }), lp(top = 12))
        // Probar la app sin tener nada montado: placa simulada
        pairBox.addView(button(getString(R.string.btn_demo)) { demoMode = true; demoHeating = false; startLink() }, lp(top = 10))
        root.addView(pairBox, lp(top = 14))

        // Franja del modo demostración
        demoBar = card().apply {
            background = rounded(Color.parseColor("#3A2A0A"), 14)
            addView(text(getString(R.string.demo_bar), 14f, cInk))
            addView(button(getString(R.string.btn_exit_demo)) { exitDemo() }, lp(top = 10))
            visibility = View.GONE
        }
        root.addView(demoBar, lp(top = 14))

        controls = LinearLayout(this).apply { orientation = LinearLayout.VERTICAL }
        root.addView(controls)

        // Estado
        val st = card().apply { gravity = Gravity.CENTER_HORIZONTAL }
        tTemp = text("--°", 64f, cInk).apply { gravity = Gravity.CENTER; typeface = Typeface.create("sans-serif-light", Typeface.NORMAL) }
        st.addView(tTemp, lp())
        st.addView(text(getString(R.string.water), 13f, cMut).apply { gravity = Gravity.CENTER }, lp())
        tPhase = text("—", 22f, cInk, true).apply { gravity = Gravity.CENTER }
        st.addView(tPhase, lp(top = 10))
        tRem = text("", 14f, cMut).apply { gravity = Gravity.CENTER }
        st.addView(tRem, lp(top = 2))
        tStats = text("", 14f, cMut).apply { gravity = Gravity.CENTER }
        st.addView(tStats, lp(top = 12))
        tGas = text("", 13f, cMut).apply { gravity = Gravity.CENTER }
        st.addView(tGas, lp(top = 6))
        tCabin = text("", 17f, cInk).apply { gravity = Gravity.CENTER; visibility = View.GONE }
        st.addView(tCabin, lp(top = 10))
        controls.addView(st, lp(top = 14))

        tNote = text("", 14f, cInk).apply {
            background = rounded(cSf, 8); setPadding(dp(12), dp(10), dp(12), dp(10)); visibility = View.GONE
        }
        controls.addView(tNote, lp(top = 12))

        segRow = LinearLayout(this).apply { orientation = LinearLayout.HORIZONTAL; background = rounded(cSf, 12); setPadding(dp(4), dp(4), dp(4), dp(4)) }
        controls.addView(segRow, lp(top = 12))
        // Objetivo de temperatura (solo con termómetro en la placa)
        spTgt = Spinner(this).apply {
            adapter = ArrayAdapter(this@MainActivity, android.R.layout.simple_spinner_dropdown_item,
                listOf(getString(R.string.tgt_none)) + targets.map { "$it °C" })
            background = rounded(cSf2, 8)
            onItemSelectedListener = object : android.widget.AdapterView.OnItemSelectedListener {
                override fun onItemSelected(parent: android.widget.AdapterView<*>?, view: View?, pos: Int, id: Long) {
                    val t = if (pos == 0) 0 else targets[pos - 1]
                    if (t != tgt) { tgt = t; drawSeg(); render() }
                }
                override fun onNothingSelected(parent: android.widget.AdapterView<*>?) {}
            }
        }
        tgtRow = LinearLayout(this).apply {
            orientation = LinearLayout.HORIZONTAL; gravity = Gravity.CENTER_VERTICAL; background = rounded(cSf, 12)
            setPadding(dp(12), dp(8), dp(12), dp(8)); visibility = View.GONE
            addView(text(getString(R.string.tgt_label), 15f, cInk), LinearLayout.LayoutParams(ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT))
            addView(spTgt, LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f).apply { leftMargin = dp(12) })
        }
        controls.addView(tgtRow, lp(top = 12))
        drawSeg()
        bigBtn = button(getString(R.string.btn_no_link)) { toggleHeater() }.apply { textSize = 18f; setPadding(dp(16), dp(18), dp(16), dp(18)) }
        controls.addView(bigBtn, lp(top = 12))
        // Hora de salida suelta: la placa decide cuánto antes encender según la temperatura
        bDepTime = button(getString(R.string.dep_label) + " " + hm(depMin)) {
            TimePickerDialog(this, { _, h, m -> depMin = h * 60 + m; bDepTime.text = getString(R.string.dep_label) + " " + hm(depMin) }, depMin / 60, depMin % 60, true).show()
        }
        bDep = button(getString(R.string.dep_set)) {
            if ((dev?.optLong("dp") ?: 0L) > 0) link.send("dep off") else link.send("dep ${hm(depMin)}" + (if (tgt > 0) " $tgt" else ""))
        }
        tDep = text("", 13f, cMut)
        controls.addView(card().apply { addView(row(bDepTime, bDep)); addView(tDep, lp(top = 8)) }, lp(top = 12))

        // Programas
        splitAt = controls.childCount                  // de aquí en adelante, la columna derecha en pantallas anchas
        section(controls, getString(R.string.sec_programs), getString(R.string.sec_programs_sub))
        swAuto = Switch(this).apply { text = getString(R.string.programs_active); setTextColor(cInk); textSize = 16f }
        swAuto.setOnCheckedChangeListener { _: CompoundButton, c: Boolean -> if (c != progsAuto) { progsAuto = c; touchProgs() } }
        controls.addView(card().apply { addView(swAuto) }, lp(top = 10))
        progList = LinearLayout(this).apply { orientation = LinearLayout.VERTICAL }
        controls.addView(progList)
        bSaveProgs = button(getString(R.string.btn_save_programs), true) { saveProgs() }
        controls.addView(row(button(getString(R.string.btn_add_program)) { addProg() }, bSaveProgs), lp(top = 10))
        drawProgs()

        // Diagnóstico
        section(controls, getString(R.string.sec_diag))
        tDiag = text("", 13f, cMut).apply { setTextIsSelectable(true) }
        controls.addView(row(button(getString(R.string.btn_read_faults)) { tDiag.text = getString(R.string.reading); link.send("errors") },
            button(getString(R.string.btn_view_log)) { tDiag.text = getString(R.string.reading); link.send("log") }), lp(top = 10))
        controls.addView(button(getString(R.string.btn_gas_reset)) { confirmGasReset() }, lp(top = 10))
        controls.addView(tDiag, lp(top = 10))

        // Configuración
        section(controls, getString(R.string.sec_config), getString(R.string.sec_config_sub))
        val cfg = card()
        cfg.addView(text(getString(R.string.cfg_own), 15f, cInk, true))
        field(getString(R.string.f_name)).let { cfg.addView(it.first); eName = it.second }
        field(getString(R.string.f_pin), InputType.TYPE_CLASS_NUMBER).let { cfg.addView(it.first); ePin = it.second }
        field(getString(R.string.f_ap), InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD, getString(R.string.hint_unchanged)).let { cfg.addView(it.first); eAp = it.second }
        cfg.addView(text(getString(R.string.f_wm), 13f, cMut), lp(top = 10))
        spWm = Spinner(this).apply {
            adapter = ArrayAdapter(this@MainActivity, android.R.layout.simple_spinner_dropdown_item,
                resources.getStringArray(R.array.wifi_modes).toList())
            background = rounded(cSf2, 10)
        }
        cfg.addView(spWm, lp(top = 4))
        cfg.addView(text(getString(R.string.cfg_inet), 15f, cInk, true), lp(top = 18))
        field(getString(R.string.f_ssid)).let { cfg.addView(it.first); eSsid = it.second }
        // Buscar redes cercanas: la placa busca y se elige una de la lista (rellena el nombre)
        cfg.addView(button(getString(R.string.scan_btn)) { scanNets() }, lp(top = 8))
        field(getString(R.string.f_pass), InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD, getString(R.string.hint_unchanged)).let { cfg.addView(it.first); ePass = it.second }
        cfg.addView(text(getString(R.string.cfg_tg), 15f, cInk, true), lp(top = 18))
        field(getString(R.string.f_tok), InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD, getString(R.string.hint_unchanged)).let { cfg.addView(it.first); eTok = it.second }
        field(getString(R.string.f_chat), InputType.TYPE_CLASS_TEXT).let { cfg.addView(it.first); eChat = it.second }
        cfg.addView(text(getString(R.string.cfg_safety), 15f, cInk, true), lp(top = 18))
        field(getString(R.string.f_minv), InputType.TYPE_CLASS_NUMBER or InputType.TYPE_NUMBER_FLAG_DECIMAL).let { cfg.addView(it.first); eMinV = it.second }
        hwBox = LinearLayout(this).apply { orientation = LinearLayout.VERTICAL; visibility = View.GONE }
        hwBox.addView(text(getString(R.string.minv_help), 13f, cMut), lp(top = 4))
        field(getString(R.string.f_warm), InputType.TYPE_CLASS_NUMBER).let { hwBox.addView(it.first); eWarm = it.second }
        hwBox.addView(text(getString(R.string.cfg_hw), 15f, cInk, true), lp(top = 18))
        tHw = text("", 13f, cMut)
        hwBox.addView(tHw, lp(top = 4))
        // Cada ajuste va en su caja (en tag) para ocultarlo si su pieza no está conectada
        fun spinner(label: Int, items: Int): Spinner {
            val box = LinearLayout(this).apply { orientation = LinearLayout.VERTICAL }
            box.addView(text(getString(label), 13f, cMut), lp(top = 10))
            val sp = Spinner(this).apply {
                adapter = ArrayAdapter(this@MainActivity, android.R.layout.simple_spinner_dropdown_item, resources.getStringArray(items).toList())
                background = rounded(cSf2, 10)
                tag = box
            }
            box.addView(sp, lp(top = 4))
            hwBox.addView(box)
            return sp
        }
        spOled = spinner(R.string.f_oled, R.array.oled_types)
        spDisp = spinner(R.string.f_disp, R.array.disp_modes)
        spLed = spinner(R.string.f_led, R.array.led_levels)
        field(getString(R.string.f_toff), InputType.TYPE_CLASS_NUMBER or InputType.TYPE_NUMBER_FLAG_DECIMAL or InputType.TYPE_NUMBER_FLAG_SIGNED).let { hwBox.addView(it.first); eToff = it.second; eToff.tag = it.first }
        cfg.addView(hwBox)
        cfg.addView(row(button(getString(R.string.btn_save), true) { saveCfg() }, button(getString(R.string.btn_tg_test)) { link.send("tgtest") }), lp(top = 14))
        cfg.addView(row(button(getString(R.string.btn_wifi15)) { link.send("wifi") }, button(getString(R.string.btn_reboot)) { confirmReboot() }), lp(top = 10))
        // Actualizaciones del firmware: la app consulta la última versión y, si hay una nueva, la placa la descarga e instala
        cfg.addView(button(getString(R.string.upd_check)) { checkUpdates() }, lp(top = 10))
        val otaAutoBox = LinearLayout(this).apply { orientation = LinearLayout.VERTICAL; visibility = View.GONE }
        otaAutoBox.addView(text(getString(R.string.f_otaauto), 13f, cMut), lp(top = 10))
        spOtaAuto = Spinner(this).apply {
            adapter = ArrayAdapter(this@MainActivity, android.R.layout.simple_spinner_dropdown_item, resources.getStringArray(R.array.ota_auto_modes).toList())
            background = rounded(cSf2, 10); tag = otaAutoBox
        }
        otaAutoBox.addView(spOtaAuto, lp(top = 4))
        cfg.addView(otaAutoBox)
        pUpd = android.widget.ProgressBar(this, null, android.R.attr.progressBarStyleHorizontal).apply {
            max = 100; visibility = View.GONE
            progressTintList = android.content.res.ColorStateList.valueOf(cFl)
        }
        cfg.addView(pUpd, lp(top = 8))
        tUpd = text("", 13f, cMut).apply { visibility = View.GONE }
        cfg.addView(tUpd, lp(top = 8))
        tCfg = text("", 13f, cMut)
        cfg.addView(tCfg, lp(top = 10))
        controls.addView(cfg, lp(top = 10))

        // Dispositivo
        section(root, getString(R.string.sec_app), getString(R.string.sec_app_sub))
        root.addView(row(button(getString(R.string.btn_forget)) { confirmForget() }, button(getString(R.string.btn_bt_settings)) {
            startActivity(Intent(Settings.ACTION_BLUETOOTH_SETTINGS))
        }), lp(top = 10))
        swStats = Switch(this).apply {
            text = getString(R.string.stats_switch); setTextColor(cInk); textSize = 15f
            isChecked = Stats.consent(this@MainActivity) == true
        }
        swStats.setOnCheckedChangeListener { _: CompoundButton, c: Boolean -> Stats.setConsent(this, c); if (c) Stats.maybeSend(this) }
        root.addView(card().apply {
            addView(swStats)
            addView(text(getString(R.string.stats_help), 13f, cMut), lp(top = 6))
            addView(button(getString(R.string.btn_public_stats)) { startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(Stats.URL_PUBLIC))) }, lp(top = 10))
        }, lp(top = 10))
        // Versión nueva de la app: aviso automático (interruptor) y búsqueda a mano
        val swAppUpd = Switch(this).apply {
            text = getString(R.string.app_upd_auto); setTextColor(cInk); textSize = 15f
            isChecked = getSharedPreferences("wttc", MODE_PRIVATE).getBoolean("app_upd_auto", true)
        }
        swAppUpd.setOnCheckedChangeListener { _: CompoundButton, c: Boolean -> getSharedPreferences("wttc", MODE_PRIVATE).edit().putBoolean("app_upd_auto", c).apply() }
        root.addView(card().apply {
            addView(swAppUpd)
            addView(text(getString(R.string.app_upd_help), 13f, cMut), lp(top = 6))
            addView(button(getString(R.string.app_upd_btn)) { checkAppUpdate(silent = false) }, lp(top = 10))
        }, lp(top = 10))
        // Acceso rápido (ajustes rápidos de Android y widget): duración con la que enciende
        val qPrefs = getSharedPreferences("wttc", MODE_PRIVATE)
        val spQuick = Spinner(this).apply {
            adapter = ArrayAdapter(this@MainActivity, android.R.layout.simple_spinner_dropdown_item, durations.map { fmtDur(it) })
            background = rounded(cSf2, 10)
            setSelection(durations.indexOf(qPrefs.getInt("qdur", 30)).coerceAtLeast(0))
            onItemSelectedListener = object : android.widget.AdapterView.OnItemSelectedListener {
                override fun onItemSelected(parent: android.widget.AdapterView<*>?, view: View?, pos: Int, id: Long) {
                    qPrefs.edit().putInt("qdur", durations[pos]).apply()
                }
                override fun onNothingSelected(parent: android.widget.AdapterView<*>?) {}
            }
        }
        root.addView(card().apply {
            addView(text(getString(R.string.quick_title), 15f, cInk, true))
            addView(text(getString(R.string.quick_help), 13f, cMut), lp(top = 6))
            addView(text(getString(R.string.quick_dur), 13f, cMut), lp(top = 10))
            addView(spQuick, lp(top = 4))
        }, lp(top = 10))
        val ver = runCatching { packageManager.getPackageInfo(packageName, 0).versionName }.getOrNull() ?: "?"
        root.addView(text(getString(R.string.footer, ver), 13f, cMut).apply {
            setOnClickListener { startActivity(Intent(Intent.ACTION_VIEW, Uri.parse("https://wttc.favala.es"))) }
        }, lp(top = 20))

        setEnabledDeep(controls, false)
        arrange()
    }

    // Pinta los botones de duración, con la elegida resaltada
    private fun drawSeg() {
        segRow.removeAllViews()
        val list = if (tgt > 0) tDurations else durations
        if (dur !in list) dur = if (tgt > 0) 120 else 30
        for (d in list) {
            val b = Button(this).apply {
                text = fmtDur(d); isAllCaps = false; textSize = 15f
                setTextColor(if (d == dur) cInk else cMut)
                background = rounded(if (d == dur) cSf2 else cSf, 9)
                setOnClickListener { dur = d; drawSeg(); render() }
            }
            segRow.addView(b, LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f).apply { if (segRow.childCount > 0) leftMargin = dp(4) })
        }
    }

    // ---------- acciones ----------
    // Botón grande: apaga si está encendida; si no, enciende con la duración elegida
    // Sale del modo demostración y vuelve a la placa real (o a la pantalla de emparejar)
    private fun exitDemo() {
        demoMode = false; demoHeating = false
        link.stop()
        dev = null; haveState = false
        if (askPermissions()) startLink()
    }

    private fun toggleHeater() {
        val on = dev?.optInt("on") == 1 || (dev?.optInt("tg") ?: 0) > 0
        bigBtn.text = getString(if (on) R.string.btn_turning_off else R.string.btn_turning_on)
        link.send(if (on) "off" else if (tgt > 0) "on $dur $tgt" else "on $dur")
    }

    // «Salida: mañana a las 08:00 (hasta 20 °C)…». dp y now en segundos desde 1970
    private fun depText(dp: Long, dt: Int, now: Long): String {
        val c = java.util.Calendar.getInstance().apply { timeInMillis = dp * 1000 }
        val n = java.util.Calendar.getInstance().apply { timeInMillis = (if (now > 0) now else System.currentTimeMillis() / 1000) * 1000 }
        val day = when (c.get(java.util.Calendar.DAY_OF_YEAR) - n.get(java.util.Calendar.DAY_OF_YEAR)) {
            0 -> getString(R.string.today); 1, -364, -365 -> getString(R.string.tomorrow)
            else -> java.text.SimpleDateFormat("EEEE", numLocale).format(c.time)
        }
        return getString(R.string.dep_status, day, hm(c.get(java.util.Calendar.HOUR_OF_DAY) * 60 + c.get(java.util.Calendar.MINUTE)),
            if (dt > 0) getString(R.string.dep_tgt, "$dt °C") else "")
    }

    // Pide a Android que active el Bluetooth (si ya está activo, conecta)
    private fun enableBluetooth() {
        if (link.bluetoothOn()) { startLink(); return }
        @Suppress("DEPRECATION")
        startActivityForResult(Intent(BluetoothAdapter.ACTION_REQUEST_ENABLE), 2)
    }

    @Deprecated("Activity sin AndroidX")
    // Vuelta del diálogo de activar el Bluetooth
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        @Suppress("DEPRECATION") super.onActivityResult(requestCode, resultCode, data)
        if (requestCode == 2) startLink()
    }

    // Diálogo de búsqueda: lista las placas WTTC que aparecen (15 s); al elegir una, se empareja
    private fun scanDialog() {
        if (!askPermissions()) return
        if (!link.bluetoothOn()) { enableBluetooth(); return }
        val found = mutableListOf<Pair<BluetoothDevice, String>>()
        val names = ArrayAdapter<String>(this, android.R.layout.simple_list_item_1)
        val dlg = AlertDialog.Builder(this)
            .setTitle(getString(R.string.scan_title))
            .setAdapter(names) { _, i -> val (d, n) = found[i]; link.choose(d, n) }
            .setNegativeButton(getString(R.string.cancel)) { _, _ -> link.stopScan(); link.start() }
            .create()
        dlg.show()
        link.scan { d, n ->
            if (found.none { it.first.address == d.address }) {
                found += d to n
                names.add("$n  (${d.address})")
            }
        }
    }

    /** Primer inicio: estadísticas anónimas. Dos botones iguales y nada marcado de antemano. */
    private fun askStats() {
        AlertDialog.Builder(this)
            .setTitle(getString(R.string.stats_title))
            .setMessage(getString(R.string.stats_msg))
            .setCancelable(false)
            .setPositiveButton(getString(R.string.stats_yes)) { _, _ -> swStats.isChecked = true }      // el interruptor guarda el permiso y envía
            .setNegativeButton(getString(R.string.stats_no)) { _, _ -> Stats.setConsent(this, false); swStats.isChecked = false }
            .show()
    }

    // La Wi-Fi de la placa con la clave de fábrica: pedir una nueva (se aplica al reiniciar la placa)
    private var apAsked = false
    private fun askApPass() {
        val e = EditText(this).apply {
            inputType = InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD
            hint = getString(R.string.ap_hint); setTextColor(cInk); setHintTextColor(cMut)
        }
        val box = FrameLayout(this).apply { setPadding(dp(20), dp(8), dp(20), 0); addView(e) }
        AlertDialog.Builder(this)
            .setTitle(getString(R.string.ap_title))
            .setMessage(getString(R.string.ap_msg))
            .setView(box)
            .setPositiveButton(getString(R.string.btn_save)) { _, _ ->
                val v = e.text.toString()
                if (v.length < 8) toast(getString(R.string.ap_short)) else { link.send("set appass=$v"); link.send("cfg") }
            }
            .setNegativeButton(getString(R.string.later), null)
            .show()
    }

    // ---------- actualizaciones del firmware ----------
    // Compara versiones «a.b.c»: <0 si a es más antigua que b
    // ---------- versión nueva de la app ----------
    // La app y el firmware llevan el mismo número: se lee de ota.json (el mismo fichero que usa «Buscar actualizaciones»).
    // Antes de avisar se comprueba que el WTTC.apk de esa versión ya está en su Release (tarda unos minutos en salir).
    // silent = la comprobación automática al abrir: una vez al día como mucho, si está activada, y sin avisar si no hay
    // nada nuevo; con «Más tarde» no vuelve a avisar de esa versión. El botón «Buscar versión nueva» la hace siempre.
    private fun checkAppUpdate(silent: Boolean) {
        val prefs = getSharedPreferences("wttc", MODE_PRIVATE)
        if (silent) {
            if (!prefs.getBoolean("app_upd_auto", true)) return
            if (System.currentTimeMillis() - prefs.getLong("app_upd_last", 0) < 24 * 3600_000L) return
            prefs.edit().putLong("app_upd_last", System.currentTimeMillis()).apply()
        }
        val mine = runCatching { packageManager.getPackageInfo(packageName, 0).versionName }.getOrNull() ?: "0.0.0"
        kotlin.concurrent.thread(name = "wttc-app-upd", isDaemon = true) {
            val m = runCatching {
                val con = java.net.URL(OTA_MANIFEST).openConnection() as java.net.HttpURLConnection
                con.connectTimeout = 8000; con.readTimeout = 8000
                JSONObject(con.inputStream.bufferedReader().use { it.readText() }).also { con.disconnect() }
            }.getOrNull()
            val v = m?.optString("version").orEmpty()
            val url = "https://github.com/matatunos/wttc/releases/download/v$v/WTTC.apk"
            val newer = v.isNotEmpty() && verCmp(v, mine) > 0
            // ¿Ya está publicado el APK? (HEAD; GitHub redirige a su almacén: 200 si existe)
            val ready = newer && runCatching {
                val con = java.net.URL(url).openConnection() as java.net.HttpURLConnection
                con.requestMethod = "HEAD"; con.connectTimeout = 8000; con.readTimeout = 8000
                (con.responseCode == 200).also { con.disconnect() }
            }.getOrDefault(false)
            runOnUiThread {
                when {
                    m == null -> if (!silent) toast(getString(R.string.upd_no_net))
                    !newer -> if (!silent) toast(getString(R.string.app_upd_latest, mine))
                    !ready -> if (!silent) toast(getString(R.string.app_upd_notyet, v))
                    silent && prefs.getString("app_upd_skip", "") == v -> {}
                    else -> AlertDialog.Builder(this)
                        .setTitle(getString(R.string.app_upd_title, v))
                        .setMessage(getString(R.string.app_upd_msg, mine, m?.optString("notas").orEmpty().ifEmpty { "—" }))
                        .setPositiveButton(getString(R.string.app_upd_dl)) { _, _ -> startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url))) }
                        .setNegativeButton(getString(R.string.later)) { _, _ -> prefs.edit().putString("app_upd_skip", v).apply() }
                        .show()
                }
            }
        }
    }

    private fun verCmp(a: String, b: String): Int {
        val x = a.split('.').map { it.toIntOrNull() ?: 0 }; val y = b.split('.').map { it.toIntOrNull() ?: 0 }
        for (i in 0 until 3) { val d = x.getOrElse(i) { 0 } - y.getOrElse(i) { 0 }; if (d != 0) return d }
        return 0
    }

    // «Buscar actualizaciones»: la app consulta la última versión (ota.json, en la web del proyecto) y, si hay una
    // nueva, pregunta; si se acepta, la placa la descarga, comprueba la firma, la instala y se reinicia (orden «update»)
    private fun checkUpdates() {
        if (link.demo) { toast(getString(R.string.upd_demo)); return }
        if (fwVer.isEmpty()) return
        if (!fwOta) { AlertDialog.Builder(this).setMessage(getString(R.string.upd_usb)).setPositiveButton(android.R.string.ok, null).show(); return }
        tUpd.visibility = View.VISIBLE; tUpd.text = getString(R.string.upd_checking)
        kotlin.concurrent.thread(name = "wttc-ota", isDaemon = true) {
            val m = runCatching {
                val con = java.net.URL(OTA_MANIFEST).openConnection() as java.net.HttpURLConnection
                con.connectTimeout = 8000; con.readTimeout = 8000
                JSONObject(con.inputStream.bufferedReader().use { it.readText() }).also { con.disconnect() }
            }.getOrNull()
            runOnUiThread {
                if (m == null) { tUpd.text = getString(R.string.upd_no_net); return@runOnUiThread }
                val v = m.optString("version")
                if (verCmp(v, fwVer) <= 0) { tUpd.text = getString(R.string.upd_latest, fwVer); return@runOnUiThread }
                tUpd.text = getString(R.string.upd_new_title, v)
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.upd_new_title, v))
                    .setMessage(getString(R.string.upd_new_msg, fwVer, m.optString("notas").ifEmpty { "—" }))
                    .setPositiveButton(getString(R.string.upd_yes)) { _, _ -> link.send("update") }
                    .setNegativeButton(getString(R.string.later), null)
                    .show()
            }
        }
    }

    // Confirmación antes de poner a cero el gasoil estimado
    private fun confirmGasReset() {
        AlertDialog.Builder(this)
            .setTitle(getString(R.string.gas_title))
            .setMessage(getString(R.string.gas_msg))
            .setPositiveButton(getString(R.string.gas_ok)) { _, _ -> link.send("gasreset") }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    // Confirmación antes de olvidar la placa en la app
    private fun confirmForget() {
        AlertDialog.Builder(this)
            .setTitle(getString(R.string.forget_title))
            .setMessage(getString(R.string.forget_msg))
            .setPositiveButton(getString(R.string.forget_ok)) { _, _ -> link.forget(); dev = null }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    // Confirmación antes de reiniciar la placa (el firmware se niega si está calentando)
    private fun confirmReboot() {
        AlertDialog.Builder(this)
            .setTitle(getString(R.string.reboot_title))
            .setMessage(getString(R.string.reboot_msg))
            .setPositiveButton(getString(R.string.reboot_ok)) { _, _ -> link.send("reboot"); needRestart = false }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    // ---------- programas ----------
    // Lee los programas que manda la placa ("auto|activo,días,inicio,duración;…"); no pisa cambios sin guardar
    private fun parseSched(s: String) {
        if (progsDirty) return
        val parts = s.split("|", limit = 2)
        progsAuto = parts.getOrNull(0) == "1"
        progs.clear()
        parts.getOrNull(1)?.split(";")?.forEach { it ->
            val p = it.split(",").mapNotNull { x -> x.trim().toIntOrNull() }
            if (p.size == 4 || p.size == 5) progs += Prog(p[0] == 1, p[1], p[2], p[3], p.getOrElse(4) { 0 })
        }
        swAuto.isChecked = progsAuto
        drawProgs()
    }

    // Marca que hay cambios en los programas pendientes de guardar
    private fun touchProgs() {
        progsDirty = true
        bSaveProgs.text = getString(R.string.btn_save_programs)
        refreshSaveBtn()
    }

    // El botón de guardar se atenúa si no hay nada que guardar
    private fun refreshSaveBtn() { bSaveProgs.alpha = if (progsDirty) 1f else 0.6f }

    // Añade un programa: 07:00, 30 min, de lunes a viernes (31 = bits de lunes a viernes)
    private fun addProg() {
        if (progs.size >= 8) { toast(getString(R.string.max_programs)); return }
        progs += Prog(true, 31, 7 * 60, 30)
        touchProgs(); drawProgs()
    }

    // Manda los programas a la placa en el formato de texto del firmware
    private fun saveProgs() {
        val list = progs.joinToString(";") { "${if (it.en) 1 else 0},${it.days},${it.start},${it.dur}" + (if (it.x != 0) ",${it.x}" else "") }
        link.send("setsched ${if (progsAuto) 1 else 0}|$list")
    }

    // Pinta la lista de programas: hora (abre un reloj), duración, activo, borrar y los 7 días
    private fun drawProgs() {
        progList.removeAllViews()
        if (progs.isEmpty()) {
            progList.addView(text(getString(R.string.no_programs), 14f, cMut), lp(top = 10))
            refreshSaveBtn(); return
        }
        progs.forEachIndexed { idx, p ->
            val c = card()
            val time = button(hm(p.start)) {
                TimePickerDialog(this, { _, h, m -> p.start = h * 60 + m; touchProgs(); drawProgs() }, p.start / 60, p.start % 60, true).show()
            }.apply { textSize = 22f; setTypeface(typeface, Typeface.BOLD); minWidth = 0; minimumWidth = 0 }
            // Cerrado, el desplegable usa la plantilla compacta; abierto, la de lista (si no, «30 min» no cabe y sale «30..»)
            // Con objetivo de temperatura la duración es la ventana (hasta 4 h); con hora de salida no se usa
            val durs = if (p.x and 63 > 0) tDurations else durations
            val durSp = Spinner(this).apply {
                adapter = ArrayAdapter(this@MainActivity, android.R.layout.simple_spinner_item, durs.map { fmtDur(it) })
                    .apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                setSelection(durs.indexOfFirst { it >= p.dur }.coerceAtLeast(0))
                background = rounded(cSf2, 8)
                visibility = if (p.x and 128 != 0) View.INVISIBLE else View.VISIBLE
                onItemSelectedListener = object : android.widget.AdapterView.OnItemSelectedListener {
                    override fun onItemSelected(parent: android.widget.AdapterView<*>?, view: View?, pos: Int, id: Long) {
                        if (durs[pos] != p.dur) { p.dur = durs[pos]; touchProgs() }
                    }
                    override fun onNothingSelected(parent: android.widget.AdapterView<*>?) {}
                }
            }
            val sw = Switch(this).apply { isChecked = p.en }
            sw.setOnCheckedChangeListener { _: CompoundButton, v: Boolean -> p.en = v; touchProgs() }
            // Botón de borrar estrecho: los Button de Android tienen 88 dp de ancho mínimo y le quitaban sitio al desplegable
            val del = button("✕") { progs.removeAt(idx); touchProgs(); drawProgs() }
                .apply { minWidth = 0; minimumWidth = 0; setPadding(dp(14), dp(10), dp(14), dp(10)) }
            val top = LinearLayout(this).apply {
                orientation = LinearLayout.HORIZONTAL; gravity = Gravity.CENTER_VERTICAL
                addView(time, LinearLayout.LayoutParams(ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT))
                addView(durSp, LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f).apply { leftMargin = dp(10) })
                addView(sw, LinearLayout.LayoutParams(ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT).apply { leftMargin = dp(10) })
                addView(del, LinearLayout.LayoutParams(ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT).apply { leftMargin = dp(8) })
            }
            c.addView(top)
            val days = LinearLayout(this).apply { orientation = LinearLayout.HORIZONTAL }
            for (d in 0 until 7) {
                val sel = (p.days shr d) and 1 == 1
                val b = Button(this).apply {
                    text = dayLetters[d]; isAllCaps = false; textSize = 14f
                    setTextColor(if (sel) Color.parseColor("#04121C") else cMut)
                    background = rounded(if (sel) cIce else cSf2, 18)
                    setOnClickListener { p.days = p.days xor (1 shl d); touchProgs(); drawProgs() }
                }
                days.addView(b, LinearLayout.LayoutParams(0, dp(40), 1f).apply { if (d > 0) leftMargin = dp(5) })
            }
            c.addView(days, lp(top = 10))
            // Modo (encender a la hora / hora de salida) y objetivo de temperatura: solo con firmware 0.2.0+
            if (fwTh) {
                fun optSpinner(items: List<String>, sel: Int, onSel: (Int) -> Unit) = Spinner(this).apply {
                    adapter = ArrayAdapter(this@MainActivity, android.R.layout.simple_spinner_item, items)
                        .apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                    setSelection(sel)
                    background = rounded(cSf2, 8)
                    onItemSelectedListener = object : android.widget.AdapterView.OnItemSelectedListener {
                        override fun onItemSelected(parent: android.widget.AdapterView<*>?, view: View?, pos: Int, id: Long) { onSel(pos) }
                        override fun onNothingSelected(parent: android.widget.AdapterView<*>?) {}
                    }
                }
                val mode = optSpinner(resources.getStringArray(R.array.prog_modes).toList(), if (p.x and 128 != 0) 1 else 0) { pos ->
                    val x = (p.x and 63) or (if (pos == 1) 128 else 0)
                    if (x != p.x) { p.x = x; touchProgs(); drawProgs() }
                }
                val tgs = listOf(getString(R.string.prog_no_tgt)) + targets.map { getString(R.string.prog_tgt, "$it °C") }
                val tsp = optSpinner(tgs, targets.indexOf(p.x and 63) + 1) { pos ->
                    val x = (p.x and 128) or (if (pos == 0) 0 else targets[pos - 1])
                    if (x != p.x) {
                        p.x = x
                        val l = if (x and 63 > 0) tDurations else durations
                        if (p.dur !in l) p.dur = if (x and 63 > 0) 120 else 30
                        touchProgs(); drawProgs()
                    }
                }
                if (hasSensor || p.x and 63 > 0) c.addView(row(mode, tsp), lp(top = 10)) else c.addView(mode, lp(top = 10))
            }
            progList.addView(c, lp(top = 10))
        }
        refreshSaveBtn()
    }

    // ---------- redes Wi-Fi cercanas ----------
    // La placa busca en segundo plano: responde «run» mientras busca; se le vuelve a preguntar cada 1,5 s (25 s como mucho)
    private val ui = android.os.Handler(android.os.Looper.getMainLooper())
    private var scanTries = 0
    private fun scanNets() {
        scanTries = 0
        toast(getString(R.string.scan_searching))
        link.send("scan")
    }
    private fun scanResult(data: String) {
        if (data == "run") {
            if (++scanTries < 16) ui.postDelayed({ link.send("scan") }, 1500) else toast(getString(R.string.scan_fail))
            return
        }
        val a = runCatching { org.json.JSONArray(data) }.getOrNull() ?: run { toast(getString(R.string.scan_fail)); return }
        if (a.length() == 0) { toast(getString(R.string.scan_none)); return }
        val nets = (0 until a.length()).map { a.getJSONObject(it) }
        fun bars(r: Int) = if (r >= -55) "▂▄▆█" else if (r >= -67) "▂▄▆" else if (r >= -78) "▂▄" else "▂"
        AlertDialog.Builder(this)
            .setTitle(getString(R.string.scan_title))
            .setItems(nets.map { "${bars(it.optInt("r"))}  ${it.optString("s")}" + (if (it.optInt("e") == 1) "  🔒" else "") }.toTypedArray()) { _, i ->
                eSsid.setText(nets[i].optString("s"))
                ePass.requestFocus()
            }
            .setNegativeButton(getString(R.string.cancel), null)
            .show()
    }

    // ---------- configuración ----------
    // Algún ajuste guardado necesita reiniciar la placa para aplicarse: se avisa al usuario
    private var needRestart = false
        set(v) { field = v; if (v) toast(getString(R.string.need_restart)) }

    // Rellena el formulario con la configuración de la placa (las claves nunca vienen: los campos quedan vacíos)
    private fun fillCfg(c: JSONObject) {
        eName.setText(c.optString("name"))
        ePin.setText(c.optString("pin"))
        spWm.setSelection(c.optInt("wifimode", 1).coerceIn(0, 2))
        eSsid.setText(c.optString("ssid"))
        eChat.setText(c.optString("tgchat"))
        eMinV.setText(c.optString("minvolt"))
        eTok.hint = getString(if (c.optBoolean("tg")) R.string.hint_token_saved else R.string.hint_not_set)
        eAp.setText(""); ePass.setText(""); eTok.setText("")
        fwVer = c.optString("ver"); fwOta = c.optInt("ota") == 1
        hasOtaAuto = c.has("otaauto")
        (spOtaAuto.tag as View).visibility = if (hasOtaAuto) View.VISIBLE else View.GONE
        if (hasOtaAuto) spOtaAuto.setSelection(c.optInt("otaauto", 1).coerceIn(0, 2))
        val th = c.optInt("th") == 1
        if (th != fwTh) { fwTh = th; drawProgs() }
        hwBox.visibility = if (fwTh) View.VISIBLE else View.GONE
        if (fwTh) {
            eWarm.setText(c.optInt("warm").toString())
            spOled.setSelection(c.optInt("oled").coerceIn(0, 2))
            spDisp.setSelection(c.optInt("disp", 1).coerceIn(0, 2))
            spLed.setSelection(c.optInt("led", 1).coerceIn(0, 3))
            eToff.setText(c.optString("toff"))
            // Solo los ajustes de lo que está conectado
            val scr = c.optBoolean("scr"); val sens = c.optString("sens").isNotEmpty()
            (spOled.tag as View).visibility = if (scr) View.VISIBLE else View.GONE
            (spDisp.tag as View).visibility = if (scr) View.VISIBLE else View.GONE
            (eToff.tag as View).visibility = if (sens) View.VISIBLE else View.GONE
            val found = listOfNotNull(c.optString("sens").ifEmpty { null }, if (c.optBoolean("scr")) getString(R.string.hw_display) else null)
            tHw.text = if (found.isEmpty()) getString(R.string.hw_none) else getString(R.string.hw_detected, found.joinToString(", "))
        }
        val bonds = c.optInt("bonds")
        tCfg.text = getString(R.string.cfg_info, c.optString("ver"), bonds)
    }

    // Manda cada ajuste con "set clave=valor"; las claves vacías no se envían (la placa conserva las que tenía)
    private fun saveCfg() {
        val sets = mutableListOf(
            "name" to eName.text.toString().trim(),
            "pin" to ePin.text.toString().trim(),
            "wifimode" to spWm.selectedItemPosition.toString(),
            "ssid" to eSsid.text.toString().trim(),
            "tgchat" to eChat.text.toString().trim(),
            "minvolt" to eMinV.text.toString().trim().replace(',', '.'),
        )
        if (hasOtaAuto) sets += "otaauto" to spOtaAuto.selectedItemPosition.toString()
        if (fwTh) {
            sets += "warm" to eWarm.text.toString().trim().ifEmpty { "0" }
            sets += "oled" to spOled.selectedItemPosition.toString()
            sets += "disp" to spDisp.selectedItemPosition.toString()
            sets += "led" to spLed.selectedItemPosition.toString()
            sets += "toff" to eToff.text.toString().trim().replace(',', '.').ifEmpty { "0" }
        }
        if (eAp.text.isNotEmpty()) sets += "appass" to eAp.text.toString()
        if (ePass.text.isNotEmpty()) sets += "pass" to ePass.text.toString()
        if (eTok.text.isNotBlank()) sets += "tgtok" to eTok.text.toString().trim()
        for ((k, v) in sets) link.send("set $k=$v")
        link.send("cfg")
        toast(getString(R.string.cfg_sent))
    }

    // ---------- utilidades ----------
    // ---- utilidades ----
    // Averías en JSON -> una línea por código
    private fun formatErrors(data: String): String = runCatching {
        val j = JSONObject(data)
        if (!j.optBoolean("ok")) return@runCatching getString(R.string.faults_no_answer)
        val codes = j.optJSONArray("codes")
        if (codes == null || codes.length() == 0) getString(R.string.faults_none)
        else (0 until codes.length()).joinToString("\n") { i ->
            val c = codes.getJSONObject(i); getString(R.string.fault_line, c.optString("c"), c.optInt("n"))
        }
    }.getOrDefault(data)

    // Formatos: duración, tiempo restante, hora del día y mensaje corto en pantalla
    private fun fmtDur(d: Int) = if (d < 60) "$d min" else "${d / 60} h" + (if (d % 60 > 0) " ${d % 60}" else "")
    private fun fmtRem(s: Int): String { val m = ceil(s / 60.0).toInt(); return if (m < 60) "$m min" else "${m / 60} h ${"%02d".format(m % 60)} min" }
    private fun hm(m: Int) = "%02d:%02d".format(m / 60, m % 60)
    private fun toast(s: String) = Toast.makeText(this, s, Toast.LENGTH_LONG).show()

    // Canal de notificaciones de Android (obligatorio desde Android 8)
    private fun createChannel() {
        val ch = NotificationChannel("avisos", getString(R.string.channel_name), NotificationManager.IMPORTANCE_HIGH)
        getSystemService(NotificationManager::class.java).createNotificationChannel(ch)
    }

    // Notificación del sistema (si hay permiso)
    private fun notifyUser(title: String, body: String) {
        if (Build.VERSION.SDK_INT >= 33 && checkSelfPermission(Manifest.permission.POST_NOTIFICATIONS) != PackageManager.PERMISSION_GRANTED) return
        val n = android.app.Notification.Builder(this, "avisos")
            .setSmallIcon(android.R.drawable.stat_sys_warning)
            .setContentTitle(title)
            .setContentText(body)
            .setStyle(android.app.Notification.BigTextStyle().bigText(body))
            .setAutoCancel(true)
            .build()
        getSystemService(NotificationManager::class.java).notify(1, n)
    }
}
