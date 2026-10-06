package es.favala.wttc

import android.Manifest
import android.annotation.SuppressLint
import android.app.Activity
import android.content.pm.PackageManager
import android.os.Build
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.widget.TextView
import org.json.JSONObject

/**
 * Acceso rápido: la abren el botón «Webasto» de los ajustes rápidos de Android (QuickTile) y el widget (QuickWidget).
 *
 * Código generado íntegramente con Claude (Anthropic).
 *
 * Es una ventana pequeña que se conecta a la placa emparejada, mira si está calentando y la apaga, o la enciende con
 * la duración elegida en la app (Ajustes de la app → Acceso rápido; 30 min si no se ha tocado). Dice el resultado y
 * se cierra sola. Va como actividad (no en segundo plano) porque Android no deja conectar por Bluetooth desde un
 * servicio sin una notificación permanente; así basta con el permiso que ya tiene la app.
 */
@SuppressLint("MissingPermission")
class QuickActivity : Activity(), BleLink.Listener {
    private lateinit var link: BleLink
    private lateinit var msg: TextView
    private val main = Handler(Looper.getMainLooper())
    private var sent = false          // ya se mandó la orden
    private var closing = false       // ya se está cerrando
    private var dur = 30

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        val pad = (24 * resources.displayMetrics.density).toInt()
        msg = TextView(this).apply { textSize = 17f; setPadding(pad, pad, pad, pad); text = getString(R.string.q_connecting) }
        setContentView(msg)
        setFinishOnTouchOutside(true)
        dur = getSharedPreferences("wttc", MODE_PRIVATE).getInt("qdur", 30)
        link = BleLink(this, this)
        val perm = Build.VERSION.SDK_INT < 31 || checkSelfPermission(Manifest.permission.BLUETOOTH_CONNECT) == PackageManager.PERMISSION_GRANTED
        if (!perm || link.savedAddress == null || !link.isPaired()) { done(getString(R.string.q_not_paired)); return }
        link.start()
        main.postDelayed({ if (!sent) done(getString(R.string.q_err)) }, 20000)   // fuera de alcance
    }

    override fun onDestroy() {
        main.removeCallbacksAndMessages(null)
        link.stop()
        super.onDestroy()
    }

    // Conectada: se pide el estado para saber si encender o apagar
    override fun onLink(state: BleLink.State, detail: String) {
        when (state) {
            BleLink.State.CONNECTED -> link.refresh()
            BleLink.State.NO_BLUETOOTH -> done(getString(R.string.link_no_bt))
            BleLink.State.NOT_PAIRED -> done(getString(R.string.q_not_paired))
            else -> {}
        }
    }

    // Con el primer estado: si calienta (o hay termostato en marcha), apagar; si no, encender
    override fun onDeviceState(j: JSONObject) {
        if (sent || closing) return
        sent = true
        val on = j.optInt("on") == 1 || j.optInt("tg") > 0
        msg.text = getString(if (on) R.string.btn_turning_off else R.string.btn_turning_on)
        link.send(if (on) "off" else "on $dur")
    }

    override fun onResponse(cmd: String, data: String) {
        if (cmd != "on" && cmd != "off") return
        done(when {
            data.startsWith("err") -> data.removePrefix("err").trim()
            cmd == "on" -> getString(R.string.q_on, "$dur min")
            else -> getString(R.string.q_off)
        })
    }

    // Enseña el resultado y se cierra al cabo de un momento
    private fun done(t: String) {
        if (closing) return
        closing = true
        msg.text = t
        main.postDelayed({ finish() }, 2000)
    }
}
