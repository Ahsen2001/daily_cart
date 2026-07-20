plugins {
    id("com.android.application")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

android {
    namespace = "com.dailycart.mobile"
    compileSdk = flutter.compileSdkVersion
    ndkVersion = flutter.ndkVersion

    flavorDimensions += "role"

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
        isCoreLibraryDesugaringEnabled = true
    }

    buildFeatures {
        resValues = true
    }

    defaultConfig {
        applicationId = "com.dailycart.customer"
        // You can update the following values to match your application needs.
        // For more information, see: https://flutter.dev/to/review-gradle-config.
        minSdk = flutter.minSdkVersion
        targetSdk = flutter.targetSdkVersion
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }

    productFlavors {
        create("customer") {
            dimension = "role"
            applicationId = "com.dailycart.customer"
            resValue("string", "app_name", "DailyCart Customer")
            resValue("string", "notification_channel_id", "dailycart_customer")
            manifestPlaceholders["deepLinkScheme"] = "dailycart-customer"
        }

        create("vendor") {
            dimension = "role"
            applicationId = "com.dailycart.vendor"
            resValue("string", "app_name", "DailyCart Vendor")
            resValue("string", "notification_channel_id", "dailycart_vendor")
            manifestPlaceholders["deepLinkScheme"] = "dailycart-vendor"
        }

        create("rider") {
            dimension = "role"
            applicationId = "com.dailycart.rider"
            resValue("string", "app_name", "DailyCart Rider")
            resValue("string", "notification_channel_id", "dailycart_rider")
            manifestPlaceholders["deepLinkScheme"] = "dailycart-rider"
        }
    }

    buildTypes {
        release {
            // Replace this with the production signing configuration before publishing.
            signingConfig = signingConfigs.getByName("debug")
        }
    }
}

kotlin {
    compilerOptions {
        jvmTarget = org.jetbrains.kotlin.gradle.dsl.JvmTarget.JVM_17
    }
}

flutter {
    source = "../.."
}

dependencies {
    coreLibraryDesugaring("com.android.tools:desugar_jdk_libs:2.1.5")
}
