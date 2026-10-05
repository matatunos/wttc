// rollback.cpp — Vuelta atrás de las actualizaciones sin cable (OTA). Generado con Claude (Anthropic).
//
// Tras instalar un programa nuevo, el núcleo ESP32 de Arduino lo da por bueno nada más arrancar salvo que
// verifyRollbackLater() devuelva true. Así lo confirma WTTC.ino (otaConfirm) cuando lleva un minuto funcionando;
// si se cuelga o se reinicia antes, el cargador de arranque vuelve solo al programa anterior.
//
// Va en un .cpp aparte porque el núcleo la declara en C (extern "C") y el preprocesador de Arduino, que genera
// declaraciones para las funciones del .ino, le daría enlace de C++ y no compilaría.
extern "C" bool verifyRollbackLater() { return true; }
