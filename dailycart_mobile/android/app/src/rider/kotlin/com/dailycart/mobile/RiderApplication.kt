package com.dailycart.mobile

import android.app.Application
import android.app.NotificationChannel
import android.app.NotificationManager
import android.os.Build

class RiderApplication : Application() {
    override fun onCreate() {
        super.onCreate()

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                TRACKING_CHANNEL_ID,
                "Active delivery tracking",
                NotificationManager.IMPORTANCE_LOW,
            ).apply {
                description =
                    "Shows when DailyCart is sharing location for an active delivery."
                setSound(null, null)
                enableVibration(false)
                setShowBadge(false)
            }

            getSystemService(NotificationManager::class.java)
                .createNotificationChannel(channel)
        }
    }

    private companion object {
        const val TRACKING_CHANNEL_ID = "dailycart_active_delivery_tracking"
    }
}
