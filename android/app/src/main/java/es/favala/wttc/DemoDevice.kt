package es.favala.wttc

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
 *
 * @param main     hilo principal (todo pasa en él)
 * @param listener a quién se le entregan el estado y las respuestas (la pantalla)
 * @param heating  empezar ya encendida y caliente (para las capturas)
 */
class DemoDevice(private val main: Handler, private val listener: BleLink.Listener, heating: Boolean) {

    // ---------- estado simulado ----------
    private var on = false                 // ¿calentando?
    private var ph = 0                     // estado real: 0 apagada, 1 arrancando, 2 con llama, 3 pausa
    private var until = 0L                 // cuándo termina el encendido (ms)
    private var startedAt = 0L             // cuándo empezó (ms): el primer minuto es el arranque
    private var total = 0                  // duración del encendido (s)
    private var src = ""                   // quién la encendió
    private var temp = 18.0                // agua del motor (°C)
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
        .put("name", "WTTC").put("pin", 482915).put("wifimode", 1).put("ssid", "Mi móvil")
        .put("tg", true).put("tgchat", "123456789").put("minvolt", "12.0").put("bonds", 1)
        .put("ver", "demostración")

    // Cada 2 s: avanza la simulación y manda el estado (igual que la placa real)
    private val tick = object : Runnable {
        override fun run() {
            step(2.0)
            emitState()
            main.postDelayed(this, 2000)
        }
    }

    init {
        addLog("Arranque (modo demostración)")
        if (heating) {
            // Para las capturas: un programa encendido hace 8 minutos, con el agua ya templada
            turnOn(30, "programa")
            startedAt = now() - 8 * 60000L
            until = now() + 22 * 60000L
            temp = 46.0
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
        addLog("Encendida ($s, $min min)")
    }

    private fun turnOff(why: String) {
        if (on) gasLast = gasCur
        on = false; ph = 0
        addLog("Apagada ($why) · gasoil ≈ ${"%.2f".format(gasLast)} l")
    }

    // Potencia como la daría la Webasto: plena carga hasta 75 °C, parcial por encima, nada sin llama
    private fun power() = if (!on || ph != 2) 0 else if (temp < 75) 5000 else 2500

    // Avanza la simulación «sec» segundos
    private fun step(sec: Double) {
        if (on) {
            if (now() >= until) { turnOff("fin de tiempo"); return }
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
    }

    /** Manda el estado con las mismas claves que el firmware (ver stateJson() en WTTC.ino). */
    fun emitState() {
        val rem = if (on) ((until - now()) / 1000).coerceAtLeast(0) else 0
        val j = JSONObject()
            .put("on", if (on) 1 else 0).put("ph", ph).put("rem", rem).put("tot", total).put("src", src)
            .put("t", temp.toInt()).put("v", if (on) 12.4 else 12.7).put("fl", if (on && ph == 2) 1 else 0)
            .put("pw", power()).put("bus", 1).put("tv", 1).put("time", now() / 1000)
            .put("auto", if (auto) 1 else 0).put("wf", 1).put("wm", 1)
            .put("gas", JSONArray().put(r2(gasCur)).put(r2(gasLast)).put(r1(gasMonth)).put(r1(gasTotal)))
            .put("note", note)
        listener.onDeviceState(j)
    }

    /** Atiende una orden como lo haría el firmware (runCmd) y entrega la respuesta "orden:datos". */
    fun handle(c: String) {
        val sp = c.indexOf(' ')
        val k = if (sp < 0) c else c.substring(0, sp)
        val a = if (sp < 0) "" else c.substring(sp + 1)
        val r = when (k) {
            "on" -> { turnOn((a.toIntOrNull() ?: 30).coerceIn(1, 60), "app"); "ok" }
            "off" -> { turnOff("app"); "ok" }
            "time" -> "ok"
            "state" -> { emitState(); return }
            "sched" -> sched
            "setsched" -> { sched = a; auto = a.startsWith("1"); addLog("Programas guardados"); "ok" }
            "errors" -> """{"ok":true,"raw":"(demostración)","codes":[{"c":"02","n":1}]}"""
            "log" -> log.reversed().joinToString("\n")
            "cfg" -> cfg.toString()
            "set", "wifi", "tgtest", "forget", "reboot" -> "ok"
            "gasreset" -> { gasLast = 0.0; gasMonth = 0.0; gasTotal = 0.0; "ok" }
            else -> "err Orden desconocida"
        }
        main.post { listener.onResponse(k, r); emitState() }
    }

    private fun r2(x: Double) = Math.round(x * 100) / 100.0
    private fun r1(x: Double) = Math.round(x * 10) / 10.0

    private fun addLog(m: String) {
        val t = SimpleDateFormat("dd/MM HH:mm", Locale("es", "ES")).format(Date())
        log.addLast("$t  $m")
        while (log.size > 20) log.removeFirst()
    }
}
