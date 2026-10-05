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
 * no hay placa emparejada), estado de la calefacción, duración y botón grande, programas, diagnóstico,
 * configuración de la placa y ajustes de la app.
 *
 * Toda la comunicación con la placa va por [BleLink]; esta clase solo pinta y reacciona:
 *  - onLink(): cambia el estado del enlace (buscando, emparejando, conectado, fuera de alcance…)
 *  - onDeviceState(): llega el estado de la placa (cada 2 s mientras está conectada)
 *  - onResponse(): llega la respuesta a una orden ("orden:datos")
 */
class MainActivity : Activity(), BleLink.Listener {

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

    // Duraciones que se ofrecen (el firmware no admite más de 60 min) y letras de los días (lunes primero)
    private val durations = intArrayOf(15, 30, 45, 60)
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
    // Modo demostración: placa simulada. Se activa con «Probar sin placa» o al abrir la app con el extra
    // "demo" (lo usan las capturas: adb shell am start … --ez demo true --ez demo_on true --ez capturas true)
    private var demoMode = false
    private var demoHeating = false     // empezar ya encendida (para las capturas)
    private var capturas = false        // sin ventana de estadísticas ni permisos (capturas automáticas)

    // Programas: [activo, días (bit0 = lunes), inicio en minutos, duración]
    private data class Prog(var en: Boolean, var days: Int, var start: Int, var dur: Int)
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
    // Estado de la calefacción
    private lateinit var tTemp: TextView
    private lateinit var tPhase: TextView
    private lateinit var tRem: TextView
    private lateinit var tStats: TextView
    private lateinit var tGas: TextView
    private lateinit var tNote: TextView
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
    private lateinit var tCfg: TextView
    // Interruptor de las estadísticas anónimas (en «Ajustes de la app»)
    private lateinit var swStats: Switch

    // ---------- ciclo de vida ----------
    // Al crear la pantalla: enlace, interfaz, canal de notificaciones y, la primera vez, la pregunta de las estadísticas
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
            "tgtest" -> toast(if (err) msg else getString(R.string.tg_sent))
            "gasreset" -> { toast(getString(R.string.gas_zeroed)); link.refresh() }
            "forget" -> toast(getString(R.string.forgot_bonds))
            "reboot" -> toast(if (err) msg else getString(R.string.rebooting))
            "time" -> {}
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
        tPhase.text = if (on) phases.getOrElse(ph) { phases[2] } else phases[0]
        tPhase.setTextColor(if (on) cFl else cInk)
        val rem = j.optInt("rem")
        tRem.text = if (on) (if (ph == 3) getString(R.string.hot_water) + " " else "") + getString(R.string.remaining, fmtRem(rem)) +
            (if (j.optString("src") == "programa") " " + getString(R.string.by_schedule) else "") else ""
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
        val note = j.optString("note")
        val warn = mutableListOf<String>()
        if (!on && note.isNotEmpty()) warn += note
        if (j.optInt("bus") == 0) warn += getString(R.string.warn_bus)
        if (j.optInt("tv") == 0) warn += getString(R.string.warn_clock)
        tNote.text = warn.joinToString("\n\n")
        tNote.visibility = if (warn.isEmpty()) View.GONE else View.VISIBLE
        bigBtn.text = if (on) getString(R.string.btn_turn_off) else getString(R.string.btn_turn_on, fmtDur(dur))
        bigBtn.background = rounded(if (on) cSf2 else cFl, 16)
        bigBtn.setTextColor(if (on) cInk else Color.parseColor("#1A1000"))

        // Avisos del sistema (con la app abierta o en segundo plano reciente) y contadores de las estadísticas
        if (haveState && !link.demo) {                 // en modo demostración no hay avisos ni estadísticas
            if (lastOn && !on && note.isNotEmpty() && note != lastNote) {
                notifyUser(getString(R.string.notif_self_off), note)
                Stats.count(this, "self_stops")
                Stats.countErrors(this, Regex("0x([0-9A-F]{2})").findAll(note).map { it.groupValues[1] }.toSet())
            }
            if (!lastOn && on && j.optString("src") == "programa") Stats.count(this, "starts_prog")
            if (on && ph == 4 && lastOn) notifyUser(getString(R.string.notif_no_answer), getString(R.string.notif_no_answer_body))
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
        // En pantallas anchas (radios de coche, tablets) la columna no pasa de 640 dp
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
        controls.addView(st, lp(top = 14))

        tNote = text("", 14f, cInk).apply {
            background = rounded(cSf, 8); setPadding(dp(12), dp(10), dp(12), dp(10)); visibility = View.GONE
        }
        controls.addView(tNote, lp(top = 12))

        segRow = LinearLayout(this).apply { orientation = LinearLayout.HORIZONTAL; background = rounded(cSf, 12); setPadding(dp(4), dp(4), dp(4), dp(4)) }
        controls.addView(segRow, lp(top = 12))
        drawSeg()
        bigBtn = button(getString(R.string.btn_no_link)) { toggleHeater() }.apply { textSize = 18f; setPadding(dp(16), dp(18), dp(16), dp(18)) }
        controls.addView(bigBtn, lp(top = 12))

        // Programas
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
        field(getString(R.string.f_pass), InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD, getString(R.string.hint_unchanged)).let { cfg.addView(it.first); ePass = it.second }
        cfg.addView(text(getString(R.string.cfg_tg), 15f, cInk, true), lp(top = 18))
        field(getString(R.string.f_tok), InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD, getString(R.string.hint_unchanged)).let { cfg.addView(it.first); eTok = it.second }
        field(getString(R.string.f_chat), InputType.TYPE_CLASS_TEXT).let { cfg.addView(it.first); eChat = it.second }
        cfg.addView(text(getString(R.string.cfg_safety), 15f, cInk, true), lp(top = 18))
        field(getString(R.string.f_minv), InputType.TYPE_CLASS_NUMBER or InputType.TYPE_NUMBER_FLAG_DECIMAL).let { cfg.addView(it.first); eMinV = it.second }
        cfg.addView(row(button(getString(R.string.btn_save), true) { saveCfg() }, button(getString(R.string.btn_tg_test)) { link.send("tgtest") }), lp(top = 14))
        cfg.addView(row(button(getString(R.string.btn_wifi15)) { link.send("wifi") }, button(getString(R.string.btn_reboot)) { confirmReboot() }), lp(top = 10))
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
        val ver = runCatching { packageManager.getPackageInfo(packageName, 0).versionName }.getOrNull() ?: "?"
        root.addView(text(getString(R.string.footer, ver), 13f, cMut).apply {
            setOnClickListener { startActivity(Intent(Intent.ACTION_VIEW, Uri.parse("https://wttc.favala.es"))) }
        }, lp(top = 20))

        setEnabledDeep(controls, false)
    }

    // Pinta los botones de duración, con la elegida resaltada
    private fun drawSeg() {
        segRow.removeAllViews()
        for (d in durations) {
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
        val on = dev?.optInt("on") == 1
        bigBtn.text = getString(if (on) R.string.btn_turning_off else R.string.btn_turning_on)
        link.send(if (on) "off" else "on $dur")
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
            if (p.size == 4) progs += Prog(p[0] == 1, p[1], p[2], p[3])
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
        val list = progs.joinToString(";") { "${if (it.en) 1 else 0},${it.days},${it.start},${it.dur}" }
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
            val durSp = Spinner(this).apply {
                adapter = ArrayAdapter(this@MainActivity, android.R.layout.simple_spinner_item, durations.map { fmtDur(it) })
                    .apply { setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item) }
                setSelection(durations.indexOfFirst { it >= p.dur }.coerceAtLeast(0))
                background = rounded(cSf2, 8)
                onItemSelectedListener = object : android.widget.AdapterView.OnItemSelectedListener {
                    override fun onItemSelected(parent: android.widget.AdapterView<*>?, view: View?, pos: Int, id: Long) {
                        if (durations[pos] != p.dur) { p.dur = durations[pos]; touchProgs() }
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
            progList.addView(c, lp(top = 10))
        }
        refreshSaveBtn()
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
