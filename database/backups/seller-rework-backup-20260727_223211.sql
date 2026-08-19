-- MySQL dump 10.13  Distrib 8.4.10, for Linux (x86_64)
--
-- Host: localhost    Database: sushako_shopping
-- ------------------------------------------------------
-- Server version	8.4.10

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tagline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `headline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accent` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#2f6b4f',
  `tax_slab_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `available_to_sellers` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_is_active_index` (`is_active`),
  KEY `categories_sort_order_index` (`sort_order`),
  KEY `categories_tax_slab_id_foreign` (`tax_slab_id`),
  KEY `categories_available_to_sellers_index` (`available_to_sellers`),
  CONSTRAINT `categories_tax_slab_id_foreign` FOREIGN KEY (`tax_slab_id`) REFERENCES `tax_slabs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (2,'Home Made Health Mix','home-made-health-mix','Homemade nutrition','Traditional health mix for daily strength','Fresh, home-style health mix powders prepared for family nutrition, breakfast routines and everyday wellness.','assets/banners/home-made-health-mix.jpg','#496d47',2,1,1,'2026-07-23 21:14:10','2026-07-26 21:28:13',1),(3,'Masala Powders','masala-powders','Fresh aroma','Small-batch masala powders','Aromatic spice powders for everyday Indian cooking, prepared with a focus on freshness, flavour and trusted ingredients.','assets/banners/masala-powders.jpg','#9b3a1d',2,1,2,'2026-07-23 21:14:10','2026-07-26 21:28:13',1),(4,'Women\'s Clothing','womens-clothing','Curated style','Women\'s clothing for every occasion','Elegant women\'s clothing selections with polished styling, clear details and Sushako support.','assets/banners/womens-clothing.jpg','#a54569',2,1,4,'2026-07-23 21:14:10','2026-07-26 21:28:13',1),(5,'Electronics','electronics','Smart essentials','Mobiles, laptops and everyday electronics','Useful electronics for work, entertainment and daily life, including laptops, mobiles, headsets and smart watches.','assets/banners/electronics.jpg','#263e68',4,1,3,'2026-07-24 02:50:00','2026-07-26 21:28:13',1);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commission_rules`
--

DROP TABLE IF EXISTS `commission_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commission_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `plan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `rate` decimal(8,2) NOT NULL DEFAULT '10.00',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commission_rules_vendor_id_foreign` (`vendor_id`),
  KEY `commission_rules_category_id_foreign` (`category_id`),
  KEY `commission_rules_product_id_foreign` (`product_id`),
  KEY `commission_rules_plan_index` (`plan`),
  KEY `commission_rules_is_active_index` (`is_active`),
  CONSTRAINT `commission_rules_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `commission_rules_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `commission_rules_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commission_rules`
--

LOCK TABLES `commission_rules` WRITE;
/*!40000 ALTER TABLE `commission_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `commission_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_settings`
--

DROP TABLE IF EXISTS `company_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `company_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sushako Shopping',
  `legal_business_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gstin` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cin` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pincode` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'India',
  `support_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `support_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INR',
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Kolkata',
  `business_hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_settings`
--

LOCK TABLES `company_settings` WRITE;
/*!40000 ALTER TABLE `company_settings` DISABLE KEYS */;
INSERT INTO `company_settings` VALUES (1,'Sushako Shopping','Sushako Shopping',NULL,NULL,NULL,NULL,'Chennai',NULL,'Chennai','Tamil Nadu','600001','India','support@sushako.test','919876543210','http://127.0.0.1:8000','INR','Asia/Kolkata','Monday to Saturday, 10 AM to 7 PM','2026-07-24 05:13:27','2026-07-24 05:13:27');
/*!40000 ALTER TABLE `company_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cookie_consents`
--

DROP TABLE IF EXISTS `cookie_consents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cookie_consents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consent_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'essential',
  `accepted` tinyint(1) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cookie_consents_user_id_consent_type_unique` (`user_id`,`consent_type`),
  UNIQUE KEY `cookie_consents_session_id_consent_type_unique` (`session_id`,`consent_type`),
  KEY `cookie_consents_consent_type_accepted_index` (`consent_type`,`accepted`),
  CONSTRAINT `cookie_consents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cookie_consents`
--

LOCK TABLES `cookie_consents` WRITE;
/*!40000 ALTER TABLE `cookie_consents` DISABLE KEYS */;
INSERT INTO `cookie_consents` VALUES (1,NULL,'1bJfcFRvffCgYf0TImOJ2UsjXMQF35WZrRS8WWbN','essential',1,'172.18.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2026-07-24 22:57:31','2026-07-24 22:57:31','2026-07-24 22:57:31'),(2,NULL,'WLUNh7HByGbW3g4xVwrh1TzDykzlzq0J3heI6ACU','essential',1,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36','2026-07-25 16:38:59','2026-07-25 16:38:59','2026-07-25 16:38:59'),(3,6,'v5bILQ7gbX0thcAO71qONkxhy9HT8HqI6EBgW2ne','essential',1,'172.18.0.1','curl/8.5.0','2026-07-25 18:59:55','2026-07-25 18:59:55','2026-07-25 18:59:55'),(4,NULL,'Lsf4wJcuKUW8YYFNlglAaIlaum4rIA6kYfOebEPF','essential',1,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36','2026-07-25 20:56:42','2026-07-25 20:56:42','2026-07-25 20:56:42'),(5,NULL,'9xbe324ZwY07jz6jJmdnLZvWVF8vWp9xV1misdHn','essential',1,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','2026-07-26 03:02:39','2026-07-26 03:02:39','2026-07-26 03:02:39'),(6,NULL,'su2vywsyIH4jN9Ku9ABQdUk9V3PJuRZikqMVW4K4','essential',1,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36','2026-07-26 07:17:19','2026-07-26 07:17:19','2026-07-26 07:17:19'),(7,NULL,'V4Rooi6tXMAMvrBOEKZTtMoFJPZQIAfB9IpwhpYo','essential',1,'172.18.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1','2026-07-26 17:41:21','2026-07-26 17:41:21','2026-07-26 17:41:21'),(8,NULL,'sNrENcvXR1nXxZanpTRqHTIf1jZGKHXqejVzsNeP','essential',1,'172.18.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-27 12:55:51','2026-07-27 12:55:51','2026-07-27 12:55:51');
/*!40000 ALTER TABLE `cookie_consents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_activities`
--

DROP TABLE IF EXISTS `customer_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_activities_user_id_foreign` (`user_id`),
  KEY `customer_activities_product_id_foreign` (`product_id`),
  KEY `customer_activities_order_id_foreign` (`order_id`),
  KEY `customer_activities_customer_email_index` (`customer_email`),
  KEY `customer_activities_customer_phone_index` (`customer_phone`),
  KEY `customer_activities_type_index` (`type`),
  KEY `customer_activities_occurred_at_index` (`occurred_at`),
  CONSTRAINT `customer_activities_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_activities_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_activities_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_activities`
--

LOCK TABLES `customer_activities` WRITE;
/*!40000 ALTER TABLE `customer_activities` DISABLE KEYS */;
INSERT INTO `customer_activities` VALUES (1,6,'kotiairport@gmail.com','9445712235','marketing_consent_changed','WhatsApp consent updated',NULL,NULL,'{\"source\": \"Admin verified consent\", \"marketing\": true, \"admin_user_id\": 1, \"order_updates\": true}','admin','2026-07-26 19:14:32','2026-07-26 19:14:32','2026-07-26 19:14:32');
/*!40000 ALTER TABLE `customer_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_addresses`
--

DROP TABLE IF EXISTS `customer_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Home',
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pincode` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `landmark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_location_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_addresses_user_id_foreign` (`user_id`),
  KEY `customer_addresses_is_default_index` (`is_default`),
  CONSTRAINT `customer_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_addresses`
--

LOCK TABLES `customer_addresses` WRITE;
/*!40000 ALTER TABLE `customer_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_cart_items`
--

DROP TABLE IF EXISTS `customer_cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_cart_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `cart_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `colour` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price` int unsigned NOT NULL,
  `line_total` int unsigned NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_cart_items_customer_cart_id_cart_key_unique` (`customer_cart_id`,`cart_key`),
  KEY `customer_cart_items_product_id_foreign` (`product_id`),
  KEY `customer_cart_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `customer_cart_items_customer_cart_id_foreign` FOREIGN KEY (`customer_cart_id`) REFERENCES `customer_carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_cart_items`
--

LOCK TABLES `customer_cart_items` WRITE;
/*!40000 ALTER TABLE `customer_cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_carts`
--

DROP TABLE IF EXISTS `customer_carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'registered',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `original_value` int unsigned NOT NULL DEFAULT '0',
  `recovery_token` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recovery_token_expires_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `abandoned_at` timestamp NULL DEFAULT NULL,
  `reminder_opened_at` timestamp NULL DEFAULT NULL,
  `reminder_marked_sent_at` timestamp NULL DEFAULT NULL,
  `recovered_at` timestamp NULL DEFAULT NULL,
  `recovered_order_id` bigint unsigned DEFAULT NULL,
  `dismissed_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `coupon_used` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_carts_recovery_token_unique` (`recovery_token`),
  KEY `customer_carts_recovered_order_id_foreign` (`recovered_order_id`),
  KEY `customer_carts_user_id_status_index` (`user_id`,`status`),
  KEY `customer_carts_session_id_index` (`session_id`),
  KEY `customer_carts_customer_type_index` (`customer_type`),
  KEY `customer_carts_status_index` (`status`),
  KEY `customer_carts_last_activity_at_index` (`last_activity_at`),
  KEY `customer_carts_abandoned_at_index` (`abandoned_at`),
  CONSTRAINT `customer_carts_recovered_order_id_foreign` FOREIGN KEY (`recovered_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_carts`
--

LOCK TABLES `customer_carts` WRITE;
/*!40000 ALTER TABLE `customer_carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_communications`
--

DROP TABLE IF EXISTS `customer_communications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_communications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `customer_cart_id` bigint unsigned DEFAULT NULL,
  `admin_user_id` bigint unsigned DEFAULT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'whatsapp',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'prepared',
  `message_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `marked_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_communications_user_id_foreign` (`user_id`),
  KEY `customer_communications_order_id_foreign` (`order_id`),
  KEY `customer_communications_customer_cart_id_foreign` (`customer_cart_id`),
  KEY `customer_communications_admin_user_id_foreign` (`admin_user_id`),
  KEY `customer_communications_channel_index` (`channel`),
  KEY `customer_communications_category_index` (`category`),
  KEY `customer_communications_status_index` (`status`),
  CONSTRAINT `customer_communications_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_communications_customer_cart_id_foreign` FOREIGN KEY (`customer_cart_id`) REFERENCES `customer_carts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_communications_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_communications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_communications`
--

LOCK TABLES `customer_communications` WRITE;
/*!40000 ALTER TABLE `customer_communications` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_communications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_export_audits`
--

DROP TABLE IF EXISTS `customer_export_audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_export_audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admin_user_id` bigint unsigned NOT NULL,
  `export_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filters` json DEFAULT NULL,
  `record_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_export_audits_admin_user_id_foreign` (`admin_user_id`),
  KEY `customer_export_audits_export_type_index` (`export_type`),
  CONSTRAINT `customer_export_audits_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_export_audits`
--

LOCK TABLES `customer_export_audits` WRITE;
/*!40000 ALTER TABLE `customer_export_audits` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_export_audits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_management_settings`
--

DROP TABLE IF EXISTS `customer_management_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_management_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_management_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_management_settings`
--

LOCK TABLES `customer_management_settings` WRITE;
/*!40000 ALTER TABLE `customer_management_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_management_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
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
-- Table structure for table `invoice_settings`
--

DROP TABLE IF EXISTS `invoice_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_prefix` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INV',
  `next_invoice_number` int unsigned NOT NULL DEFAULT '1',
  `invoice_footer` text COLLATE utf8mb4_unicode_ci,
  `terms_conditions` text COLLATE utf8mb4_unicode_ci,
  `authorized_signatory_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `authorized_signatory_designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_settings`
--

LOCK TABLES `invoice_settings` WRITE;
/*!40000 ALTER TABLE `invoice_settings` DISABLE KEYS */;
INSERT INTO `invoice_settings` VALUES (1,'INV',3,'Thank you for shopping with Sushako.','Goods once delivered are governed by the Sushako return and support policies.','Sushako Store Admin','Authorized Signatory','2026-07-24 05:13:27','2026-07-25 17:20:13');
/*!40000 ALTER TABLE `invoice_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
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
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_07_23_000001_add_phase_one_fields_to_users_table',1),(5,'2026_07_23_000002_create_vendors_table',1),(6,'2026_07_24_000001_add_google_fields_to_users_table',1),(7,'2026_07_24_000002_create_orders_table',1),(8,'2026_07_24_000003_add_delivery_location_to_orders_table',1),(9,'2026_07_24_000004_add_manual_shipping_fields_to_orders_table',1),(10,'2026_07_24_000005_create_customer_addresses_table',1),(11,'2026_07_24_000006_add_customer_profile_details_to_users_table',1),(12,'2026_07_24_000007_create_catalog_tables',1),(13,'2026_07_24_000008_add_catalog_links_to_order_items',1),(14,'2026_07_24_000009_add_option_pricing_to_product_variants',1),(15,'2026_07_24_000010_create_cookie_consents_table',1),(16,'2026_07_24_000011_create_operational_settings_tables',1),(17,'2026_07_26_000001_add_location_capture_fields_to_orders_table',2),(18,'2026_07_26_000002_create_shipping_labels_table',2),(19,'2026_07_26_000003_create_customer_management_tables',3),(20,'2026_07_27_000001_create_seller_platform_tables',4),(21,'2026_07_27_000002_complete_seller_onboarding_flow',5),(22,'2026_07_27_000003_create_seller_plans_and_snapshots',6);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_variant_id` bigint unsigned DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `colour` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int unsigned NOT NULL,
  `unit_price` int unsigned NOT NULL,
  `line_total` int unsigned NOT NULL,
  `gst_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tax_amount` int unsigned NOT NULL DEFAULT '0',
  `cgst_amount` int unsigned NOT NULL DEFAULT '0',
  `sgst_amount` int unsigned NOT NULL DEFAULT '0',
  `igst_amount` int unsigned NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `seller_plan_at_order` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gross_line_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `commission_base` decimal(12,2) NOT NULL DEFAULT '0.00',
  `commission_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `commission_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `seller_earning` decimal(12,2) NOT NULL DEFAULT '0.00',
  `commission_rule_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_calculated_at` timestamp NULL DEFAULT NULL,
  `settlement_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'upcoming',
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  KEY `order_items_vendor_id_settlement_status_index` (`vendor_id`,`settlement_status`),
  KEY `order_items_settlement_status_index` (`settlement_status`),
  KEY `order_items_vendor_settlement_idx` (`vendor_id`,`settlement_status`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (6,6,NULL,NULL,NULL,'Sushako Razorpay Test Product','sushako-razorpay-test-product','Sushako Blue','Test Unit',1,1,1,0.00,0,0,0,0,'http://127.0.0.1:8000/assets/products/sushako-razorpay-test-product.svg','2026-07-23 20:22:58','2026-07-23 20:22:58',NULL,0.00,0.00,NULL,0.00,0.00,0.00,NULL,NULL,'upcoming'),(7,7,NULL,6,6,'Lenovo 100e Celeron Laptop','lenovo-100e-celeron-laptop','Standard','500GB HDD',4,9000,36000,0.00,0,0,0,0,'http://127.0.0.1:8000/assets/products/lenovo-100e/lenovo-100e-01.jpg','2026-07-24 04:14:31','2026-07-24 04:14:31',NULL,0.00,0.00,NULL,0.00,0.00,0.00,NULL,NULL,'upcoming'),(8,17,NULL,NULL,NULL,'Sushako Staging Sample Product','sushako-razorpay-test-product','Standard','Standard',1,1,1,5.00,0,0,0,0,'https://shop.sushako.in/assets/banners/home-made-health-mix.jpg','2026-07-25 17:15:17','2026-07-25 17:15:17',NULL,0.00,0.00,NULL,0.00,0.00,0.00,NULL,NULL,'upcoming'),(9,18,NULL,NULL,NULL,'Sushako Staging Sample Product','sushako-razorpay-test-product','Standard','Standard',1,1,1,5.00,0,0,0,0,'https://shop.sushako.in/assets/banners/home-made-health-mix.jpg','2026-07-25 17:17:36','2026-07-25 17:17:36',NULL,0.00,0.00,NULL,0.00,0.00,0.00,NULL,NULL,'upcoming'),(10,19,NULL,6,6,'Lenovo 100e Celeron Laptop','lenovo-100e-celeron-laptop','Standard','500GB HDD',1,9000,9000,18.00,1373,686,687,0,'https://shop.sushako.in/assets/products/lenovo-100e/lenovo-100e-01.jpg','2026-07-25 19:15:05','2026-07-25 19:15:05',NULL,0.00,0.00,NULL,0.00,0.00,0.00,NULL,NULL,'upcoming');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_sequence` int unsigned DEFAULT NULL,
  `invoiced_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pincode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `landmark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_location_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_latitude` decimal(10,7) DEFAULT NULL,
  `delivery_longitude` decimal(10,7) DEFAULT NULL,
  `location_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `location_captured_at` timestamp NULL DEFAULT NULL,
  `location_updated_by` bigint unsigned DEFAULT NULL,
  `location_capture_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtotal` int unsigned NOT NULL,
  `shipping_amount` int unsigned DEFAULT NULL,
  `shipping_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'free',
  `tax_amount` int unsigned NOT NULL DEFAULT '0',
  `cgst_amount` int unsigned NOT NULL DEFAULT '0',
  `sgst_amount` int unsigned NOT NULL DEFAULT '0',
  `igst_amount` int unsigned NOT NULL DEFAULT '0',
  `discount_amount` int unsigned NOT NULL DEFAULT '0',
  `total_amount` int unsigned NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'placed',
  `seller_order_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'razorpay',
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `shipping_provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_provider_other` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `placed_at` timestamp NULL DEFAULT NULL,
  `packed_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `shipment_deadline_at` timestamp NULL DEFAULT NULL,
  `seller_overdue_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  UNIQUE KEY `orders_invoice_number_unique` (`invoice_number`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_customer_phone_index` (`customer_phone`),
  KEY `orders_status_index` (`status`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_location_updated_by_foreign` (`location_updated_by`),
  KEY `orders_seller_order_status_index` (`seller_order_status`),
  KEY `orders_shipment_deadline_at_index` (`shipment_deadline_at`),
  CONSTRAINT `orders_location_updated_by_foreign` FOREIGN KEY (`location_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (6,'SS20260700001',NULL,NULL,NULL,6,'Koteeswaran T','9445712235','kotiairport@gmail.com','fksdkfsdjkfnjkasjk','jdbnfksdjkfnsdjkn','Chengalpattu','603001',NULL,'https://www.google.com/maps?q=13.0514944,80.2455552',NULL,NULL,0,NULL,NULL,NULL,1,0,'free',0,0,0,0,0,1,'payment_pending','new','unselected','pending',NULL,NULL,NULL,NULL,'order_THoTkpBO0EvAt5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-23 20:22:58','2026-07-25 17:11:02'),(7,'SS20260700002','INV-00001',1,'2026-07-25 17:16:31',6,'Koteeswaran T','9445712235','kotiairport@gmail.com','36e/99','mettu street','Chengalpattu','603001',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,36000,0,'free',0,0,0,0,0,36000,'placed','new','cod','pending',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 04:16:00',NULL,NULL,NULL,NULL,NULL,'2026-07-24 04:14:31','2026-07-25 17:16:31'),(17,'SS20260700003',NULL,NULL,NULL,6,'Koteeswaran T','9445712235','kotiairport@gmail.com','QA Test Door 1','Live Razorpay QA','Chengalpattu','603001','QA Landmark','https://www.google.com/maps?q=12.6819,79.9888',NULL,NULL,0,NULL,NULL,NULL,1,NULL,'delivery_charges_applicable',0,0,0,0,0,1,'payment_pending','new','unselected','pending',NULL,NULL,NULL,NULL,'order_THoYU6kbzuJr3q',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-25 17:15:17','2026-07-25 17:15:31'),(18,'SS20260700004','INV-00002',2,'2026-07-25 17:20:13',6,'Koteeswaran T','9445712235','kotiairport@gmail.com','Mettu Street',NULL,'Chengalpattu','603001',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,1,NULL,'delivery_charges_applicable',0,0,0,0,0,1,'placed','new','razorpay','paid',NULL,NULL,NULL,NULL,'order_THoaiEubqfEex4','pay_THocYNdAD39KlD','2026-07-25 17:19:40',NULL,NULL,NULL,NULL,NULL,'2026-07-25 17:17:36','2026-07-25 17:20:13'),(19,'SS20260700005',NULL,NULL,NULL,6,'Koteeswaran T','9445712235','kotiairport@gmail.com','Mettu Street','mettu street','Chengalpattu','603001',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,9000,0,'free',1373,686,687,0,0,9000,'payment_pending','new','unselected','pending',NULL,NULL,NULL,NULL,'order_THqanhLJbeyUIJ',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-25 19:15:05','2026-07-25 19:15:05');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `payment_settings`
--

DROP TABLE IF EXISTS `payment_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `environment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `key_placeholder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_url_placeholder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_future_provider` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_settings_provider_unique` (`provider`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_settings`
--

LOCK TABLES `payment_settings` WRITE;
/*!40000 ALTER TABLE `payment_settings` DISABLE KEYS */;
INSERT INTO `payment_settings` VALUES (1,'cod','Cash on Delivery',1,'sandbox',NULL,NULL,0,'2026-07-24 05:13:27','2026-07-24 05:13:27'),(2,'razorpay','Razorpay',1,'live','RAZORPAY_KEY_ID','RAZORPAY_WEBHOOK_URL',0,'2026-07-24 05:13:27','2026-07-25 17:10:14'),(3,'future_provider','Future Provider',0,'sandbox','PROVIDER_KEY','PROVIDER_WEBHOOK_URL',1,'2026-07-24 05:13:27','2026-07-24 05:13:27');
/*!40000 ALTER TABLE `payment_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Product Image',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_foreign` (`product_id`),
  KEY `product_images_sort_order_index` (`sort_order`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (33,9,'assets/banners/home-made-health-mix.jpg','Primary View',1,'2026-07-26 21:28:13','2026-07-26 21:28:13'),(34,9,'assets/banners/masala-powders.jpg','Ingredient View',2,'2026-07-26 21:28:13','2026-07-26 21:28:13'),(35,9,'assets/banners/womens-clothing.jpg','Store View',3,'2026-07-26 21:28:13','2026-07-26 21:28:13'),(36,6,'assets/products/lenovo-100e/lenovo-100e-01.jpg','Open front view',1,'2026-07-26 21:28:13','2026-07-26 21:28:13'),(37,6,'assets/products/lenovo-100e/lenovo-100e-02.jpg','Side ports view',2,'2026-07-26 21:28:13','2026-07-26 21:28:13'),(38,6,'assets/products/lenovo-100e/lenovo-100e-03.jpg','Touch display angle',3,'2026-07-26 21:28:13','2026-07-26 21:28:13'),(39,6,'assets/products/lenovo-100e/lenovo-100e-04.jpg','Hinge profile view',4,'2026-07-26 21:28:13','2026-07-26 21:28:13'),(40,6,'assets/products/lenovo-100e/lenovo-100e-05.jpg','Top lid view',5,'2026-07-26 21:28:13','2026-07-26 21:28:13');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `colour` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Standard',
  `colour_hex` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#d8ccb9',
  `size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Standard',
  `option_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` int unsigned DEFAULT NULL,
  `stock` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_product_id_colour_size_unique` (`product_id`,`colour`,`size`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  KEY `product_variants_product_id_stock_index` (`product_id`,`stock`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (6,6,'SS-LEN100E-HDD','Standard','#d8ccb9','500GB HDD','Storage','500GB HDD',9000,5,'2026-07-24 03:02:46','2026-07-24 05:13:30'),(7,6,'SS-LEN100E-SSD','Standard','#d8ccb9','500GB SSD','Storage','500GB SSD',11000,5,'2026-07-24 03:02:46','2026-07-24 03:02:46'),(10,9,'SS-STAGE-001','Standard','#d8ccb9','Standard',NULL,NULL,NULL,99,'2026-07-26 21:28:13','2026-07-26 21:28:13');
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned NOT NULL,
  `tax_slab_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `collection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sushako Signature',
  `subcategory` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sushako',
  `badge` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Featured',
  `short_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_description` text COLLATE utf8mb4_unicode_ci,
  `fabric` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sleeve` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pattern` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occasion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_of_origin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'India',
  `return_policy` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Easy return support',
  `mrp` int unsigned NOT NULL,
  `selling_price` int unsigned NOT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT '5.0',
  `reviews` int unsigned NOT NULL DEFAULT '0',
  `is_published` tinyint(1) NOT NULL DEFAULT '1',
  `is_new` tinyint(1) NOT NULL DEFAULT '1',
  `is_best_seller` tinyint(1) NOT NULL DEFAULT '0',
  `local_delivery` tinyint(1) NOT NULL DEFAULT '1',
  `fulfillment_scope` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Sushako Fulfillment',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `seller_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `product_condition` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refurbishment_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `refurbishment_grade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condition_description` text COLLATE utf8mb4_unicode_ci,
  `cosmetic_condition` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `testing_details` text COLLATE utf8mb4_unicode_ci,
  `warranty_period` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `package_contents` text COLLATE utf8mb4_unicode_ci,
  `weight_grams` int unsigned DEFAULT NULL,
  `length_cm` decimal(10,2) DEFAULT NULL,
  `width_cm` decimal(10,2) DEFAULT NULL,
  `height_cm` decimal(10,2) DEFAULT NULL,
  `low_stock_threshold` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_id_is_published_index` (`category_id`,`is_published`),
  KEY `products_is_published_index` (`is_published`),
  KEY `products_is_new_index` (`is_new`),
  KEY `products_is_best_seller_index` (`is_best_seller`),
  KEY `products_sort_order_index` (`sort_order`),
  KEY `products_tax_slab_id_foreign` (`tax_slab_id`),
  KEY `products_vendor_id_seller_status_index` (`vendor_id`,`seller_status`),
  KEY `products_seller_status_index` (`seller_status`),
  KEY `products_vendor_seller_status_idx` (`vendor_id`,`seller_status`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_tax_slab_id_foreign` FOREIGN KEY (`tax_slab_id`) REFERENCES `tax_slabs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (6,NULL,5,NULL,'Lenovo 100e Celeron Laptop','lenovo-100e-celeron-laptop','Sushako Budget Tech','Budgeted Laptop','Lenovo','Budget Pick','Lenovo 100e Celeron laptop with 4GB RAM and a choice of 500GB HDD or 500GB SSD storage.','A practical Lenovo 100e Celeron laptop for students, browsing, online classes, billing work and everyday office use. Choose the 500GB HDD model for the lowest price or the 500GB SSD model for faster startup and smoother daily performance.','Intel Celeron','4GB RAM','500GB HDD or 500GB SSD','Lenovo 100e compact laptop','Students, browsing and office basics','India','Sushako support with invoice after order',11000,9000,4.7,8,1,1,1,1,'Sushako Electronics Fulfillment',10,'2026-07-24 03:02:46','2026-07-24 03:02:46','draft',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(9,NULL,2,NULL,'Sushako Staging Sample Product','sushako-razorpay-test-product','Payment Testing','Staging Sample','Sushako','Staging','A one rupee staging product for checking cart, order creation and payment gateway flow before launch.','This staging sample product is kept only to confirm checkout, order ID, invoice and payment gateway behavior before real products are published.','Staging Sample','Standard','Not Applicable','Sample','Gateway Check','India','Not for customer sale',1,1,5.0,1,1,1,0,1,'Sushako Fulfillment',999,'2026-07-26 21:28:13','2026-07-26 21:28:13','draft',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_api_integrations`
--

DROP TABLE IF EXISTS `seller_api_integrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_api_integrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` text COLLATE utf8mb4_unicode_ci,
  `api_secret` text COLLATE utf8mb4_unicode_ci,
  `webhook_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_connected',
  `last_successful_sync_at` timestamp NULL DEFAULT NULL,
  `last_error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seller_api_integrations_vendor_id_foreign` (`vendor_id`),
  KEY `seller_api_integrations_status_index` (`status`),
  CONSTRAINT `seller_api_integrations_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_api_integrations`
--

LOCK TABLES `seller_api_integrations` WRITE;
/*!40000 ALTER TABLE `seller_api_integrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_api_integrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_audit_logs`
--

DROP TABLE IF EXISTS `seller_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `actor_user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seller_audit_logs_vendor_id_foreign` (`vendor_id`),
  KEY `seller_audit_logs_actor_user_id_foreign` (`actor_user_id`),
  KEY `seller_audit_logs_action_index` (`action`),
  CONSTRAINT `seller_audit_logs_actor_user_id_foreign` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_audit_logs_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_audit_logs`
--

LOCK TABLES `seller_audit_logs` WRITE;
/*!40000 ALTER TABLE `seller_audit_logs` DISABLE KEYS */;
INSERT INTO `seller_audit_logs` VALUES (1,5,24,'initial_onboarding_submitted','Seller submitted initial onboarding.','{\"plan\": \"growth\", \"price\": 999}','2026-07-27 04:22:34','2026-07-27 04:22:34'),(2,5,24,'seller_payment_order_created','Seller onboarding payment order created.','{\"payment_id\": 1, \"provider_order_id\": \"order_TIOSFMIPl25VoY\"}','2026-07-27 04:22:35','2026-07-27 04:22:35'),(3,5,24,'initial_onboarding_submitted','Seller submitted initial onboarding.','{\"plan\": \"free\", \"price\": 0}','2026-07-27 04:23:07','2026-07-27 04:23:07'),(4,5,24,'starter_plan_activated','Starter onboarding activated.','[]','2026-07-27 04:23:07','2026-07-27 04:23:07');
/*!40000 ALTER TABLE `seller_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_categories`
--

DROP TABLE IF EXISTS `seller_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_categories_vendor_id_category_id_unique` (`vendor_id`,`category_id`),
  KEY `seller_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `seller_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_categories_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_categories`
--

LOCK TABLES `seller_categories` WRITE;
/*!40000 ALTER TABLE `seller_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_inventory_adjustments`
--

DROP TABLE IF EXISTS `seller_inventory_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_inventory_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned NOT NULL,
  `quantity_delta` int NOT NULL,
  `stock_after` int unsigned NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adjusted_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seller_inventory_adjustments_vendor_id_foreign` (`vendor_id`),
  KEY `seller_inventory_adjustments_product_variant_id_foreign` (`product_variant_id`),
  KEY `seller_inventory_adjustments_adjusted_by_foreign` (`adjusted_by`),
  CONSTRAINT `seller_inventory_adjustments_adjusted_by_foreign` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_inventory_adjustments_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_inventory_adjustments_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_inventory_adjustments`
--

LOCK TABLES `seller_inventory_adjustments` WRITE;
/*!40000 ALTER TABLE `seller_inventory_adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_inventory_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_ledger_entries`
--

DROP TABLE IF EXISTS `seller_ledger_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_ledger_entries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `order_item_id` bigint unsigned DEFAULT NULL,
  `entry_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gross_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `commission_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'posted',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seller_ledger_entries_vendor_id_foreign` (`vendor_id`),
  KEY `seller_ledger_entries_order_id_foreign` (`order_id`),
  KEY `seller_ledger_entries_order_item_id_foreign` (`order_item_id`),
  KEY `seller_ledger_entries_created_by_foreign` (`created_by`),
  KEY `seller_ledger_entries_entry_type_index` (`entry_type`),
  KEY `seller_ledger_entries_status_index` (`status`),
  CONSTRAINT `seller_ledger_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_ledger_entries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_ledger_entries_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_ledger_entries_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_ledger_entries`
--

LOCK TABLES `seller_ledger_entries` WRITE;
/*!40000 ALTER TABLE `seller_ledger_entries` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_ledger_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_notifications`
--

DROP TABLE IF EXISTS `seller_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `action_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dedupe_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_notifications_vendor_dedupe_unique` (`vendor_id`,`dedupe_key`),
  KEY `seller_notifications_type_index` (`type`),
  CONSTRAINT `seller_notifications_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_notifications`
--

LOCK TABLES `seller_notifications` WRITE;
/*!40000 ALTER TABLE `seller_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_onboarding_payments`
--

DROP TABLE IF EXISTS `seller_onboarding_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_onboarding_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `seller_plan_id` bigint unsigned DEFAULT NULL,
  `plan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plan_name_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'INR',
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'razorpay',
  `internal_order_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_purpose` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'seller_onboarding',
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `verified_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `plan_price_snapshot` decimal(12,2) DEFAULT NULL,
  `commission_type_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_value_snapshot` decimal(8,2) DEFAULT NULL,
  `product_limit_snapshot` int unsigned DEFAULT NULL,
  `billing_period_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_onboarding_payments_provider_order_id_unique` (`provider_order_id`),
  UNIQUE KEY `seller_onboarding_payments_provider_payment_id_unique` (`provider_payment_id`),
  UNIQUE KEY `seller_onboarding_payments_internal_order_reference_unique` (`internal_order_reference`),
  KEY `seller_onboarding_payments_vendor_plan_status_idx` (`vendor_id`,`plan`,`status`),
  KEY `seller_onboarding_payments_status_index` (`status`),
  KEY `seller_onboarding_payments_seller_plan_id_foreign` (`seller_plan_id`),
  CONSTRAINT `seller_onboarding_payments_seller_plan_id_foreign` FOREIGN KEY (`seller_plan_id`) REFERENCES `seller_plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_onboarding_payments_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_onboarding_payments`
--

LOCK TABLES `seller_onboarding_payments` WRITE;
/*!40000 ALTER TABLE `seller_onboarding_payments` DISABLE KEYS */;
INSERT INTO `seller_onboarding_payments` VALUES (1,5,2,'growth','Growth',999.00,'INR','razorpay','seller-5-20260727042234-growth','order_TIOSFMIPl25VoY',NULL,NULL,'pending','seller_onboarding',NULL,NULL,'{\"commission\": \"Zero commission on orders\", \"order_limit\": \"Unlimited\", \"plan_snapshot\": {\"id\": 2, \"key\": \"growth\", \"name\": \"Growth\", \"slug\": \"growth\", \"price\": 999, \"amount\": 999, \"is_paid\": true, \"currency\": \"INR\", \"features\": [\"Up to 100 products\", \"Unlimited orders\", \"Zero commission on orders\", \"Advanced seller dashboard\", \"Professional storefront\", \"Sales and order reports\", \"Marketing tools\", \"Seller labelling features available as an add-on\", \"Priority support\", \"One-month plan validity\"], \"commission\": \"Zero commission on orders\", \"grace_days\": 0, \"order_limit\": \"Unlimited\", \"product_limit\": 100, \"validity_days\": 30, \"billing_period\": \"monthly\", \"commission_type\": \"none\", \"supporting_text\": \"Built for growing sellers who want predictable monthly pricing and no commission on orders.\", \"commission_value\": 0}, \"product_limit\": 100}','2026-07-27 04:22:34','2026-07-27 04:22:35',999.00,'none',0.00,100,'monthly');
/*!40000 ALTER TABLE `seller_onboarding_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_plan_payments`
--

DROP TABLE IF EXISTS `seller_plan_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_plan_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `plan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_payment',
  `activated_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `grace_starts_at` timestamp NULL DEFAULT NULL,
  `grace_ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seller_plan_payments_vendor_id_foreign` (`vendor_id`),
  KEY `seller_plan_payments_status_index` (`status`),
  CONSTRAINT `seller_plan_payments_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_plan_payments`
--

LOCK TABLES `seller_plan_payments` WRITE;
/*!40000 ALTER TABLE `seller_plan_payments` DISABLE KEYS */;
INSERT INTO `seller_plan_payments` VALUES (3,5,'free',0.00,NULL,'active','2026-07-27 04:23:07',NULL,NULL,NULL,'2026-07-27 04:23:07','2026-07-27 04:23:07');
/*!40000 ALTER TABLE `seller_plan_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_plans`
--

DROP TABLE IF EXISTS `seller_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `billing_period` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `product_limit` int unsigned DEFAULT NULL,
  `unlimited_products` tinyint(1) NOT NULL DEFAULT '0',
  `order_limit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commission_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `commission_value` decimal(8,2) NOT NULL DEFAULT '0.00',
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `grace_period_days` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `features` json DEFAULT NULL,
  `supporting_text` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_plans_slug_unique` (`slug`),
  KEY `seller_plans_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_plans`
--

LOCK TABLES `seller_plans` WRITE;
/*!40000 ALTER TABLE `seller_plans` DISABLE KEYS */;
INSERT INTO `seller_plans` VALUES (1,'Free','free',0.00,'none',25,0,'Unlimited','percentage',10.00,0,0,'active','[\"Commission charged on every order\", \"Product limit based on the configured free-plan limit\", \"Unlimited or configured order access\", \"Basic seller dashboard\", \"Basic storefront\", \"Self-shipping\", \"Standard reports\", \"Standard support\"]','Launch your store with no monthly commitment. A commission applies to every successful order.','2026-07-26 21:28:12','2026-07-26 21:28:12'),(2,'Growth','growth',999.00,'monthly',100,0,'Unlimited','none',0.00,1,0,'active','[\"Up to 100 products\", \"Unlimited orders\", \"Zero commission on orders\", \"Advanced seller dashboard\", \"Professional storefront\", \"Sales and order reports\", \"Marketing tools\", \"Seller labelling features available as an add-on\", \"Priority support\", \"One-month plan validity\"]','Built for growing sellers who want predictable monthly pricing and no commission on orders.','2026-07-26 21:28:12','2026-07-26 21:28:12'),(3,'Enterprise','enterprise',4999.00,'monthly',NULL,1,'Unlimited','none',0.00,1,5,'active','[\"Unlimited products\", \"Unlimited orders\", \"Zero commission\", \"Complete storefront branding\", \"Advanced analytics\", \"Premium reports\", \"Marketing tools\", \"Seller labelling included\", \"Priority support\", \"Settlement insights\", \"Five-day renewal grace period\", \"One-month plan validity\"]','A complete premium selling suite for established businesses that need scale, branding, and operational control.','2026-07-26 21:28:12','2026-07-26 21:28:12');
/*!40000 ALTER TABLE `seller_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_policy_acceptances`
--

DROP TABLE IF EXISTS `seller_policy_acceptances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_policy_acceptances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `policy_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `policy_version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '2026-07',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accepted_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_policy_vendor_key_version_unique` (`vendor_id`,`policy_key`,`policy_version`),
  KEY `seller_policy_acceptances_user_id_foreign` (`user_id`),
  CONSTRAINT `seller_policy_acceptances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_policy_acceptances_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_policy_acceptances`
--

LOCK TABLES `seller_policy_acceptances` WRITE;
/*!40000 ALTER TABLE `seller_policy_acceptances` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_policy_acceptances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_settlement_items`
--

DROP TABLE IF EXISTS `seller_settlement_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_settlement_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seller_settlement_id` bigint unsigned NOT NULL,
  `order_item_id` bigint unsigned NOT NULL,
  `gross_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `commission_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `seller_earning` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seller_settlement_items_seller_settlement_id_foreign` (`seller_settlement_id`),
  KEY `seller_settlement_items_order_item_id_foreign` (`order_item_id`),
  CONSTRAINT `seller_settlement_items_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_settlement_items_seller_settlement_id_foreign` FOREIGN KEY (`seller_settlement_id`) REFERENCES `seller_settlements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_settlement_items`
--

LOCK TABLES `seller_settlement_items` WRITE;
/*!40000 ALTER TABLE `seller_settlement_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_settlement_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_settlements`
--

DROP TABLE IF EXISTS `seller_settlements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_settlements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `settlement_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `gross_product_value` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_commission` decimal(12,2) NOT NULL DEFAULT '0.00',
  `refund_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `other_adjustments` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net_payable` decimal(12,2) NOT NULL DEFAULT '0.00',
  `expected_settlement_date` date DEFAULT NULL,
  `paid_at` date DEFAULT NULL,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_settlements_settlement_number_unique` (`settlement_number`),
  KEY `seller_settlements_vendor_id_foreign` (`vendor_id`),
  KEY `seller_settlements_status_index` (`status`),
  CONSTRAINT `seller_settlements_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_settlements`
--

LOCK TABLES `seller_settlements` WRITE;
/*!40000 ALTER TABLE `seller_settlements` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_settlements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
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
INSERT INTO `sessions` VALUES ('3hTBN8lJ61fM2kOkcZOQnZHiP8rWRFYnpLb6SfCC',24,'172.18.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','eyJfdG9rZW4iOiJFMVowM1FUOUtYN0E0TlZZalpaTmN3akxLYlFDSUNtaUFpdzFIY3hSIiwidXJsIjpbXSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cHM6XC9cL3Nob3Auc3VzaGFrby5pblwvc2VsbGVyXC9wbGFucyIsInJvdXRlIjoic2VsbGVyLnBsYW5zLmluZGV4In0sInN0YXRlIjoielpraUl1WE9RNERyeTdMTThkb1dwcTNnRWRyNGd3N1NEMGdHMHEydiIsImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoyNH0=',1785126658),('CM2zoG65EMGIcZQAKW0bsxYFg25fI0f79HbvUlcX',NULL,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36','eyJfdG9rZW4iOiJ5bTRYbnoxa1dEeFJpVE16bDJHckIxcndRWWpJNHVIRHkwUE50c2ZuIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW4iLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1785158919),('gOIthKN0TRG5hiGAtiSvqsh30486SIPVyejwP2PE',NULL,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36','eyJfdG9rZW4iOiJMRElxdVNMZlBNWWoweDJaY2VNUGRoTUZMaHMyOHYwM3g1UzBNNVZjIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1785160233),('sNrENcvXR1nXxZanpTRqHTIf1jZGKHXqejVzsNeP',NULL,'172.18.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','eyJfdG9rZW4iOiJ6dWdEcEdlTVVCeFBxWWllTmNFRUE5aGJZZ1o1SzBhV3ZXU2REQ1NuIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW4iLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1785156952),('ViIEk0SMN14LGidCbZHZ7C0JWjsrNqconbC2v4yD',NULL,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36','eyJfdG9rZW4iOiJidkFCMzRNcEJXZGpUVnBrWjdYeUFaWWJyaGJuaFBwMGlGNXhDMFNLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW4iLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1785131853);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_label_events`
--

DROP TABLE IF EXISTS `shipping_label_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_label_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `shipping_label_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shipping_label_events_shipping_label_id_foreign` (`shipping_label_id`),
  KEY `shipping_label_events_order_id_foreign` (`order_id`),
  KEY `shipping_label_events_user_id_foreign` (`user_id`),
  KEY `shipping_label_events_event_index` (`event`),
  CONSTRAINT `shipping_label_events_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shipping_label_events_shipping_label_id_foreign` FOREIGN KEY (`shipping_label_id`) REFERENCES `shipping_labels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shipping_label_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_label_events`
--

LOCK TABLES `shipping_label_events` WRITE;
/*!40000 ALTER TABLE `shipping_label_events` DISABLE KEYS */;
INSERT INTO `shipping_label_events` VALUES (1,1,18,1,'generated','{\"brand_mode\": \"sushako\", \"fulfillment_type\": \"fulfilled_by_sushako\", \"previous_label_id\": null}','2026-07-26 12:46:37','2026-07-26 12:46:37'),(2,2,18,1,'generated','{\"brand_mode\": \"sushako\", \"fulfillment_type\": \"fulfilled_by_sushako\", \"previous_label_id\": null}','2026-07-26 13:05:05','2026-07-26 13:05:05'),(3,2,18,1,'printed','{\"copies\": 1, \"reason\": null, \"printer\": null}','2026-07-26 13:05:20','2026-07-26 13:05:20');
/*!40000 ALTER TABLE `shipping_label_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_labels`
--

DROP TABLE IF EXISTS `shipping_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_labels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `label_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'generated',
  `fulfillment_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fulfilled_by_sushako',
  `brand_mode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sushako',
  `print_format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'a6_thermal',
  `courier_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `courier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warehouse_address` text COLLATE utf8mb4_unicode_ci,
  `seller_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seller_logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seller_address` text COLLATE utf8mb4_unicode_ci,
  `seller_gst` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seller_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seller_return_address` text COLLATE utf8mb4_unicode_ci,
  `package_count` int unsigned NOT NULL DEFAULT '1',
  `package_index` int unsigned NOT NULL DEFAULT '1',
  `weight_grams` int unsigned DEFAULT NULL,
  `generated_by_id` bigint unsigned DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `regenerated_by_id` bigint unsigned DEFAULT NULL,
  `regenerated_at` timestamp NULL DEFAULT NULL,
  `previous_label_id` bigint unsigned DEFAULT NULL,
  `printed_at` timestamp NULL DEFAULT NULL,
  `printed_by_id` bigint unsigned DEFAULT NULL,
  `print_count` int unsigned NOT NULL DEFAULT '0',
  `last_printed_printer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internal_lookup_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_qr_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipping_labels_label_number_unique` (`label_number`),
  UNIQUE KEY `shipping_labels_internal_lookup_code_unique` (`internal_lookup_code`),
  KEY `shipping_labels_order_id_foreign` (`order_id`),
  KEY `shipping_labels_generated_by_id_foreign` (`generated_by_id`),
  KEY `shipping_labels_regenerated_by_id_foreign` (`regenerated_by_id`),
  KEY `shipping_labels_previous_label_id_foreign` (`previous_label_id`),
  KEY `shipping_labels_printed_by_id_foreign` (`printed_by_id`),
  KEY `shipping_labels_status_printed_at_index` (`status`,`printed_at`),
  KEY `shipping_labels_generated_at_printed_at_index` (`generated_at`,`printed_at`),
  KEY `shipping_labels_status_index` (`status`),
  KEY `shipping_labels_fulfillment_type_index` (`fulfillment_type`),
  KEY `shipping_labels_brand_mode_index` (`brand_mode`),
  KEY `shipping_labels_courier_code_index` (`courier_code`),
  CONSTRAINT `shipping_labels_generated_by_id_foreign` FOREIGN KEY (`generated_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shipping_labels_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `shipping_labels_previous_label_id_foreign` FOREIGN KEY (`previous_label_id`) REFERENCES `shipping_labels` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shipping_labels_printed_by_id_foreign` FOREIGN KEY (`printed_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shipping_labels_regenerated_by_id_foreign` FOREIGN KEY (`regenerated_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_labels`
--

LOCK TABLES `shipping_labels` WRITE;
/*!40000 ALTER TABLE `shipping_labels` DISABLE KEYS */;
INSERT INTO `shipping_labels` VALUES (1,18,'SS20260700004-LBL-001',1,'generated','fulfilled_by_sushako','sushako','a6_thermal',NULL,NULL,'Sushako Dispatch','Sushako Shopping Fulfilment Desk','Sushako Shopping',NULL,'Sushako Shopping',NULL,'919876543210','Return to Sushako Shopping Fulfilment Desk',1,1,NULL,1,'2026-07-26 12:46:37',NULL,NULL,NULL,NULL,NULL,0,NULL,'SL-4V7X9FIVNZ-a55356ba',NULL,'{\"source\": \"manual_admin_generation\", \"future_courier_payload\": []}','2026-07-26 12:46:37','2026-07-26 12:46:37'),(2,18,'SS20260700004-LBL-002',2,'printed','fulfilled_by_sushako','sushako','a6_thermal',NULL,NULL,'Sushako Dispatch','Sushako Shopping Fulfilment Desk','Sushako Shopping',NULL,'Sushako Shopping',NULL,'919876543210','Return to Sushako Shopping Fulfilment Desk',1,1,NULL,1,'2026-07-26 13:05:05',NULL,NULL,NULL,'2026-07-26 13:05:20',1,1,NULL,'SL-RSGBP49Z1M-a4e16080',NULL,'{\"source\": \"manual_admin_generation\", \"future_courier_payload\": []}','2026-07-26 13:05:05','2026-07-26 13:05:20');
/*!40000 ALTER TABLE `shipping_labels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipping_settings`
--

DROP TABLE IF EXISTS `shipping_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shipping_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `free_shipping_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `free_shipping_threshold` int unsigned NOT NULL DEFAULT '1000',
  `shipping_policy_text` text COLLATE utf8mb4_unicode_ci,
  `weight_based_shipping_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `courier_integration_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `zone_based_shipping_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipping_settings`
--

LOCK TABLES `shipping_settings` WRITE;
/*!40000 ALTER TABLE `shipping_settings` DISABLE KEYS */;
INSERT INTO `shipping_settings` VALUES (1,1,1000,'Delivery charges applicable for eligible orders below the free shipping threshold. Our team will contact the customer after order confirmation regarding delivery charges and logistics.',0,0,0,'2026-07-24 05:13:27','2026-07-24 05:13:27');
/*!40000 ALTER TABLE `shipping_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `storefront_promotional_banners`
--

DROP TABLE IF EXISTS `storefront_promotional_banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `storefront_promotional_banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `desktop_image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cta_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cta_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `storefront_promotional_banners_display_order_index` (`display_order`),
  KEY `storefront_promotional_banners_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `storefront_promotional_banners`
--

LOCK TABLES `storefront_promotional_banners` WRITE;
/*!40000 ALTER TABLE `storefront_promotional_banners` DISABLE KEYS */;
/*!40000 ALTER TABLE `storefront_promotional_banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tax_slabs`
--

DROP TABLE IF EXISTS `tax_slabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tax_slabs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tax_slabs_name_rate_unique` (`name`,`rate`),
  KEY `tax_slabs_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tax_slabs`
--

LOCK TABLES `tax_slabs` WRITE;
/*!40000 ALTER TABLE `tax_slabs` DISABLE KEYS */;
INSERT INTO `tax_slabs` VALUES (1,'GST 0%',0.00,1,'2026-07-24 05:13:27','2026-07-24 05:13:27'),(2,'GST 5%',5.00,1,'2026-07-24 05:13:27','2026-07-24 05:13:27'),(3,'GST 12%',12.00,1,'2026-07-24 05:13:27','2026-07-24 05:13:27'),(4,'GST 18%',18.00,1,'2026-07-24 05:13:27','2026-07-24 05:13:27'),(5,'GST 28%',28.00,1,'2026-07-24 05:13:27','2026-07-24 05:13:27');
/*!40000 ALTER TABLE `tax_slabs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_is_whatsapp` tinyint(1) NOT NULL DEFAULT '0',
  `whatsapp_order_updates` tinyint(1) NOT NULL DEFAULT '0',
  `whatsapp_marketing_consent` tinyint(1) NOT NULL DEFAULT '0',
  `whatsapp_marketing_consent_at` timestamp NULL DEFAULT NULL,
  `whatsapp_marketing_consent_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_activity_at` timestamp NULL DEFAULT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  KEY `users_role_index` (`role`),
  KEY `users_status_index` (`status`),
  KEY `users_whatsapp_marketing_consent_index` (`whatsapp_marketing_consent`),
  KEY `users_last_activity_at_index` (`last_activity_at`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Sushako Super Admin','superadmin@sushako.test','9000000001',0,0,0,NULL,NULL,NULL,'$2y$12$pyc2nDYjbk8DsLJpjW9xdOAjRFMC3rnQIQRexrKBDC7PN3yFMwnsa','super_admin','active',0,'2026-07-26 17:50:24',NULL,NULL,NULL,NULL,NULL,'2026-07-23 12:50:35','2026-07-26 21:28:13'),(2,'Sushako Admin','admin@sushako.test','9000000002',0,0,0,NULL,NULL,NULL,'$2y$12$Lo2yMAPQN7rnTiaMITlHX.uz598fsDDwcPIG/4dGzBQYy8tylU/em','admin','inactive',0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-23 12:50:36','2026-07-26 21:28:12'),(5,'Sushako Customer','customer@sushako.test','9000000005',0,0,0,NULL,NULL,NULL,'$2y$12$UMkCUzJs2fmQF2n3QdpV.uYPKDGyWeF8mlNtnZ06IZUxBYpxYDa/u','customer','active',0,'2026-07-23 13:46:45',NULL,NULL,NULL,NULL,NULL,'2026-07-23 12:50:38','2026-07-26 21:28:13'),(6,'Koteeswaran T','kotiairport@gmail.com','9445712235',0,1,1,'2026-07-26 19:14:32','Admin verified consent','2026-07-23 19:43:08','$2y$12$J399./Wr6yKPZJwSdCsbDurjHb/Veyvq28uqYapZGxY6SZo2XKJeG','customer','active',0,'2026-07-25 19:29:04',NULL,'106769944664787550675','https://lh3.googleusercontent.com/a/ACg8ocLvXSX7sPXp9RsBiZfO7t0aK-KU7nk4yBO5aUABXV0l9i2rDw=s96-c',NULL,NULL,'2026-07-23 19:43:08','2026-07-26 19:14:32'),(24,'Sharmila V','vsharmilaui@gmail.com','9000000000',0,0,0,NULL,NULL,'2026-07-27 04:15:57','$2y$12$ZTvJIIh0eg.ngb1kWj8BDu5qmx3cOteiQVDagebDA/PXLL0KFX9j.','vendor','active',0,'2026-07-27 04:30:58',NULL,'113215432553098276413','https://lh3.googleusercontent.com/a/ACg8ocK3Wd-Q0iWxUW43FW2Yhq-61I_bYWsbpKFnFvTPwG8tcdD2tA=s96-c',NULL,NULL,'2026-07-27 04:15:57','2026-07-27 04:30:58');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `business_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `legal_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alternate_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'India',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `onboarding_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `store_display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_description` text COLLATE utf8mb4_unicode_ci,
  `business_logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gstin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `landmark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_address` text COLLATE utf8mb4_unicode_ci,
  `return_address` text COLLATE utf8mb4_unicode_ci,
  `working_days` json DEFAULT NULL,
  `opens_at` time DEFAULT NULL,
  `closes_at` time DEFAULT NULL,
  `order_cutoff_at` time DEFAULT NULL,
  `support_hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_radius` int unsigned DEFAULT NULL,
  `radius_unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_timezone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_commitment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flat_shipping_charge` decimal(12,2) DEFAULT NULL,
  `free_shipping_threshold` decimal(12,2) DEFAULT NULL,
  `local_delivery_preference` tinyint(1) NOT NULL DEFAULT '0',
  `preferred_courier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manual_tracking_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `store_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_plan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_activated_at` timestamp NULL DEFAULT NULL,
  `plan_expires_at` timestamp NULL DEFAULT NULL,
  `grace_starts_at` timestamp NULL DEFAULT NULL,
  `grace_ends_at` timestamp NULL DEFAULT NULL,
  `onboarding_step` tinyint unsigned NOT NULL DEFAULT '1',
  `onboarding_completed_at` timestamp NULL DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `vacation_starts_at` timestamp NULL DEFAULT NULL,
  `vacation_ends_at` timestamp NULL DEFAULT NULL,
  `vacation_message` text COLLATE utf8mb4_unicode_ci,
  `vacation_auto_reactivate` tinyint(1) NOT NULL DEFAULT '0',
  `bank_account_holder_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` text COLLATE utf8mb4_unicode_ci,
  `bank_ifsc` text COLLATE utf8mb4_unicode_ci,
  `bank_branch_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_upi_id` text COLLATE utf8mb4_unicode_ci,
  `bank_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_verification_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `years_in_business` smallint unsigned DEFAULT NULL,
  `store_tagline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_banner_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_support_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_support_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legal_compliance_confirmed_at` timestamp NULL DEFAULT NULL,
  `gst_certificate_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_latitude` decimal(10,7) DEFAULT NULL,
  `address_longitude` decimal(10,7) DEFAULT NULL,
  `billing_address` json DEFAULT NULL,
  `use_pickup_as_return` tinyint(1) NOT NULL DEFAULT '0',
  `use_pickup_as_billing` tinyint(1) NOT NULL DEFAULT '0',
  `delivery_settings` json DEFAULT NULL,
  `settlement_cycle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minimum_settlement_amount` decimal(12,2) DEFAULT NULL,
  `pending_settlement_notice` text COLLATE utf8mb4_unicode_ci,
  `commission_deductions_note` text COLLATE utf8mb4_unicode_ci,
  `razorpay_payment_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selected_plan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selected_plan_id` bigint unsigned DEFAULT NULL,
  `selected_plan_slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `selected_plan_snapshot` json DEFAULT NULL,
  `plan_selected_at` timestamp NULL DEFAULT NULL,
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dashboard_access_enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendors_user_id_unique` (`user_id`),
  UNIQUE KEY `vendors_business_name_unique` (`business_name`),
  UNIQUE KEY `vendors_slug_unique` (`slug`),
  KEY `vendors_approved_by_foreign` (`approved_by`),
  KEY `vendors_status_onboarding_status_index` (`status`,`onboarding_status`),
  KEY `vendors_status_index` (`status`),
  KEY `vendors_onboarding_status_index` (`onboarding_status`),
  KEY `vendors_store_status_index` (`store_status`),
  KEY `vendors_current_plan_index` (`current_plan`),
  KEY `vendors_plan_status_index` (`plan_status`),
  KEY `vendors_bank_verification_status_index` (`bank_verification_status`),
  KEY `vendors_gst_status_index` (`gst_status`),
  KEY `vendors_selected_plan_index` (`selected_plan`),
  KEY `vendors_payment_status_index` (`payment_status`),
  KEY `vendors_selected_plan_id_foreign` (`selected_plan_id`),
  KEY `vendors_selected_plan_slug_index` (`selected_plan_slug`),
  CONSTRAINT `vendors_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendors_selected_plan_id_foreign` FOREIGN KEY (`selected_plan_id`) REFERENCES `seller_plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
INSERT INTO `vendors` VALUES (5,24,'Sharmila V Store','Sharmila V','sharmila-v-store','vsharmilaui@gmail.com','9000000000',NULL,NULL,NULL,NULL,NULL,NULL,'Chengalpattu','Tamilnadu',NULL,'India',NULL,'active','complete',NULL,NULL,NULL,'2026-07-27 04:15:57','2026-07-27 04:23:07','Sharmila V Store',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Asia/Kolkata',NULL,NULL,NULL,0,NULL,1,'setup_required','free','active','2026-07-27 04:23:07',NULL,NULL,NULL,3,'2026-07-27 04:23:07',NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'not_submitted','Sharmila V','Electronics',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,'not_required','free',1,'free','{\"id\": 1, \"key\": \"free\", \"name\": \"Free\", \"slug\": \"free\", \"price\": 0, \"amount\": 0, \"is_paid\": false, \"currency\": \"INR\", \"features\": [\"Commission charged on every order\", \"Product limit based on the configured free-plan limit\", \"Unlimited or configured order access\", \"Basic seller dashboard\", \"Basic storefront\", \"Self-shipping\", \"Standard reports\", \"Standard support\"], \"commission\": \"Commission charged on every order\", \"grace_days\": 0, \"order_limit\": \"Unlimited\", \"product_limit\": 25, \"validity_days\": null, \"billing_period\": \"none\", \"commission_type\": \"percentage\", \"supporting_text\": \"Launch your store with no monthly commitment. A commission applies to every successful order.\", \"commission_value\": 10}','2026-07-27 04:23:07','not_required',1);
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-27 17:02:12
