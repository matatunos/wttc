package es.favala.wttc

import android.app.UiModeManager
import android.content.Context
import android.content.pm.PackageManager
import android.content.res.Configuration
import android.os.Build
import org.json.JSONObject
import java.net.HttpURLConnection
import java.net.URL
import java.util.Locale
import java.util.UUID
import kotlin.concurrent.thread

/**
 * Estadísticas anónimas, solo con permiso (se pregunta al abrir la app por primera vez).
 *
 * Una vez al día como mucho se envía a https://wttc.favala.es/api/stats.php: identificador aleatorio de
 * instalación, versiones (app, firmware, Android), tipo de dispositivo, país según el idioma del sistema y
 * los contadores acumulados desde el último envío. Nada de ubicación, nombres, redes, PIN ni horarios.
 * Los resultados son públicos en https://wttc.favala.es/estadisticas.php
 */
object Stats {
    private const val URL_STATS = "https://wttc.favala.es/api/stats.php"
    const val URL_PUBLIC = "https://wttc.favala.es/estadisticas.php"
    private const val DAY_MS = 20L * 3600 * 1000      // un informe cada ~día (margen para quien abre la app a la misma hora)

    private fun prefs(c: Context) = c.getSharedPreferences("wttc_stats", Context.MODE_PRIVATE)

    /** null = aún no se ha preguntado */
    fun consent(c: Context): Boolean? = prefs(c).let { if (it.contains("consent")) it.getBoolean("consent", false) else null }

    fun setConsent(c: Context, on: Boolean) {
        val p = prefs(c)
        val oldId = p.getString("id", null)
        if (on) {
            if (oldId == null) p.edit().putString("id", UUID.randomUUID().toString()).apply()
            p.edit().putBoolean("consent", true).apply()
        } else {
            // Al retirar el permiso: se pide borrar los datos de esta instalación y se olvida el identificador
            p.edit().putBoolean("consent", false).remove("id").remove("last").remove("pending").apply()
            if (oldId != null) post(JSONObject().put("id", oldId).put("borrar", true)) {}
        }
    }

    // ---------- contadores (se acumulan hasta el próximo envío aceptado) ----------
    private fun pending(c: Context): JSONObject =
        runCatching { JSONObject(prefs(c).getString("pending", "{}") ?: "{}") }.getOrDefault(JSONObject())

    private fun savePending(c: Context, j: JSONObject) = prefs(c).edit().putString("pending", j.toString()).apply()

    fun count(c: Context, key: String) {
        if (consent(c) != true) return
        val j = pending(c)
        j.put(key, j.optInt(key) + 1)
        savePending(c, j)
    }

    fun countErrors(c: Context, codes: Collection<String>) {
        if (consent(c) != true || codes.isEmpty()) return
        val j = pending(c)
        val e = j.optJSONObject("errors") ?: JSONObject()
        for (code in codes) e.put(code, e.optInt(code) + 1)
        j.put("errors", e)
        savePending(c, j)
    }

    fun setFirmware(c: Context, ver: String) {
        if (Regex("^[0-9]{1,3}(\\.[0-9]{1,4}){0,3}$").matches(ver)) prefs(c).edit().putString("fw", ver).apply()
    }

    // ---------- envío ----------
    /** Envía el informe del día si hay permiso y ya toca. No bloquea: va en otro hilo. */
    fun maybeSend(c: Context) {
        val p = prefs(c)
        if (consent(c) != true) return
        val id = p.getString("id", null) ?: return
        if (System.currentTimeMillis() - p.getLong("last", 0) < DAY_MS) return
        val pend = pending(c)
        val j = JSONObject()
            .put("id", id)
            .put("app", runCatching { c.packageManager.getPackageInfo(c.packageName, 0).versionName }.getOrNull() ?: "")
            .put("fw", p.getString("fw", "") ?: "")
            .put("sdk", Build.VERSION.SDK_INT)
            .put("kind", kind(c))
            .put("country", Locale.getDefault().country.takeIf { it.length == 2 } ?: "")
            .put("starts_app", pend.optInt("starts_app"))
            .put("starts_prog", pend.optInt("starts_prog"))
            .put("self_stops", pend.optInt("self_stops"))
        pend.optJSONObject("errors")?.let { j.put("errors", it) }
        post(j) { resp ->
            val r = runCatching { JSONObject(resp) }.getOrNull() ?: return@post
            if (!r.optBoolean("ok")) return@post
            val e = p.edit().putLong("last", System.currentTimeMillis())
            if (!r.optBoolean("dup")) e.remove("pending")      // aceptado: los contadores empiezan de cero
            e.apply()
        }
    }

    private fun post(j: JSONObject, done: (String) -> Unit) {
        thread(name = "wttc-stats", isDaemon = true) {
            runCatching {
                val con = URL(URL_STATS).openConnection() as HttpURLConnection
                con.requestMethod = "POST"
                con.connectTimeout = 8000
                con.readTimeout = 8000
                con.doOutput = true
                con.setRequestProperty("Content-Type", "application/json")
                con.outputStream.use { it.write(j.toString().toByteArray(Charsets.UTF_8)) }
                val body = (if (con.responseCode in 200..299) con.inputStream else con.errorStream)?.bufferedReader()?.use { it.readText() } ?: ""
                con.disconnect()
                done(body)
            }
        }
    }

    /** Móvil, tablet o radio de coche (aproximado). */
    fun kind(c: Context): String {
        val ui = c.getSystemService(UiModeManager::class.java)
        if (ui?.currentModeType == Configuration.UI_MODE_TYPE_CAR) return "radio"
        val dm = c.resources.displayMetrics
        val aspect = maxOf(dm.widthPixels, dm.heightPixels).toFloat() / minOf(dm.widthPixels, dm.heightPixels).coerceAtLeast(1)
        val phone = c.packageManager.hasSystemFeature(PackageManager.FEATURE_TELEPHONY)
        // Las radios: sin telefonía, apaisadas y alargadas (1024×600, 1280×720…); las tablets suelen ser 16:10 o 4:3
        if (!phone && aspect >= 1.65f && dm.widthPixels > dm.heightPixels) return "radio"
        return if (c.resources.configuration.smallestScreenWidthDp >= 600) "tablet" else "movil"
    }
}
