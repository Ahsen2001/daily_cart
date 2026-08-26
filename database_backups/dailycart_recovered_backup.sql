-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: dailycart_recovered
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `activity_logs_action_index` (`action`),
  KEY `activity_logs_module_index` (`module`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT 'Home',
  `recipient_name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `district` varchar(255) NOT NULL,
  `province` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `formatted_address` varchar(500) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_customer_id_is_default_index` (`customer_id`,`is_default`),
  KEY `addresses_province_index` (`province`),
  CONSTRAINT `addresses_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (1,1,'Home','Rifka','076 236 3232','Kaaddu marathady Road','Semmanodai - 7','Valaichenai','Batticaloa','Eastern','30400',NULL,NULL,'456 customer Road, Batticaloa, Batticaloa',1,'2026-08-26 11:15:27','2026-08-26 11:24:48');
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `access_level` enum('admin','super_admin') NOT NULL DEFAULT 'admin',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_user_id_unique` (`user_id`),
  KEY `admins_access_level_index` (`access_level`),
  KEY `admins_status_index` (`status`),
  CONSTRAINT `admins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,1,NULL,'super_admin','active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(2,2,NULL,'admin','active','2026-08-26 11:15:23','2026-08-26 11:15:23',NULL);
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `advertisements`
--

DROP TABLE IF EXISTS `advertisements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `advertisements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `link_type` enum('product','category','vendor','url') NOT NULL DEFAULT 'url',
  `link_id` bigint(20) unsigned DEFAULT NULL,
  `position` enum('homepage_slider','homepage_banner','category_banner','sidebar','product_page') NOT NULL DEFAULT 'homepage_banner',
  `target_url` varchar(255) DEFAULT NULL,
  `placement` enum('home_banner','category_banner','sidebar','product_page') NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `status` enum('pending','active','paused','expired','rejected') NOT NULL DEFAULT 'pending',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `views_count` int(10) unsigned NOT NULL DEFAULT 0,
  `clicks_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `advertisements_vendor_id_foreign` (`vendor_id`),
  KEY `advertisements_placement_index` (`placement`),
  KEY `advertisements_starts_at_index` (`starts_at`),
  KEY `advertisements_ends_at_index` (`ends_at`),
  KEY `advertisements_status_index` (`status`),
  KEY `advertisements_created_by_foreign` (`created_by`),
  KEY `advertisements_link_type_index` (`link_type`),
  KEY `advertisements_link_id_index` (`link_id`),
  KEY `advertisements_position_index` (`position`),
  CONSTRAINT `advertisements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `advertisements_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `advertisements`
--

LOCK TABLES `advertisements` WRITE;
/*!40000 ALTER TABLE `advertisements` DISABLE KEYS */;
/*!40000 ALTER TABLE `advertisements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_integration_logs`
--

DROP TABLE IF EXISTS `api_integration_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `api_integration_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `provider` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `reference` varchar(255) DEFAULT NULL,
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_payload`)),
  `response_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_payload`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `api_integration_logs_provider_index` (`provider`),
  KEY `api_integration_logs_action_index` (`action`),
  KEY `api_integration_logs_status_index` (`status`),
  KEY `api_integration_logs_reference_index` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_integration_logs`
--

LOCK TABLES `api_integration_logs` WRITE;
/*!40000 ALTER TABLE `api_integration_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_integration_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brands` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `brands_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_variant_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_product_id_foreign` (`product_id`),
  KEY `cart_items_product_variant_id_foreign` (`product_variant_id`),
  KEY `cart_items_cart_id_product_id_index` (`cart_id`,`product_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `status` enum('active','converted','abandoned') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_customer_id_foreign` (`customer_id`),
  KEY `carts_status_index` (`status`),
  CONSTRAINT `carts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
INSERT INTO `carts` VALUES (1,1,'active','2026-08-26 11:23:18','2026-08-26 11:23:18',NULL);
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  KEY `categories_status_index` (`status`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,NULL,'Grocery','grocery','Grocery products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(2,NULL,'Vegetables','vegetables','Vegetables products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(3,NULL,'Fruits','fruits','Fruits products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(4,NULL,'Household','household','Household products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(5,NULL,'Powder Products','powder-products','Powder Products products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(6,NULL,'Beverages','beverages','Beverages products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(7,NULL,'Frozen Food','frozen-food','Frozen Food products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(8,NULL,'Bakery','bakery','Bakery products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(9,NULL,'Pharmacy','pharmacy','Pharmacy products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(10,NULL,'Baby Care','baby-care','Baby Care products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(11,NULL,'Personal Care','personal-care','Personal Care products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(12,NULL,'Pet Supplies','pet-supplies','Pet Supplies products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL),(13,NULL,'Office Essentials','office-essentials','Office Essentials products for DailyCart customers.',NULL,'active','2026-08-26 11:15:22','2026-08-26 11:15:22',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `district_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cities_district_id_name_unique` (`district_id`,`name`),
  KEY `cities_status_index` (`status`),
  CONSTRAINT `cities_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cities`
--

LOCK TABLES `cities` WRITE;
/*!40000 ALTER TABLE `cities` DISABLE KEYS */;
/*!40000 ALTER TABLE `cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon_redemptions`
--

DROP TABLE IF EXISTS `coupon_redemptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_redemptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupon_redemptions_coupon_id_order_id_unique` (`coupon_id`,`order_id`),
  KEY `coupon_redemptions_customer_id_foreign` (`customer_id`),
  KEY `coupon_redemptions_order_id_foreign` (`order_id`),
  KEY `coupon_redemptions_coupon_id_customer_id_index` (`coupon_id`,`customer_id`),
  CONSTRAINT `coupon_redemptions_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_redemptions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_redemptions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon_redemptions`
--

LOCK TABLES `coupon_redemptions` WRITE;
/*!40000 ALTER TABLE `coupon_redemptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupon_redemptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `code` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('fixed_amount','percentage','free_delivery') NOT NULL DEFAULT 'fixed_amount',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `type` enum('fixed','percentage') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `minimum_order_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `maximum_discount_amount` decimal(10,2) DEFAULT NULL,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(10) unsigned DEFAULT NULL,
  `used_count` int(10) unsigned NOT NULL DEFAULT 0,
  `per_customer_limit` int(10) unsigned DEFAULT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`),
  KEY `coupons_starts_at_expires_at_index` (`starts_at`,`expires_at`),
  KEY `coupons_status_index` (`status`),
  KEY `coupons_vendor_id_foreign` (`vendor_id`),
  KEY `coupons_discount_type_index` (`discount_type`),
  CONSTRAINT `coupons_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `status` enum('active','inactive','blocked') NOT NULL DEFAULT 'active',
  `wallet_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_user_id_unique` (`user_id`),
  KEY `customers_status_index` (`status`),
  CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,4,'Rifkabanu','Customer','076 236 3232','active',0.00,'2026-08-26 11:15:26','2026-08-26 11:24:48',NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deliveries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `rider_id` bigint(20) unsigned DEFAULT NULL,
  `pickup_address` text NOT NULL,
  `delivery_address` text NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `picked_up_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `rider_payout` decimal(10,2) DEFAULT NULL,
  `failed_reason` text DEFAULT NULL,
  `status` enum('pending','assigned','accepted','picked_up','on_the_way','delivered','failed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `deliveries_order_id_unique` (`order_id`),
  KEY `deliveries_rider_id_status_index` (`rider_id`,`status`),
  KEY `deliveries_scheduled_at_index` (`scheduled_at`),
  KEY `deliveries_status_index` (`status`),
  CONSTRAINT `deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `deliveries_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_fees`
--

DROP TABLE IF EXISTS `delivery_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_fees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `district` varchar(255) NOT NULL,
  `base_fee` decimal(10,2) NOT NULL,
  `per_km_fee` decimal(10,2) NOT NULL,
  `minimum_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `free_delivery_limit` decimal(10,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `delivery_fees_district_unique` (`district`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_fees`
--

LOCK TABLES `delivery_fees` WRITE;
/*!40000 ALTER TABLE `delivery_fees` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_fees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_holidays`
--

DROP TABLE IF EXISTS `delivery_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_holidays` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `extra_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `starts_on` date NOT NULL,
  `ends_on` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_holidays_starts_on_index` (`starts_on`),
  KEY `delivery_holidays_ends_on_index` (`ends_on`),
  KEY `delivery_holidays_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_holidays`
--

LOCK TABLES `delivery_holidays` WRITE;
/*!40000 ALTER TABLE `delivery_holidays` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_pricing_rules`
--

DROP TABLE IF EXISTS `delivery_pricing_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_pricing_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` bigint(20) unsigned DEFAULT NULL,
  `scope` enum('zone','district','province','default') NOT NULL DEFAULT 'default',
  `district` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `base_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `per_km_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `minimum_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `free_delivery_threshold` decimal(10,2) DEFAULT NULL,
  `maximum_distance_km` decimal(8,2) DEFAULT NULL,
  `estimated_delivery_minutes` int(10) unsigned DEFAULT NULL,
  `priority` int(10) unsigned NOT NULL DEFAULT 100,
  `starts_on` date DEFAULT NULL,
  `ends_on` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_pricing_rules_zone_id_foreign` (`zone_id`),
  KEY `delivery_pricing_rules_scope_index` (`scope`),
  KEY `delivery_pricing_rules_district_index` (`district`),
  KEY `delivery_pricing_rules_province_index` (`province`),
  KEY `delivery_pricing_rules_priority_index` (`priority`),
  KEY `delivery_pricing_rules_starts_on_index` (`starts_on`),
  KEY `delivery_pricing_rules_ends_on_index` (`ends_on`),
  KEY `delivery_pricing_rules_status_index` (`status`),
  CONSTRAINT `delivery_pricing_rules_zone_id_foreign` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_pricing_rules`
--

LOCK TABLES `delivery_pricing_rules` WRITE;
/*!40000 ALTER TABLE `delivery_pricing_rules` DISABLE KEYS */;
INSERT INTO `delivery_pricing_rules` VALUES (1,1,'zone','Batticaloa','Eastern',50.00,5.00,250.00,NULL,NULL,NULL,100,'2026-08-26','2027-08-26','active','2026-08-26 11:30:59','2026-08-26 11:30:59'),(2,2,'zone','Batticaloa','Eastern',50.00,5.00,250.00,NULL,NULL,NULL,100,'2026-08-26','2027-08-26','active','2026-08-26 11:31:44','2026-08-26 11:31:44');
/*!40000 ALTER TABLE `delivery_pricing_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_promotions`
--

DROP TABLE IF EXISTS `delivery_promotions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_promotions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('free_delivery','percentage_discount','vendor_sponsored') NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `minimum_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `starts_on` date DEFAULT NULL,
  `ends_on` date DEFAULT NULL,
  `priority` int(10) unsigned NOT NULL DEFAULT 100,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_promotions_vendor_id_foreign` (`vendor_id`),
  KEY `delivery_promotions_starts_on_index` (`starts_on`),
  KEY `delivery_promotions_ends_on_index` (`ends_on`),
  KEY `delivery_promotions_status_index` (`status`),
  CONSTRAINT `delivery_promotions_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_promotions`
--

LOCK TABLES `delivery_promotions` WRITE;
/*!40000 ALTER TABLE `delivery_promotions` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_promotions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_proofs`
--

DROP TABLE IF EXISTS `delivery_proofs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_proofs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_id` bigint(20) unsigned NOT NULL,
  `proof_image` varchar(255) DEFAULT NULL,
  `customer_signature` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `submitted_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_proofs_delivery_id_foreign` (`delivery_id`),
  CONSTRAINT `delivery_proofs_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_proofs`
--

LOCK TABLES `delivery_proofs` WRITE;
/*!40000 ALTER TABLE `delivery_proofs` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_proofs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_rule_histories`
--

DROP TABLE IF EXISTS `delivery_rule_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_rule_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_pricing_rule_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delivery_rule_histories_delivery_pricing_rule_id_foreign` (`delivery_pricing_rule_id`),
  KEY `delivery_rule_histories_user_id_foreign` (`user_id`),
  CONSTRAINT `delivery_rule_histories_delivery_pricing_rule_id_foreign` FOREIGN KEY (`delivery_pricing_rule_id`) REFERENCES `delivery_pricing_rules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `delivery_rule_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_rule_histories`
--

LOCK TABLES `delivery_rule_histories` WRITE;
/*!40000 ALTER TABLE `delivery_rule_histories` DISABLE KEYS */;
INSERT INTO `delivery_rule_histories` VALUES (1,1,1,'created','{\"zone_id\":\"1\",\"scope\":\"zone\",\"district\":\"Batticaloa\",\"province\":\"Eastern\",\"base_fee\":\"50\",\"per_km_fee\":\"5\",\"minimum_order\":\"250\",\"free_delivery_threshold\":null,\"maximum_distance_km\":null,\"estimated_delivery_minutes\":null,\"priority\":\"100\",\"starts_on\":\"2026-08-26 00:00:00\",\"ends_on\":\"2027-08-26 00:00:00\",\"status\":\"active\",\"updated_at\":\"2026-08-26 17:00:59\",\"created_at\":\"2026-08-26 17:00:59\",\"id\":1}','2026-08-26 11:30:59','2026-08-26 11:30:59'),(2,2,1,'created','{\"zone_id\":\"2\",\"scope\":\"zone\",\"district\":\"Batticaloa\",\"province\":\"Eastern\",\"base_fee\":\"50\",\"per_km_fee\":\"5\",\"minimum_order\":\"250\",\"free_delivery_threshold\":null,\"maximum_distance_km\":null,\"estimated_delivery_minutes\":null,\"priority\":\"100\",\"starts_on\":\"2026-08-26 00:00:00\",\"ends_on\":\"2027-08-26 00:00:00\",\"status\":\"active\",\"updated_at\":\"2026-08-26 17:01:44\",\"created_at\":\"2026-08-26 17:01:44\",\"id\":2}','2026-08-26 11:31:44','2026-08-26 11:31:44');
/*!40000 ALTER TABLE `delivery_rule_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delivery_schedules`
--

DROP TABLE IF EXISTS `delivery_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `delivery_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `scheduled_date` date NOT NULL,
  `scheduled_time` time DEFAULT NULL,
  `delivery_window` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_schedules`
--

LOCK TABLES `delivery_schedules` WRITE;
/*!40000 ALTER TABLE `delivery_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `device_tokens`
--

DROP TABLE IF EXISTS `device_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `device_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `app_role` varchar(20) NOT NULL DEFAULT 'customer',
  `token` varchar(255) NOT NULL,
  `platform` varchar(30) NOT NULL,
  `app_version` varchar(40) DEFAULT NULL,
  `refreshed_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_tokens_token_unique` (`token`),
  KEY `device_tokens_user_id_platform_index` (`user_id`,`platform`),
  KEY `device_tokens_delivery_index` (`user_id`,`app_role`,`revoked_at`),
  KEY `device_tokens_device_index` (`user_id`,`device_id`,`app_role`),
  CONSTRAINT `device_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `device_tokens`
--

LOCK TABLES `device_tokens` WRITE;
/*!40000 ALTER TABLE `device_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `device_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `districts`
--

DROP TABLE IF EXISTS `districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `districts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `districts_name_unique` (`name`),
  KEY `districts_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `districts`
--

LOCK TABLES `districts` WRITE;
/*!40000 ALTER TABLE `districts` DISABLE KEYS */;
/*!40000 ALTER TABLE `districts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_otps`
--

DROP TABLE IF EXISTS `email_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_otps` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `purpose` enum('email_verification','login','password_reset') NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_otps_user_id_foreign` (`user_id`),
  KEY `email_otps_email_purpose_expires_at_index` (`email`,`purpose`,`expires_at`),
  KEY `email_otps_email_index` (`email`),
  KEY `email_otps_purpose_index` (`purpose`),
  KEY `email_otps_expires_at_index` (`expires_at`),
  CONSTRAINT `email_otps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_otps`
--

LOCK TABLES `email_otps` WRITE;
/*!40000 ALTER TABLE `email_otps` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorite_vendors`
--

DROP TABLE IF EXISTS `favorite_vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favorite_vendors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `vendor_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorite_vendors_customer_id_vendor_id_unique` (`customer_id`,`vendor_id`),
  KEY `favorite_vendors_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `favorite_vendors_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorite_vendors_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorite_vendors`
--

LOCK TABLES `favorite_vendors` WRITE;
/*!40000 ALTER TABLE `favorite_vendors` DISABLE KEYS */;
/*!40000 ALTER TABLE `favorite_vendors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `free_delivery_rules`
--

DROP TABLE IF EXISTS `free_delivery_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `free_delivery_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `condition_type` enum('subtotal','first_order','weekend','coupon','premium_membership') NOT NULL,
  `minimum_order` decimal(10,2) NOT NULL DEFAULT 0.00,
  `coupon_code` varchar(255) DEFAULT NULL,
  `starts_on` date DEFAULT NULL,
  `ends_on` date DEFAULT NULL,
  `priority` int(10) unsigned NOT NULL DEFAULT 100,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `free_delivery_rules_starts_on_index` (`starts_on`),
  KEY `free_delivery_rules_ends_on_index` (`ends_on`),
  KEY `free_delivery_rules_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `free_delivery_rules`
--

LOCK TABLES `free_delivery_rules` WRITE;
/*!40000 ALTER TABLE `free_delivery_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `free_delivery_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_variant_id` bigint(20) unsigned DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `low_stock_threshold` int(10) unsigned NOT NULL DEFAULT 5,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_product_id_product_variant_id_unique` (`product_id`,`product_variant_id`),
  KEY `inventory_product_variant_id_foreign` (`product_variant_id`),
  KEY `inventory_product_id_quantity_index` (`product_id`,`quantity`),
  CONSTRAINT `inventory_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory`
--

LOCK TABLES `inventory` WRITE;
/*!40000 ALTER TABLE `inventory` DISABLE KEYS */;
INSERT INTO `inventory` VALUES (1,2,NULL,60,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(2,2,1,60,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(3,3,NULL,55,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(4,3,2,55,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(5,4,NULL,80,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(6,4,3,80,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(7,5,NULL,90,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(8,5,4,90,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(9,6,NULL,70,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(10,6,5,70,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(11,7,NULL,45,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(12,7,6,45,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(13,8,NULL,50,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(14,8,7,50,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(15,9,NULL,40,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(16,9,8,40,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(17,10,NULL,100,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(18,10,9,100,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(19,11,NULL,65,10,'2026-08-26 11:15:28','2026-08-26 11:15:28'),(20,11,10,65,10,'2026-08-26 11:15:28','2026-08-26 11:15:28');
/*!40000 ALTER TABLE `inventory` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_points`
--

DROP TABLE IF EXISTS `loyalty_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loyalty_points` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `points` int(11) NOT NULL,
  `type` enum('earned','redeemed','reversed','adjusted','expired') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `balance_after` int(11) NOT NULL DEFAULT 0,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loyalty_points_order_id_foreign` (`order_id`),
  KEY `loyalty_points_customer_id_type_index` (`customer_id`,`type`),
  KEY `loyalty_points_type_index` (`type`),
  KEY `loyalty_points_expires_at_index` (`expires_at`),
  CONSTRAINT `loyalty_points_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loyalty_points_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_points`
--

LOCK TABLES `loyalty_points` WRITE;
/*!40000 ALTER TABLE `loyalty_points` DISABLE KEYS */;
/*!40000 ALTER TABLE `loyalty_points` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loyalty_settings`
--

DROP TABLE IF EXISTS `loyalty_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loyalty_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `spend_amount_per_point` int(10) unsigned NOT NULL DEFAULT 100,
  `redemption_value_per_point` decimal(10,2) NOT NULL DEFAULT 1.00,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `loyalty_settings_updated_by_foreign` (`updated_by`),
  KEY `loyalty_settings_status_index` (`status`),
  CONSTRAINT `loyalty_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loyalty_settings`
--

LOCK TABLES `loyalty_settings` WRITE;
/*!40000 ALTER TABLE `loyalty_settings` DISABLE KEYS */;
INSERT INTO `loyalty_settings` VALUES (1,100,1.00,'active',NULL,'2026-08-26 11:04:26','2026-08-26 11:04:26');
/*!40000 ALTER TABLE `loyalty_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_05_14_162547_create_permission_tables',1),(5,'2026_05_14_170000_add_dailycart_fields_to_users_table',1),(6,'2026_05_14_170001_create_dailycart_profile_tables',1),(7,'2026_05_14_170002_create_dailycart_catalog_tables',1),(8,'2026_05_14_170003_create_dailycart_cart_order_tables',1),(9,'2026_05_14_170004_create_dailycart_payment_delivery_tables',1),(10,'2026_05_14_170005_create_dailycart_engagement_tables',1),(11,'2026_05_14_170006_normalize_dailycart_money_and_soft_deletes',1),(12,'2026_05_14_170007_add_model_relationship_foreign_keys',1),(13,'2026_05_14_170008_add_product_category_management_fields',1),(14,'2026_05_14_170009_add_checkout_fields_and_statuses',1),(15,'2026_05_14_170010_add_order_delivery_workflow_fields',1),(16,'2026_05_15_090000_add_finance_management_fields',1),(17,'2026_05_15_100000_add_support_review_notification_fields',1),(18,'2026_05_15_110000_add_promotions_coupons_loyalty_fields',1),(19,'2026_05_15_120000_add_subscription_recurring_order_fields',1),(20,'2026_05_17_000000_create_newsletter_subscriptions_table',1),(21,'2026_05_17_010000_add_product_variant_id_to_subscriptions_table',1),(22,'2026_05_17_020000_add_external_api_integration_tables',1),(23,'2026_07_10_000001_create_addresses_table',1),(24,'2026_07_10_000002_create_delivery_schedules_table',1),(25,'2026_07_10_000003_create_brands_table',1),(26,'2026_07_10_000004_create_favorite_vendors_table',1),(27,'2026_07_10_000005_create_order_status_history_table',1),(28,'2026_07_10_000006_create_vendor_wallets_table',1),(29,'2026_07_10_000007_create_delivery_fees_table',1),(30,'2026_07_10_000008_create_settings_table',1),(31,'2026_07_10_000009_create_otp_verifications_table',1),(32,'2026_07_10_000010_create_search_history_table',1),(33,'2026_07_10_000011_create_contact_messages_table',1),(34,'2026_07_10_000012_create_location_tables',1),(35,'2026_07_10_092929_create_personal_access_tokens_table',1),(36,'2026_07_10_999999_update_existing_tables_schema',1),(37,'2026_07_17_000001_add_phone_verified_at_to_users_table',1),(38,'2026_07_17_000001_add_registration_location_to_riders_table',1),(39,'2026_07_18_000001_release_deleted_account_identifiers',1),(40,'2026_07_18_000002_add_rider_payout_to_deliveries_table',1),(41,'2026_07_18_000003_build_delivery_zone_and_pricing_rule_engine',1),(42,'2026_07_18_000004_make_legacy_zone_city_optional',1),(43,'2026_07_18_000005_create_delivery_rule_histories_table',1),(44,'2026_07_18_000006_create_delivery_policy_rule_tables',1),(45,'2026_07_18_000007_migrate_legacy_delivery_fee_rules_to_pricing_engine',1),(46,'2026_07_19_000001_add_delivery_location_fields_to_profiles_and_addresses',1),(47,'2026_07_20_180000_add_password_reset_to_email_otp_purposes',1),(48,'2026_07_20_190000_add_mobile_account_fields',1),(49,'2026_07_20_200000_add_vendor_mobile_finance_fields',1),(50,'2026_07_20_205000_add_accepted_delivery_status',1),(51,'2026_07_20_210000_link_rider_locations_to_deliveries',1),(52,'2026_07_20_220000_complete_notification_infrastructure',1),(53,'2026_07_30_000001_create_notification_delivery_logs',1),(54,'2026_07_30_150000_create_vendor_storefront_tables',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(3,'App\\Models\\User',3),(4,'App\\Models\\User',5),(5,'App\\Models\\User',4);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter_subscriptions`
--

DROP TABLE IF EXISTS `newsletter_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `subscribed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `newsletter_subscriptions_email_unique` (`email`),
  KEY `newsletter_subscriptions_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter_subscriptions`
--

LOCK TABLES `newsletter_subscriptions` WRITE;
/*!40000 ALTER TABLE `newsletter_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_delivery_logs`
--

DROP TABLE IF EXISTS `notification_delivery_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_delivery_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `notification_id` bigint(20) unsigned DEFAULT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `event_id` varchar(64) NOT NULL,
  `channel` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'queued',
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `max_attempts` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `failure_reason` text DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_delivery_event_user_channel_unique` (`event_id`,`user_id`,`channel`),
  KEY `notification_delivery_logs_notification_id_foreign` (`notification_id`),
  KEY `notification_delivery_user_channel_status_index` (`user_id`,`channel`,`status`),
  KEY `notification_delivery_order_created_index` (`order_id`,`created_at`),
  CONSTRAINT `notification_delivery_logs_notification_id_foreign` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notification_delivery_logs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notification_delivery_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_delivery_logs`
--

LOCK TABLES `notification_delivery_logs` WRITE;
/*!40000 ALTER TABLE `notification_delivery_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_delivery_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_preferences`
--

DROP TABLE IF EXISTS `notification_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_preferences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `app_role` varchar(20) NOT NULL,
  `push_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `order_updates` tinyint(1) NOT NULL DEFAULT 1,
  `delivery_updates` tinyint(1) NOT NULL DEFAULT 1,
  `wallet_updates` tinyint(1) NOT NULL DEFAULT 1,
  `support_updates` tinyint(1) NOT NULL DEFAULT 1,
  `promotions` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_preferences_user_id_app_role_unique` (`user_id`,`app_role`),
  CONSTRAINT `notification_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_preferences`
--

LOCK TABLES `notification_preferences` WRITE;
/*!40000 ALTER TABLE `notification_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `event_id` varchar(64) DEFAULT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(255) NOT NULL,
  `app_role` varchar(20) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `deep_link` varchar(2048) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notifications_user_event_unique` (`user_id`,`event_id`),
  KEY `notifications_type_index` (`type`),
  KEY `notifications_read_at_index` (`read_at`),
  KEY `notifications_role_index` (`user_id`,`app_role`,`created_at`),
  KEY `notifications_order_id_foreign` (`order_id`),
  CONSTRAINT `notifications_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_variant_id` bigint(20) unsigned DEFAULT NULL,
  `vendor_id` bigint(20) unsigned NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  KEY `order_items_order_id_product_id_index` (`order_id`,`product_id`),
  KEY `order_items_vendor_id_index` (`vendor_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_history_order_id_foreign` (`order_id`),
  KEY `order_status_history_updated_by_foreign` (`updated_by`),
  CONSTRAINT `order_status_history_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_history_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_history`
--

LOCK TABLES `order_status_history` WRITE;
/*!40000 ALTER TABLE `order_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `vendor_id` bigint(20) unsigned NOT NULL,
  `coupon_id` bigint(20) unsigned DEFAULT NULL,
  `subscription_id` bigint(20) unsigned DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `loyalty_points_redeemed` int(10) unsigned NOT NULL DEFAULT 0,
  `loyalty_discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'LKR',
  `delivery_address` text NOT NULL,
  `delivery_latitude` decimal(10,7) DEFAULT NULL,
  `delivery_longitude` decimal(10,7) DEFAULT NULL,
  `delivery_distance_meters` int(10) unsigned DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `order_status` enum('pending','confirmed','packed','assigned_to_rider','out_for_delivery','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `placed_at` datetime NOT NULL,
  `scheduled_delivery_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `delivery_schedule_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_coupon_id_foreign` (`coupon_id`),
  KEY `orders_customer_id_order_status_index` (`customer_id`,`order_status`),
  KEY `orders_vendor_id_order_status_index` (`vendor_id`,`order_status`),
  KEY `orders_placed_at_index` (`placed_at`),
  KEY `orders_order_status_index` (`order_status`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_scheduled_delivery_at_index` (`scheduled_delivery_at`),
  KEY `orders_subscription_id_order_status_index` (`subscription_id`,`order_status`),
  KEY `orders_delivery_schedule_id_foreign` (`delivery_schedule_id`),
  CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `orders_delivery_schedule_id_foreign` FOREIGN KEY (`delivery_schedule_id`) REFERENCES `delivery_schedules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otp_verifications`
--

DROP TABLE IF EXISTS `otp_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `otp_verifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `otp` varchar(255) NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `otp_verifications_user_id_type_otp_index` (`user_id`,`type`,`otp`),
  CONSTRAINT `otp_verifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otp_verifications`
--

LOCK TABLES `otp_verifications` WRITE;
/*!40000 ALTER TABLE `otp_verifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `otp_verifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_gateway_transactions`
--

DROP TABLE IF EXISTS `payment_gateway_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_gateway_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned NOT NULL,
  `gateway` varchar(255) NOT NULL,
  `gateway_order_id` varchar(255) DEFAULT NULL,
  `gateway_payment_id` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'LKR',
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_payload`)),
  `response_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_payload`)),
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gateway_order_payment_unique` (`gateway`,`gateway_order_id`,`payment_id`),
  KEY `payment_gateway_transactions_payment_id_foreign` (`payment_id`),
  KEY `payment_gateway_transactions_gateway_index` (`gateway`),
  KEY `payment_gateway_transactions_gateway_order_id_index` (`gateway_order_id`),
  KEY `payment_gateway_transactions_gateway_payment_id_index` (`gateway_payment_id`),
  KEY `payment_gateway_transactions_status_index` (`status`),
  CONSTRAINT `payment_gateway_transactions_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_gateway_transactions`
--

LOCK TABLES `payment_gateway_transactions` WRITE;
/*!40000 ALTER TABLE `payment_gateway_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_gateway_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `payment_method` enum('cash_on_delivery','card','bank_transfer','wallet') NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `transaction_reference` varchar(255) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'LKR',
  `status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_order_id_unique` (`order_id`),
  UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
  KEY `payments_status_index` (`status`),
  KEY `payments_transaction_reference_index` (`transaction_reference`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'platform.statistics.view','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(2,'analytics.revenue.view','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(3,'analytics.orders.view','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(4,'analytics.customers.view','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(5,'analytics.vendors.view','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(6,'analytics.riders.view','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(7,'admins.manage','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(8,'roles.manage','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(9,'permissions.manage','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(10,'logs.activity.view','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(11,'logs.api.view','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(12,'logs.security.view','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(13,'settings.manage','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(14,'delivery.pricing.manage','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(15,'delivery.service_charges.manage','web','2026-08-26 11:15:16','2026-08-26 11:15:16'),(16,'delivery.promotions.manage','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(17,'delivery.rider_payouts.manage','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(18,'finance.commissions.manage','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(19,'delivery.analytics.view','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(20,'backup.manage','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(21,'reports.export','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(22,'advertisements.manage','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(23,'newsletter.manage','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(24,'customers.manage','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(25,'vendors.approve','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(26,'riders.approve','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(27,'products.manage','web','2026-08-26 11:15:17','2026-08-26 11:15:17'),(28,'categories.manage','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(29,'brands.manage','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(30,'orders.manage','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(31,'refunds.manage','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(32,'deliveries.manage','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(33,'coupons.manage','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(34,'promotions.manage','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(35,'support.manage','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(36,'contact_messages.manage','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(37,'notifications.manage','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(38,'reports.view','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(39,'analytics.view','web','2026-08-26 11:15:18','2026-08-26 11:15:18'),(40,'vendor.dashboard.view','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(41,'vendor.store.manage','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(42,'vendor.products.manage','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(43,'vendor.inventory.manage','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(44,'vendor.orders.manage','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(45,'vendor.promotions.manage','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(46,'vendor.coupons.manage','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(47,'vendor.reports.view','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(48,'vendor.wallet.view','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(49,'vendor.analytics.view','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(50,'customer.products.browse','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(51,'customer.cart.manage','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(52,'customer.checkout','web','2026-08-26 11:15:19','2026-08-26 11:15:19'),(53,'customer.orders.track','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(54,'customer.reviews.manage','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(55,'customer.loyalty.view','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(56,'customer.subscriptions.manage','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(57,'customer.support.manage','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(58,'customer.notifications.view','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(59,'rider.dashboard.view','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(60,'rider.orders.assigned','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(61,'rider.navigation.view','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(62,'rider.delivery.timeline','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(63,'rider.proof.manage','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(64,'rider.history.view','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(65,'rider.earnings.view','web','2026-08-26 11:15:20','2026-08-26 11:15:20'),(66,'rider.notifications.view','web','2026-08-26 11:15:20','2026-08-26 11:15:20');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_sort_order_index` (`product_id`,`sort_order`),
  KEY `product_images_is_primary_index` (`is_primary`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  KEY `product_variants_product_id_foreign` (`product_id`),
  KEY `product_variants_status_index` (`status`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (1,2,'Standard','GROC-KEKULU-RICE-5KG-STD',1250.00,'active','2026-08-26 11:15:28','2026-08-26 11:15:28',NULL),(2,3,'Standard','GROC-SAMBA-RICE-5KG-STD',1450.00,'active','2026-08-26 11:15:28','2026-08-26 11:15:28',NULL),(3,4,'Standard','GROC-WHEAT-FLOUR-1KG-STD',260.00,'active','2026-08-26 11:15:28','2026-08-26 11:15:28',NULL),(4,5,'Standard','GROC-WHITE-SUGAR-1KG-STD',290.00,'active','2026-08-26 11:15:28','2026-08-26 11:15:28',NULL),(5,6,'Standard','GROC-DHAL-MYSORE-500G-STD',340.00,'active','2026-08-26 11:15:28','2026-08-26 11:15:28',NULL),(6,7,'Standard','GROC-COCONUT-OIL-1L-STD',790.00,'active','2026-08-26 11:15:28','2026-08-26 11:15:28',NULL),(7,8,'Standard','GROC-CEYLON-TEA-200G-STD',520.00,'active','2026-08-26 11:15:28','2026-08-26 11:15:28',NULL),(8,9,'Standard','GROC-FULL-CREAM-MILK-POWDER-400G-STD',1180.00,'active','2026-08-26 11:15:28','2026-08-26 11:15:28',NULL),(9,10,'Standard','GROC-IODISED-SALT-1KG-STD',180.00,'active','2026-08-26 11:15:28','2026-08-26 11:15:28',NULL),(10,11,'Standard','GROC-RED-CHILLI-POWDER-100G-STD',230.00,'active','2026-08-26 11:15:28','2026-08-26 11:15:28',NULL);
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `unit_type` varchar(255) NOT NULL DEFAULT 'item',
  `weight` varchar(255) DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `stock_quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `unit` varchar(255) NOT NULL DEFAULT 'item',
  `status` enum('pending','approved','rejected','inactive','out_of_stock') NOT NULL DEFAULT 'pending',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_subscription_eligible` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `brand_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_vendor_id_slug_unique` (`vendor_id`,`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  UNIQUE KEY `products_barcode_unique` (`barcode`),
  KEY `products_category_id_status_index` (`category_id`,`status`),
  KEY `products_status_index` (`status`),
  KEY `products_is_featured_index` (`is_featured`),
  KEY `products_created_by_foreign` (`created_by`),
  KEY `products_vendor_status_featured_index` (`vendor_id`,`status`,`is_featured`),
  KEY `products_name_brand_index` (`name`,`brand`),
  KEY `products_brand_index` (`brand`),
  KEY `products_stock_quantity_index` (`stock_quantity`),
  KEY `products_is_subscription_eligible_index` (`is_subscription_eligible`),
  KEY `products_brand_id_foreign` (`brand_id`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,3,'Fresh Mangoes 1kg','fresh-mangoes-1kg','DailyCart Fresh','Fresh, hand-selected mangoes supplied by an approved DailyCart vendor.',750.00,NULL,'pack','1','DC-MANGO-1KG',NULL,50,NULL,NULL,3,750.00,NULL,'1 kg','approved',1,0,'2026-08-26 11:15:28','2026-08-26 11:15:28',NULL,NULL),(2,1,1,'Kekulu Rice 5kg','kekulu-rice-5kg',NULL,'Quality grocery essential supplied by DailyCart Vendor Store.',1250.00,NULL,'pack',NULL,'GROC-KEKULU-RICE-5KG',NULL,60,NULL,NULL,3,1250.00,NULL,'5 kg bag','approved',0,0,'2026-08-26 11:15:28','2026-08-26 11:15:28',NULL,NULL),(3,1,1,'Samba Rice 5kg','samba-rice-5kg',NULL,'Quality grocery essential supplied by DailyCart Vendor Store.',1450.00,NULL,'pack',NULL,'GROC-SAMBA-RICE-5KG',NULL,55,NULL,NULL,3,1450.00,NULL,'5 kg bag','approved',0,0,'2026-08-26 11:15:28','2026-08-26 11:15:28',NULL,NULL),(4,1,1,'Wheat Flour 1kg','wheat-flour-1kg',NULL,'Quality grocery essential supplied by DailyCart Vendor Store.',260.00,NULL,'pack',NULL,'GROC-WHEAT-FLOUR-1KG',NULL,80,NULL,NULL,3,260.00,NULL,'1 kg pack','approved',0,0,'2026-08-26 11:15:28','2026-08-26 11:15:28',NULL,NULL),(5,1,1,'White Sugar 1kg','white-sugar-1kg',NULL,'Quality grocery essential supplied by DailyCart Vendor Store.',290.00,NULL,'pack',NULL,'GROC-WHITE-SUGAR-1KG',NULL,90,NULL,NULL,3,290.00,NULL,'1 kg pack','approved',0,0,'2026-08-26 11:15:28','2026-08-26 11:15:28',NULL,NULL),(6,1,1,'Dhal Mysore 500g','dhal-mysore-500g',NULL,'Quality grocery essential supplied by DailyCart Vendor Store.',340.00,NULL,'pack',NULL,'GROC-DHAL-MYSORE-500G',NULL,70,NULL,NULL,3,340.00,NULL,'500 g pack','approved',0,0,'2026-08-26 11:15:28','2026-08-26 11:15:28',NULL,NULL),(7,1,1,'Coconut Oil 1L','coconut-oil-1l',NULL,'Quality grocery essential supplied by DailyCart Vendor Store.',790.00,NULL,'pack',NULL,'GROC-COCONUT-OIL-1L',NULL,45,NULL,NULL,3,790.00,NULL,'1 litre bottle','approved',0,0,'2026-08-26 11:15:28','2026-08-26 11:15:28',NULL,NULL),(8,1,1,'Ceylon Tea 200g','ceylon-tea-200g',NULL,'Quality grocery essential supplied by DailyCart Vendor Store.',520.00,NULL,'pack',NULL,'GROC-CEYLON-TEA-200G',NULL,50,NULL,NULL,3,520.00,NULL,'200 g pack','approved',0,0,'2026-08-26 11:15:28','2026-08-26 11:15:28',NULL,NULL),(9,1,1,'Full Cream Milk Powder 400g','full-cream-milk-powder-400g',NULL,'Quality grocery essential supplied by DailyCart Vendor Store.',1180.00,NULL,'pack',NULL,'GROC-FULL-CREAM-MILK-POWDER-400G',NULL,40,NULL,NULL,3,1180.00,NULL,'400 g pack','approved',0,0,'2026-08-26 11:15:28','2026-08-26 11:15:28',NULL,NULL),(10,1,1,'Iodised Salt 1kg','iodised-salt-1kg',NULL,'Quality grocery essential supplied by DailyCart Vendor Store.',180.00,NULL,'pack',NULL,'GROC-IODISED-SALT-1KG',NULL,100,NULL,NULL,3,180.00,NULL,'1 kg pack','approved',0,0,'2026-08-26 11:15:28','2026-08-26 11:15:28',NULL,NULL),(11,1,1,'Red Chilli Powder 100g','red-chilli-powder-100g',NULL,'Quality grocery essential supplied by DailyCart Vendor Store.',230.00,NULL,'pack',NULL,'GROC-RED-CHILLI-POWDER-100G',NULL,65,NULL,NULL,3,230.00,NULL,'100 g pack','approved',0,0,'2026-08-26 11:15:28','2026-08-26 11:15:28',NULL,NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `promotions`
--

DROP TABLE IF EXISTS `promotions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promotions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `promotion_type` enum('flash_sale','seasonal_offer','featured_offer','clearance_sale') NOT NULL,
  `target_type` enum('product','category','vendor','global') NOT NULL DEFAULT 'global',
  `target_id` bigint(20) unsigned DEFAULT NULL,
  `discount_type` enum('fixed_amount','percentage') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active',
  `created_by` bigint(20) unsigned NOT NULL,
  `views_count` int(10) unsigned NOT NULL DEFAULT 0,
  `clicks_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `promotions_vendor_id_foreign` (`vendor_id`),
  KEY `promotions_created_by_foreign` (`created_by`),
  KEY `promotions_promotion_type_index` (`promotion_type`),
  KEY `promotions_target_type_index` (`target_type`),
  KEY `promotions_target_id_index` (`target_id`),
  KEY `promotions_starts_at_index` (`starts_at`),
  KEY `promotions_ends_at_index` (`ends_at`),
  KEY `promotions_status_index` (`status`),
  CONSTRAINT `promotions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `promotions_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `promotions`
--

LOCK TABLES `promotions` WRITE;
/*!40000 ALTER TABLE `promotions` DISABLE KEYS */;
INSERT INTO `promotions` VALUES (1,1,'Fresh Mango Weekend Deal','Save 20% on fresh mangoes while stocks last.','flash_sale','product',1,'percentage',20.00,NULL,'2026-08-25 16:45:28','2027-08-26 16:45:28','active',2,7,0,'2026-08-26 11:15:28','2026-08-26 11:28:18',NULL);
/*!40000 ALTER TABLE `promotions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refunds`
--

DROP TABLE IF EXISTS `refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refunds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `payment_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `refund_method` enum('wallet') NOT NULL DEFAULT 'wallet',
  `reason` text NOT NULL,
  `admin_note` text DEFAULT NULL,
  `vendor_note` text DEFAULT NULL,
  `vendor_responded_at` timestamp NULL DEFAULT NULL,
  `status` enum('requested','approved','rejected','processed','failed') NOT NULL DEFAULT 'requested',
  `requested_at` timestamp NULL DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `refunds_payment_id_foreign` (`payment_id`),
  KEY `refunds_order_id_status_index` (`order_id`,`status`),
  KEY `refunds_status_index` (`status`),
  CONSTRAINT `refunds_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `refunds_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refunds`
--

LOCK TABLES `refunds` WRITE;
/*!40000 ALTER TABLE `refunds` DISABLE KEYS */;
/*!40000 ALTER TABLE `refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `vendor_id` bigint(20) unsigned DEFAULT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comment` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('visible','hidden','reported') NOT NULL DEFAULT 'visible',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reviews_customer_product_order_unique` (`customer_id`,`product_id`,`order_id`),
  KEY `reviews_order_id_foreign` (`order_id`),
  KEY `reviews_product_id_status_index` (`product_id`,`status`),
  KEY `reviews_vendor_id_status_index` (`vendor_id`,`status`),
  KEY `reviews_status_index` (`status`),
  CONSTRAINT `reviews_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rider_locations`
--

DROP TABLE IF EXISTS `rider_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rider_locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `rider_id` bigint(20) unsigned NOT NULL,
  `delivery_id` bigint(20) unsigned DEFAULT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `recorded_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rider_locations_rider_id_recorded_at_index` (`rider_id`,`recorded_at`),
  KEY `rider_locations_recorded_at_index` (`recorded_at`),
  KEY `rider_locations_delivery_id_foreign` (`delivery_id`),
  CONSTRAINT `rider_locations_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE SET NULL,
  CONSTRAINT `rider_locations_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rider_locations`
--

LOCK TABLES `rider_locations` WRITE;
/*!40000 ALTER TABLE `rider_locations` DISABLE KEYS */;
/*!40000 ALTER TABLE `rider_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rider_payment_rules`
--

DROP TABLE IF EXISTS `rider_payment_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rider_payment_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `base_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `per_km_bonus` decimal(10,2) NOT NULL DEFAULT 0.00,
  `peak_hour_bonus` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rain_bonus` decimal(10,2) NOT NULL DEFAULT 0.00,
  `holiday_bonus` decimal(10,2) NOT NULL DEFAULT 0.00,
  `night_bonus` decimal(10,2) NOT NULL DEFAULT 0.00,
  `peak_start_hour` tinyint(3) unsigned DEFAULT NULL,
  `peak_end_hour` tinyint(3) unsigned DEFAULT NULL,
  `night_start_hour` tinyint(3) unsigned DEFAULT NULL,
  `night_end_hour` tinyint(3) unsigned DEFAULT NULL,
  `starts_on` date DEFAULT NULL,
  `ends_on` date DEFAULT NULL,
  `priority` int(10) unsigned NOT NULL DEFAULT 100,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rider_payment_rules_starts_on_index` (`starts_on`),
  KEY `rider_payment_rules_ends_on_index` (`ends_on`),
  KEY `rider_payment_rules_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rider_payment_rules`
--

LOCK TABLES `rider_payment_rules` WRITE;
/*!40000 ALTER TABLE `rider_payment_rules` DISABLE KEYS */;
INSERT INTO `rider_payment_rules` VALUES (1,'Base Delivery Fee (40)',40.00,0.00,0.00,0.00,0.00,0.00,NULL,NULL,NULL,NULL,'2026-08-26','2027-08-26',100,'active','2026-08-26 11:33:06','2026-08-26 11:33:06');
/*!40000 ALTER TABLE `rider_payment_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riders`
--

DROP TABLE IF EXISTS `riders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `riders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `vehicle_type` enum('bicycle','motorbike','three_wheeler','van') NOT NULL,
  `vehicle_number` varchar(255) DEFAULT NULL,
  `license_number` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `formatted_address` varchar(500) DEFAULT NULL,
  `availability_status` enum('available','unavailable','delivering') NOT NULL DEFAULT 'unavailable',
  `verification_status` enum('pending','verified','rejected','suspended') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `riders_user_id_unique` (`user_id`),
  UNIQUE KEY `riders_license_number_unique` (`license_number`),
  KEY `riders_availability_status_index` (`availability_status`),
  KEY `riders_verification_status_index` (`verification_status`),
  KEY `riders_city_district_index` (`city`,`district`),
  KEY `riders_province_index` (`province`),
  CONSTRAINT `riders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riders`
--

LOCK TABLES `riders` WRITE;
/*!40000 ALTER TABLE `riders` DISABLE KEYS */;
INSERT INTO `riders` VALUES (1,5,'motorbike','WP-AB-1234','L-123456',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'available','verified','2026-08-26 11:15:27','2026-08-26 11:15:28',NULL);
/*!40000 ALTER TABLE `riders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(14,2),(15,1),(15,2),(16,1),(16,2),(17,1),(18,1),(19,1),(19,2),(20,1),(21,1),(22,1),(23,1),(24,2),(25,2),(26,2),(27,2),(28,2),(29,2),(30,2),(31,2),(32,2),(33,2),(34,2),(35,2),(36,2),(37,2),(38,2),(39,2),(40,3),(41,3),(42,3),(43,3),(44,3),(45,3),(46,3),(47,3),(48,3),(49,3),(50,5),(51,5),(52,5),(53,5),(54,5),(55,5),(56,5),(57,5),(58,5),(59,4),(60,4),(61,4),(62,4),(63,4),(64,4),(65,4),(66,4);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Super Admin','web','2026-08-26 11:13:58','2026-08-26 11:13:58'),(2,'Admin','web','2026-08-26 11:13:58','2026-08-26 11:13:58'),(3,'Vendor','web','2026-08-26 11:13:58','2026-08-26 11:13:58'),(4,'Rider','web','2026-08-26 11:13:58','2026-08-26 11:13:58'),(5,'Customer','web','2026-08-26 11:13:58','2026-08-26 11:13:58');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `search_history`
--

DROP TABLE IF EXISTS `search_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `searched_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `search_history_customer_id_foreign` (`customer_id`),
  CONSTRAINT `search_history_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `search_history`
--

LOCK TABLES `search_history` WRITE;
/*!40000 ALTER TABLE `search_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `search_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_setting_key_unique` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'smtp_host','smtp.mailtrap.io',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(2,'smtp_port','2525',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(3,'smtp_username','uahsens1@gmail.com',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(4,'smtp_password','Password@123',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(5,'smtp_encryption','tls',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(6,'firebase_credentials','',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(7,'google_maps_key','',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(8,'sms_gateway_url','https://smslenz.lk/api',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(9,'sms_gateway_api_key','ca415c16-20d2-4f66-8acd-0a5e6289459b',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(10,'payhere_merchant_id','1235980',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(11,'payhere_merchant_secret','MjMwNjk4MzY0MjM0NDU2NTk2ODYyMTg1MDk1NzE2MjMxNTI1ODk5OA==',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(12,'currency_code','LKR',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(13,'currency_symbol','Rs.',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(14,'timezone','Asia/Colombo',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(15,'maintenance_mode','0',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(16,'delivery_charge_single_item','100',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(17,'delivery_charge_bulk_items','50',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20'),(18,'service_charge_rate_percent','2.00',NULL,'2026-08-26 11:41:20','2026-08-26 11:41:20');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `store_banners`
--

DROP TABLE IF EXISTS `store_banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_banners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_profile_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_banners_vendor_profile_id_is_active_index` (`vendor_profile_id`,`is_active`),
  KEY `store_banners_is_active_index` (`is_active`),
  CONSTRAINT `store_banners_vendor_profile_id_foreign` FOREIGN KEY (`vendor_profile_id`) REFERENCES `vendor_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store_banners`
--

LOCK TABLES `store_banners` WRITE;
/*!40000 ALTER TABLE `store_banners` DISABLE KEYS */;
INSERT INTO `store_banners` VALUES (1,1,'Front','stores/1/banners/qLRgETCjLBJ8Pi7x2IWTKfqiCJZSMXsAi3q66jEi.png',NULL,0,1,NULL,NULL,'2026-08-26 11:27:43','2026-08-26 11:27:43');
/*!40000 ALTER TABLE `store_banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `store_followers`
--

DROP TABLE IF EXISTS `store_followers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store_followers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_profile_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_followers_vendor_profile_id_customer_id_unique` (`vendor_profile_id`,`customer_id`),
  KEY `store_followers_customer_id_foreign` (`customer_id`),
  CONSTRAINT `store_followers_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `store_followers_vendor_profile_id_foreign` FOREIGN KEY (`vendor_profile_id`) REFERENCES `vendor_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store_followers`
--

LOCK TABLES `store_followers` WRITE;
/*!40000 ALTER TABLE `store_followers` DISABLE KEYS */;
INSERT INTO `store_followers` VALUES (1,1,1,'2026-08-26 11:25:35','2026-08-26 11:25:35');
/*!40000 ALTER TABLE `store_followers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `product_variant_id` bigint(20) unsigned DEFAULT NULL,
  `vendor_id` bigint(20) unsigned NOT NULL,
  `frequency` enum('daily','weekly','monthly') NOT NULL DEFAULT 'weekly',
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery_address` text DEFAULT NULL,
  `preferred_delivery_time` time DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `next_delivery_date` date DEFAULT NULL,
  `payment_method` varchar(255) NOT NULL DEFAULT 'cash_on_delivery',
  `notes` text DEFAULT NULL,
  `last_generated_at` timestamp NULL DEFAULT NULL,
  `failed_reason` text DEFAULT NULL,
  `plan_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'LKR',
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `status` enum('active','expired','cancelled','pending') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_vendor_id_status_index` (`vendor_id`,`status`),
  KEY `subscriptions_ends_at_index` (`ends_at`),
  KEY `subscriptions_status_index` (`status`),
  KEY `subscriptions_customer_id_status_index` (`customer_id`,`status`),
  KEY `subscriptions_vendor_id_status_next_delivery_date_index` (`vendor_id`,`status`,`next_delivery_date`),
  KEY `subscriptions_product_id_status_index` (`product_id`,`status`),
  KEY `subscriptions_frequency_index` (`frequency`),
  KEY `subscriptions_start_date_index` (`start_date`),
  KEY `subscriptions_end_date_index` (`end_date`),
  KEY `subscriptions_next_delivery_date_index` (`next_delivery_date`),
  KEY `subscriptions_product_variant_id_foreign` (`product_variant_id`),
  KEY `subscriptions_product_variant_status_index` (`product_id`,`product_variant_id`,`status`),
  CONSTRAINT `subscriptions_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subscriptions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subscriptions_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `subscriptions_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_ticket_replies`
--

DROP TABLE IF EXISTS `support_ticket_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_ticket_replies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `support_ticket_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_ticket_replies_user_id_foreign` (`user_id`),
  KEY `support_ticket_replies_support_ticket_id_created_at_index` (`support_ticket_id`,`created_at`),
  CONSTRAINT `support_ticket_replies_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `support_ticket_replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket_replies`
--

LOCK TABLES `support_ticket_replies` WRITE;
/*!40000 ALTER TABLE `support_ticket_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_ticket_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `assigned_admin_id` bigint(20) unsigned DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_tickets_user_id_foreign` (`user_id`),
  KEY `support_tickets_order_id_foreign` (`order_id`),
  KEY `support_tickets_priority_index` (`priority`),
  KEY `support_tickets_status_index` (`status`),
  KEY `support_tickets_assigned_admin_id_foreign` (`assigned_admin_id`),
  KEY `support_tickets_closed_at_index` (`closed_at`),
  CONSTRAINT `support_tickets_assigned_admin_id_foreign` FOREIGN KEY (`assigned_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_tickets`
--

LOCK TABLES `support_tickets` WRITE;
/*!40000 ALTER TABLE `support_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `support_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive','suspended','pending') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  KEY `users_status_index` (`status`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'Ahsen','uahsens1@gmail.com','075 460 3008',NULL,'2026-08-26 11:15:21','2026-08-26 11:15:21','$2y$12$yHFwWZzeLs8YdMaZ1TFumeUfACdiw2D0dhkgK3OX9KSHEFYGoE4ii','active',NULL,'2026-08-26 11:15:21','2026-08-26 11:28:47',NULL),(2,2,'Uahsens Admin','uahsens2@gmail.com','0701000002',NULL,'2026-08-26 11:15:23','2026-08-26 11:15:23','$2y$12$zgmRwZeI23NTi3RLiJeH4OFmQ.7J6MpD.LsI7FO4sAAYdlzrpRCJm','active',NULL,'2026-08-26 11:15:23','2026-08-26 11:15:23',NULL),(3,3,'Vendor-1','uahsens2001@gmail.com','076 771 1020',NULL,'2026-08-26 11:15:24','2026-08-26 11:15:24','$2y$12$Sh.S8BFAZ6NSX71y4GlfpugGFmYqyGkB2whtTvIkNJVA1AHC87suu','active',NULL,'2026-08-26 11:15:24','2026-08-26 11:26:46',NULL),(4,5,'Rifka','rifkabanu870@gmail.com','076 236 3232',NULL,'2026-08-26 11:15:26','2026-08-26 11:15:26','$2y$12$rGPl31LdmlOZlxWrY.GFYeHrFgkEnlpQ3x.2ZcmumvGE6U5ZkN3ZC','active',NULL,'2026-08-26 11:15:26','2026-08-26 11:24:48',NULL),(5,4,'Ofnaaa Rider','ofnaaa@gmai.com','0701000005',NULL,'2026-08-26 11:15:27','2026-08-26 11:15:27','$2y$12$n/Til7DH5W5ZHUwY7VfIkOgELpM4U0qkBawwy6CTrPPjupCyVvOu2','active',NULL,'2026-08-26 11:15:27','2026-08-26 11:15:27',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor_payout_requests`
--

DROP TABLE IF EXISTS `vendor_payout_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendor_payout_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_number` varchar(255) NOT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'requested',
  `admin_note` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_payout_requests_vendor_id_status_index` (`vendor_id`,`status`),
  CONSTRAINT `vendor_payout_requests_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_payout_requests`
--

LOCK TABLES `vendor_payout_requests` WRITE;
/*!40000 ALTER TABLE `vendor_payout_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_payout_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor_profiles`
--

DROP TABLE IF EXISTS `vendor_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendor_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint(20) unsigned NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `cover_image_path` varchar(255) DEFAULT NULL,
  `opening_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`opening_hours`)),
  `delivery_estimate` varchar(100) DEFAULT NULL,
  `minimum_order` decimal(12,2) DEFAULT NULL,
  `contact_phone` varchar(60) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `profile_status` varchar(20) NOT NULL DEFAULT 'pending',
  `approved_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_profiles_vendor_id_foreign` (`vendor_id`),
  KEY `vendor_profiles_reviewed_by_foreign` (`reviewed_by`),
  CONSTRAINT `vendor_profiles_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_profiles_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_profiles`
--

LOCK TABLES `vendor_profiles` WRITE;
/*!40000 ALTER TABLE `vendor_profiles` DISABLE KEYS */;
INSERT INTO `vendor_profiles` VALUES (1,1,'dailycart-vendor-store-1','Daily essentials from an approved DailyCart vendor.',NULL,NULL,'{\"summary\":\"Mon-Sun, 08:00 AM-09:00 PM\"}','30-60 minutes',250.00,'076 771 1020','uahsens2001@gmail.com',1,1,'approved','2026-08-26 11:15:26',2,'2026-08-26 11:15:26','2026-08-26 11:27:22');
/*!40000 ALTER TABLE `vendor_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor_wallets`
--

DROP TABLE IF EXISTS `vendor_wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendor_wallets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint(20) unsigned NOT NULL,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `pending_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_earned` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_withdrawn` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_wallets_vendor_id_foreign` (`vendor_id`),
  CONSTRAINT `vendor_wallets_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_wallets`
--

LOCK TABLES `vendor_wallets` WRITE;
/*!40000 ALTER TABLE `vendor_wallets` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_wallets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vendors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `store_name` varchar(255) NOT NULL,
  `business_registration_no` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(255) NOT NULL,
  `province` varchar(255) DEFAULT NULL,
  `district` varchar(255) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `formatted_address` varchar(255) DEFAULT NULL,
  `commission_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendors_user_id_unique` (`user_id`),
  UNIQUE KEY `vendors_business_registration_no_unique` (`business_registration_no`),
  KEY `vendors_city_district_index` (`city`,`district`),
  KEY `vendors_status_index` (`status`),
  KEY `vendors_province_index` (`province`),
  CONSTRAINT `vendors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
INSERT INTO `vendors` VALUES (1,3,'DailyCart Vendor Store','BR-12345','076 771 1020','#479 OFNAA EX-CHAIRMAN ROAD ODDAMAVEDI-02','Batticaloa','Eastern','Colombo',NULL,NULL,'123 Store Street, Colombo, Batticaloa',10.00,'approved','2026-08-26 11:15:25','2026-08-26 11:15:24','2026-08-26 11:26:46',NULL);
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallet_transactions`
--

DROP TABLE IF EXISTS `wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallet_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `transaction_type` enum('top_up','payment','refund','cashback','adjustment') NOT NULL DEFAULT 'adjustment',
  `type` enum('credit','debit') NOT NULL,
  `source` enum('refund','order_payment','admin_adjustment','loyalty_redeem') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `balance_after` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'LKR',
  `reference` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wallet_transactions_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `wallet_transactions_type_index` (`type`),
  KEY `wallet_transactions_transaction_type_index` (`transaction_type`),
  KEY `wallet_transactions_reference_index` (`reference`),
  CONSTRAINT `wallet_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_transactions`
--

LOCK TABLES `wallet_transactions` WRITE;
/*!40000 ALTER TABLE `wallet_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `wallet_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlists_customer_id_product_id_unique` (`customer_id`,`product_id`),
  KEY `wishlists_product_id_foreign` (`product_id`),
  CONSTRAINT `wishlists_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlists`
--

LOCK TABLES `wishlists` WRITE;
/*!40000 ALTER TABLE `wishlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zones`
--

DROP TABLE IF EXISTS `zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `city_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `district` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `radius_km` decimal(8,2) DEFAULT NULL,
  `estimated_delivery_minutes` int(10) unsigned DEFAULT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `zones_city_id_name_unique` (`city_id`,`name`),
  KEY `zones_status_index` (`status`),
  KEY `zones_district_index` (`district`),
  KEY `zones_province_index` (`province`),
  CONSTRAINT `zones_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zones`
--

LOCK TABLES `zones` WRITE;
/*!40000 ALTER TABLE `zones` DISABLE KEYS */;
INSERT INTO `zones` VALUES (1,NULL,'Oddamavadi','Batticaloa','Eastern',NULL,NULL,10.00,NULL,0.00,'active','2026-08-26 11:29:18','2026-08-26 11:29:18'),(2,NULL,'Valaichchenai','Batticaloa','Eastern',NULL,NULL,10.00,NULL,0.00,'active','2026-08-26 11:29:32','2026-08-26 11:29:32');
/*!40000 ALTER TABLE `zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'dailycart_recovered'
--

--
-- Dumping routines for database 'dailycart_recovered'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-26 17:25:14
