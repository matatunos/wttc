package es.favala.wttc

import android.content.Context
import android.os.Handler
import org.json.JSONArray
import org.json.JSONObject
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

/**
 * Placa simulada para el modo demostración.
 *
 * Código generado íntegramente con Claude (Anthropic).
 *
 * Sirve para probar la app sin tener nada montado y para hacer las capturas del README. Se comporta como el
 * firmware a grandes rasgos: responde a las mismas órdenes de texto ("on 30", "off", "cfg", "sched"…) con el
 * mismo formato "orden:datos" y manda el estado cada 2 s con las mismas claves que stateJson() del firmware.
 * No usa Bluetooth ni manda nada a ninguna parte.
 *
 * La "física" es muy simple y aproximada: el agua sube mientras hay llama (más rápido a plena carga), a 75 °C
 * pasa a carga parcial, a 82 °C entra en pausa, y el gasoil se integra con la potencia (≈ 0,124 l por kWh).
 * Lleva termómetro de dentro (sube con el agua caliente y baja hacia los 6 °C de fuera), termostato sencillo
 * («hasta X °C»: apaga al llegar) y salida suelta (solo se guarda y se muestra).
 *
 * @param ctx      para los textos (en el idioma de la app, como haría la placa real al ponerse en ese idioma)
 * @param main     hilo principal (todo pasa en él)
 * @param listener a quién se le entregan el estado y las respuestas (la pantalla)
 * @param heating  empezar ya encendida y caliente (para las capturas)
 */
class DemoDevice(private val ctx: Context, private val main: Handler, private val listener: BleLink.Listener, heating: Boolean) {

    // ---------- estado simulado ----------
    private var on = false                 // ¿calentando?
    private var ph = 0                     // estado real: 0 apagada, 1 arrancando, 2 con llama, 3 pausa
    private var until = 0L                 // cuándo termina el encendido (ms)
    private var startedAt = 0L             // cuándo empezó (ms): el primer minuto es el arranque
    private var total = 0                  // duración del encendido (s)
    private var src = ""                   // quién la encendió
    private var temp = 18.0                // agua del motor (°C)
    private var cab = 9.0                  // dentro (°C)
    private var tgt = 0                    // termostato: objetivo (0 = no) y cuándo acaba su ventana (ms)
    private var tgtUntil = 0L
    private var dep = 0L                   // salida suelta (s desde 1970) y su objetivo
    private var depT = 0
    private var gasCur = 0.0               // gasoil estimado de este encendido (l)
    private var gasLast = 0.42             // último encendido (l)
    private var gasMonth = 3.6             // este mes (l)
    private var gasTotal = 27.9            // total (l)
    private var note = ""                  // aviso de apagado (vacío)
    private var auto = true                // programas activos
    private var sched = "1|1,31,420,30;1,96,600,45"   // dos programas: L-V 07:00 30 min y S-D 10:00 45 min
    private val log = ArrayDeque<String>() // registro de eventos (como el del firmware)

    // Configuración de ejemplo (datos inventados)
    private val cfg = JSONObject()
        .put("name", "WTTC").put("pin", 482915).put("wifimode", 0).put("ssid", ctx.getString(R.string.demo_ssid))
        .put("tg", true).put("tgchat", "123456789").put("minvolt", "12.0").put("bonds", 1)
        .put("lang", ctx.getString(R.string.lang_code)).put("ver", ctx.getString(R.string.demo_ver))
        .put("ota", 1).put("th", 1).put("oled", 2).put("disp", 1).put("led", 1).put("toff", "0.0").put("warm", 50)
        .put("sens", "SHT31").put("scr", true)
    // Números con la coma o el punto del idioma de la app
    private val numLocale = Locale(ctx.getString(R.string.lang_code))

    // Cada 2 s: avanza la simulación y manda el estado (igual que la placa real)
    private val tick = object : Runnable {
        override fun run() {
            step(2.0)
            emitState()
            main.postDelayed(this, 2000)
        }
    }

    init {
        addLog(ctx.getString(R.string.demo_log_boot))
        if (heating) {
            // Para las capturas: un programa encendido hace 8 minutos, con el agua ya templada
            turnOn(30, "programa")
            startedAt = now() - 8 * 60000L
            until = now() + 22 * 60000L
            temp = 46.0
            cab = 14.5
            gasCur = 0.09
            ph = 2
        }
    }

    fun start() { main.post(tick) }
    fun stop() { main.removeCallbacks(tick) }

    private fun now() = System.currentTimeMillis()

    private fun turnOn(min: Int, s: String) {
        on = true; ph = 1; total = min * 60; until = now() + min * 60000L; startedAt = now()
        src = s; note = ""; gasCur = 0.0
        addLog(ctx.getString(R.string.demo_log_on, srcName(s), min))
    }

    private fun turnOff(why: String) {
        if (on) gasLast = gasCur
        on = false; ph = 0
        addLog(ctx.getString(R.string.demo_log_off, why, String.format(numLocale, "%.2f", gasLast)))
    }

    // Quién la encendió, para el registro (src guarda la palabra del protocolo, como el firmware)
    private fun srcName(s: String) = ctx.getString(if (s == "programa") R.string.demo_src_prog else R.string.demo_src_app)

    // Potencia como la daría la Webasto: plena carga hasta 75 °C, parcial por encima, nada sin llama
    private fun power() = if (!on || ph != 2) 0 else if (temp < 75) 5000 else 2500

    // Avanza la simulación «sec» segundos
    private fun step(sec: Double) {
        if (on) {
            if (now() >= until) { turnOff(ctx.getString(R.string.demo_why_end)); return }
            val since = (now() - startedAt) / 1000
            ph = when {
                since < 60 -> 1                          // primer minuto: arrancando (bujía, ventilador…)
                ph == 3 && temp > 70 -> 3                // en pausa hasta que el agua baja a 70 °C
                temp >= 82 -> 3                          // agua caliente: pausa de regulación
                else -> 2                                // con llama
            }
            val pw = power()
            temp += when (ph) { 2 -> if (pw > 3000) 0.6 else 0.25; 3 -> -0.2; else -> 0.0 }
            val l = pw / 1000.0 * 0.124 * sec / 3600     // l/h × horas
            gasCur += l; gasMonth += l; gasTotal += l
        } else if (temp > 18) temp -= 0.05               // apagada: se enfría poco a poco
        // Dentro: el ventilador mete el calor del agua; si no, se enfría hacia los 6 °C de fuera
        cab += ((if (on && temp > 30) (temp - cab) * 0.0006 else 0.0) - (cab - 6) * 0.0002) * sec
        if (tgt > 0) {
            if (now() >= tgtUntil) { tgt = 0; if (on) turnOff(ctx.getString(R.string.demo_why_end)) }
            else if (on && cab >= tgt && now() - startedAt >= 15 * 60000L) turnOff("$tgt °C")
            else if (!on && cab <= tgt - 1.5) { val keep = tgt; turnOn(60, src.ifEmpty { "app" }); tgt = keep }
        }
    }

    /** Manda el estado con las mismas claves que el firmware (ver stateJson() en WTTC.ino). */
    fun emitState() {
        val rem = if (on) ((until - now()) / 1000).coerceAtLeast(0) else 0
        val j = JSONObject()
            .put("on", if (on) 1 else 0).put("ph", ph).put("rem", rem).put("tot", total).put("src", src)
            .put("t", temp.toInt()).put("v", if (on) 12.4 else 12.7).put("fl", if (on && ph == 2) 1 else 0)
            .put("pw", power()).put("bus", 1).put("tv", 1).put("time", now() / 1000)
            .put("auto", if (auto) 1 else 0).put("wf", 1).put("wm", 1)
            .put("gas", JSONArray().put(r2(gasCur)).put(r2(gasLast)).put(r2(gasMonth)).put(r2(gasTotal)))
            .put("ct", Math.round(cab * 10)).put("ch", 58).put("tg", tgt)
            .put("tu", if (tgt > 0) ((tgtUntil - now()) / 1000).coerceAtLeast(0) else 0)
            .put("dp", dep).put("dt", depT).put("wa", if (on && temp >= 50) 1 else 0)
            .put("note", note)
        listener.onDeviceState(j)
    }

    /** Atiende una orden como lo haría el firmware (runCmd) y entrega la respuesta "orden:datos". */
    fun handle(c: String) {
        val sp = c.indexOf(' ')
        val k = if (sp < 0) c else c.substring(0, sp)
        val a = if (sp < 0) "" else c.substring(sp + 1)
        val r = when (k) {
            "on" -> {
                val p = a.split(" "); val m = p.getOrNull(0)?.toIntOrNull() ?: 30; val t = p.getOrNull(1)?.toIntOrNull() ?: 0
                if (t in 5..25) { tgt = t; tgtUntil = now() + m.coerceIn(1, 240) * 60000L; if (cab < t) turnOn(60.coerceAtMost(m), "app") }
                else { tgt = 0; turnOn(m.coerceIn(1, 60), "app") }
                "ok"
            }
            "off" -> { tgt = 0; turnOff(srcName("app")); "ok" }
            "dep" -> {
                if (a == "off") { dep = 0; depT = 0 }
                else {
                    val p = a.split(" ", ":").mapNotNull { it.toIntOrNull() }
                    val c = java.util.Calendar.getInstance().apply { set(java.util.Calendar.HOUR_OF_DAY, p.getOrElse(0) { 8 }); set(java.util.Calendar.MINUTE, p.getOrElse(1) { 0 }); set(java.util.Calendar.SECOND, 0) }
                    if (c.timeInMillis <= now() + 60000) c.add(java.util.Calendar.DAY_OF_MONTH, 1)
                    dep = c.timeInMillis / 1000; depT = p.getOrElse(2) { 0 }
                }
                "ok"
            }
            "time" -> "ok"
            "state" -> { emitState(); return }
            "sched" -> sched
            "setsched" -> { sched = a; auto = a.startsWith("1"); addLog(ctx.getString(R.string.demo_log_sched)); "ok" }
            "errors" -> """{"ok":true,"raw":"(${ctx.getString(R.string.demo_ver)})","codes":[{"c":"02","n":1}]}"""
            "log" -> log.reversed().joinToString("\n")
            "cfg" -> cfg.toString()
            "set" -> { if (a.startsWith("lang=")) cfg.put("lang", a.substring(5)); "ok" }
            "scan" -> """[{"s":"Furgo 4G","r":-48,"e":1},{"s":"Casa","r":-61,"e":1},{"s":"Camping La Playa","r":-72,"e":0},{"s":"MOVISTAR_8F21","r":-83,"e":1}]"""
            "wifi", "tgtest", "forget", "reboot" -> "ok"
            "gasreset" -> { gasCur = 0.0; gasLast = 0.0; gasMonth = 0.0; gasTotal = 0.0; "ok" }
            else -> "err " + ctx.getString(R.string.demo_unknown)
        }
        main.post { listener.onResponse(k, r); emitState() }
    }

    private fun r2(x: Double) = Math.round(x * 100) / 100.0

    private fun addLog(m: String) {
        val t = SimpleDateFormat("dd/MM HH:mm", Locale.ROOT).format(Date())
        log.addLast("$t  $m")
        while (log.size > 20) log.removeFirst()
    }
}
