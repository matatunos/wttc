package es.favala.wttc

import android.app.PendingIntent
import android.appwidget.AppWidgetManager
import android.appwidget.AppWidgetProvider
import android.content.Context
import android.content.Intent
import android.widget.RemoteViews

/**
 * Widget «Webasto» de la pantalla de inicio: al pulsarlo abre QuickActivity, que enciende o apaga.
 * Código generado íntegramente con Claude (Anthropic).
 */
class QuickWidget : AppWidgetProvider() {
    override fun onUpdate(ctx: Context, mgr: AppWidgetManager, ids: IntArray) {
        val pi = PendingIntent.getActivity(ctx, 0, Intent(ctx, QuickActivity::class.java).addFlags(Intent.FLAG_ACTIVITY_NEW_TASK),
            PendingIntent.FLAG_IMMUTABLE or PendingIntent.FLAG_UPDATE_CURRENT)
        for (id in ids) {
            val v = RemoteViews(ctx.packageName, R.layout.widget)
            v.setOnClickPendingIntent(R.id.widget_root, pi)
            mgr.updateAppWidget(id, v)
        }
    }
}
