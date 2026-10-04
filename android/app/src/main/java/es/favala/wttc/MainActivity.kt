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

    private val durations = intArrayOf(15, 30, 45, 60)
    private val dayLetters = arrayOf("L", "M", "X", "J", "V", "S", "D")
    private val phases = arrayOf("Apagada", "Arrancando…", "Calentando", "En pausa", "Sin respuesta")

    private lateinit var link: BleLink
    private var dev: JSONObject? = null
    private var dur = 30
    private var lastOn = false
    private var lastNote = ""

    // Programas: [activo, días (bit0 = lunes), inicio en minutos, duración]
    private data class Prog(var en: Boolean, var days: Int, var start: Int, var dur: Int)
    private val progs = mutableListOf<Prog>()
    private var progsAuto = true
    private var progsDirty = false

    // Vistas
    private lateinit var tLink: TextView
    private lateinit var dot: View
    private lateinit var pairBox: LinearLayout
    private lateinit var tPair: TextView
    private lateinit var controls: LinearLayout
    private lateinit var tTemp: TextView
    private lateinit var tPhase: TextView
    private lateinit var tRem: TextView
    private lateinit var tStats: TextView
    private lateinit var tNote: TextView
    private lateinit var segRow: LinearLayout
    private lateinit var bigBtn: Button
    private lateinit var swAuto: Switch
    private lateinit var progList: LinearLayout
    private lateinit var bSaveProgs: Button
    private lateinit var tDiag: TextView
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

    // ---------- ciclo de vida ----------
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        link = BleLink(this, this)
        buildUi()
        createChannel()
    }

    override fun onStart() {
        super.onStart()
        if (askPermissions()) startLink()
    }

    override fun onStop() {
        super.onStop()
        link.stop()
    }

    private fun startLink() {
        if (!link.bluetoothOn()) {
            onLink(BleLink.State.NO_BLUETOOTH, "")
            return
        }
        link.start()
    }

    // ---------- permisos ----------
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

    override fun onRequestPermissionsResult(requestCode: Int, permissions: Array<out String>, grantResults: IntArray) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)
        val bt = permissions.indices.all { i ->
            permissions[i] == Manifest.permission.POST_NOTIFICATIONS || grantResults[i] == PackageManager.PERMISSION_GRANTED
        }
        if (bt) startLink()
        else onLink(BleLink.State.NO_BLUETOOTH, "Sin permiso de Bluetooth la app no puede hablar con la placa.")
    }

    // ---------- BleLink.Listener ----------
    override fun onLink(state: BleLink.State, detail: String) {
        val (txt, col) = when (state) {
            BleLink.State.NO_BLUETOOTH -> "Bluetooth desactivado" to cBad
            BleLink.State.NOT_PAIRED -> "Sin placa emparejada" to cMut
            BleLink.State.SCANNING -> "Buscando…" to cIce
            BleLink.State.PAIRING -> "Emparejando: escribe el PIN" to cIce
            BleLink.State.CONNECTING -> "Conectando…" to cIce
            BleLink.State.CONNECTED -> "Conectado a ${link.savedName ?: "WTTC"}" to cOk
            BleLink.State.OUT_OF_RANGE -> "Fuera de alcance: se conectará sola" to cMut
        }
        tLink.text = if (detail.isNotEmpty()) "$txt · $detail" else txt
        (dot.background as GradientDrawable).setColor(col)

        val paired = link.savedAddress != null && link.isPaired()
        pairBox.visibility = if (paired && state != BleLink.State.NO_BLUETOOTH) View.GONE else View.VISIBLE
        tPair.text = when (state) {
            BleLink.State.NO_BLUETOOTH -> "Activa el Bluetooth del dispositivo para conectar con la placa."
            BleLink.State.PAIRING -> "Android te pedirá el PIN de 6 cifras de la placa. Sale en la consola serie al arrancar " +
                "y en la web del ESP32 (Configuración)."
            else -> "Pulsa «Buscar placa», elige tu WTTC y escribe su PIN. Solo hay que hacerlo una vez: después la app se " +
                "conecta sola cuando la placa está cerca."
        }
        setEnabledDeep(controls, state == BleLink.State.CONNECTED)
        if (state != BleLink.State.CONNECTED) bigBtn.text = if (state == BleLink.State.OUT_OF_RANGE) "Fuera de alcance" else "Sin conexión"
    }

    override fun onDeviceState(j: JSONObject) {
        dev = j
        render()
    }

    override fun onResponse(cmd: String, data: String) {
        val err = data.startsWith("err")
        val msg = if (err) data.removePrefix("err").trim() else data
        when (cmd) {
            "on", "off" -> { if (err) toast(msg); link.refresh() }
            "cfg" -> runCatching { fillCfg(JSONObject(data)) }
            "sched" -> parseSched(data)
            "setsched" -> { if (err) toast(msg) else { progsDirty = false; bSaveProgs.text = "Guardado"; refreshSaveBtn() } }
            "errors" -> tDiag.text = formatErrors(data)
            "log" -> tDiag.text = if (data.isBlank()) "Registro vacío." else data
            "set" -> when {
                err -> toast(msg)
                data.startsWith("restart") -> needRestart = true
            }
            "wifi" -> toast("Wi-Fi del ESP32 encendida 15 minutos")
            "tgtest" -> toast(if (err) msg else "Aviso de prueba enviado: mira Telegram en unos segundos")
            "forget" -> toast("Emparejamientos borrados en la placa. Quita también la placa en los Ajustes de Bluetooth de Android.")
            "reboot" -> toast(if (err) msg else "Reiniciando la placa…")
            "time" -> {}
            else -> if (err) toast(msg)
        }
    }

    // ---------- pintado del estado ----------
    private fun render() {
        val j = dev ?: return
        val on = j.optInt("on") == 1
        val ph = j.optInt("ph")
        val t = j.optInt("t", -999)
        tTemp.text = if (t > -100) "$t°" else "--°"
        tPhase.text = if (on) phases.getOrElse(ph) { "Calentando" } else "Apagada"
        tPhase.setTextColor(if (on) cFl else cInk)
        val rem = j.optInt("rem")
        tRem.text = if (on) (if (ph == 3) "Agua caliente: vuelve a prender sola. " else "") + "Quedan ${fmtRem(rem)}" +
            (if (j.optString("src") == "programa") " (programa)" else "") else ""
        val v = j.optDouble("v", -1.0)
        val fl = j.optInt("fl", -1)
        val pw = j.optInt("pw", -1)
        tStats.text = "Batería ${if (v > 0) String.format("%.1f V", v) else "--"}   ·   Llama ${if (fl < 0) "--" else if (fl > 0) "sí" else "no"}   ·   " +
            "Potencia ${if (pw < 0) "--" else "$pw W"}"
        val note = j.optString("note")
        val warn = mutableListOf<String>()
        if (!on && note.isNotEmpty()) warn += note
        if (j.optInt("bus") == 0) warn += "La Webasto no responde por W-Bus. Revisa el cable del bus, la masa común y el módulo TJA1020."
        if (j.optInt("tv") == 0) warn += "La placa no está en hora: los programas no se ejecutarán."
        tNote.text = warn.joinToString("\n\n")
        tNote.visibility = if (warn.isEmpty()) View.GONE else View.VISIBLE
        bigBtn.text = if (on) "Apagar" else "Encender ${fmtDur(dur)}"
        bigBtn.background = rounded(if (on) cSf2 else cFl, 16)
        bigBtn.setTextColor(if (on) cInk else Color.parseColor("#1A1000"))

        // Avisos del sistema (con la app abierta o en segundo plano reciente)
        if (lastOn && !on && note.isNotEmpty() && note != lastNote) notifyUser("La calefacción se ha apagado sola", note)
        if (on && ph == 4 && lastOn) notifyUser("La Webasto no responde", "Sin respuesta por W-Bus; sin mantenimiento se apaga sola.")
        lastOn = on
        lastNote = note
    }

    // ---------- interfaz ----------
    private fun dp(v: Int) = (v * resources.displayMetrics.density).toInt()

    private fun rounded(color: Int, radiusDp: Int) = GradientDrawable().apply {
        setColor(color); cornerRadius = dp(radiusDp).toFloat()
    }

    private fun text(s: String = "", size: Float = 15f, color: Int = cInk, bold: Boolean = false) = TextView(this).apply {
        text = s; textSize = size; setTextColor(color)
        if (bold) setTypeface(typeface, Typeface.BOLD)
    }

    private fun button(s: String, primary: Boolean = false, onClick: () -> Unit) = Button(this).apply {
        text = s; isAllCaps = false; textSize = 15f
        setTextColor(if (primary) Color.parseColor("#04121C") else cInk)
        background = rounded(if (primary) cIce else cSf2, 12)
        setPadding(dp(14), dp(12), dp(14), dp(12))
        setOnClickListener { onClick() }
    }

    private fun lp(w: Int = ViewGroup.LayoutParams.MATCH_PARENT, top: Int = 0, weight: Float = 0f) =
        LinearLayout.LayoutParams(w, ViewGroup.LayoutParams.WRAP_CONTENT, weight).apply { topMargin = dp(top) }

    private fun row(vararg views: View) = LinearLayout(this).apply {
        orientation = LinearLayout.HORIZONTAL
        views.forEachIndexed { i, v -> addView(v, LinearLayout.LayoutParams(0, ViewGroup.LayoutParams.WRAP_CONTENT, 1f).apply { if (i > 0) leftMargin = dp(10) }) }
    }

    private fun card(): LinearLayout = LinearLayout(this).apply {
        orientation = LinearLayout.VERTICAL
        background = rounded(cSf, 14)
        setPadding(dp(14), dp(14), dp(14), dp(14))
    }

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

    private fun section(parent: LinearLayout, title: String, sub: String = "") {
        parent.addView(text(title, 20f, cInk, true), lp(top = 30))
        if (sub.isNotEmpty()) parent.addView(text(sub, 14f, cMut), lp(top = 2))
    }

    private fun setEnabledDeep(v: View, en: Boolean) {
        v.isEnabled = en
        v.alpha = if (en) 1f else 0.55f
        if (v is ViewGroup) for (i in 0 until v.childCount) { val c = v.getChildAt(i); c.isEnabled = en; if (c is ViewGroup) setEnabledDeep(c, en) }
    }

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
        pairBox.addView(row(button("Buscar placa", true) { scanDialog() }, button("Activar Bluetooth") { enableBluetooth() }), lp(top = 12))
        root.addView(pairBox, lp(top = 14))

        controls = LinearLayout(this).apply { orientation = LinearLayout.VERTICAL }
        root.addView(controls)

        // Estado
        val st = card().apply { gravity = Gravity.CENTER_HORIZONTAL }
        tTemp = text("--°", 64f, cInk).apply { gravity = Gravity.CENTER; typeface = Typeface.create("sans-serif-light", Typeface.NORMAL) }
        st.addView(tTemp, lp())
        st.addView(text("agua del motor", 13f, cMut).apply { gravity = Gravity.CENTER }, lp())
        tPhase = text("—", 22f, cInk, true).apply { gravity = Gravity.CENTER }
        st.addView(tPhase, lp(top = 10))
        tRem = text("", 14f, cMut).apply { gravity = Gravity.CENTER }
        st.addView(tRem, lp(top = 2))
        tStats = text("", 14f, cMut).apply { gravity = Gravity.CENTER }
        st.addView(tStats, lp(top = 12))
        controls.addView(st, lp(top = 14))

        tNote = text("", 14f, cInk).apply {
            background = rounded(cSf, 8); setPadding(dp(12), dp(10), dp(12), dp(10)); visibility = View.GONE
        }
        controls.addView(tNote, lp(top = 12))

        segRow = LinearLayout(this).apply { orientation = LinearLayout.HORIZONTAL; background = rounded(cSf, 12); setPadding(dp(4), dp(4), dp(4), dp(4)) }
        controls.addView(segRow, lp(top = 12))
        drawSeg()
        bigBtn = button("Sin conexión") { toggleHeater() }.apply { textSize = 18f; setPadding(dp(16), dp(18), dp(16), dp(18)) }
        controls.addView(bigBtn, lp(top = 12))

        // Programas
        section(controls, "Programas", "Se encienden solos a una hora y unos días; la placa los guarda.")
        swAuto = Switch(this).apply { text = "Programas activos"; setTextColor(cInk); textSize = 16f }
        swAuto.setOnCheckedChangeListener { _: CompoundButton, c: Boolean -> if (c != progsAuto) { progsAuto = c; touchProgs() } }
        controls.addView(card().apply { addView(swAuto) }, lp(top = 10))
        progList = LinearLayout(this).apply { orientation = LinearLayout.VERTICAL }
        controls.addView(progList)
        bSaveProgs = button("Guardar programas", true) { saveProgs() }
        controls.addView(row(button("Añadir programa") { addProg() }, bSaveProgs), lp(top = 10))
        drawProgs()

        // Diagnóstico
        section(controls, "Diagnóstico")
        tDiag = text("", 13f, cMut).apply { setTextIsSelectable(true) }
        controls.addView(row(button("Leer averías") { tDiag.text = "Leyendo…"; link.send("errors") },
            button("Ver registro") { tDiag.text = "Leyendo…"; link.send("log") }), lp(top = 10))
        controls.addView(tDiag, lp(top = 10))

        // Configuración
        section(controls, "Configuración", "Se guarda en la placa. El nombre, el PIN y la red con internet se aplican al reiniciarla.")
        val cfg = card()
        cfg.addView(text("Bluetooth y Wi-Fi propios", 15f, cInk, true))
        field("Nombre (Wi-Fi y Bluetooth)").let { cfg.addView(it.first); eName = it.second }
        field("PIN de emparejamiento (6 cifras)", InputType.TYPE_CLASS_NUMBER).let { cfg.addView(it.first); ePin = it.second }
        field("Clave de la Wi-Fi propia (mínimo 8)", InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD, "sin cambios").let { cfg.addView(it.first); eAp = it.second }
        cfg.addView(text("Wi-Fi propia", 13f, cMut), lp(top = 10))
        spWm = Spinner(this).apply {
            adapter = ArrayAdapter(this@MainActivity, android.R.layout.simple_spinner_dropdown_item,
                listOf("Siempre encendida (gasta más)", "Solo mientras calienta", "Solo a petición (máximo ahorro)"))
            background = rounded(cSf2, 10)
        }
        cfg.addView(spWm, lp(top = 4))
        cfg.addView(text("Red con internet (opcional)", 15f, cInk, true), lp(top = 18))
        field("Red Wi-Fi (casa o punto de acceso del móvil)").let { cfg.addView(it.first); eSsid = it.second }
        field("Contraseña", InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD, "sin cambios").let { cfg.addView(it.first); ePass = it.second }
        cfg.addView(text("Avisos por Telegram (opcional)", 15f, cInk, true), lp(top = 18))
        field("Token del bot (de @BotFather)", InputType.TYPE_CLASS_TEXT or InputType.TYPE_TEXT_VARIATION_PASSWORD, "sin cambios").let { cfg.addView(it.first); eTok = it.second }
        field("Chat ID (vacío: avisos desactivados)", InputType.TYPE_CLASS_TEXT).let { cfg.addView(it.first); eChat = it.second }
        cfg.addView(text("Seguridad", 15f, cInk, true), lp(top = 18))
        field("Batería mínima para arrancar un programa (V)", InputType.TYPE_CLASS_NUMBER or InputType.TYPE_NUMBER_FLAG_DECIMAL).let { cfg.addView(it.first); eMinV = it.second }
        cfg.addView(row(button("Guardar", true) { saveCfg() }, button("Probar Telegram") { link.send("tgtest") }), lp(top = 14))
        cfg.addView(row(button("Encender Wi-Fi 15 min") { link.send("wifi") }, button("Reiniciar placa") { confirmReboot() }), lp(top = 10))
        tCfg = text("", 13f, cMut)
        cfg.addView(tCfg, lp(top = 10))
        controls.addView(cfg, lp(top = 10))

        // Dispositivo
        section(root, "Este dispositivo")
        root.addView(row(button("Olvidar placa") { confirmForget() }, button("Ajustes Bluetooth") {
            startActivity(Intent(Settings.ACTION_BLUETOOTH_SETTINGS))
        }), lp(top = 10))
        val ver = runCatching { packageManager.getPackageInfo(packageName, 0).versionName }.getOrNull() ?: "?"
        root.addView(text("App WTTC $ver · wttc.favala.es", 13f, cMut).apply {
            setOnClickListener { startActivity(Intent(Intent.ACTION_VIEW, Uri.parse("https://wttc.favala.es"))) }
        }, lp(top = 20))

        setEnabledDeep(controls, false)
    }

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
    private fun toggleHeater() {
        val on = dev?.optInt("on") == 1
        bigBtn.text = if (on) "Apagando…" else "Encendiendo…"
        link.send(if (on) "off" else "on $dur")
    }

    private fun enableBluetooth() {
        if (link.bluetoothOn()) { startLink(); return }
        @Suppress("DEPRECATION")
        startActivityForResult(Intent(BluetoothAdapter.ACTION_REQUEST_ENABLE), 2)
    }

    @Deprecated("Activity sin AndroidX")
    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        @Suppress("DEPRECATION") super.onActivityResult(requestCode, resultCode, data)
        if (requestCode == 2) startLink()
    }

    private fun scanDialog() {
        if (!askPermissions()) return
        if (!link.bluetoothOn()) { enableBluetooth(); return }
        val found = mutableListOf<Pair<BluetoothDevice, String>>()
        val names = ArrayAdapter<String>(this, android.R.layout.simple_list_item_1)
        val dlg = AlertDialog.Builder(this)
            .setTitle("Buscando placas WTTC…")
            .setAdapter(names) { _, i -> val (d, n) = found[i]; link.choose(d, n) }
            .setNegativeButton("Cancelar") { _, _ -> link.stopScan(); link.start() }
            .create()
        dlg.show()
        link.scan { d, n ->
            if (found.none { it.first.address == d.address }) {
                found += d to n
                names.add("$n  (${d.address})")
            }
        }
    }

    private fun confirmForget() {
        AlertDialog.Builder(this)
            .setTitle("Olvidar la placa")
            .setMessage("La app dejará de conectarse a esta placa. Para emparejarla de nuevo, quítala también en los Ajustes de " +
                "Bluetooth de Android y vuelve a buscarla.")
            .setPositiveButton("Olvidar") { _, _ -> link.forget(); dev = null }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    private fun confirmReboot() {
        AlertDialog.Builder(this)
            .setTitle("Reiniciar la placa")
            .setMessage("Se aplican los cambios pendientes. Si está calentando, la placa no se reinicia.")
            .setPositiveButton("Reiniciar") { _, _ -> link.send("reboot"); needRestart = false }
            .setNegativeButton("Cancelar", null)
            .show()
    }

    // ---------- programas ----------
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

    private fun touchProgs() {
        progsDirty = true
        bSaveProgs.text = "Guardar programas"
        refreshSaveBtn()
    }

    private fun refreshSaveBtn() { bSaveProgs.alpha = if (progsDirty) 1f else 0.6f }

    private fun addProg() {
        if (progs.size >= 8) { toast("Máximo 8 programas"); return }
        progs += Prog(true, 31, 7 * 60, 30)
        touchProgs(); drawProgs()
    }

    private fun saveProgs() {
        val list = progs.joinToString(";") { "${if (it.en) 1 else 0},${it.days},${it.start},${it.dur}" }
        link.send("setsched ${if (progsAuto) 1 else 0}|$list")
    }

    private fun drawProgs() {
        progList.removeAllViews()
        if (progs.isEmpty()) {
            progList.addView(text("Sin programas.", 14f, cMut), lp(top = 10))
            refreshSaveBtn(); return
        }
        progs.forEachIndexed { idx, p ->
            val c = card()
            val time = button(hm(p.start)) {
                TimePickerDialog(this, { _, h, m -> p.start = h * 60 + m; touchProgs(); drawProgs() }, p.start / 60, p.start % 60, true).show()
            }.apply { textSize = 22f; setTypeface(typeface, Typeface.BOLD) }
            val durSp = Spinner(this).apply {
                adapter = ArrayAdapter(this@MainActivity, android.R.layout.simple_spinner_dropdown_item, durations.map { fmtDur(it) })
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
            val del = button("✕") { progs.removeAt(idx); touchProgs(); drawProgs() }
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
    private var needRestart = false
        set(v) { field = v; if (v) toast("Guardado. Se aplicará al reiniciar la placa («Reiniciar placa»).") }

    private fun fillCfg(c: JSONObject) {
        eName.setText(c.optString("name"))
        ePin.setText(c.optString("pin"))
        spWm.setSelection(c.optInt("wifimode", 1).coerceIn(0, 2))
        eSsid.setText(c.optString("ssid"))
        eChat.setText(c.optString("tgchat"))
        eMinV.setText(c.optString("minvolt"))
        eTok.hint = if (c.optBoolean("tg")) "guardado (vacío: sin cambios)" else "sin configurar"
        eAp.setText(""); ePass.setText(""); eTok.setText("")
        val bonds = c.optInt("bonds")
        tCfg.text = "Firmware WTTC ${c.optString("ver")} · dispositivos emparejados con la placa: $bonds"
    }

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
        toast("Configuración enviada")
    }

    // ---------- utilidades ----------
    private fun formatErrors(data: String): String = runCatching {
        val j = JSONObject(data)
        if (!j.optBoolean("ok")) return@runCatching "La Webasto no respondió."
        val codes = j.optJSONArray("codes")
        if (codes == null || codes.length() == 0) "Sin averías guardadas."
        else (0 until codes.length()).joinToString("\n") { i ->
            val c = codes.getJSONObject(i); "Código 0x${c.optString("c")} (${c.optInt("n")} veces)"
        }
    }.getOrDefault(data)

    private fun fmtDur(d: Int) = if (d < 60) "$d min" else "${d / 60} h" + (if (d % 60 > 0) " ${d % 60}" else "")
    private fun fmtRem(s: Int): String { val m = ceil(s / 60.0).toInt(); return if (m < 60) "$m min" else "${m / 60} h ${"%02d".format(m % 60)} min" }
    private fun hm(m: Int) = "%02d:%02d".format(m / 60, m % 60)
    private fun toast(s: String) = Toast.makeText(this, s, Toast.LENGTH_LONG).show()

    private fun createChannel() {
        val ch = NotificationChannel("avisos", "Avisos de la calefacción", NotificationManager.IMPORTANCE_HIGH)
        getSystemService(NotificationManager::class.java).createNotificationChannel(ch)
    }

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
