package es.favala.wttc

import android.app.PendingIntent
import android.content.Intent
import android.os.Build
import android.service.quicksettings.Tile
import android.service.quicksettings.TileService

/**
 * Botón «Webasto» de los ajustes rápidos de Android: abre QuickActivity, que enciende o apaga.
 * Código generado íntegramente con Claude (Anthropic).
 * No muestra el estado (para eso habría que estar conectado siempre por Bluetooth, gastando batería en las dos partes).
 */
class QuickTile : TileService() {
    override fun onStartListening() {
        qsTile?.apply { state = Tile.STATE_INACTIVE; label = getString(R.string.tile_label); updateTile() }
    }

    override fun onClick() {
        val i = Intent(this, QuickActivity::class.java).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
        if (Build.VERSION.SDK_INT >= 34)
            startActivityAndCollapse(PendingIntent.getActivity(this, 0, i, PendingIntent.FLAG_IMMUTABLE or PendingIntent.FLAG_UPDATE_CURRENT))
        else @Suppress("DEPRECATION") startActivityAndCollapse(i)
    }
}
