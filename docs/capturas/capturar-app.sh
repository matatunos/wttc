#!/bin/bash
# capturar-app.sh — Capturas de la app Android en un emulador, en modo demostración (placa simulada, con programas de ejemplo).
# Código generado íntegramente con Claude (Anthropic). Lo ejecuta .github/workflows/capturas.yml dentro del emulador.
#   app-estado.png    — móvil: calentando (estado, duración, botón)
#   app-programas.png — móvil: más abajo (programas)
#   app-radio.png     — pantalla apaisada de 1280×720, como una radio de coche
set -euo pipefail
APK=android/app/build/outputs/apk/debug/app-debug.apk
OUT=docs/capturas
ACT=es.favala.wttc/.MainActivity
# demo = placa simulada · demo_on = empezar encendida · capturas = sin ventana de estadísticas ni permisos
EXTRAS="--ez demo true --ez demo_on true --ez capturas true"

adb install -r "$APK"
# La app sigue el idioma del móvil (el emulador está en inglés): las capturas del README, en español
adb shell cmd locale set-app-locales es.favala.wttc --locales es-ES
adb shell settings put system screen_off_timeout 1800000
adb shell input keyevent KEYCODE_WAKEUP
adb shell am start -n "$ACT" $EXTRAS
sleep 10
adb exec-out screencap -p > "$OUT/app-estado.png"

# Desplazar hacia abajo para ver los programas
adb shell input swipe 540 1900 540 700 600
sleep 3
adb exec-out screencap -p > "$OUT/app-programas.png"

# Como una radio de coche: 1280×720 apaisada, densidad baja
adb shell am force-stop es.favala.wttc
adb shell wm size 1280x720
adb shell wm density 160
adb shell settings put system accelerometer_rotation 0
adb shell settings put system user_rotation 0      # con 1280×720 la pantalla ya es apaisada: sin girar
sleep 3
adb shell am start -n "$ACT" $EXTRAS
sleep 10
adb exec-out screencap -p > "$OUT/app-radio.png"
adb shell wm size reset
adb shell wm density reset
echo "Capturas de la app hechas"
