// app/build.gradle.kts — Módulo de la app WTTC.
// Código generado íntegramente con Claude (Anthropic).
//  - Sin dependencias: solo el framework de Android y la biblioteca estándar de Kotlin (APK de ~650 KB).
//  - minSdk 26 (Android 8): incluye la mayoría de radios Android de coche.
//  - versionName y versionCode los pone la CI (fichero VERSION y número de ejecución); en local, 0.0.0 y 1.
//  - La firma de release sale de los secretos del repo (ver .github/workflows/android.yml).
plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

// Firma: en GitHub Actions viene de secretos (WTTC_KEYSTORE_B64 y compañía); sin ellos se firma con la clave de depuración
val ksFile = System.getenv("WTTC_KEYSTORE_FILE")

android {
    namespace = "es.favala.wttc"
    compileSdk = 34

    defaultConfig {
        applicationId = "es.favala.wttc"
        minSdk = 26
        targetSdk = 34
        versionCode = (System.getenv("WTTC_VERSION_CODE") ?: "1").toInt()
        versionName = System.getenv("WTTC_VERSION_NAME") ?: "0.0.0"
    }

    signingConfigs {
        if (ksFile != null) {
            create("release") {
                storeFile = file(ksFile)
                storePassword = System.getenv("WTTC_KEYSTORE_PASSWORD")
                keyAlias = System.getenv("WTTC_KEY_ALIAS")
                keyPassword = System.getenv("WTTC_KEY_PASSWORD")
            }
        }
    }

    buildTypes {
        release {
            isMinifyEnabled = false
            signingConfig = if (ksFile != null) signingConfigs.getByName("release") else signingConfigs.getByName("debug")
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }
    kotlinOptions {
        jvmTarget = "17"
    }
    lint {
        // Los permisos de Bluetooth se piden en tiempo de ejecución; lint no siempre lo ve
        checkReleaseBuilds = false
        abortOnError = false
    }
}
