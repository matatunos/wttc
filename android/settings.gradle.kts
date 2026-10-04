// settings.gradle.kts — Proyecto Gradle de la app Android WTTC (un solo módulo, :app).
// Código generado íntegramente con Claude (Anthropic).
// Repositorios: google() para el plugin de Android y mavenCentral() para Kotlin. No hay más dependencias.
pluginManagement {
    repositories {
        google()
        mavenCentral()
        gradlePluginPortal()
    }
}
dependencyResolutionManagement {
    repositoriesMode.set(RepositoriesMode.FAIL_ON_PROJECT_REPOS)
    repositories {
        google()
        mavenCentral()
    }
}
rootProject.name = "WTTC"
include(":app")
