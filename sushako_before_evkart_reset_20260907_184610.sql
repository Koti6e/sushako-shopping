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
  `parent_id` bigint unsigned DEFAULT NULL,
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
  KEY `categories_parent_id_is_active_index` (`parent_id`,`is_active`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `categories_tax_slab_id_foreign` FOREIGN KEY (`tax_slab_id`) REFERENCES `tax_slabs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (2,NULL,'Home Made Health Mix','home-made-health-mix','Homemade nutrition','Traditional health mix for daily strength','Fresh, home-style health mix powders prepared for family nutrition, breakfast routines and everyday wellness.','assets/banners/home-made-health-mix.jpg','#496d47',2,1,1,'2026-07-23 21:14:10','2026-07-27 17:15:13',1),(3,NULL,'Masala Powders','masala-powders','Fresh aroma','Small-batch masala powders','Aromatic spice powders for everyday Indian cooking, prepared with a focus on freshness, flavour and trusted ingredients.','assets/banners/masala-powders.jpg','#9b3a1d',2,1,2,'2026-07-23 21:14:10','2026-07-27 17:15:13',1),(4,NULL,'Women\'s Clothing','womens-clothing','Curated style','Women\'s clothing for every occasion','Elegant women\'s clothing selections with polished styling, clear details and Sushako support.','assets/banners/womens-clothing.jpg','#a54569',2,1,4,'2026-07-23 21:14:10','2026-07-27 17:15:13',1),(5,NULL,'Electronics','electronics','Smart essentials','Mobiles, laptops and everyday electronics','Useful electronics for work, entertainment and daily life, including laptops, mobiles, headsets and smart watches.','assets/banners/electronics.jpg','#263e68',4,1,3,'2026-07-24 02:50:00','2026-07-27 17:15:13',1),(6,NULL,'General','general',NULL,NULL,'Default category for starter seller onboarding.',NULL,'#2f6b4f',NULL,1,1,'2026-07-27 20:32:26','2026-07-27 20:32:26',1),(7,NULL,'Fashion','fashion',NULL,NULL,'Fashion and accessories for marketplace sellers.',NULL,'#2f6b4f',NULL,1,2,'2026-07-27 20:32:26','2026-07-27 20:32:26',1),(8,NULL,'Test Products','test-products','Temporary validation products','Test Products','Temporary products used for production storefront validation.','/assets/banners/electronics.jpg','#175a7a',NULL,1,999,'2026-08-19 21:13:47','2026-08-19 21:13:47',1),(9,NULL,'EV & Automotive','ev-automotive','Electric mobility & automotive essentials','EV parts, accessories, batteries and automotive products','Electric vehicle parts, accessories, batteries, chargers, electrical components, tools and automotive essentials for modern mobility.',NULL,'#00E676',4,1,10,'2026-08-31 01:05:09','2026-08-31 01:05:09',1),(10,9,'EV Accessories','ev-accessories','EV & Automotive','EV Accessories','EV Accessories for electric vehicles and automotive applications.',NULL,'#00E676',4,1,1,'2026-08-31 01:11:31','2026-08-31 01:11:31',1),(11,9,'EV Switches, Flashers & MOSFETs','ev-switches-flashers-mosfets','EV & Automotive','EV Switches, Flashers & MOSFETs','EV Switches, Flashers & MOSFETs for electric vehicles and automotive applications.',NULL,'#00E676',4,1,2,'2026-08-31 01:11:31','2026-08-31 01:11:31',1),(12,9,'Brake Pads & Discs','brake-pads-discs','EV & Automotive','Brake Pads & Discs','Brake Pads & Discs for electric vehicles and automotive applications.',NULL,'#00E676',4,1,3,'2026-08-31 01:11:31','2026-08-31 01:11:31',1),(13,9,'EV Motors & Motor Accessories','ev-motors-motor-accessories','EV & Automotive','EV Motors & Motor Accessories','EV Motors & Motor Accessories for electric vehicles and automotive applications.',NULL,'#00E676',4,1,4,'2026-08-31 01:11:31','2026-08-31 01:11:31',1),(14,9,'EV Throttles','ev-throttles','EV & Automotive','EV Throttles','EV Throttles for electric vehicles and automotive applications.',NULL,'#00E676',4,1,5,'2026-08-31 01:11:31','2026-08-31 01:11:31',1),(15,9,'OLA Spares','ola-spares','EV & Automotive','OLA Spares','OLA Spares for electric vehicles and automotive applications.',NULL,'#00E676',4,1,6,'2026-08-31 01:11:31','2026-08-31 01:11:31',1),(16,9,'EV Brake & Cable Parts','ev-brake-cable-parts','EV & Automotive','EV Brake & Cable Parts','EV Brake & Cable Parts for electric vehicles and automotive applications.',NULL,'#00E676',4,1,7,'2026-08-31 01:11:31','2026-08-31 01:11:31',1),(17,9,'EV Controllers & Kits','ev-controllers-kits','EV & Automotive','EV Controllers & Kits','EV Controllers & Kits for electric vehicles and automotive applications.',NULL,'#00E676',4,1,8,'2026-08-31 01:11:31','2026-08-31 01:11:31',1),(18,9,'DC-DC Converters','dc-dc-converters','EV & Automotive','DC-DC Converters','DC-DC Converters for electric vehicles and automotive applications.',NULL,'#00E676',4,1,9,'2026-08-31 01:11:31','2026-08-31 01:11:31',1),(19,NULL,'Grocery & Food','grocery-food','Marketplace category','Grocery & Food','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,0,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(20,19,'Groceries','grocery-food-groceries','Marketplace category','Groceries','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(21,19,'Staples','grocery-food-staples','Marketplace category','Staples','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(22,19,'Snacks','grocery-food-snacks','Marketplace category','Snacks','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(23,19,'Beverages','grocery-food-beverages','Marketplace category','Beverages','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(24,19,'Packaged Food','grocery-food-packaged-food','Marketplace category','Packaged Food','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,50,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(25,19,'Cooking Essentials','grocery-food-cooking-essentials','Marketplace category','Cooking Essentials','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,60,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(26,19,'Household Supplies','grocery-food-household-supplies','Marketplace category','Household Supplies','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,70,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(27,NULL,'Mobiles & Accessories','mobiles-accessories','Marketplace category','Mobiles & Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(28,27,'Smartphones','mobiles-accessories-smartphones','Marketplace category','Smartphones','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(29,27,'Feature Phones','mobiles-accessories-feature-phones','Marketplace category','Feature Phones','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(30,27,'Mobile Cases','mobiles-accessories-mobile-cases','Marketplace category','Mobile Cases','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(31,27,'Chargers & Cables','mobiles-accessories-chargers-cables','Marketplace category','Chargers & Cables','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(32,27,'Power Banks','mobiles-accessories-power-banks','Marketplace category','Power Banks','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,50,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(33,27,'Screen Protectors','mobiles-accessories-screen-protectors','Marketplace category','Screen Protectors','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,60,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(34,27,'Mobile Accessories','mobiles-accessories-mobile-accessories','Marketplace category','Mobile Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,70,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(35,5,'Headphones','electronics-headphones','Marketplace category','Headphones','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(36,5,'Earphones','electronics-earphones','Marketplace category','Earphones','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(37,5,'Speakers','electronics-speakers','Marketplace category','Speakers','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(38,5,'Cameras','electronics-cameras','Marketplace category','Cameras','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(39,5,'Smart Watches','electronics-smart-watches','Marketplace category','Smart Watches','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,50,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(40,5,'Gaming','electronics-gaming','Marketplace category','Gaming','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,60,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(41,5,'Electronic Accessories','electronics-electronic-accessories','Marketplace category','Electronic Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,70,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(42,NULL,'Computers & Laptops','computers-laptops','Marketplace category','Computers & Laptops','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(43,42,'Laptops','computers-laptops-laptops','Marketplace category','Laptops','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(44,42,'Desktops','computers-laptops-desktops','Marketplace category','Desktops','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(45,42,'Monitors','computers-laptops-monitors','Marketplace category','Monitors','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(46,42,'Keyboards','computers-laptops-keyboards','Marketplace category','Keyboards','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(47,42,'Mouse','computers-laptops-mouse','Marketplace category','Mouse','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,50,'2026-09-07 06:59:42','2026-09-07 06:59:42',1),(48,42,'Storage','computers-laptops-storage','Marketplace category','Storage','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,60,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(49,42,'Computer Accessories','computers-laptops-computer-accessories','Marketplace category','Computer Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,70,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(50,42,'Networking','computers-laptops-networking','Marketplace category','Networking','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,80,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(51,NULL,'Home & Kitchen','home-kitchen','Marketplace category','Home & Kitchen','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(52,51,'Kitchen','home-kitchen-kitchen','Marketplace category','Kitchen','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(53,51,'Cookware','home-kitchen-cookware','Marketplace category','Cookware','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(54,51,'Home Decor','home-kitchen-home-decor','Marketplace category','Home Decor','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(55,51,'Storage','home-kitchen-storage','Marketplace category','Storage','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(56,51,'Cleaning','home-kitchen-cleaning','Marketplace category','Cleaning','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,50,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(57,51,'Dining','home-kitchen-dining','Marketplace category','Dining','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,60,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(58,51,'Furniture','home-kitchen-furniture','Marketplace category','Furniture','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,70,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(59,51,'Home Improvement','home-kitchen-home-improvement','Marketplace category','Home Improvement','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,80,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(60,7,'Men\'s Clothing','fashion-mens-clothing','Marketplace category','Men\'s Clothing','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(61,7,'Women\'s Clothing','fashion-womens-clothing','Marketplace category','Women\'s Clothing','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(62,7,'Kids Clothing','fashion-kids-clothing','Marketplace category','Kids Clothing','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(63,7,'Footwear','fashion-footwear','Marketplace category','Footwear','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(64,7,'Bags','fashion-bags','Marketplace category','Bags','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,50,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(65,7,'Watches','fashion-watches','Marketplace category','Watches','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,60,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(66,7,'Fashion Accessories','fashion-fashion-accessories','Marketplace category','Fashion Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,70,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(67,NULL,'Beauty & Personal Care','beauty-personal-care','Marketplace category','Beauty & Personal Care','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,60,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(68,67,'Skincare','beauty-personal-care-skincare','Marketplace category','Skincare','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(69,67,'Hair Care','beauty-personal-care-hair-care','Marketplace category','Hair Care','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(70,67,'Makeup','beauty-personal-care-makeup','Marketplace category','Makeup','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(71,67,'Fragrances','beauty-personal-care-fragrances','Marketplace category','Fragrances','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(72,67,'Grooming','beauty-personal-care-grooming','Marketplace category','Grooming','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,50,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(73,67,'Personal Care','beauty-personal-care-personal-care','Marketplace category','Personal Care','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,60,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(74,NULL,'Appliances','appliances','Marketplace category','Appliances','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,70,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(75,74,'Kitchen Appliances','appliances-kitchen-appliances','Marketplace category','Kitchen Appliances','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(76,74,'Home Appliances','appliances-home-appliances','Marketplace category','Home Appliances','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(77,74,'Fans & Coolers','appliances-fans-coolers','Marketplace category','Fans & Coolers','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(78,74,'Small Appliances','appliances-small-appliances','Marketplace category','Small Appliances','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(79,74,'Large Appliances','appliances-large-appliances','Marketplace category','Large Appliances','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,50,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(80,NULL,'Baby & Kids','baby-kids','Marketplace category','Baby & Kids','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,80,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(81,80,'Baby Care','baby-kids-baby-care','Marketplace category','Baby Care','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(82,80,'Baby Clothing','baby-kids-baby-clothing','Marketplace category','Baby Clothing','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(83,80,'Kids Clothing','baby-kids-kids-clothing','Marketplace category','Kids Clothing','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(84,80,'Toys','baby-kids-toys','Marketplace category','Toys','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(85,80,'Learning','baby-kids-learning','Marketplace category','Learning','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,50,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(86,80,'School Supplies','baby-kids-school-supplies','Marketplace category','School Supplies','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,60,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(87,NULL,'Sports & Fitness','sports-fitness','Marketplace category','Sports & Fitness','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,90,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(88,87,'Fitness Equipment','sports-fitness-fitness-equipment','Marketplace category','Fitness Equipment','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(89,87,'Sports Equipment','sports-fitness-sports-equipment','Marketplace category','Sports Equipment','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(90,87,'Outdoor','sports-fitness-outdoor','Marketplace category','Outdoor','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(91,87,'Fitness Accessories','sports-fitness-fitness-accessories','Marketplace category','Fitness Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(92,NULL,'Books & Stationery','books-stationery','Marketplace category','Books & Stationery','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,100,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(93,92,'Books','books-stationery-books','Marketplace category','Books','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(94,92,'School Supplies','books-stationery-school-supplies','Marketplace category','School Supplies','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(95,92,'Office Supplies','books-stationery-office-supplies','Marketplace category','Office Supplies','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(96,92,'Art & Craft','books-stationery-art-craft','Marketplace category','Art & Craft','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(97,NULL,'Automotive','automotive','Marketplace category','Automotive','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,110,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(98,97,'Car Accessories','automotive-car-accessories','Marketplace category','Car Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(99,97,'Bike Accessories','automotive-bike-accessories','Marketplace category','Bike Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(100,97,'Vehicle Care','automotive-vehicle-care','Marketplace category','Vehicle Care','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(101,97,'Tools','automotive-tools','Marketplace category','Tools','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(102,NULL,'Jewellery & Accessories','jewellery-accessories','Marketplace category','Jewellery & Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,120,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(103,102,'Jewellery','jewellery-accessories-jewellery','Marketplace category','Jewellery','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(104,102,'Fashion Jewellery','jewellery-accessories-fashion-jewellery','Marketplace category','Fashion Jewellery','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(105,102,'Accessories','jewellery-accessories-accessories','Marketplace category','Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(106,102,'Watches','jewellery-accessories-watches','Marketplace category','Watches','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(107,NULL,'Travel & Luggage','travel-luggage','Marketplace category','Travel & Luggage','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,130,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(108,107,'Luggage','travel-luggage-luggage','Marketplace category','Luggage','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(109,107,'Backpacks','travel-luggage-backpacks','Marketplace category','Backpacks','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(110,107,'Travel Accessories','travel-luggage-travel-accessories','Marketplace category','Travel Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(111,NULL,'Pet Supplies','pet-supplies','Marketplace category','Pet Supplies','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,140,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(112,111,'Pet Food','pet-supplies-pet-food','Marketplace category','Pet Food','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(113,111,'Pet Accessories','pet-supplies-pet-accessories','Marketplace category','Pet Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(114,111,'Pet Care','pet-supplies-pet-care','Marketplace category','Pet Care','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(115,NULL,'Tools & Hardware','tools-hardware','Marketplace category','Tools & Hardware','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,150,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(116,115,'Hand Tools','tools-hardware-hand-tools','Marketplace category','Hand Tools','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(117,115,'Hardware','tools-hardware-hardware','Marketplace category','Hardware','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(118,115,'Electrical','tools-hardware-electrical','Marketplace category','Electrical','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(119,115,'Home Repair','tools-hardware-home-repair','Marketplace category','Home Repair','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,40,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(120,NULL,'Office Supplies','office-supplies','Marketplace category','Office Supplies','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,160,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(121,120,'Office Stationery','office-supplies-office-stationery','Marketplace category','Office Stationery','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(122,120,'Office Equipment','office-supplies-office-equipment','Marketplace category','Office Equipment','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,20,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(123,120,'Desk Accessories','office-supplies-desk-accessories','Marketplace category','Desk Accessories','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,30,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(124,NULL,'Other','other','Marketplace category','Other','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,170,'2026-09-07 06:59:43','2026-09-07 06:59:43',1),(125,124,'Other Products','other-other-products','Marketplace category','Other Products','Products listed in this marketplace category.',NULL,'#2563eb',NULL,1,10,'2026-09-07 06:59:43','2026-09-07 06:59:43',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cookie_consents`
--

LOCK TABLES `cookie_consents` WRITE;
/*!40000 ALTER TABLE `cookie_consents` DISABLE KEYS */;
INSERT INTO `cookie_consents` VALUES (1,NULL,'1bJfcFRvffCgYf0TImOJ2UsjXMQF35WZrRS8WWbN','essential',1,'172.18.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:153.0) Gecko/20100101 Firefox/153.0','2026-07-24 22:57:31','2026-07-24 22:57:31','2026-07-24 22:57:31'),(2,NULL,'WLUNh7HByGbW3g4xVwrh1TzDykzlzq0J3heI6ACU','essential',1,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36','2026-07-25 16:38:59','2026-07-25 16:38:59','2026-07-25 16:38:59'),(3,6,'v5bILQ7gbX0thcAO71qONkxhy9HT8HqI6EBgW2ne','essential',1,'172.18.0.1','curl/8.5.0','2026-07-25 18:59:55','2026-07-25 18:59:55','2026-07-25 18:59:55'),(4,NULL,'Lsf4wJcuKUW8YYFNlglAaIlaum4rIA6kYfOebEPF','essential',1,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36','2026-07-25 20:56:42','2026-07-25 20:56:42','2026-07-25 20:56:42'),(5,NULL,'9xbe324ZwY07jz6jJmdnLZvWVF8vWp9xV1misdHn','essential',1,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36','2026-07-26 03:02:39','2026-07-26 03:02:39','2026-07-26 03:02:39'),(6,NULL,'su2vywsyIH4jN9Ku9ABQdUk9V3PJuRZikqMVW4K4','essential',1,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Mobile Safari/537.36','2026-07-26 07:17:19','2026-07-26 07:17:19','2026-07-26 07:17:19'),(7,NULL,'V4Rooi6tXMAMvrBOEKZTtMoFJPZQIAfB9IpwhpYo','essential',1,'172.18.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1','2026-07-26 17:41:21','2026-07-26 17:41:21','2026-07-26 17:41:21'),(8,NULL,'sNrENcvXR1nXxZanpTRqHTIf1jZGKHXqejVzsNeP','essential',1,'172.18.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:152.0) Gecko/20100101 Firefox/152.0','2026-07-27 12:55:51','2026-07-27 12:55:51','2026-07-27 12:55:51'),(9,NULL,'3IZBc4I5sO0hwz5J7saxqBLVdpCmhi76WjOwqpVc','essential',1,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','2026-08-04 09:36:12','2026-08-04 09:36:12','2026-08-04 09:36:12'),(10,NULL,'3aTTVN0h0kEH7E0qnJ1exgXjsHFERpEqR2VNneh9','essential',1,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','2026-08-04 09:36:18','2026-08-04 09:36:18','2026-08-04 09:36:18'),(11,NULL,'6EfFdYYcOEv2eI7OJ3LFiHIw62Gn4KnicF6tmwcT','essential',1,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 09:36:50','2026-08-04 09:36:50','2026-08-04 09:36:50'),(12,NULL,'YLouFme4OvJzrjCxByrLS5UbTXEzgztmxJBBRqjv','essential',1,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 09:36:56','2026-08-04 09:36:56','2026-08-04 09:36:56'),(13,NULL,'BupjHlbPj4WUAEAX839EtZxl50X14LKFf6pmYbNc','essential',1,'172.18.0.1','Mozilla/5.0 (Linux; Android 16; A001T Build/BP2A.250605.031.A3; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/151.0.7922.51 Mobile Safari/537.36 Instagram 440.1.0.46.86 Android (36/16; 375dpi; 1080x2392; Nothing; A001T; Galaxian; mt6878; en_GB; 1028057492; IABMV/1)','2026-08-04 10:00:45','2026-08-04 10:00:45','2026-08-04 10:00:45'),(14,NULL,'trL1NffxSjvZr1k5fnyoxzi59pPIiXHdgMP3b1Yg','essential',1,'172.18.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-04 17:30:30','2026-08-04 17:30:30','2026-08-04 17:30:30'),(15,NULL,'svXLvrK2yZOVxtWvTFZqnMGHB3Jcos4jDJpuaNwp','essential',1,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','2026-08-05 02:43:15','2026-08-05 02:43:15','2026-08-05 02:43:15'),(16,NULL,'3vV7H5El37AOxlZOV2CFGixiwqmFqSheVIgJ6LSr','essential',1,'172.18.0.1','curl/8.5.0','2026-08-19 21:16:20','2026-08-19 21:16:20','2026-08-19 21:16:20'),(17,NULL,'875A1bqJLY616vIegKsaLZnvOymZud81rC91oNDJ','essential',1,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Mobile Safari/537.36','2026-08-21 15:06:28','2026-08-21 15:06:28','2026-08-21 15:06:28'),(18,NULL,'0kmUFqREVid1J8xz3Ktz89dKwYyYLDr2AbtU1sxc','essential',1,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36','2026-08-28 08:32:41','2026-08-28 08:32:41','2026-08-28 08:32:41'),(19,NULL,'M59OylAN7hL9wFMRejYfI3lncpzgMLPb7pNt8AuD','essential',1,'172.18.0.1','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Mobile Safari/537.36','2026-08-29 14:11:39','2026-08-29 14:11:39','2026-08-29 14:11:39'),(20,NULL,'qnkye1XDZC8rKNEbNTS96xeTIjGzFAj91stApgDJ','essential',1,'172.18.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:147.0) Gecko/20100101 Firefox/147.0','2026-09-02 21:52:52','2026-09-02 21:52:52','2026-09-02 21:52:52'),(21,NULL,'UHzCho2HE8Ov89kXDJmcv57WqLyQiFy9HrM5Sqzo','essential',1,'172.18.0.1','Mozilla/5.0 (iPhone; CPU iPhone OS 26_6_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/152.0.7977.64 Mobile/15E148 Safari/604.1','2026-09-03 06:51:01','2026-09-03 06:51:01','2026-09-03 06:51:01');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_activities`
--

LOCK TABLES `customer_activities` WRITE;
/*!40000 ALTER TABLE `customer_activities` DISABLE KEYS */;
INSERT INTO `customer_activities` VALUES (1,6,'kotiairport@gmail.com','9445712235','marketing_consent_changed','WhatsApp consent updated',NULL,NULL,'{\"source\": \"Admin verified consent\", \"marketing\": true, \"admin_user_id\": 1, \"order_updates\": true}','admin','2026-07-26 19:14:32','2026-07-26 19:14:32','2026-07-26 19:14:32'),(2,26,'mailmerajan15@gmail.com','9003213624','cart_updated','Cart updated',NULL,NULL,'{\"items\": 1}','storefront','2026-07-28 12:11:56','2026-07-28 12:11:56','2026-07-28 12:11:56'),(3,26,'mailmerajan15@gmail.com','9003213624','marketing_consent_changed','WhatsApp consent updated',NULL,NULL,'{\"source\": \"Admin verified consent\", \"marketing\": false, \"admin_user_id\": 1, \"order_updates\": true}','admin','2026-08-08 21:54:17','2026-08-08 21:54:17','2026-08-08 21:54:17');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_cart_items`
--

LOCK TABLES `customer_cart_items` WRITE;
/*!40000 ALTER TABLE `customer_cart_items` DISABLE KEYS */;
INSERT INTO `customer_cart_items` VALUES (1,1,10,11,'sushako-razorpay-test-product-Standard-Standard','Sushako Staging Sample Product','sushako-razorpay-test-product','Standard','Standard',1,1,1,'https://shop.sushako.in/assets/banners/home-made-health-mix.jpg','2026-07-28 12:11:56','2026-07-28 12:11:56');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_carts`
--

LOCK TABLES `customer_carts` WRITE;
/*!40000 ALTER TABLE `customer_carts` DISABLE KEYS */;
INSERT INTO `customer_carts` VALUES (1,26,'XV2cGZ0HvEzv5DAwCDcQ4nZwkV3pZP3ZwXv2yTfG','registered','abandoned',1,'voN11xc992mZIuY1y2kSlHh4vTc3psTN01wjd95Ho4vwAL2T','2026-08-11 12:11:56','2026-07-28 12:11:56','2026-08-05 17:26:03',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-28 12:11:56','2026-08-05 17:26:03');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_export_audits`
--

LOCK TABLES `customer_export_audits` WRITE;
/*!40000 ALTER TABLE `customer_export_audits` DISABLE KEYS */;
INSERT INTO `customer_export_audits` VALUES (1,1,'customer_list','[]',3,'2026-08-08 20:33:05','2026-08-08 20:33:05');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_management_settings`
--

LOCK TABLES `customer_management_settings` WRITE;
/*!40000 ALTER TABLE `customer_management_settings` DISABLE KEYS */;
INSERT INTO `customer_management_settings` VALUES (1,'abandoned_cart_hours','2','2026-08-05 17:26:03','2026-08-05 17:26:03');
/*!40000 ALTER TABLE `customer_management_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_status_logs`
--

DROP TABLE IF EXISTS `customer_status_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_status_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `admin_user_id` bigint unsigned DEFAULT NULL,
  `from_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_status_logs_user_id_foreign` (`user_id`),
  KEY `customer_status_logs_admin_user_id_foreign` (`admin_user_id`),
  CONSTRAINT `customer_status_logs_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customer_status_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_status_logs`
--

LOCK TABLES `customer_status_logs` WRITE;
/*!40000 ALTER TABLE `customer_status_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_status_logs` ENABLE KEYS */;
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
-- Table structure for table `financial_audit_logs`
--

DROP TABLE IF EXISTS `financial_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `financial_audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned DEFAULT NULL,
  `seller_settlement_id` bigint unsigned DEFAULT NULL,
  `admin_user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `financial_audit_logs_vendor_id_foreign` (`vendor_id`),
  KEY `financial_audit_logs_seller_settlement_id_foreign` (`seller_settlement_id`),
  KEY `financial_audit_logs_admin_user_id_foreign` (`admin_user_id`),
  KEY `financial_audit_logs_action_index` (`action`),
  CONSTRAINT `financial_audit_logs_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `financial_audit_logs_seller_settlement_id_foreign` FOREIGN KEY (`seller_settlement_id`) REFERENCES `seller_settlements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `financial_audit_logs_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `financial_audit_logs`
--

LOCK TABLES `financial_audit_logs` WRITE;
/*!40000 ALTER TABLE `financial_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `financial_audit_logs` ENABLE KEYS */;
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
-- Table structure for table `marketing_contents`
--

DROP TABLE IF EXISTS `marketing_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marketing_contents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `placement` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'homepage',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `cta_label` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `destination_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `product_collection_id` bigint unsigned DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `marketing_contents_slug_unique` (`slug`),
  KEY `marketing_contents_category_id_foreign` (`category_id`),
  KEY `marketing_contents_product_id_foreign` (`product_id`),
  KEY `marketing_contents_product_collection_id_foreign` (`product_collection_id`),
  KEY `marketing_contents_placement_is_active_display_order_index` (`placement`,`is_active`,`display_order`),
  KEY `marketing_contents_type_index` (`type`),
  KEY `marketing_contents_placement_index` (`placement`),
  KEY `marketing_contents_destination_type_index` (`destination_type`),
  KEY `marketing_contents_display_order_index` (`display_order`),
  KEY `marketing_contents_is_active_index` (`is_active`),
  KEY `marketing_contents_starts_at_index` (`starts_at`),
  KEY `marketing_contents_ends_at_index` (`ends_at`),
  CONSTRAINT `marketing_contents_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `marketing_contents_product_collection_id_foreign` FOREIGN KEY (`product_collection_id`) REFERENCES `product_collections` (`id`) ON DELETE SET NULL,
  CONSTRAINT `marketing_contents_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marketing_contents`
--

LOCK TABLES `marketing_contents` WRITE;
/*!40000 ALTER TABLE `marketing_contents` DISABLE KEYS */;
INSERT INTO `marketing_contents` VALUES (1,'hero_banner','homepage_hero','Discover What\'s New','homepage-discover-whats-new','Browse active products and marketplace categories as they become available.',NULL,'Browse Products','url','/shop',NULL,NULL,NULL,NULL,NULL,10,1,NULL,NULL,'2026-09-07 07:00:03','2026-09-07 07:00:03'),(2,'product_collection','homepage_collection','New Arrivals','homepage-new-arrivals','A collection built from products already listed in Sushako.',NULL,'View Collection','collection',NULL,NULL,NULL,1,NULL,NULL,20,1,NULL,NULL,'2026-09-07 07:00:03','2026-09-07 07:00:03'),(3,'promotional_banner','homepage_promotion','Start Selling with Zero Upfront Cost','homepage-seller-onboarding','The Sushako Free Plan is available for sellers who are ready to set up a marketplace store.',NULL,'Become a Seller','url','/seller/login',NULL,NULL,NULL,NULL,NULL,30,1,NULL,NULL,'2026-09-07 07:00:03','2026-09-07 07:00:03');
/*!40000 ALTER TABLE `marketing_contents` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_07_23_000001_add_phase_one_fields_to_users_table',1),(5,'2026_07_23_000002_create_vendors_table',1),(6,'2026_07_24_000001_add_google_fields_to_users_table',1),(7,'2026_07_24_000002_create_orders_table',1),(8,'2026_07_24_000003_add_delivery_location_to_orders_table',1),(9,'2026_07_24_000004_add_manual_shipping_fields_to_orders_table',1),(10,'2026_07_24_000005_create_customer_addresses_table',1),(11,'2026_07_24_000006_add_customer_profile_details_to_users_table',1),(12,'2026_07_24_000007_create_catalog_tables',1),(13,'2026_07_24_000008_add_catalog_links_to_order_items',1),(14,'2026_07_24_000009_add_option_pricing_to_product_variants',1),(15,'2026_07_24_000010_create_cookie_consents_table',1),(16,'2026_07_24_000011_create_operational_settings_tables',1),(17,'2026_07_26_000001_add_location_capture_fields_to_orders_table',2),(18,'2026_07_26_000002_create_shipping_labels_table',2),(19,'2026_07_26_000003_create_customer_management_tables',3),(20,'2026_07_27_000001_create_seller_platform_tables',4),(21,'2026_07_27_000002_complete_seller_onboarding_flow',5),(22,'2026_07_27_000003_create_seller_plans_and_snapshots',6),(23,'2026_07_27_000004_rebuild_seller_platform_core_fields',7),(24,'2026_07_29_000001_complete_seller_finance_and_categories',8),(25,'2026_07_29_000002_make_seller_category_marketplace_mapping_optional',9),(26,'2026_08_20_000001_add_seller_os_v2_onboarding_fields',10),(27,'2026_08_20_000002_add_seller_os_v2_product_order_workflow_fields',10),(28,'2026_08_31_002837_add_parent_id_to_categories_table',11),(29,'2026_08_31_003137_add_parent_id_to_categories_table_v2',12),(30,'2026_09_07_000001_create_marketing_content_tables',13);
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
  `eligible_quantity` int unsigned NOT NULL DEFAULT '0',
  `platform_fee_per_unit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `platform_fee_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `refund_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `return_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `retained_quantity` int unsigned DEFAULT NULL,
  `settled_quantity` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  KEY `order_items_vendor_id_settlement_status_index` (`vendor_id`,`settlement_status`),
  KEY `order_items_settlement_status_index` (`settlement_status`),
  KEY `order_items_vendor_settlement_idx` (`vendor_id`,`settlement_status`),
  KEY `order_items_vendor_platform_fee_idx` (`vendor_id`,`platform_fee_per_unit`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (6,6,NULL,NULL,NULL,'Sushako Razorpay Test Product','sushako-razorpay-test-product','Sushako Blue','Test Unit',1,1,1,0.00,0,0,0,0,'http://127.0.0.1:8000/assets/products/sushako-razorpay-test-product.svg','2026-07-23 20:22:58','2026-07-23 20:22:58',NULL,0.00,0.00,NULL,0.00,0.00,0.00,NULL,NULL,'upcoming',0,0.00,0.00,0.00,0.00,0.00,NULL,0),(7,7,NULL,6,6,'Lenovo 100e Celeron Laptop','lenovo-100e-celeron-laptop','Standard','500GB HDD',4,9000,36000,0.00,0,0,0,0,'http://127.0.0.1:8000/assets/products/lenovo-100e/lenovo-100e-01.jpg','2026-07-24 04:14:31','2026-07-24 04:14:31',NULL,0.00,0.00,NULL,0.00,0.00,0.00,NULL,NULL,'upcoming',0,0.00,0.00,0.00,0.00,0.00,NULL,0),(8,17,NULL,NULL,NULL,'Sushako Staging Sample Product','sushako-razorpay-test-product','Standard','Standard',1,1,1,5.00,0,0,0,0,'https://shop.sushako.in/assets/banners/home-made-health-mix.jpg','2026-07-25 17:15:17','2026-07-25 17:15:17',NULL,0.00,0.00,NULL,0.00,0.00,0.00,NULL,NULL,'upcoming',0,0.00,0.00,0.00,0.00,0.00,NULL,0),(9,18,NULL,NULL,NULL,'Sushako Staging Sample Product','sushako-razorpay-test-product','Standard','Standard',1,1,1,5.00,0,0,0,0,'https://shop.sushako.in/assets/banners/home-made-health-mix.jpg','2026-07-25 17:17:36','2026-07-25 17:17:36',NULL,0.00,0.00,NULL,0.00,0.00,0.00,NULL,NULL,'upcoming',0,0.00,0.00,0.00,0.00,0.00,NULL,0),(10,19,NULL,6,6,'Lenovo 100e Celeron Laptop','lenovo-100e-celeron-laptop','Standard','500GB HDD',1,9000,9000,18.00,1373,686,687,0,'https://shop.sushako.in/assets/products/lenovo-100e/lenovo-100e-01.jpg','2026-07-25 19:15:05','2026-07-25 19:15:05',NULL,0.00,0.00,NULL,0.00,0.00,0.00,NULL,NULL,'upcoming',0,0.00,0.00,0.00,0.00,0.00,NULL,0),(20,27,70,95,81,'Indicated flashers','indicated-flashers','Standard','Standard',1,55,55,18.00,8,4,4,0,'https://shop.sushako.in/assets/banners/home-made-health-mix.jpg','2026-09-07 06:08:59','2026-09-07 06:08:59','free',55.00,55.00,'flat_per_unit',1.00,1.00,54.00,'free_plan_unit_commission','2026-09-07 06:08:59','upcoming',1,1.00,1.00,0.00,0.00,0.00,1,0);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_events`
--

DROP TABLE IF EXISTS `order_status_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_status_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `actor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_events_order_id_foreign` (`order_id`),
  KEY `order_status_events_user_id_foreign` (`user_id`),
  KEY `order_status_events_actor_index` (`actor`),
  KEY `order_status_events_event_index` (`event`),
  CONSTRAINT `order_status_events_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_events`
--

LOCK TABLES `order_status_events` WRITE;
/*!40000 ALTER TABLE `order_status_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_status_events` ENABLE KEYS */;
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
  `seller_acceptance_due_at` timestamp NULL DEFAULT NULL,
  `seller_accepted_at` timestamp NULL DEFAULT NULL,
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
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipment_deadline_at` timestamp NULL DEFAULT NULL,
  `seller_overdue_at` timestamp NULL DEFAULT NULL,
  `customer_overdue_choice` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_overdue_choice_at` timestamp NULL DEFAULT NULL,
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
  KEY `orders_seller_acceptance_due_at_index` (`seller_acceptance_due_at`),
  CONSTRAINT `orders_location_updated_by_foreign` FOREIGN KEY (`location_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (6,'SS20260700001',NULL,NULL,NULL,6,'Koteeswaran T','9445712235','kotiairport@gmail.com','fksdkfsdjkfnjkasjk','jdbnfksdjkfnsdjkn','Chengalpattu','603001',NULL,'https://www.google.com/maps?q=13.0514944,80.2455552',NULL,NULL,0,NULL,NULL,NULL,1,0,'free',0,0,0,0,0,1,'payment_pending','new',NULL,NULL,'unselected','pending',NULL,NULL,NULL,NULL,'order_THoTkpBO0EvAt5',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-23 20:22:58','2026-07-25 17:11:02'),(7,'SS20260700002','INV-00001',1,'2026-07-25 17:16:31',6,'Koteeswaran T','9445712235','kotiairport@gmail.com','36e/99','mettu street','Chengalpattu','603001',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,36000,0,'free',0,0,0,0,0,36000,'placed','new',NULL,NULL,'cod','pending',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 04:16:00',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 04:14:31','2026-07-25 17:16:31'),(17,'SS20260700003',NULL,NULL,NULL,6,'Koteeswaran T','9445712235','kotiairport@gmail.com','QA Test Door 1','Live Razorpay QA','Chengalpattu','603001','QA Landmark','https://www.google.com/maps?q=12.6819,79.9888',NULL,NULL,0,NULL,NULL,NULL,1,NULL,'delivery_charges_applicable',0,0,0,0,0,1,'payment_pending','new',NULL,NULL,'unselected','pending',NULL,NULL,NULL,NULL,'order_THoYU6kbzuJr3q',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-25 17:15:17','2026-07-25 17:15:31'),(18,'SS20260700004','INV-00002',2,'2026-07-25 17:20:13',6,'Koteeswaran T','9445712235','kotiairport@gmail.com','Mettu Street',NULL,'Chengalpattu','603001',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,1,NULL,'delivery_charges_applicable',0,0,0,0,0,1,'placed','new',NULL,NULL,'razorpay','paid',NULL,NULL,NULL,NULL,'order_THoaiEubqfEex4','pay_THocYNdAD39KlD','2026-07-25 17:19:40',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-25 17:17:36','2026-07-25 17:20:13'),(19,'SS20260700005',NULL,NULL,NULL,6,'Koteeswaran T','9445712235','kotiairport@gmail.com','Mettu Street','mettu street','Chengalpattu','603001',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,9000,0,'free',1373,686,687,0,0,9000,'payment_pending','new',NULL,NULL,'unselected','pending',NULL,NULL,NULL,NULL,'order_THqanhLJbeyUIJ',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-25 19:15:05','2026-07-25 19:15:05'),(27,'SS20260900001',NULL,NULL,NULL,6,'Koteeswaran Koteeswaran','9445712235','koteeswaran.t@hotmail.com','1/424 Cheran Street',NULL,'Redhills, Chennai','600052',NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,55,NULL,'delivery_charges_applicable',8,4,4,0,0,55,'payment_pending','new',NULL,NULL,'unselected','pending',NULL,NULL,NULL,NULL,'order_TZ2hjD0VnKpMAB',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-07 06:08:59','2026-09-07 06:09:00');
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
-- Table structure for table `product_collection_product`
--

DROP TABLE IF EXISTS `product_collection_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_collection_product` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_collection_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `display_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection_product_unique` (`product_collection_id`,`product_id`),
  KEY `product_collection_product_product_id_foreign` (`product_id`),
  CONSTRAINT `product_collection_product_product_collection_id_foreign` FOREIGN KEY (`product_collection_id`) REFERENCES `product_collections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_collection_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_collection_product`
--

LOCK TABLES `product_collection_product` WRITE;
/*!40000 ALTER TABLE `product_collection_product` DISABLE KEYS */;
INSERT INTO `product_collection_product` VALUES (1,1,39,1,NULL,NULL),(2,1,40,2,NULL,NULL),(3,1,41,3,NULL,NULL),(4,1,42,4,NULL,NULL),(5,1,118,5,NULL,NULL),(6,1,31,6,NULL,NULL),(7,1,32,7,NULL,NULL),(8,1,33,8,NULL,NULL),(9,1,34,9,NULL,NULL),(10,1,35,10,NULL,NULL),(11,1,36,11,NULL,NULL),(12,1,37,12,NULL,NULL);
/*!40000 ALTER TABLE `product_collection_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_collections`
--

DROP TABLE IF EXISTS `product_collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_collections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int unsigned NOT NULL DEFAULT '0',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_collections_slug_unique` (`slug`),
  KEY `product_collections_category_id_foreign` (`category_id`),
  KEY `product_collections_is_active_index` (`is_active`),
  KEY `product_collections_display_order_index` (`display_order`),
  KEY `product_collections_starts_at_index` (`starts_at`),
  KEY `product_collections_ends_at_index` (`ends_at`),
  CONSTRAINT `product_collections_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_collections`
--

LOCK TABLES `product_collections` WRITE;
/*!40000 ALTER TABLE `product_collections` DISABLE KEYS */;
INSERT INTO `product_collections` VALUES (1,'New Arrivals','new-arrivals','Recently added products that are currently available in the marketplace.',NULL,1,10,NULL,NULL,'2026-09-07 06:59:45','2026-09-07 06:59:45');
/*!40000 ALTER TABLE `product_collections` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (41,10,'assets/banners/home-made-health-mix.jpg','Primary View',1,'2026-07-27 17:15:13','2026-07-27 17:15:13'),(42,10,'assets/banners/masala-powders.jpg','Ingredient View',2,'2026-07-27 17:15:13','2026-07-27 17:15:13'),(43,10,'assets/banners/womens-clothing.jpg','Store View',3,'2026-07-27 17:15:13','2026-07-27 17:15:13'),(44,6,'assets/products/lenovo-100e/lenovo-100e-01.jpg','Open front view',1,'2026-07-27 17:15:13','2026-07-27 17:15:13'),(45,6,'assets/products/lenovo-100e/lenovo-100e-02.jpg','Side ports view',2,'2026-07-27 17:15:13','2026-07-27 17:15:13'),(46,6,'assets/products/lenovo-100e/lenovo-100e-03.jpg','Touch display angle',3,'2026-07-27 17:15:13','2026-07-27 17:15:13'),(47,6,'assets/products/lenovo-100e/lenovo-100e-04.jpg','Hinge profile view',4,'2026-07-27 17:15:13','2026-07-27 17:15:13'),(48,6,'assets/products/lenovo-100e/lenovo-100e-05.jpg','Top lid view',5,'2026-07-27 17:15:13','2026-07-27 17:15:13'),(55,28,'/assets/banners/electronics.jpg','Primary View',0,'2026-08-19 21:13:47','2026-08-19 21:13:47'),(56,29,'seller-products/43c7b7bd-2980-4510-a184-862129a933c3.jpg','Primary View',0,'2026-08-20 14:30:06','2026-08-20 14:30:06'),(57,31,'seller-products/throttle-evkart-pro-series-throttle-waterproof-1-2-3-reverse-forward-3-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(58,32,'seller-products/throttle-evkart-pro-series-waterproof-1-2-3-push-6-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(59,33,'seller-products/throttle-evkart-pro-series-waterproof-hl-high-speedlow-speed-7-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(60,34,'seller-products/throttle-evkart-pro-series-throttle-1-2-3-reverse-forward-8-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(61,35,'seller-products/throttle-evkart-pro-series-1-2-3-9-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(62,36,'seller-products/throttle-evkart-pro-series-throttle-hl-highlow-speed-10-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(63,36,'seller-products/throttle-evkart-pro-series-throttle-hl-highlow-speed-10-2.webp','Product View',1,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(64,37,'seller-products/throttle-throttle-series-with-switch-left-right-11-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(65,38,'seller-products/throttle-evkart-pro-mono-drive-hall-effect-throttle-12-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(66,39,'seller-products/throttle-evkart-pro-twin-grip-precision-hall-throttle-set-13-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(67,40,'seller-products/throttle-evkart-pro-yufeng-z-series-precision-solo-throttle-14-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(68,41,'seller-products/throttle-evkart-pro-terra-foot-precision-accelerator-pedal-15-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(69,42,'seller-products/brake-pad-for-discs-evkart-pro-dp08-kinetic-series-performance-disc-pads-16-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(70,43,'seller-products/brake-pad-for-discs-evkart-pro-dp101-titan-series-heavy-duty-disc-pads-17-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(71,44,'seller-products/brake-pad-for-discs-evkart-pro-hydro-master-left-side-brake-pump-18-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(72,45,'seller-products/brake-pad-for-discs-evkart-pro-hydro-master-right-side-brake-pump-19-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(73,46,'seller-products/brake-pad-for-discs-evkart-pro-vision-master-left-side-hydraulic-pump-20-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(74,47,'seller-products/brake-pad-for-discs-evkart-pro-vision-master-right-side-hydraulic-pump-21-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(75,48,'seller-products/brake-pad-for-discs-evkart-pro-bls1-lt-kinetic-control-left-yoke-lever-22-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(76,49,'seller-products/brake-pad-for-discs-premium-110mm-drum-brake-shoe-high-performance-ev-braking-24-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(77,50,'seller-products/110mm-performance-drum-brake-shoe-yellow-series-heavy-duty-stopping-power-17767724360.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(78,51,'seller-products/brake-pad-for-discs-130mm-ultra-grip-drum-brake-shoe-heavy-duty-safety-upgrade-26-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(79,52,'seller-products/110mm-elite-imported-drum-brake-shoe-red-edition-precision-ev-braking-17767727810.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(80,53,'seller-products/brake-pad-for-discs-ev-pro-dot3-hydraulic-brake-fluid-precision-control-heat-resistance-28-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(81,54,'seller-products/dc-dc-convertors-ev-pro-10a-smart-dc-dc-converter-precision-12v-power-hub-29-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(82,55,'seller-products/dc-dc-convertors-cyok-10a-heavy-duty-dc-dc-converter-ultra-wide-voltage-specialist-30-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(83,56,'seller-products/ev-switches-flashers-mosfets-ev-pro-sb50-high-current-connector-set-industrial-grade-power-link-31-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(84,57,'seller-products/ev-switches-flashers-mosfets-ev-pro-sb75-smart-connector-high-power-data-integration-32-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(85,58,'seller-products/ev-switches-flashers-mosfets-ice-spring-loaded-screw-mount-male-connector-high-stability-power-link-33-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(86,59,'seller-products/ev-switches-flashers-mosfets-ev-pro-63a-safety-master-mcb-heavy-duty-circuit-protection-34-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(87,60,'seller-products/ev-switches-flashers-mosfets-ev-pro-63a-clearview-mcb-transparent-heavy-duty-protection-35-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(88,61,'seller-products/ev-switches-flashers-mosfets-ev-pro-classic-horn-switch-old-model-durable-tactile-response-36-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(89,62,'seller-products/ev-switches-flashers-mosfets-ev-pro-highlow-beam-dimmer-switch-old-model-reliable-night-control-37-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(90,63,'seller-products/ev-switches-flashers-mosfets-ev-pro-classic-indicator-switch-old-model-smooth-directional-control-38-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(91,64,'seller-products/ev-switches-flashers-mosfets-ev-pro-classic-headlight-switch-old-model-durable-master-light-control-39-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(92,65,'seller-products/ev-switches-flashers-mosfets-ev-pro-classic-ignitionstart-switch-old-model-reliable-power-start-40-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(93,66,'seller-products/ev-switches-flashers-mosfets-ev-pro-next-gen-indicator-switch-new-model-345-high-precision-signaling-41-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(94,67,'seller-products/ev-switches-flashers-mosfets-ev-pro-next-gen-headlight-switch-new-model-029a-precision-light-control-42-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(95,68,'seller-products/ev-switches-flashers-mosfets-ev-pro-next-gen-dimmer-switch-new-model-0348-high-precision-beam-control-43-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(96,69,'seller-products/ev-switches-flashers-mosfets-ev-pro-next-gen-start-switch-new-model-0c81-high-response-ignition-44-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(97,70,'seller-products/ev-switches-flashers-mosfets-ev-pro-next-gen-horn-switch-new-model-01f7-high-alert-precision-45-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(98,71,'seller-products/ev-switches-flashers-mosfets-ev-pro-3-pin-junction-box-precision-wiring-management-46-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(99,72,'seller-products/ev-switches-flashers-mosfets-ev-pro-premium-imported-horn-model-046d-high-decibel-alert-system-47-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(100,73,'seller-products/accessories-evkart-pro-premium-electric-scooter-helmet-48-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(101,74,'seller-products/accessories-aerostream-3019-urban-open-face-series-49-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(102,75,'seller-products/accessories-citylite-301a-ultra-compact-mini-cap-series-50-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(103,76,'seller-products/ev-controllers-aerodrive-3dc8-12-inch-1200w-high-hub-motor-51-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(104,77,'seller-products/ev-controllers-evkart-pro-smart-controller-48v-72v-35a-52-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(105,78,'seller-products/ev-controllers-evkart-pro-intelligent-aluminum-controller-48v-72v-35a-53-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(106,79,'seller-products/ev-controllers-evkart-pro-wruly-intelligent-controller-48v-72v-30a-54-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(107,80,'seller-products/ev-motors-motor-accessories-evkart-pro-12-high-torque-1000w-hub-motor-40v-60v-55-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(108,81,'seller-products/ev-motors-motor-accessories-evkart-pro-12-high-precision-stainless-steel-brake-disc-56-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(109,82,'seller-products/ev-motors-motor-accessories-evkart-pro-hero-optima-12-inch-high-performance-hub-motor-57-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(110,83,'seller-products/ev-motors-motor-accessories-evkart-pro-10-heavy-duty-1200w-drum-motor-130mm-58-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(111,84,'seller-products/ev-motors-motor-accessories-evkart-pro-17-elite-high-speed-1500w-hub-motor-60v-72v-59-1.webp','Primary View',0,'2026-09-07 05:27:10','2026-09-07 05:27:10'),(112,85,'seller-products/ev-motors-motor-accessories-evkart-pro-heavy-duty-1000w-motor-power-cable-black-60-1.webp','Primary View',0,'2026-09-07 05:27:11','2026-09-07 05:27:11'),(113,86,'seller-products/ev-motors-motor-accessories-evkart-pro-ultra-high-performance-2000w-motor-cable-61-1.webp','Primary View',0,'2026-09-07 05:27:11','2026-09-07 05:27:11'),(114,87,'seller-products/ev-motors-motor-accessories-evkart-pro-10-precision-alloy-wheel-rim-disc-compatible-62-1.webp','Primary View',0,'2026-09-07 05:27:11','2026-09-07 05:27:11'),(115,88,'seller-products/ev-motors-motor-accessories-evkart-pro-10-heavy-duty-alloy-wheel-rim-drum-compatible-63-1.webp','Primary View',0,'2026-09-07 05:27:11','2026-09-07 05:27:11'),(116,89,'seller-products/ev-motors-motor-accessories-evkart-pro-12-precision-alloy-wheel-rim-disc-compatible-64-1.webp','Primary View',0,'2026-09-07 05:27:11','2026-09-07 05:27:11'),(117,90,'seller-products/ev-brake-cable-parts-evkart-pro-82-heavy-duty-precision-brake-cable-65-1.webp','Primary View',0,'2026-09-07 05:27:11','2026-09-07 05:27:11'),(118,91,'seller-products/ev-brake-cable-parts-evkart-pro-52-heavy-duty-precision-brake-cable-66-1.webp','Primary View',0,'2026-09-07 05:27:11','2026-09-07 05:27:11');
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
) ENGINE=InnoDB AUTO_INCREMENT=105 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (6,6,'SS-LEN100E-HDD','Standard','#d8ccb9','500GB HDD','Storage','500GB HDD',9000,5,'2026-07-24 03:02:46','2026-07-24 05:13:30'),(7,6,'SS-LEN100E-SSD','Standard','#d8ccb9','500GB SSD','Storage','500GB SSD',11000,5,'2026-07-24 03:02:46','2026-07-24 03:02:46'),(11,10,'SS-STAGE-001','Standard','#d8ccb9','Standard',NULL,NULL,NULL,99,'2026-07-27 17:15:13','2026-07-27 17:15:13'),(14,28,'SUBASRI-STOREFRONT-TEST-1','Standard','#175a7a','Standard',NULL,NULL,1,5,'2026-08-19 21:13:47','2026-08-19 21:13:47'),(15,29,'SELLER-29-KM7G2Z','Standard','#d8ccb9','Standard',NULL,NULL,350,20,'2026-08-20 14:30:06','2026-08-20 14:30:06'),(16,30,'SELLER-30-O69MPX','Standard','#d8ccb9','Standard',NULL,NULL,6600,2,'2026-08-27 10:53:26','2026-08-31 01:39:35'),(17,31,'EVKARTPRO-3','Standard','#d8ccb9','Standard',NULL,NULL,256,6,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(18,32,'EVKARTPRO-6','Standard','#d8ccb9','Standard',NULL,NULL,256,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(19,33,'EVKARTPRO-7','Standard','#d8ccb9','Standard',NULL,NULL,256,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(20,34,'EVKARTPRO-8','Standard','#d8ccb9','Standard',NULL,NULL,128,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(21,35,'EVKARTPRO-9','Standard','#d8ccb9','Standard',NULL,NULL,128,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(22,36,'EVKARTPRO-10','Standard','#d8ccb9','Standard',NULL,NULL,128,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(23,37,'EVKARTPRO-11','Standard','#d8ccb9','Standard',NULL,NULL,560,2,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(24,38,'EVKARTPRO-12','Standard','#d8ccb9','Standard',NULL,NULL,88,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(25,39,'EVKARTPRO-13','Standard','#d8ccb9','Standard',NULL,NULL,112,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(26,40,'EVKARTPRO-14','Standard','#d8ccb9','Standard',NULL,NULL,88,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(27,41,'EVKARTPRO-15','Standard','#d8ccb9','Standard',NULL,NULL,350,3,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(28,42,'EVKARTPRO-16','Standard','#d8ccb9','Standard',NULL,NULL,75,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(29,43,'EVKARTPRO-17','Standard','#d8ccb9','Standard',NULL,NULL,90,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(30,44,'EVKARTPRO-18','Standard','#d8ccb9','Standard',NULL,NULL,0,6,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(31,45,'EVKARTPRO-19','Standard','#d8ccb9','Standard',NULL,NULL,0,6,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(32,46,'EVKARTPRO-20','Standard','#d8ccb9','Standard',NULL,NULL,368,6,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(33,47,'EVKARTPRO-21','Standard','#d8ccb9','Standard',NULL,NULL,368,6,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(34,48,'EVKARTPRO-22','Standard','#d8ccb9','Standard',NULL,NULL,140,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(35,49,'EVKARTPRO-24','Standard','#d8ccb9','Standard',NULL,NULL,80,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(36,50,'EVKARTPRO-25','Standard','#d8ccb9','Standard',NULL,NULL,74,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(37,51,'EVKARTPRO-26','Standard','#d8ccb9','Standard',NULL,NULL,93,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(38,52,'EVKARTPRO-27','Standard','#d8ccb9','Standard',NULL,NULL,145,1,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(39,53,'EVKARTPRO-28','Standard','#d8ccb9','Standard',NULL,NULL,38,49,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(40,54,'EVKARTPRO-29','Standard','#d8ccb9','Standard',NULL,NULL,170,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(41,55,'EVKARTPRO-30','Standard','#d8ccb9','Standard',NULL,NULL,140,9,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(42,56,'EVKARTPRO-31','Standard','#d8ccb9','Standard',NULL,NULL,47,50,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(43,57,'EVKARTPRO-32','Standard','#d8ccb9','Standard',NULL,NULL,199,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(44,58,'EVKARTPRO-33','Standard','#d8ccb9','Standard',NULL,NULL,30,50,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(45,59,'EVKARTPRO-34','Standard','#d8ccb9','Standard',NULL,NULL,75,24,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(46,60,'EVKARTPRO-35','Standard','#d8ccb9','Standard',NULL,NULL,90,12,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(47,61,'EVKARTPRO-36','Standard','#d8ccb9','Standard',NULL,NULL,15,100,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(48,62,'EVKARTPRO-37','Standard','#d8ccb9','Standard',NULL,NULL,15,97,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(49,63,'EVKARTPRO-38','Standard','#d8ccb9','Standard',NULL,NULL,15,98,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(50,64,'EVKARTPRO-39','Standard','#d8ccb9','Standard',NULL,NULL,15,100,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(51,65,'EVKARTPRO-40','Standard','#d8ccb9','Standard',NULL,NULL,18,98,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(52,66,'EVKARTPRO-41','Standard','#d8ccb9','Standard',NULL,NULL,18,98,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(53,67,'EVKARTPRO-42','Standard','#d8ccb9','Standard',NULL,NULL,18,98,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(54,68,'EVKARTPRO-43','Standard','#d8ccb9','Standard',NULL,NULL,18,99,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(55,69,'EVKARTPRO-44','Standard','#d8ccb9','Standard',NULL,NULL,18,96,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(56,70,'EVKARTPRO-45','Standard','#d8ccb9','Standard',NULL,NULL,18,96,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(57,71,'EVKARTPRO-46','Standard','#d8ccb9','Standard',NULL,NULL,32,60,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(58,72,'EVKARTPRO-47','Standard','#d8ccb9','Standard',NULL,NULL,112,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(59,73,'EVKARTPRO-48','Standard','#d8ccb9','Standard',NULL,NULL,300,3,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(60,74,'EVKARTPRO-49','Standard','#d8ccb9','Standard',NULL,NULL,325,3,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(61,75,'EVKARTPRO-50','Standard','#d8ccb9','Standard',NULL,NULL,300,6,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(62,76,'EVKARTPRO-51','Standard','#d8ccb9','Standard',NULL,NULL,6200,1,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(63,77,'EVKARTPRO-52','Standard','#d8ccb9','Standard',NULL,NULL,1680,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(64,78,'EVKARTPRO-53','Standard','#d8ccb9','Standard',NULL,NULL,1820,9,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(65,79,'EVKARTPRO-54','Standard','#d8ccb9','Standard',NULL,NULL,1699,8,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(66,80,'EVKARTPRO-55','Standard','#d8ccb9','Standard',NULL,NULL,6200,3,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(67,81,'EVKARTPRO-56','Standard','#d8ccb9','Standard',NULL,NULL,1399,3,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(68,82,'EVKARTPRO-57','Standard','#d8ccb9','Standard',NULL,NULL,6600,5,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(69,83,'EVKARTPRO-58','Standard','#d8ccb9','Standard',NULL,NULL,4680,5,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(70,84,'EVKARTPRO-59','Standard','#d8ccb9','Standard',NULL,NULL,13299,2,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(71,85,'EVKARTPRO-60','Standard','#d8ccb9','Standard',NULL,NULL,299,5,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(72,86,'EVKARTPRO-61','Standard','#d8ccb9','Standard',NULL,NULL,499,2,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(73,87,'EVKARTPRO-62','Standard','#d8ccb9','Standard',NULL,NULL,1430,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(74,88,'EVKARTPRO-63','Standard','#d8ccb9','Standard',NULL,NULL,1430,2,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(75,89,'EVKARTPRO-64','Standard','#d8ccb9','Standard',NULL,NULL,1699,1,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(76,90,'EVKARTPRO-65','Standard','#d8ccb9','Standard',NULL,NULL,98,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(77,91,'EVKARTPRO-66','Standard','#d8ccb9','Standard',NULL,NULL,64,19,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(78,92,'EVKARTPRO-67','Standard','#d8ccb9','Standard',NULL,NULL,64,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(79,93,'EVKARTPRO-68','Standard','#d8ccb9','Standard',NULL,NULL,80,16,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(80,94,'EVKARTPRO-133','Standard','#d8ccb9','Standard',NULL,NULL,1357,3,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(81,95,'EVKARTPRO-134','Standard','#d8ccb9','Standard',NULL,NULL,55,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(82,96,'EVKARTPRO-136','Standard','#d8ccb9','Standard',NULL,NULL,26,60,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(83,97,'EVKARTPRO-137','Standard','#d8ccb9','Standard',NULL,NULL,80,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(84,98,'EVKARTPRO-143','Standard','#d8ccb9','Standard',NULL,NULL,48,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(85,99,'EVKARTPRO-147','Standard','#d8ccb9','Standard',NULL,NULL,1120,4,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(86,100,'EVKARTPRO-148','Standard','#d8ccb9','Standard',NULL,NULL,720,2,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(87,101,'EVKARTPRO-149','Standard','#d8ccb9','Standard',NULL,NULL,80,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(88,102,'EVKARTPRO-150','Standard','#d8ccb9','Standard',NULL,NULL,720,3,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(89,103,'EVKARTPRO-151','Standard','#d8ccb9','Standard',NULL,NULL,7000,2,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(90,104,'EVKARTPRO-152','Standard','#d8ccb9','Standard',NULL,NULL,3000,2,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(91,105,'EVKARTPRO-153','Standard','#d8ccb9','Standard',NULL,NULL,45,50,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(92,106,'EVKARTPRO-154','Standard','#d8ccb9','Standard',NULL,NULL,45,50,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(93,107,'EVKARTPRO-155','Standard','#d8ccb9','Standard',NULL,NULL,750,2,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(94,108,'EVKARTPRO-156','Standard','#d8ccb9','Standard',NULL,NULL,105,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(95,109,'EVKARTPRO-157','Standard','#d8ccb9','Standard',NULL,NULL,5,100,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(96,110,'EVKARTPRO-158','Standard','#d8ccb9','Standard',NULL,NULL,840,6,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(97,111,'EVKARTPRO-159','Standard','#d8ccb9','Standard',NULL,NULL,160,10,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(98,112,'EVKARTPRO-160','Standard','#d8ccb9','Standard',NULL,NULL,36,50,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(99,113,'EVKARTPRO-161','Standard','#d8ccb9','Standard',NULL,NULL,275,5,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(100,114,'EVKARTPRO-162','Standard','#d8ccb9','Standard',NULL,NULL,270,11,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(101,115,'EVKARTPRO-163','Standard','#d8ccb9','Standard',NULL,NULL,149,20,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(102,116,'EVKARTPRO-164','Standard','#d8ccb9','Standard',NULL,NULL,149,9,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(103,117,'EVKARTPRO-166','Standard','#d8ccb9','Standard',NULL,NULL,480,5,'2026-08-31 01:39:35','2026-08-31 01:39:35'),(104,118,'EVKARTPRO-167','Standard','#d8ccb9','Standard',NULL,NULL,80,10,'2026-08-31 01:39:35','2026-08-31 01:39:35');
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
  `seller_storefront_category_id` bigint unsigned DEFAULT NULL,
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
  `buying_price` int unsigned NOT NULL DEFAULT '0',
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
  `scheduled_go_live_at` timestamp NULL DEFAULT NULL,
  `published_mode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'publish_now',
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
  `seller_rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `continue_selling_when_out_of_stock` tinyint(1) NOT NULL DEFAULT '0',
  `seller_product_views` bigint unsigned NOT NULL DEFAULT '0',
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
  KEY `products_seller_storefront_category_id_foreign` (`seller_storefront_category_id`),
  KEY `products_scheduled_go_live_at_index` (`scheduled_go_live_at`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_seller_storefront_category_id_foreign` FOREIGN KEY (`seller_storefront_category_id`) REFERENCES `seller_storefront_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_tax_slab_id_foreign` FOREIGN KEY (`tax_slab_id`) REFERENCES `tax_slabs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (6,9,5,NULL,NULL,'Lenovo 100e Celeron Laptop','lenovo-100e-celeron-laptop','Sushako Budget Tech','Budgeted Laptop','Lenovo','Budget Pick','Lenovo 100e Celeron laptop with 4GB RAM and a choice of 500GB HDD or 500GB SSD storage.','A practical Lenovo 100e Celeron laptop for students, browsing, online classes, billing work and everyday office use. Choose the 500GB HDD model for the lowest price or the 500GB SSD model for faster startup and smoother daily performance.','Intel Celeron','4GB RAM','500GB HDD or 500GB SSD','Lenovo 100e compact laptop','Students, browsing and office basics','India','Sushako support with invoice after order',11000,9000,0,4.7,8,1,1,1,1,'Sushako Electronics Fulfillment',10,'2026-07-24 03:02:46','2026-09-07 08:15:08','approved',NULL,'publish_now',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,21),(10,9,2,NULL,NULL,'Sushako Staging Sample Product','sushako-razorpay-test-product','Payment Testing','Staging Sample','Sushako','Staging','A one rupee staging product for checking cart, order creation and payment gateway flow before launch.','This staging sample product is kept only to confirm checkout, order ID, invoice and payment gateway behavior before real products are published.','Staging Sample','Standard','Not Applicable','Sample','Gateway Check','India','Not for customer sale',1,1,0,5.0,1,1,1,0,1,'Sushako Fulfillment',999,'2026-07-27 17:15:13','2026-07-29 13:30:20','approved',NULL,'publish_now',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0),(28,6,8,17,NULL,'Sushako Storefront Test Product','sushako-storefront-test-product','Seller Marketplace','Test Products','Subasri Koteeswaran Store','Seller Pick','Temporary Rs 1 product for final storefront validation. Not for actual purchase.','Temporary Rs 1 product for final storefront validation. Not for actual purchase.',NULL,NULL,NULL,NULL,NULL,'India','No Returns',1,1,0,5.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-19 21:13:47','2026-09-02 13:31:49','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,'Temporary validation item',NULL,NULL,NULL,NULL,1,NULL,0,41),(29,67,6,NULL,NULL,'Zaafi Rosemary Premium oil','zaafi-rosemary-premium-oil','Seller Marketplace','Seller Listed','Zaafi Rosemary oil','Seller Pick','Nature Meets Perfection it nourishing herbal hair oil crafted with the goodness of Rosemary, Coconut Oil, Olive Oil, Sesame Seeds, Black Zeera, Fenugreek Seeds, Walnuts, Almonds and other carefully selected natural ingredients.\r\n\r\nDesigned as a complete hair-care ritual,','Nature Meets Perfection it nourishing herbal hair oil crafted with the goodness of Rosemary, Coconut Oil, Olive Oil, Sesame Seeds, Black Zeera, Fenugreek Seeds, Walnuts, Almonds and other carefully selected natural ingredients.\r\n\r\nDesigned as a complete hair-care ritual,',NULL,NULL,NULL,NULL,NULL,'India','No Returns',400,350,350,5.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-20 14:30:06','2026-08-20 14:30:06','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,'Product package',NULL,NULL,NULL,NULL,2,NULL,1,0),(30,70,10,NULL,4,'Premium 12-Inch Electric Scooter Hub Motor for Hero Optima Series','premium-12-inch-electric-scooter-hub-motor-for-hero-optima-series','Seller Marketplace','Accessories','Evkartpro','Seller Pick','Premium 12-Inch Electric Scooter Hub Motor for Hero Optima Series for EV Applications\r\n\r\nPremium 12-Inch Electric Scooter Hub Motor for Hero Optima Series is listed on EVKart Pro as an EV spare part for electric scooter owners, repair workshops, service teams, and B2B buyers who need reliable component discovery with clean canonical product information.','Premium 12-Inch Electric Scooter Hub Motor for Hero Optima Series for EV Applications\r\n\r\nPremium 12-Inch Electric Scooter Hub Motor for Hero Optima Series is listed on EVKart Pro as an EV spare part for electric scooter owners, repair workshops, service teams, and B2B buyers who need reliable component discovery with clean canonical product information.',NULL,NULL,NULL,NULL,NULL,'India','Returns accepted within 1 day from delivery',6600,6600,6000,5.0,0,0,1,0,1,'Seller Self-Shipping',0,'2026-08-27 10:53:26','2026-09-07 10:06:06','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,'Product package',NULL,NULL,NULL,NULL,2,NULL,1,25),(31,70,14,NULL,4,'EVKart Pro Series Throttle Waterproof 1 2 3+ Reverse Forward','evkart-pro-series-throttle-waterproof-1-2-3-reverse-forward','Seller Marketplace','Throttle','Evkartpro','Seller Pick','EVKart Pro Series Throttle Waterproof 1 2 3+ Reverse Forward for EV and automotive applications.','EVKart Pro Series Throttle Waterproof 1 2 3+ Reverse Forward is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',256,256,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 10:21:52','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,18),(32,70,14,NULL,4,'EVKart Pro Series Waterproof 1 2 3 (Push)','evkart-pro-series-waterproof-1-2-3-push','Seller Marketplace','Throttle','Evkartpro','Seller Pick','EVKart Pro Series Waterproof 1 2 3 (Push) for EV and automotive applications.','EVKart Pro Series Waterproof 1 2 3 (Push) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',256,256,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 21:05:52','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(33,70,14,NULL,4,'EVKart Pro Series Waterproof HL (High Speed/Low Speed)','evkart-pro-series-waterproof-hl-high-speedlow-speed','Seller Marketplace','Throttle','Evkartpro','Seller Pick','EVKart Pro Series Waterproof HL (High Speed/Low Speed) for EV and automotive applications.','EVKart Pro Series Waterproof HL (High Speed/Low Speed) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',256,256,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-03 02:11:50','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,7),(34,70,14,NULL,4,'EVKart Pro Series Throttle  1 2 3+ Reverse Forward','evkart-pro-series-throttle-1-2-3-reverse-forward','Seller Marketplace','Throttle','Evkartpro','Seller Pick','EVKart Pro Series Throttle  1 2 3+ Reverse Forward for EV and automotive applications.','EVKart Pro Series Throttle  1 2 3+ Reverse Forward is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',128,128,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 14:55:22','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(35,70,14,NULL,4,'EVKart Pro Series 1 2 3','evkart-pro-series-1-2-3','Seller Marketplace','Throttle','Evkartpro','Seller Pick','EVKart Pro Series 1 2 3 for EV and automotive applications.','EVKart Pro Series 1 2 3 is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',128,128,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 22:41:58','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(36,70,14,NULL,4,'EVKart Pro Series Throttle HL (High/Low Speed)','evkart-pro-series-throttle-hl-highlow-speed','Seller Marketplace','Throttle','Evkartpro','Seller Pick','EVKart Pro Series Throttle HL (High/Low Speed) for EV and automotive applications.','EVKart Pro Series Throttle HL (High/Low Speed) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',128,128,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 21:27:20','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(37,70,14,NULL,4,'Throttle series with Switch Left & Right','throttle-series-with-switch-left-right','Seller Marketplace','Throttle','Evkartpro','Seller Pick','Throttle series with Switch Left & Right for EV and automotive applications.','Throttle series with Switch Left & Right is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',560,560,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 12:16:37','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(38,70,14,NULL,4,'EVKart Pro | Mono-Drive Hall Effect Throttle','evkart-pro-mono-drive-hall-effect-throttle','Seller Marketplace','Throttle','Evkartpro','Seller Pick','EVKart Pro | Mono-Drive Hall Effect Throttle for EV and automotive applications.','EVKart Pro | Mono-Drive Hall Effect Throttle is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',88,88,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 18:07:15','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(39,70,14,NULL,4,'EVKart Pro | Twin-Grip Precision Hall Throttle Set','evkart-pro-twin-grip-precision-hall-throttle-set','Seller Marketplace','Throttle','Evkartpro','Seller Pick','EVKart Pro | Twin-Grip Precision Hall Throttle Set for EV and automotive applications.','EVKart Pro | Twin-Grip Precision Hall Throttle Set is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',112,112,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 14:47:06','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(40,70,14,NULL,4,'EVKart Pro | Yufeng-Z Series Precision Solo Throttle','evkart-pro-yufeng-z-series-precision-solo-throttle','Seller Marketplace','Throttle','Evkartpro','Seller Pick','EVKart Pro | Yufeng-Z Series Precision Solo Throttle for EV and automotive applications.','EVKart Pro | Yufeng-Z Series Precision Solo Throttle is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',88,88,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 15:13:53','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(41,70,14,NULL,4,'EVKart Pro | Terra-Foot Precision Accelerator Pedal','evkart-pro-terra-foot-precision-accelerator-pedal','Seller Marketplace','Throttle','Evkartpro','Seller Pick','EVKart Pro | Terra-Foot Precision Accelerator Pedal for EV and automotive applications.','EVKart Pro | Terra-Foot Precision Accelerator Pedal is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',350,350,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 09:34:28','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(42,70,12,NULL,4,'EVKart Pro | DP08 Kinetic-Series Performance Disc Pads','evkart-pro-dp08-kinetic-series-performance-disc-pads','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','EVKart Pro | DP08 Kinetic-Series Performance Disc Pads for EV and automotive applications.','EVKart Pro | DP08 Kinetic-Series Performance Disc Pads is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',75,75,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 18:57:17','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(43,70,12,NULL,4,'EVKart Pro | DP101 Titan-Series Heavy-Duty Disc Pads','evkart-pro-dp101-titan-series-heavy-duty-disc-pads','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','EVKart Pro | DP101 Titan-Series Heavy-Duty Disc Pads for EV and automotive applications.','EVKart Pro | DP101 Titan-Series Heavy-Duty Disc Pads is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',90,90,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 17:19:27','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(44,70,12,NULL,4,'EVKart Pro | Hydro-Master Left-Side Brake Pump','evkart-pro-hydro-master-left-side-brake-pump','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','EVKart Pro | Hydro-Master Left-Side Brake Pump for EV and automotive applications.','EVKart Pro | Hydro-Master Left-Side Brake Pump is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',0,0,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 12:36:21','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(45,70,12,NULL,4,'EVKart Pro | Hydro-Master Right-Side Brake Pump','evkart-pro-hydro-master-right-side-brake-pump','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','EVKart Pro | Hydro-Master Right-Side Brake Pump for EV and automotive applications.','EVKart Pro | Hydro-Master Right-Side Brake Pump is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',0,0,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 21:30:12','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(46,70,12,NULL,4,'EVKart Pro | Vision-Master Left-Side Hydraulic Pump','evkart-pro-vision-master-left-side-hydraulic-pump','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','EVKart Pro | Vision-Master Left-Side Hydraulic Pump for EV and automotive applications.','EVKart Pro | Vision-Master Left-Side Hydraulic Pump is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',368,368,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 18:43:10','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(47,70,12,NULL,4,'EVKart Pro | Vision-Master Right-Side Hydraulic Pump','evkart-pro-vision-master-right-side-hydraulic-pump','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','EVKart Pro | Vision-Master Right-Side Hydraulic Pump for EV and automotive applications.','EVKart Pro | Vision-Master Right-Side Hydraulic Pump is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',368,368,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 11:23:28','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(48,70,12,NULL,4,'EVKart Pro | BLS1_LT Kinetic-Control Left Yoke Lever','evkart-pro-bls1-lt-kinetic-control-left-yoke-lever','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','EVKart Pro | BLS1_LT Kinetic-Control Left Yoke Lever for EV and automotive applications.','EVKart Pro | BLS1_LT Kinetic-Control Left Yoke Lever is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',140,140,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 10:59:03','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,7),(49,70,12,NULL,4,'Premium 110mm Drum Brake Shoe – High-Performance EV Braking','premium-110mm-drum-brake-shoe-high-performance-ev-braking','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','Premium 110mm Drum Brake Shoe – High-Performance EV Braking for EV and automotive applications.','Premium 110mm Drum Brake Shoe – High-Performance EV Braking is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',80,80,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 21:17:45','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(50,70,12,NULL,4,'110mm Performance Drum Brake Shoe (Yellow Series) – Heavy-Duty Stopping Power','110mm-performance-drum-brake-shoe-yellow-series-heavy-duty-stopping-power','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','110mm Performance Drum Brake Shoe (Yellow Series) – Heavy-Duty Stopping Power for EV and automotive applications.','110mm Performance Drum Brake Shoe (Yellow Series) – Heavy-Duty Stopping Power is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',74,74,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 07:05:27','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(51,70,12,NULL,4,'130mm Ultra-Grip Drum Brake Shoe – Heavy-Duty Safety Upgrade','130mm-ultra-grip-drum-brake-shoe-heavy-duty-safety-upgrade','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','130mm Ultra-Grip Drum Brake Shoe – Heavy-Duty Safety Upgrade for EV and automotive applications.','130mm Ultra-Grip Drum Brake Shoe – Heavy-Duty Safety Upgrade is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',93,93,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 07:05:17','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,8),(52,70,12,NULL,4,'110mm Elite Imported Drum Brake Shoe (Red Edition) – Precision EV Braking','110mm-elite-imported-drum-brake-shoe-red-edition-precision-ev-braking','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','110mm Elite Imported Drum Brake Shoe (Red Edition) – Precision EV Braking for EV and automotive applications.','110mm Elite Imported Drum Brake Shoe (Red Edition) – Precision EV Braking is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',145,145,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 18:54:32','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(53,70,12,NULL,4,'EV-Pro DOT3 Hydraulic Brake Fluid – Precision Control & Heat Resistance','ev-pro-dot3-hydraulic-brake-fluid-precision-control-heat-resistance','Seller Marketplace','Brake Pad for Discs','Evkartpro','Seller Pick','EV-Pro DOT3 Hydraulic Brake Fluid – Precision Control & Heat Resistance for EV and automotive applications.','EV-Pro DOT3 Hydraulic Brake Fluid – Precision Control & Heat Resistance is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',38,38,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 21:47:05','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(54,70,18,NULL,4,'EV-Pro 10A Smart DC-DC Converter – Precision 12V Power Hub','ev-pro-10a-smart-dc-dc-converter-precision-12v-power-hub','Seller Marketplace','DC-DC Convertors','Evkartpro','Seller Pick','EV-Pro 10A Smart DC-DC Converter – Precision 12V Power Hub for EV and automotive applications.','EV-Pro 10A Smart DC-DC Converter – Precision 12V Power Hub is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',170,170,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 16:49:17','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(55,70,18,NULL,4,'CYOK 10A Heavy-Duty DC-DC Converter – Ultra-Wide Voltage Specialist','cyok-10a-heavy-duty-dc-dc-converter-ultra-wide-voltage-specialist','Seller Marketplace','DC-DC Convertors','Evkartpro','Seller Pick','CYOK 10A Heavy-Duty DC-DC Converter – Ultra-Wide Voltage Specialist for EV and automotive applications.','CYOK 10A Heavy-Duty DC-DC Converter – Ultra-Wide Voltage Specialist is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',140,140,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-05 16:05:35','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(56,70,11,NULL,4,'EV-Pro SB50 High-Current Connector Set – Industrial-Grade Power Link','ev-pro-sb50-high-current-connector-set-industrial-grade-power-link','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro SB50 High-Current Connector Set – Industrial-Grade Power Link for EV and automotive applications.','EV-Pro SB50 High-Current Connector Set – Industrial-Grade Power Link is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',47,47,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 15:00:55','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(57,70,11,NULL,4,'EV-Pro SB75 Smart Connector – High-Power & Data Integration','ev-pro-sb75-smart-connector-high-power-data-integration','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro SB75 Smart Connector – High-Power & Data Integration for EV and automotive applications.','EV-Pro SB75 Smart Connector – High-Power & Data Integration is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',199,199,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 11:49:56','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(58,70,11,NULL,4,'ICE Spring-Loaded Screw Mount Male Connector – High-Stability Power Link','ice-spring-loaded-screw-mount-male-connector-high-stability-power-link','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','ICE Spring-Loaded Screw Mount Male Connector – High-Stability Power Link for EV and automotive applications.','ICE Spring-Loaded Screw Mount Male Connector – High-Stability Power Link is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',30,30,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 19:07:51','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(59,70,11,NULL,4,'EV-Pro 63A Safety Master MCB – Heavy-Duty Circuit Protection','ev-pro-63a-safety-master-mcb-heavy-duty-circuit-protection','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro 63A Safety Master MCB – Heavy-Duty Circuit Protection for EV and automotive applications.','EV-Pro 63A Safety Master MCB – Heavy-Duty Circuit Protection is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',75,75,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 21:00:01','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,7),(60,70,11,NULL,4,'EV-Pro 63A ClearView MCB – Transparent Heavy-Duty Protection','ev-pro-63a-clearview-mcb-transparent-heavy-duty-protection','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro 63A ClearView MCB – Transparent Heavy-Duty Protection for EV and automotive applications.','EV-Pro 63A ClearView MCB – Transparent Heavy-Duty Protection is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',90,90,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 05:15:23','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(61,70,11,NULL,4,'EV-Pro Classic Horn Switch (Old Model) – Durable Tactile Response','ev-pro-classic-horn-switch-old-model-durable-tactile-response','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro Classic Horn Switch (Old Model) – Durable Tactile Response for EV and automotive applications.','EV-Pro Classic Horn Switch (Old Model) – Durable Tactile Response is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',15,15,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 17:00:07','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(62,70,11,NULL,4,'EV-Pro High/Low Beam Dimmer Switch (Old Model) – Reliable Night Control','ev-pro-highlow-beam-dimmer-switch-old-model-reliable-night-control','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro High/Low Beam Dimmer Switch (Old Model) – Reliable Night Control for EV and automotive applications.','EV-Pro High/Low Beam Dimmer Switch (Old Model) – Reliable Night Control is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',15,15,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 23:07:33','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,7),(63,70,11,NULL,4,'EV-Pro Classic Indicator Switch (Old Model) – Smooth Directional Control','ev-pro-classic-indicator-switch-old-model-smooth-directional-control','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro Classic Indicator Switch (Old Model) – Smooth Directional Control for EV and automotive applications.','EV-Pro Classic Indicator Switch (Old Model) – Smooth Directional Control is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',15,15,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 15:10:47','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(64,70,11,NULL,4,'EV-Pro Classic Headlight Switch (Old Model) – Durable Master Light Control','ev-pro-classic-headlight-switch-old-model-durable-master-light-control','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro Classic Headlight Switch (Old Model) – Durable Master Light Control for EV and automotive applications.','EV-Pro Classic Headlight Switch (Old Model) – Durable Master Light Control is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',15,15,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 16:30:02','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(65,70,11,NULL,4,'EV-Pro Classic Ignition/Start Switch (Old Model) – Reliable Power Start','ev-pro-classic-ignitionstart-switch-old-model-reliable-power-start','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro Classic Ignition/Start Switch (Old Model) – Reliable Power Start for EV and automotive applications.','EV-Pro Classic Ignition/Start Switch (Old Model) – Reliable Power Start is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',18,18,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 16:24:41','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(66,70,11,NULL,4,'EV-Pro Next-Gen Indicator Switch (New Model 345) – High-Precision Signaling','ev-pro-next-gen-indicator-switch-new-model-345-high-precision-signaling','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro Next-Gen Indicator Switch (New Model 345) – High-Precision Signaling for EV and automotive applications.','EV-Pro Next-Gen Indicator Switch (New Model 345) – High-Precision Signaling is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',18,18,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 08:50:15','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,4),(67,70,11,NULL,4,'EV-Pro Next-Gen Headlight Switch (New Model 029a) – Precision Light Control','ev-pro-next-gen-headlight-switch-new-model-029a-precision-light-control','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro Next-Gen Headlight Switch (New Model 029a) – Precision Light Control for EV and automotive applications.','EV-Pro Next-Gen Headlight Switch (New Model 029a) – Precision Light Control is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',18,18,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 14:40:46','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(68,70,11,NULL,4,'EV-Pro Next-Gen Dimmer Switch (New Model 0348) – High-Precision Beam Control','ev-pro-next-gen-dimmer-switch-new-model-0348-high-precision-beam-control','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro Next-Gen Dimmer Switch (New Model 0348) – High-Precision Beam Control for EV and automotive applications.','EV-Pro Next-Gen Dimmer Switch (New Model 0348) – High-Precision Beam Control is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',18,18,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 03:00:26','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(69,70,11,NULL,4,'EV-Pro Next-Gen Start Switch (New Model 0c81) – High-Response Ignition','ev-pro-next-gen-start-switch-new-model-0c81-high-response-ignition','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro Next-Gen Start Switch (New Model 0c81) – High-Response Ignition for EV and automotive applications.','EV-Pro Next-Gen Start Switch (New Model 0c81) – High-Response Ignition is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',18,18,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 06:16:53','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(70,70,11,NULL,4,'EV-Pro Next-Gen Horn Switch (New Model 01f7) – High-Alert Precision','ev-pro-next-gen-horn-switch-new-model-01f7-high-alert-precision','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro Next-Gen Horn Switch (New Model 01f7) – High-Alert Precision for EV and automotive applications.','EV-Pro Next-Gen Horn Switch (New Model 01f7) – High-Alert Precision is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',18,18,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 09:04:35','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(71,70,11,NULL,4,'EV-Pro 3-Pin Junction Box – Precision Wiring Management','ev-pro-3-pin-junction-box-precision-wiring-management','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro 3-Pin Junction Box – Precision Wiring Management for EV and automotive applications.','EV-Pro 3-Pin Junction Box – Precision Wiring Management is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',32,32,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 22:40:12','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(72,70,11,NULL,4,'EV-Pro Premium Imported Horn (Model 046d) – High-Decibel Alert System','ev-pro-premium-imported-horn-model-046d-high-decibel-alert-system','Seller Marketplace','EV Switches & Flashers Mosfets','Evkartpro','Seller Pick','EV-Pro Premium Imported Horn (Model 046d) – High-Decibel Alert System for EV and automotive applications.','EV-Pro Premium Imported Horn (Model 046d) – High-Decibel Alert System is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',112,112,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 21:31:40','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(73,70,10,NULL,4,'EVKart Pro | Premium Electric Scooter Helmet','evkart-pro-premium-electric-scooter-helmet','Seller Marketplace','Accessories','Evkartpro','Seller Pick','EVKart Pro | Premium Electric Scooter Helmet for EV and automotive applications.','EVKart Pro | Premium Electric Scooter Helmet is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',300,300,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 21:36:18','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(74,70,10,NULL,4,'AeroStream 3019 | Urban Open-Face Series','aerostream-3019-urban-open-face-series','Seller Marketplace','Accessories','Evkartpro','Seller Pick','AeroStream 3019 | Urban Open-Face Series for EV and automotive applications.','AeroStream 3019 | Urban Open-Face Series is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',325,325,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 17:50:22','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(75,70,10,NULL,4,'CityLite 301A | Ultra-Compact Mini Cap Series','citylite-301a-ultra-compact-mini-cap-series','Seller Marketplace','Accessories','Evkartpro','Seller Pick','CityLite 301A | Ultra-Compact Mini Cap Series for EV and automotive applications.','CityLite 301A | Ultra-Compact Mini Cap Series is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',300,300,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 12:19:30','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(76,70,13,NULL,4,'AeroDrive 3dc8 | 12-Inch 1200W High-Hub Motor','aerodrive-3dc8-12-inch-1200w-high-hub-motor','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','AeroDrive 3dc8 | 12-Inch 1200W High-Hub Motor for EV and automotive applications.','AeroDrive 3dc8 | 12-Inch 1200W High-Hub Motor is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',6200,6200,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 10:18:17','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,7),(77,70,17,NULL,4,'EVKart Pro | Smart Controller 48V-72V (35A)','evkart-pro-smart-controller-48v-72v-35a','Seller Marketplace','EV Controllers & Kits','Evkartpro','Seller Pick','EVKart Pro | Smart Controller 48V-72V (35A) for EV and automotive applications.','EVKart Pro | Smart Controller 48V-72V (35A) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',1680,1680,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 20:43:19','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(78,70,17,NULL,4,'EVKart Pro | Intelligent Aluminum Controller (48V-72V | 35A)','evkart-pro-intelligent-aluminum-controller-48v-72v-35a','Seller Marketplace','EV Controllers & Kits','Evkartpro','Seller Pick','EVKart Pro | Intelligent Aluminum Controller (48V-72V | 35A) for EV and automotive applications.','EVKart Pro | Intelligent Aluminum Controller (48V-72V | 35A) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',1820,1820,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-05 19:32:57','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,8),(79,70,17,NULL,4,'EVKart Pro | WRULY Intelligent Controller (48V-72V | 30A)','evkart-pro-wruly-intelligent-controller-48v-72v-30a','Seller Marketplace','EV Controllers & Kits','Evkartpro','Seller Pick','EVKart Pro | WRULY Intelligent Controller (48V-72V | 30A) for EV and automotive applications.','EVKart Pro | WRULY Intelligent Controller (48V-72V | 30A) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',1699,1699,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 12:11:57','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,7),(80,70,13,NULL,4,'EVKart Pro | 12\" High-Torque 1000W Hub Motor (40V-60V)','evkart-pro-12-high-torque-1000w-hub-motor-40v-60v','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','EVKart Pro | 12\" High-Torque 1000W Hub Motor (40V-60V) for EV and automotive applications.','EVKart Pro | 12\" High-Torque 1000W Hub Motor (40V-60V) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',6200,6200,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 21:11:38','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(81,70,13,NULL,4,'EVKart Pro | 12\" High-Precision Stainless Steel Brake Disc','evkart-pro-12-high-precision-stainless-steel-brake-disc','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','EVKart Pro | 12\" High-Precision Stainless Steel Brake Disc for EV and automotive applications.','EVKart Pro | 12\" High-Precision Stainless Steel Brake Disc is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',1399,1399,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 13:41:15','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(82,70,13,NULL,4,'EVKart Pro | Hero Optima 12-Inch High-Performance Hub Motor','evkart-pro-hero-optima-12-inch-high-performance-hub-motor','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','EVKart Pro | Hero Optima 12-Inch High-Performance Hub Motor for EV and automotive applications.','EVKart Pro | Hero Optima 12-Inch High-Performance Hub Motor is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',6600,6600,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 22:35:25','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(83,70,13,NULL,4,'EVKart Pro | 10\" Heavy-Duty 1200W Drum Motor (130mm)','evkart-pro-10-heavy-duty-1200w-drum-motor-130mm','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','EVKart Pro | 10\" Heavy-Duty 1200W Drum Motor (130mm) for EV and automotive applications.','EVKart Pro | 10\" Heavy-Duty 1200W Drum Motor (130mm) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',4680,4680,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 17:12:52','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,7),(84,70,13,NULL,4,'EVKart Pro | 17\" Elite High-Speed 1500W Hub Motor (60V-72V)','evkart-pro-17-elite-high-speed-1500w-hub-motor-60v-72v','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','EVKart Pro | 17\" Elite High-Speed 1500W Hub Motor (60V-72V) for EV and automotive applications.','EVKart Pro | 17\" Elite High-Speed 1500W Hub Motor (60V-72V) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',13299,13299,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 10:09:47','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,12),(85,70,13,NULL,4,'EVKart Pro | Heavy-Duty 1000W Motor Power Cable (Black)','evkart-pro-heavy-duty-1000w-motor-power-cable-black','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','EVKart Pro | Heavy-Duty 1000W Motor Power Cable (Black) for EV and automotive applications.','EVKart Pro | Heavy-Duty 1000W Motor Power Cable (Black) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',299,299,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 13:48:53','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(86,70,13,NULL,4,'EVKart Pro | Ultra-High-Performance 2000W Motor Cable','evkart-pro-ultra-high-performance-2000w-motor-cable','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','EVKart Pro | Ultra-High-Performance 2000W Motor Cable for EV and automotive applications.','EVKart Pro | Ultra-High-Performance 2000W Motor Cable is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',499,499,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 18:47:52','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(87,70,13,NULL,4,'EVKart Pro | 10\" Precision Alloy Wheel Rim (Disc Compatible)','evkart-pro-10-precision-alloy-wheel-rim-disc-compatible','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','EVKart Pro | 10\" Precision Alloy Wheel Rim (Disc Compatible) for EV and automotive applications.','EVKart Pro | 10\" Precision Alloy Wheel Rim (Disc Compatible) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',1430,1430,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 18:36:28','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(88,70,13,NULL,4,'EVKart Pro | 10\" Heavy-Duty Alloy Wheel Rim (Drum Compatible)','evkart-pro-10-heavy-duty-alloy-wheel-rim-drum-compatible','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','EVKart Pro | 10\" Heavy-Duty Alloy Wheel Rim (Drum Compatible) for EV and automotive applications.','EVKart Pro | 10\" Heavy-Duty Alloy Wheel Rim (Drum Compatible) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',1430,1430,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 06:10:09','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,8),(89,70,13,NULL,4,'EVKart Pro | 12\" Precision Alloy Wheel Rim (Disc Compatible)','evkart-pro-12-precision-alloy-wheel-rim-disc-compatible','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','EVKart Pro | 12\" Precision Alloy Wheel Rim (Disc Compatible) for EV and automotive applications.','EVKart Pro | 12\" Precision Alloy Wheel Rim (Disc Compatible) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',1699,1699,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 08:12:39','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,15),(90,70,16,NULL,4,'EVKart Pro | 82\" Heavy-Duty Precision Brake Cable','evkart-pro-82-heavy-duty-precision-brake-cable','Seller Marketplace','EV Brake & Cable Parts','Evkartpro','Seller Pick','EVKart Pro | 82\" Heavy-Duty Precision Brake Cable for EV and automotive applications.','EVKart Pro | 82\" Heavy-Duty Precision Brake Cable is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',98,98,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-06 22:14:41','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,11),(91,70,16,NULL,4,'EVKart Pro | 52\" Heavy-Duty Precision Brake Cable','evkart-pro-52-heavy-duty-precision-brake-cable','Seller Marketplace','EV Brake & Cable Parts','Evkartpro','Seller Pick','EVKart Pro | 52\" Heavy-Duty Precision Brake Cable for EV and automotive applications.','EVKart Pro | 52\" Heavy-Duty Precision Brake Cable is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',64,64,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 22:30:19','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,9),(92,70,16,NULL,4,'EVKart Pro | 48\" Heavy-Duty Precision Brake Cable','evkart-pro-48-heavy-duty-precision-brake-cable','Seller Marketplace','EV Brake & Cable Parts','Evkartpro','Seller Pick','EVKart Pro | 48\" Heavy-Duty Precision Brake Cable for EV and automotive applications.','EVKart Pro | 48\" Heavy-Duty Precision Brake Cable is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',64,64,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 08:12:41','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,11),(93,70,16,NULL,4,'EVKart Pro | 78\" Heavy-Duty Precision Brake Cable','evkart-pro-78-heavy-duty-precision-brake-cable','Seller Marketplace','EV Brake & Cable Parts','Evkartpro','Seller Pick','EVKart Pro | 78\" Heavy-Duty Precision Brake Cable for EV and automotive applications.','EVKart Pro | 78\" Heavy-Duty Precision Brake Cable is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',80,80,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-06 23:15:03','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,10),(94,70,13,NULL,4,'EVKartPro Precision-67 (60V/16S Li-Ion Charger)','evkartpro-precision-67-60v16s-li-ion-charger','Seller Marketplace','EV Motors & Motor Accessories','Evkartpro','Seller Pick','EVKartPro Precision-67 (60V/16S Li-Ion Charger) for EV and automotive applications.','EVKartPro Precision-67 (60V/16S Li-Ion Charger) is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',1357,1357,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 06:04:55','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,14),(95,70,10,NULL,4,'Indicated flashers','indicated-flashers','Seller Marketplace','Accessories','Evkartpro','Seller Pick','Indicated flashers for EV and automotive applications.','Indicated flashers is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',55,55,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 16:58:06','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,11),(96,70,10,NULL,4,'3 pin junction box yellow','3-pin-junction-box-yellow','Seller Marketplace','Accessories','Evkartpro','Seller Pick','3 pin junction box yellow for EV and automotive applications.','3 pin junction box yellow is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',26,26,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 20:11:21','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(97,70,10,NULL,4,'BEARING 6204 HD','bearing-6204-hd','Seller Marketplace','Accessories','Evkartpro','Seller Pick','BEARING 6204 HD for EV and automotive applications.','BEARING 6204 HD is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',80,80,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-05 10:08:32','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(98,70,10,NULL,4,'BEARING','bearing','Seller Marketplace','Accessories','Evkartpro','Seller Pick','BEARING for EV and automotive applications.','BEARING is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',48,48,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 11:20:21','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(99,70,15,NULL,4,'EVKARPRO Premium Drive Belt for OLA S1 Pro (Gen 2) Electric Scooter | High-Durability Toothed Rubber Synchronous Belt','evkarpro-premium-drive-belt-for-ola-s1-pro-gen-2-electric-scooter-high-durability-toothed-rubber-synchronous-belt','Seller Marketplace','OLA Spares','Evkartpro','Seller Pick','EVKARPRO Premium Drive Belt for OLA S1 Pro (Gen 2) Electric Scooter | High-Durability Toothed Rubber Synchronous Belt for EV and automotive applications.','EVKARPRO Premium Drive Belt for OLA S1 Pro (Gen 2) Electric Scooter | High-Durability Toothed Rubber Synchronous Belt is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',1120,1120,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 20:01:59','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(100,70,15,NULL,4,'EVKARPRO Heavy-Duty Front Motor Belt Pulley Sprocket for OLA S1 Pro (Gen 1 & Gen 2) Electric Scooter | High-Torque Precision Drive Gear','evkarpro-heavy-duty-front-motor-belt-pulley-sprocket-for-ola-s1-pro-gen-1-gen-2-electric-scooter-high-torque-precision-drive-gear','Seller Marketplace','OLA Spares','Evkartpro','Seller Pick','EVKARPRO Heavy-Duty Front Motor Belt Pulley Sprocket for OLA S1 Pro (Gen 1 & Gen 2) Electric Scooter | High-Torque Precision Drive Gear for EV and automotive applications.','EVKARPRO Heavy-Duty Front Motor Belt Pulley Sprocket for OLA S1 Pro (Gen 1 & Gen 2) Electric Scooter | High-Torque Precision Drive Gear is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',720,720,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 16:01:12','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(101,70,15,NULL,4,'EVKARPRO Premium Front Brake Pads for OLA S1 Pro & S1 Air Electric Scooter | High-Performance Ceramic-Sintered Braking Kit','evkarpro-premium-front-brake-pads-for-ola-s1-pro-s1-air-electric-scooter-high-performance-ceramic-sintered-braking-kit','Seller Marketplace','OLA Spares','Evkartpro','Seller Pick','EVKARPRO Premium Front Brake Pads for OLA S1 Pro & S1 Air Electric Scooter | High-Performance Ceramic-Sintered Braking Kit for EV and automotive applications.','EVKARPRO Premium Front Brake Pads for OLA S1 Pro & S1 Air Electric Scooter | High-Performance Ceramic-Sintered Braking Kit is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',80,80,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 15:20:35','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(102,70,10,NULL,4,'EVKARPRO Universal Digital Speedometer & Battery Indicator Dashboard for Electric Scooters | 48V, 60V, 72V Multi-Voltage Compatible HD LED Display Console','evkarpro-universal-digital-speedometer-battery-indicator-dashboard-for-electric-scooters-48v-60v-72v-multi-voltage-compatible-hd-led-display-console','Seller Marketplace','Accessories','Evkartpro','Seller Pick','EVKARPRO Universal Digital Speedometer & Battery Indicator Dashboard for Electric Scooters | 48V, 60V, 72V Multi-Voltage Compatible HD LED Display Console for EV and automotive applications.','EVKARPRO Universal Digital Speedometer & Battery Indicator Dashboard for Electric Scooters | 48V, 60V, 72V Multi-Voltage Compatible HD LED Display Console is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',720,720,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 03:08:34','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,9),(103,70,10,NULL,4,'EVKARPRO Universal Controller for All EV Bikes | Multi-Voltage 60V-72V Smart Sine Wave BLDC Motor Controller | Heavy-Duty Programmable ECU Module','evkarpro-universal-controller-for-all-ev-bikes-multi-voltage-60v-72v-smart-sine-wave-bldc-motor-controller-heavy-duty-programmable-ecu-module','Seller Marketplace','Accessories','Evkartpro','Seller Pick','EVKARPRO Universal Controller for All EV Bikes | Multi-Voltage 60V-72V Smart Sine Wave BLDC Motor Controller | Heavy-Duty Programmable ECU Module for EV and automotive applications.','EVKARPRO Universal Controller for All EV Bikes | Multi-Voltage 60V-72V Smart Sine Wave BLDC Motor Controller | Heavy-Duty Programmable ECU Module is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',7000,7000,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 00:39:21','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(104,70,10,NULL,4,'EVKARTPRO Premium Replacement Controller for Hero Electric Optima (48V / 60V) | Smart Intelligent BLDC Motor Controller Module for Hero Optima CX / HX','evkartpro-premium-replacement-controller-for-hero-electric-optima-48v-60v-smart-intelligent-bldc-motor-controller-module-for-hero-optima-cx-hx','Seller Marketplace','Accessories','Evkartpro','Seller Pick','EVKARTPRO Premium Replacement Controller for Hero Electric Optima (48V / 60V) | Smart Intelligent BLDC Motor Controller Module for Hero Optima CX / HX for EV and automotive applications.','EVKARTPRO Premium Replacement Controller for Hero Electric Optima (48V / 60V) | Smart Intelligent BLDC Motor Controller Module for Hero Optima CX / HX is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',3000,3000,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 22:52:00','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(105,70,10,NULL,4,'EVKARTPRO Premium 31mm Front Fork Oil Seal Set for Electric Bikes & Scooters (31x40x10.5) | High-Density Rubber Leak-Proof Suspension Seal Kit','evkartpro-premium-31mm-front-fork-oil-seal-set-for-electric-bikes-scooters-31x40x105-high-density-rubber-leak-proof-suspension-seal-kit','Seller Marketplace','Accessories','Evkartpro','Seller Pick','EVKARTPRO Premium 31mm Front Fork Oil Seal Set for Electric Bikes & Scooters (31x40x10.5) | High-Density Rubber Leak-Proof Suspension Seal Kit for EV and automotive applications.','EVKARTPRO Premium 31mm Front Fork Oil Seal Set for Electric Bikes & Scooters (31x40x10.5) | High-Density Rubber Leak-Proof Suspension Seal Kit is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',45,45,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 12:36:31','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(106,70,10,NULL,4,'EVKARTPRO Premium 27mm Front Fork Oil Seal Set for Electric Bikes & Scooters (27x37x10.5) | High-Performance Leak-Proof Suspension Seal Kit','evkartpro-premium-27mm-front-fork-oil-seal-set-for-electric-bikes-scooters-27x37x105-high-performance-leak-proof-suspension-seal-kit','Seller Marketplace','Accessories','Evkartpro','Seller Pick','EVKARTPRO Premium 27mm Front Fork Oil Seal Set for Electric Bikes & Scooters (27x37x10.5) | High-Performance Leak-Proof Suspension Seal Kit for EV and automotive applications.','EVKARTPRO Premium 27mm Front Fork Oil Seal Set for Electric Bikes & Scooters (27x37x10.5) | High-Performance Leak-Proof Suspension Seal Kit is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',45,45,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-06 13:19:02','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,7),(107,70,10,NULL,4,'EVKARTPRO Universal Dual Rear Shock Absorber Suspension Set for Electric Scooters | Heavy-Duty Adjustable Spring Hydraulic Damper Pair','evkartpro-universal-dual-rear-shock-absorber-suspension-set-for-electric-scooters-heavy-duty-adjustable-spring-hydraulic-damper-pair','Seller Marketplace','Accessories','Evkartpro','Seller Pick','EVKARTPRO Universal Dual Rear Shock Absorber Suspension Set for Electric Scooters | Heavy-Duty Adjustable Spring Hydraulic Damper Pair for EV and automotive applications.','EVKARTPRO Universal Dual Rear Shock Absorber Suspension Set for Electric Scooters | Heavy-Duty Adjustable Spring Hydraulic Damper Pair is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',750,750,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 18:46:46','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(108,70,10,NULL,4,'EVKARTPRO Premium Heavy-Duty Seat Lock Catch Latch Mechanism for Electric Scooters | Anti-Rattle Under-Seat Storage Lock Striker Assembly','evkartpro-premium-heavy-duty-seat-lock-catch-latch-mechanism-for-electric-scooters-anti-rattle-under-seat-storage-lock-striker-assembly','Seller Marketplace','Accessories','Evkartpro','Seller Pick','EVKARTPRO Premium Heavy-Duty Seat Lock Catch Latch Mechanism for Electric Scooters | Anti-Rattle Under-Seat Storage Lock Striker Assembly for EV and automotive applications.','EVKARTPRO Premium Heavy-Duty Seat Lock Catch Latch Mechanism for Electric Scooters | Anti-Rattle Under-Seat Storage Lock Striker Assembly is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',105,105,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-05 17:52:53','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(109,70,10,NULL,4,'EVKARTPRO Premium Body Panel U-Clips for Electric Scooters (Pack of 10) | Heavy-Duty M5/M6 Speed Nut Thimbles for Battery Lock & Chassis Fairings','evkartpro-premium-body-panel-u-clips-for-electric-scooters-pack-of-10-heavy-duty-m5m6-speed-nut-thimbles-for-battery-lock-chassis-fairings','Seller Marketplace','Accessories','Evkartpro','Seller Pick','EVKARTPRO Premium Body Panel U-Clips for Electric Scooters (Pack of 10) | Heavy-Duty M5/M6 Speed Nut Thimbles for Battery Lock & Chassis Fairings for EV and automotive applications.','EVKARTPRO Premium Body Panel U-Clips for Electric Scooters (Pack of 10) | Heavy-Duty M5/M6 Speed Nut Thimbles for Battery Lock & Chassis Fairings is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',5,5,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 13:04:37','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,7),(110,70,10,NULL,4,'Evkartpro Heavy-Duty Electric Cargo Loader Bike (Dual Battery Compatible) – Equipped with High-Performance Triple Rear Suspension System','evkartpro-heavy-duty-electric-cargo-loader-bike-dual-battery-compatible-equipped-with-high-performance-triple-rear-suspension-system','Seller Marketplace','Accessories','Evkartpro','Seller Pick','Evkartpro Heavy-Duty Electric Cargo Loader Bike (Dual Battery Compatible) – Equipped with High-Performance Triple Rear Suspension System for EV and automotive applications.','Evkartpro Heavy-Duty Electric Cargo Loader Bike (Dual Battery Compatible) – Equipped with High-Performance Triple Rear Suspension System is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',840,840,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 12:48:10','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(111,70,10,NULL,4,'evkartpro Heavy-Duty Metal Box Handle – 100mm Chrome-Plated Iron Pull Handle for EV Battery Box & Cargo Containers','evkartpro-heavy-duty-metal-box-handle-100mm-chrome-plated-iron-pull-handle-for-ev-battery-box-cargo-containers','Seller Marketplace','Accessories','Evkartpro','Seller Pick','evkartpro Heavy-Duty Metal Box Handle – 100mm Chrome-Plated Iron Pull Handle for EV Battery Box & Cargo Containers for EV and automotive applications.','evkartpro Heavy-Duty Metal Box Handle – 100mm Chrome-Plated Iron Pull Handle for EV Battery Box & Cargo Containers is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',160,160,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 19:16:22','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(112,70,10,NULL,4,'evkartpro Premium 6201-2RS Universal Ball Bearing – Heavy-Duty Rubber Sealed Deep Groove Bearing for Electric Scooters & E-Bikes','evkartpro-premium-6201-2rs-universal-ball-bearing-heavy-duty-rubber-sealed-deep-groove-bearing-for-electric-scooters-e-bikes','Seller Marketplace','Accessories','Evkartpro','Seller Pick','evkartpro Premium 6201-2RS Universal Ball Bearing – Heavy-Duty Rubber Sealed Deep Groove Bearing for Electric Scooters & E-Bikes for EV and automotive applications.','evkartpro Premium 6201-2RS Universal Ball Bearing – Heavy-Duty Rubber Sealed Deep Groove Bearing for Electric Scooters & E-Bikes is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',36,36,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 21:09:43','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(113,70,10,NULL,4,'Universal EV Ignition Switch Lock Assembly with Key & Seat Lock for Ampere, Komaki, Okinawa Electric Scooters','universal-ev-ignition-switch-lock-assembly-with-key-seat-lock-for-ampere-komaki-okinawa-electric-scooters','Seller Marketplace','Accessories','Evkartpro','Seller Pick','Universal EV Ignition Switch Lock Assembly with Key & Seat Lock for Ampere, Komaki, Okinawa Electric Scooters for EV and automotive applications.','Universal EV Ignition Switch Lock Assembly with Key & Seat Lock for Ampere, Komaki, Okinawa Electric Scooters is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',275,275,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 06:16:50','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(114,70,10,NULL,4,'Premium EV Ignition Switch Lock Assembly with Key & Seat Lock Set for Hero Electric Optima, Hero Nyx','premium-ev-ignition-switch-lock-assembly-with-key-seat-lock-set-for-hero-electric-optima-hero-nyx','Seller Marketplace','Accessories','Evkartpro','Seller Pick','Premium EV Ignition Switch Lock Assembly with Key & Seat Lock Set for Hero Electric Optima, Hero Nyx for EV and automotive applications.','Premium EV Ignition Switch Lock Assembly with Key & Seat Lock Set for Hero Electric Optima, Hero Nyx is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',270,270,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-02 12:53:07','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(115,70,10,NULL,4,'Premium EV Front Fork Steering Cone Set (Racer Kit) with Ball Bearings for Hero Optima, Hero Nyx, Pure EV Scooters','premium-ev-front-fork-steering-cone-set-racer-kit-with-ball-bearings-for-hero-optima-hero-nyx-pure-ev-scooters','Seller Marketplace','Accessories','Evkartpro','Seller Pick','Premium EV Front Fork Steering Cone Set (Racer Kit) with Ball Bearings for Hero Optima, Hero Nyx, Pure EV Scooters for EV and automotive applications.','Premium EV Front Fork Steering Cone Set (Racer Kit) with Ball Bearings for Hero Optima, Hero Nyx, Pure EV Scooters is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',149,149,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 03:25:53','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,6),(116,70,10,NULL,4,'Premium EV Steering Cone Set (Racer Kit) with Ball Bearings for Okinawa, Komaki, Pure EV Scooters','premium-ev-steering-cone-set-racer-kit-with-ball-bearings-for-okinawa-komaki-pure-ev-scooters','Seller Marketplace','Accessories','Evkartpro','Seller Pick','Premium EV Steering Cone Set (Racer Kit) with Ball Bearings for Okinawa, Komaki, Pure EV Scooters for EV and automotive applications.','Premium EV Steering Cone Set (Racer Kit) with Ball Bearings for Okinawa, Komaki, Pure EV Scooters is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',149,149,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-04 09:40:39','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,5),(117,70,15,NULL,4,'Ola side stand','ola-side-stand','Seller Marketplace','OLA Spares','Evkartpro','Seller Pick','Ola side stand for EV and automotive applications.','Ola side stand is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',480,480,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 10:03:27','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,8),(118,70,15,NULL,4,'Ola brake pad Rear','ola-brake-pad-rear','Seller Marketplace','OLA Spares','Evkartpro','Seller Pick','Ola brake pad Rear for EV and automotive applications.','Ola brake pad Rear is listed on EVKart Pro as an EV spare part or automotive component for electric vehicle owners, repair workshops, service teams, and buyers.',NULL,NULL,NULL,NULL,NULL,'India','Sushako support with invoice after order',80,80,0,0.0,0,1,1,0,1,'Seller Self-Shipping',0,'2026-08-31 01:39:35','2026-09-07 10:24:00','approved',NULL,'publish_now','new',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,NULL,1,10);
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
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_audit_logs`
--

LOCK TABLES `seller_audit_logs` WRITE;
/*!40000 ALTER TABLE `seller_audit_logs` DISABLE KEYS */;
INSERT INTO `seller_audit_logs` VALUES (5,6,25,'onboarding_business_details_saved','Seller saved business details.','[]','2026-07-27 17:41:24','2026-07-27 17:41:24'),(6,6,25,'onboarding_delivery_saved','Seller saved delivery settings.','[]','2026-07-27 17:41:56','2026-07-27 17:41:56'),(7,6,25,'onboarding_plan_selected','Seller selected onboarding plan.','{\"plan\": \"free\", \"price\": 0}','2026-07-27 17:59:46','2026-07-27 17:59:46'),(8,6,25,'starter_plan_activated','Starter onboarding activated.','[]','2026-07-27 17:59:46','2026-07-27 17:59:46'),(9,8,28,'onboarding_plan_selected','Seller selected onboarding plan.','{\"plan\": \"growth\", \"price\": 999}','2026-07-29 10:48:02','2026-07-29 10:48:02'),(10,8,28,'seller_payment_order_created','Seller onboarding payment order created.','{\"payment_id\": 2, \"provider_order_id\": \"order_TJI5fkJcTZW2mW\"}','2026-07-29 10:48:03','2026-07-29 10:48:03'),(11,7,27,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 15:19:03','2026-07-29 15:19:03'),(12,7,27,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 15:19:09','2026-07-29 15:19:09'),(13,7,27,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 15:19:14','2026-07-29 15:19:14'),(14,7,27,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 15:19:17','2026-07-29 15:19:17'),(15,7,27,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 15:19:22','2026-07-29 15:19:22'),(16,7,27,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 15:19:31','2026-07-29 15:19:31'),(17,7,27,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 15:19:34','2026-07-29 15:19:34'),(18,7,27,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 15:19:40','2026-07-29 15:19:40'),(19,7,27,'onboarding_business_details_saved','Seller saved business details.','[]','2026-07-29 15:19:50','2026-07-29 15:19:50'),(20,7,27,'onboarding_delivery_saved','Seller saved delivery settings.','[]','2026-07-29 15:20:06','2026-07-29 15:20:06'),(21,7,27,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 3}','2026-07-29 15:20:43','2026-07-29 15:20:43'),(22,7,27,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 3}','2026-07-29 15:20:44','2026-07-29 15:20:44'),(23,7,27,'onboarding_plan_selected','Seller selected onboarding plan.','{\"plan\": \"free\", \"price\": 0}','2026-07-29 15:20:45','2026-07-29 15:20:45'),(24,7,27,'starter_plan_activated','Starter onboarding activated.','[]','2026-07-29 15:20:45','2026-07-29 15:20:45'),(30,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 21:13:35','2026-07-29 21:13:35'),(31,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 21:13:43','2026-07-29 21:13:43'),(32,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 21:13:44','2026-07-29 21:13:44'),(33,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 21:13:47','2026-07-29 21:13:47'),(34,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-07-29 21:16:09','2026-07-29 21:16:09'),(35,65,115,'onboarding_business_details_saved','Seller saved business details.','[]','2026-07-29 21:16:15','2026-07-29 21:16:15'),(36,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:16:35','2026-07-29 21:16:35'),(37,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:16:36','2026-07-29 21:16:36'),(38,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:16:41','2026-07-29 21:16:41'),(39,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:16:46','2026-07-29 21:16:46'),(40,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:16:47','2026-07-29 21:16:47'),(41,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:16:48','2026-07-29 21:16:48'),(42,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:16:51','2026-07-29 21:16:51'),(43,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:16:55','2026-07-29 21:16:55'),(44,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:16:56','2026-07-29 21:16:56'),(45,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:16:57','2026-07-29 21:16:57'),(46,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:16:59','2026-07-29 21:16:59'),(47,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:17:27','2026-07-29 21:17:27'),(48,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:17:30','2026-07-29 21:17:30'),(49,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:17:32','2026-07-29 21:17:32'),(50,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-07-29 21:17:35','2026-07-29 21:17:35'),(51,65,115,'onboarding_delivery_saved','Seller saved delivery settings.','[]','2026-07-29 21:17:47','2026-07-29 21:17:47'),(52,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 3}','2026-07-29 21:18:31','2026-07-29 21:18:31'),(53,65,115,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 3}','2026-07-29 21:18:34','2026-07-29 21:18:34'),(54,65,115,'onboarding_plan_selected','Seller selected onboarding plan.','{\"plan\": \"free\", \"price\": 0}','2026-07-29 21:18:40','2026-07-29 21:18:40'),(55,65,115,'starter_plan_activated','Starter onboarding activated.','[]','2026-07-29 21:18:40','2026-07-29 21:18:40'),(56,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:48:46','2026-08-04 16:48:46'),(57,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:48:48','2026-08-04 16:48:48'),(58,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:51:11','2026-08-04 16:51:11'),(59,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:51:24','2026-08-04 16:51:24'),(60,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:51:31','2026-08-04 16:51:31'),(61,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:51:43','2026-08-04 16:51:43'),(62,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:51:46','2026-08-04 16:51:46'),(63,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:51:57','2026-08-04 16:51:57'),(64,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:52:02','2026-08-04 16:52:02'),(65,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:52:03','2026-08-04 16:52:03'),(66,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:52:15','2026-08-04 16:52:15'),(67,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:52:22','2026-08-04 16:52:22'),(68,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:52:29','2026-08-04 16:52:29'),(69,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:53:11','2026-08-04 16:53:11'),(70,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 1}','2026-08-04 16:53:26','2026-08-04 16:53:26'),(71,67,117,'onboarding_business_details_saved','Seller saved business details.','[]','2026-08-04 16:53:34','2026-08-04 16:53:34'),(72,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:54:07','2026-08-04 16:54:07'),(73,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:54:12','2026-08-04 16:54:12'),(74,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:54:20','2026-08-04 16:54:20'),(75,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:54:30','2026-08-04 16:54:30'),(76,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:54:54','2026-08-04 16:54:54'),(77,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:00','2026-08-04 16:55:00'),(78,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:02','2026-08-04 16:55:02'),(79,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:04','2026-08-04 16:55:04'),(80,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:07','2026-08-04 16:55:07'),(81,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:14','2026-08-04 16:55:14'),(82,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:15','2026-08-04 16:55:15'),(83,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:19','2026-08-04 16:55:19'),(84,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:20','2026-08-04 16:55:20'),(85,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:22','2026-08-04 16:55:22'),(86,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:24','2026-08-04 16:55:24'),(87,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:25','2026-08-04 16:55:25'),(88,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 2}','2026-08-04 16:55:27','2026-08-04 16:55:27'),(89,67,117,'onboarding_delivery_saved','Seller saved delivery settings.','[]','2026-08-04 16:55:35','2026-08-04 16:55:35'),(90,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 3}','2026-08-04 16:56:28','2026-08-04 16:56:28'),(91,67,117,'onboarding_autosaved','Seller onboarding draft autosaved.','{\"step\": 3}','2026-08-04 16:56:31','2026-08-04 16:56:31'),(92,67,117,'onboarding_plan_selected','Seller selected onboarding plan.','{\"plan\": \"free\", \"price\": 0}','2026-08-04 16:56:38','2026-08-04 16:56:38'),(93,67,117,'starter_plan_activated','Starter onboarding activated.','[]','2026-08-04 16:56:38','2026-08-04 16:56:38'),(94,67,117,'seller_settings_updated','Seller updated store settings and policies.','[]','2026-08-04 16:57:50','2026-08-04 16:57:50'),(95,65,1,'super_admin_status_update','Approved By Koti','{\"reason\": \"Approved By Koti\", \"status\": \"active\", \"store_status\": \"live\"}','2026-08-05 04:13:04','2026-08-05 04:13:04'),(96,67,1,'super_admin_status_update','Approved By Koti','{\"reason\": \"Approved By Koti\", \"status\": \"active\", \"store_status\": \"setup_required\"}','2026-08-05 04:13:24','2026-08-05 04:13:24'),(97,70,120,'starter_plan_activated','Starter onboarding activated.','[]','2026-08-27 10:51:04','2026-08-27 10:51:04'),(98,70,120,'simple_onboarding_completed','Seller completed simplified business setup.','{\"gst_status\": \"not_registered\", \"delivery_radius\": 10, \"returns_accepted\": true}','2026-08-27 10:51:04','2026-08-27 10:51:04');
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_categories`
--

LOCK TABLES `seller_categories` WRITE;
/*!40000 ALTER TABLE `seller_categories` DISABLE KEYS */;
INSERT INTO `seller_categories` VALUES (1,7,7,'2026-07-29 15:19:03','2026-07-29 15:19:03'),(20,65,7,'2026-07-29 21:16:09','2026-07-29 21:16:09'),(21,65,4,'2026-07-29 21:16:09','2026-07-29 21:16:09'),(22,67,7,'2026-08-04 16:51:43','2026-08-04 16:51:43'),(23,67,6,'2026-08-04 16:51:46','2026-08-04 16:51:46'),(24,6,8,'2026-08-19 21:13:47','2026-08-19 21:13:47'),(25,70,6,'2026-08-27 10:53:25','2026-08-27 10:53:25');
/*!40000 ALTER TABLE `seller_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_category_requests`
--

DROP TABLE IF EXISTS `seller_category_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_category_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `requested_category_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `suggested_parent_category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suggested_parent_id` bigint unsigned DEFAULT NULL,
  `example_products` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_approval',
  `admin_comment` text COLLATE utf8mb4_unicode_ci,
  `resolved_category_id` bigint unsigned DEFAULT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seller_category_requests_vendor_id_foreign` (`vendor_id`),
  KEY `seller_category_requests_reviewed_by_foreign` (`reviewed_by`),
  KEY `seller_category_requests_status_index` (`status`),
  KEY `seller_category_requests_suggested_parent_id_foreign` (`suggested_parent_id`),
  KEY `seller_category_requests_resolved_category_id_foreign` (`resolved_category_id`),
  CONSTRAINT `seller_category_requests_resolved_category_id_foreign` FOREIGN KEY (`resolved_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_category_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_category_requests_suggested_parent_id_foreign` FOREIGN KEY (`suggested_parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_category_requests_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_category_requests`
--

LOCK TABLES `seller_category_requests` WRITE;
/*!40000 ALTER TABLE `seller_category_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_category_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_holidays`
--

DROP TABLE IF EXISTS `seller_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_holidays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `holiday_date` date NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_holidays_vendor_date_unique` (`vendor_id`,`holiday_date`),
  CONSTRAINT `seller_holidays_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_holidays`
--

LOCK TABLES `seller_holidays` WRITE;
/*!40000 ALTER TABLE `seller_holidays` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_holidays` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_ledger_entries`
--

LOCK TABLES `seller_ledger_entries` WRITE;
/*!40000 ALTER TABLE `seller_ledger_entries` DISABLE KEYS */;
INSERT INTO `seller_ledger_entries` VALUES (1,70,27,20,'commission_charged',55.00,1.00,54.00,'posted','Order item commission captured at order time.',NULL,'2026-09-07 06:08:59','2026-09-07 06:08:59');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_onboarding_payments`
--

LOCK TABLES `seller_onboarding_payments` WRITE;
/*!40000 ALTER TABLE `seller_onboarding_payments` DISABLE KEYS */;
INSERT INTO `seller_onboarding_payments` VALUES (2,8,2,'growth','Growth',999.00,'INR','razorpay','seller-8-20260729104802-growth','order_TJI5fkJcTZW2mW',NULL,NULL,'pending','seller_onboarding',NULL,NULL,'{\"commission\": \"Zero commission on orders\", \"order_limit\": \"Unlimited\", \"plan_snapshot\": {\"id\": 2, \"key\": \"growth\", \"name\": \"Growth\", \"slug\": \"growth\", \"price\": 999, \"amount\": 999, \"is_paid\": true, \"currency\": \"INR\", \"features\": [\"Up to 100 products\", \"Unlimited orders\", \"Zero commission on orders\", \"Advanced seller dashboard\", \"Professional storefront\", \"Sales and order reports\", \"Marketing tools\", \"Seller labelling features available as an add-on\", \"Priority support\", \"One-month plan validity\"], \"commission\": \"Zero commission on orders\", \"grace_days\": 0, \"order_limit\": \"Unlimited\", \"product_limit\": 100, \"validity_days\": 30, \"billing_period\": \"monthly\", \"commission_type\": \"none\", \"supporting_text\": \"Built for growing sellers who want predictable monthly pricing and no commission on orders.\", \"commission_value\": 0}, \"product_limit\": 100}','2026-07-29 10:48:02','2026-07-29 10:48:03',999.00,'none',0.00,100,'monthly');
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_plan_payments`
--

LOCK TABLES `seller_plan_payments` WRITE;
/*!40000 ALTER TABLE `seller_plan_payments` DISABLE KEYS */;
INSERT INTO `seller_plan_payments` VALUES (4,6,'free',0.00,NULL,'active','2026-07-27 17:59:46',NULL,NULL,NULL,'2026-07-27 17:59:46','2026-07-27 17:59:46'),(5,7,'free',0.00,NULL,'active','2026-07-29 15:20:45',NULL,NULL,NULL,'2026-07-29 15:20:45','2026-07-29 15:20:45'),(11,65,'free',0.00,NULL,'active','2026-07-29 21:18:40',NULL,NULL,NULL,'2026-07-29 21:18:40','2026-07-29 21:18:40'),(12,67,'free',0.00,NULL,'active','2026-08-04 16:56:38',NULL,NULL,NULL,'2026-08-04 16:56:38','2026-08-04 16:56:38'),(13,70,'free',0.00,NULL,'active','2026-08-27 10:51:04',NULL,NULL,NULL,'2026-08-27 10:51:04','2026-08-27 10:51:04');
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
INSERT INTO `seller_plans` VALUES (1,'Free','free',0.00,'none',25,0,'Unlimited','flat',1.00,0,0,'active','[\"Unlimited time\", \"Rs 1 commission on every product unit sold\", \"Self shipping\", \"Draft to published workflow\", \"Professional dashboard\"]','Start free for unlimited time with a simple Rs 1 unit commission.','2026-07-26 21:28:12','2026-07-27 17:15:12'),(2,'Growth','growth',999.00,'monthly',100,0,'Unlimited','none',0.00,1,0,'active','[\"Up to 100 products\", \"Unlimited orders\", \"Zero commission on orders\", \"Advanced seller dashboard\", \"Professional storefront\", \"Sales and order reports\", \"Marketing tools\", \"Seller labelling features available as an add-on\", \"Priority support\", \"One-month plan validity\"]','Built for growing sellers who want predictable monthly pricing and no commission on orders.','2026-07-26 21:28:12','2026-07-26 21:28:12'),(3,'Enterprise','enterprise',4999.00,'monthly',NULL,1,'Unlimited','none',0.00,1,5,'active','[\"Unlimited products\", \"Unlimited orders\", \"Zero commission\", \"Complete storefront branding\", \"Advanced analytics\", \"Premium reports\", \"Marketing tools\", \"Seller labelling included\", \"Priority support\", \"Settlement insights\", \"Five-day renewal grace period\", \"One-month plan validity\"]','A complete premium selling suite for established businesses that need scale, branding, and operational control.','2026-07-26 21:28:12','2026-07-26 21:28:12');
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_policy_acceptances`
--

LOCK TABLES `seller_policy_acceptances` WRITE;
/*!40000 ALTER TABLE `seller_policy_acceptances` DISABLE KEYS */;
INSERT INTO `seller_policy_acceptances` VALUES (1,6,25,'seller_agreement','v1','172.18.0.1','2026-07-27 17:59:46','2026-07-27 17:59:46','2026-07-27 17:59:46'),(2,6,25,'privacy_policy','v1','172.18.0.1','2026-07-27 17:59:46','2026-07-27 17:59:46','2026-07-27 17:59:46'),(3,6,25,'commission_policy','v1','172.18.0.1','2026-07-27 17:59:46','2026-07-27 17:59:46','2026-07-27 17:59:46'),(4,6,25,'return_refund_policy','v1','172.18.0.1','2026-07-27 17:59:46','2026-07-27 17:59:46','2026-07-27 17:59:46'),(5,8,28,'seller_agreement','v1','172.18.0.1','2026-07-29 10:48:02','2026-07-29 10:48:02','2026-07-29 10:48:02'),(6,8,28,'privacy_policy','v1','172.18.0.1','2026-07-29 10:48:02','2026-07-29 10:48:02','2026-07-29 10:48:02'),(7,8,28,'commission_policy','v1','172.18.0.1','2026-07-29 10:48:02','2026-07-29 10:48:02','2026-07-29 10:48:02'),(8,8,28,'return_refund_policy','v1','172.18.0.1','2026-07-29 10:48:02','2026-07-29 10:48:02','2026-07-29 10:48:02'),(9,7,27,'seller_agreement','v1','172.18.0.1','2026-07-29 15:20:45','2026-07-29 15:20:45','2026-07-29 15:20:45'),(10,7,27,'privacy_policy','v1','172.18.0.1','2026-07-29 15:20:45','2026-07-29 15:20:45','2026-07-29 15:20:45'),(11,7,27,'commission_policy','v1','172.18.0.1','2026-07-29 15:20:45','2026-07-29 15:20:45','2026-07-29 15:20:45'),(12,7,27,'return_refund_policy','v1','172.18.0.1','2026-07-29 15:20:45','2026-07-29 15:20:45','2026-07-29 15:20:45'),(13,65,115,'seller_agreement','v1','172.18.0.1','2026-07-29 21:18:39','2026-07-29 21:18:39','2026-07-29 21:18:39'),(14,65,115,'privacy_policy','v1','172.18.0.1','2026-07-29 21:18:39','2026-07-29 21:18:39','2026-07-29 21:18:39'),(15,65,115,'commission_policy','v1','172.18.0.1','2026-07-29 21:18:39','2026-07-29 21:18:39','2026-07-29 21:18:39'),(16,65,115,'return_refund_policy','v1','172.18.0.1','2026-07-29 21:18:39','2026-07-29 21:18:39','2026-07-29 21:18:39'),(17,67,117,'seller_agreement','v1','172.18.0.1','2026-08-04 16:57:50','2026-08-04 16:56:38','2026-08-04 16:57:50'),(18,67,117,'privacy_policy','v1','172.18.0.1','2026-08-04 16:57:50','2026-08-04 16:56:38','2026-08-04 16:57:50'),(19,67,117,'commission_policy','v1','172.18.0.1','2026-08-04 16:57:50','2026-08-04 16:56:38','2026-08-04 16:57:50'),(20,67,117,'return_refund_policy','v1','172.18.0.1','2026-08-04 16:57:50','2026-08-04 16:56:38','2026-08-04 16:57:50');
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
  `eligible_quantity` int unsigned NOT NULL DEFAULT '0',
  `platform_fee_per_unit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `platform_fee_total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `refund_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `return_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `final_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `settlement_items_order_item_unique` (`order_item_id`),
  KEY `seller_settlement_items_seller_settlement_id_foreign` (`seller_settlement_id`),
  CONSTRAINT `seller_settlement_items_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_settlement_items_seller_settlement_id_foreign` FOREIGN KEY (`seller_settlement_id`) REFERENCES `seller_settlements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `gross_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `platform_fee_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `refund_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `return_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `adjustment_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `final_settlement_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `eligible_quantity` int unsigned NOT NULL DEFAULT '0',
  `platform_fee_per_unit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `settlement_period_start` date DEFAULT NULL,
  `settlement_period_end` date DEFAULT NULL,
  `payment_mode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hold_reason` text COLLATE utf8mb4_unicode_ci,
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `payment_marked_by` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_settlements_settlement_number_unique` (`settlement_number`),
  KEY `seller_settlements_vendor_id_foreign` (`vendor_id`),
  KEY `seller_settlements_status_index` (`status`),
  KEY `seller_settlements_approved_by_foreign` (`approved_by`),
  KEY `seller_settlements_payment_marked_by_foreign` (`payment_marked_by`),
  CONSTRAINT `seller_settlements_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_settlements_payment_marked_by_foreign` FOREIGN KEY (`payment_marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_settlements_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_settlements`
--

LOCK TABLES `seller_settlements` WRITE;
/*!40000 ALTER TABLE `seller_settlements` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_settlements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seller_storefront_categories`
--

DROP TABLE IF EXISTS `seller_storefront_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_storefront_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_storefront_categories_vendor_name_unique` (`vendor_id`,`name`),
  UNIQUE KEY `seller_storefront_categories_vendor_slug_unique` (`vendor_id`,`slug`),
  KEY `seller_storefront_categories_category_id_foreign` (`category_id`),
  KEY `seller_storefront_categories_display_order_index` (`display_order`),
  KEY `seller_storefront_categories_status_index` (`status`),
  CONSTRAINT `seller_storefront_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seller_storefront_categories_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_storefront_categories`
--

LOCK TABLES `seller_storefront_categories` WRITE;
/*!40000 ALTER TABLE `seller_storefront_categories` DISABLE KEYS */;
INSERT INTO `seller_storefront_categories` VALUES (16,7,7,'Fashion','fashion',NULL,NULL,0,'active','2026-07-29 18:21:18','2026-07-29 18:21:18'),(17,6,8,'Test Products','test-products','Temporary production validation products.',NULL,999,'active','2026-08-19 21:13:47','2026-08-19 21:13:47');
/*!40000 ALTER TABLE `seller_storefront_categories` ENABLE KEYS */;
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
INSERT INTO `sessions` VALUES ('0GeYvQdSLaBdvsfFMyzSENqv5IhqVeFIERli3xfd',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +searchbot@anthropic.com)','eyJfdG9rZW4iOiJ2aVI4bG9oRDBIdnpUTU1SSDhYQzNMTTBNcGlKRFFsUkR6dXhiUVluIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9jb21wdXRlcnMtbGFwdG9wcy1sYXB0b3BzIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776082),('0URlcAXTRgnKCOXpAWvbI6U2dJwtLVhkctQusR0l',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJUeGM2REdpU1dxaFd4TXB3RE9mc1VMRnQ1UjMyb3hlS1IwRXN6VFlXIiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjE4OjQ1KzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776325),('0z7BfCjBLngr2t6QkQaN3TXftnLxBuiECUa4WzU1',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJJQUZwdDMwVzhNUkxocHNPZEtKaGJNMll6SkgxTFJNUjBVR09iV3p4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9lbGVjdHJvbmljcz9wcmljZT0xMDAwLTQ5OTkiLCJyb3V0ZSI6ImRlcGFydG1lbnQuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788775665),('1dpFGQWs1nh51UevnaGtKHW12TxsPvqq12SBLV1x',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJ4UkdPYzluTXdqNnlDRVBSMThvRDAyV1pmMG53Q1NrcW1oOWZNclNMIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Nob3A/Y2F0ZWdvcnk9TmV3JTIwQXJyaXZhbHMmcHJpY2U9NTAwMC1wbHVzJnNvcnQ9cHJpY2UtaGlnaC1sb3ciLCJyb3V0ZSI6InNob3AifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788771536),('1H9bzCKEW65P2YCZICSDarV3yjizur0IgUfIGcBF',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJqSkgyc2hKenh4YnlyVWdwNlZ6NjRvZWc4SzlydnBiNjlrOXpSb2MwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWdlbmVyYWwmcHJpY2U9MTAwMC00OTk5JnNob3A9ZmVhdHVyZWQmc3RvY2s9aW4tc3RvY2smd2lzaGxpc3Q9MSIsInJvdXRlIjoicHJvZHVjdHMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788783826),('1T2WP5JT8G6vIrRBSgNijXcY5xkRa8Ec6b4BsAf5',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiIwRGRScWU3dE93WTdlcEZnbDhCMEpSWkxWN3REbWpDS1FOb2VMMVpnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PXRlc3QtcHJvZHVjdHMmc2hvcD1mZWF0dXJlZCZzb3J0PW5hbWUtYS16JnN0b2NrPWluLXN0b2NrIiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788786517),('2HmPzwOnUbVtyuP0MixVG9Uix4QrGJ7guKVyYWJR',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJaemMyMmVtRnZLNDhBdThHSTAxaTM1OWRUbUZXelBVeGZZQlUwYUJtIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Nob3A/c29ydD1mZWF0dXJlZCIsInJvdXRlIjoic2hvcCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788772353),('2ZOCvndMwWRn3p48OyFO80sJAA80WMVBiMc7O00K',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJMdmpGdno2NW1ob3VlalBWMTdyY1RnUVpISmVWcUNzWUFmaDZOdDhjIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzXC8zMSIsInJvdXRlIjoicHJvZHVjdHMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788776125),('5yl5K827Ze3ycmWHRkhmNx18OhmpS1oLg2ob22O8',NULL,'172.18.0.1','Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)','eyJfdG9rZW4iOiJDMTVBQUpWc05QakhiNm5mTG1pR0N5SklZcnlGUUVieUNtR2kyemlYIiwicHJvZHVjdF92aWV3ZWQiOnsiNzAiOiIyMDI2LTA5LTA3VDA5OjA0OjM1KzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXYtcHJvLW5leHQtZ2VuLWhvcm4tc3dpdGNoLW5ldy1tb2RlbC0wMWY3LWhpZ2gtYWxlcnQtcHJlY2lzaW9uIiwicm91dGUiOiJwcm9kdWN0cy5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788771875),('6RU23W1MDbUgYG3Q47GXk9eZ1RvSClJSQEMY7hdF',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJwQXhnZFczeWNhb3M2enZaTEpkYkY5UFlYYnZ1QU54WXoxMzAycmlFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP3ByaWNlPTUwMDAtcGx1cyIsInJvdXRlIjoicHJvZHVjdHMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788769783),('8kAfcjKZqLsEGTDEVUpkeg1sjlBlTx8NkmbzMp3J',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJUc041S0RyZ0xtVUh3bmRjTHNISWd4bmlCMXRnOFlkTUxGcklKZERJIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW4iLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788775898),('8o4rWsx3yM3iAstPte6kAgEnV7GP7WnGafGXX8Ez',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJ6OUZzaGhDNmxOMHU0MEtKa2pPWTd6cm1SU0V5a3BWUENsM2NkU0xOIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3NlYXJjaD9zb3J0PWZlYXR1cmVkIiwicm91dGUiOiJzZWFyY2gifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776129),('8ofqD5ZCvKzJiT9pAznw5tgsz9h9SYW7hia0lT0I',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJuUTduWGtXbFgwNjNlVzZta3R6OFJRTENZMWJ6ZWFzamZYMEJOUDVLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC93b21lbnMtY2xvdGhpbmc/cHJpY2U9NTAwLTk5OSZzb3J0PW5hbWUtYS16Iiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788771265),('94m3vfUnIgJ1FbSbqov8kRG1536e4OXZKdfgFVNi',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJpM0VudE9RaUp3STcyOXYyWGRWZURiYnRnQUxDNEVHZHR3YVNwZ0JwIiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjE2OjMyKzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776193),('9UscbCEKgpCCIBVd2wm2RthFlhrFOCyAieVcYOaM',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJkRnN3TFJlR01zdURGd09pN3kwblhpc3NYNXBPRmJkM0ZvcnpkRW1lIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PXRlc3QtcHJvZHVjdHMmcHJpY2U9MTAwMC00OTk5JnNvcnQ9cHJpY2UtaGlnaC1sb3ciLCJyb3V0ZSI6InByb2R1Y3RzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788774876),('a61Q4tgifHfNZ8JdFxFB3ExdgCrOEm6uKpb5njMJ',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJhSVUzZm5hdUNVQ09WRUdWNmFkRmJvZGMxc052Ym1raXh0QUFnS3FVIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW4iLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776212),('A97srU3fLtSFcomVxof5szden8MjUvRVR0Zz5l1J',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJGcmxXMGtVcGpFNzNKWE9mVEZBOGRnMnlsUUJjWjBOMHdnYmZHZjZLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWdlbmVyYWwmcHJpY2U9NTAwMC1wbHVzJnNob3A9bmV3JnNvcnQ9bmFtZS1hLXoiLCJyb3V0ZSI6InByb2R1Y3RzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788773164),('ac3iL2jD7KsQbDD6LF7GSlvLEAABPAC9xM4wDuuW',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +searchbot@anthropic.com)','eyJfdG9rZW4iOiJDUWxHNktnZEtkbW5ENnRqQ0cwak5BdXdvZ2hNalVwY3Z4bmFOY0ZXIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9ob21lLWtpdGNoZW4ta2l0Y2hlbiIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788776561),('aIBhQ3QJ34HwB0PAhafhzIhoRu1MG5uyoOrBN3qw',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJ5Z3FBbEtpOVk3dWYySkdIN2lGRm5pMnpZazRZYnNJeEw4eGNnRDZOIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9nZW5lcmFsP3NvcnQ9bmFtZS1hLXoiLCJyb3V0ZSI6ImRlcGFydG1lbnQuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788772892),('aYr20C9FIxFDxN6a8dHweqNGCAoHIDvtA3lz45oH',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJZQjFtODZRT0hubmZlMDRRRFY2WWtjd0RIMWZWWVRPU210c3k3YlRsIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9nZW5lcmFsP3ByaWNlPTUwMC05OTkiLCJyb3V0ZSI6ImRlcGFydG1lbnQuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788774662),('BcwzfYZmw65YjHfrv2QdvLHrad2ruknPnmdJPmzs',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJWbUpnYUtubGJRQUc0TWY4czBpYmRmOEJldGpLbzZHTTczMHY1c1IyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC90ZXN0LXByb2R1Y3RzIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788773724),('Bk3vVoLctL6g3uLrAY9Ny90Py0mkam0CIXDWXFB7',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJUNVo5R2loTFZLZ0RqYThnaU5QWG42MHBOcHVLMlVDbURHQUtqWmJFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9ob21lLW1hZGUtaGVhbHRoLW1peD9zb3J0PW5ld2VzdCIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788775251),('blnWqvirL8BXdXOsYhtMcsXkjUk8G1Oavcd5NDAN',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJPWkNkNTIycFVvb1Q4TmFJNFE0dzQ1Rm5rSU5WVkdGQ0wzR2xZaFE0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW4iLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788773360),('Bz662U3pYX0HsWfLv4t11bBXnkXZgRrIh9pdB8Mz',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Amzn-SearchBot/0.1) Chrome/119.0.6045.214 Safari/537.36','eyJfdG9rZW4iOiJoRENNVDBQVkxNM2toVUs1dnpCNjBsdlBSM3hBeDBSZGZWTFpKRkZFIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9ldi1hY2Nlc3Nvcmllcz9wYWdlPTEmc2hvcD1uZXcmc29ydD1uZXdlc3Qmc3RvY2s9aW4tc3RvY2siLCJyb3V0ZSI6ImRlcGFydG1lbnQuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788772628),('c9pAuZuASQDzkH5hVKo2JbDf6iK13J7UU1zynTlI',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJpaGxzeDFLMlIwOWVoTkc2OG5mTVh1NU1WTWxTdUJRMEc0YnFWdjRpIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWVsZWN0cm9uaWNzJnByaWNlPTUwMC05OTkmc2hvcD1uZXcmc29ydD1uYW1lLWEteiZzdG9jaz1pbi1zdG9jayIsInJvdXRlIjoicHJvZHVjdHMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788771428),('Cb1p8gZztr65X3YfNWYNmvgOm7zdFkNmXlmF9giA',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiI2elppcVNHeFZzMFF5V25oaUozRVdGM3BPcHdtSTZkZW0zdnB1SWtkIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC90ZXN0LXByb2R1Y3RzP3ByaWNlPXVuZGVyLTUwMCZzaG9wPW5ldyZzb3J0PWZlYXR1cmVkJnN0b2NrPWluLXN0b2NrIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788771396),('CivFWWc7qIAvF8XFqRGYq1VSYkvCNzIzW7PTyZ8a',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiI5UFJwd0I5dFZJbDBWdWUyZGtjVEJKNzY3M2pSTFY2eW5VZmhwVWdYIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzXC8zMSIsInJvdXRlIjoicHJvZHVjdHMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788776150),('cJFRl7oqB1AOB6OXnIvNiPqIVu8E4GeEk00kYzRx',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJaYktYRTdoa1BvYmw2cEZETDd3cVdGaERYcHlyY0E4UkpWRllMVml4IiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjE4OjUxKzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776331),('cpnvxC7PZuxV6Yp5VcGK71ZQjYMrhfSSggm6z28B',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJNOWZ2WDN1aHJOVWdhT01tczR3WWZmSENRcGhFRUc0SHFUNlJ2OWRxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWdlbmVyYWwmcHJpY2U9NTAwLTk5OSZzaG9wPW5ldyZzb3J0PW5ld2VzdCIsInJvdXRlIjoicHJvZHVjdHMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788773550),('eAdF1ZsWLgkPzlYwhAPNKnDdGzFUQjyy24TdIppP',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJEUTM0TmpBY21iOGlkRnMzaElEOVc3T0I3T2VFaXZxZHRic2I2Rkw0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9lbGVjdHJvbmljcz9wcmljZT01MDAtOTk5JnNvcnQ9bmV3ZXN0Iiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788772107),('EBOikBfQEeZdb9jmlo2SDhf1q7zsFavgg7M9eFjb',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJvRzVoZm8wSXJ4TnpxRk1UZVpMOXRndWZNZ3NTOE8wOU5zR251VkJiIiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjE3OjUwKzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776270),('EbqueD8SrKEbPwydiYzSMiK20YvhMPZ3pTqS9tNc',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJuak9weENFdnNKMm9KV0hhTzJGOVdTVXpqaUYwQXpIMnd2cTVqdnp4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9ldi1jb250cm9sbGVycy1raXRzIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788772654),('eDSRLL0wE7nplnKoRSZNWsngiilEMEiBlmiBAJge',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +searchbot@anthropic.com)','eyJfdG9rZW4iOiI4ZklYTlBPS1JteEk0RElOVTRiOXdhOWJQa09vS1dGeE15b2VGYVhqIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9ncm9jZXJ5LWZvb2Qtc25hY2tzIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776889),('eHw9nrradQC1zmYBNDCvWxmBUhOQRhDI0DdrbZ7S',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJtaUFmd3hON1dOZjA2cWU5TWR3cW1CVGFPS0k4UjZlU3ZDa0pkSUVpIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWdlbmVyYWwmcHJpY2U9NTAwMC1wbHVzJnNob3A9ZmVhdHVyZWQmc29ydD1wcmljZS1sb3ctaGlnaCZ3aXNobGlzdD0xIiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788769535),('Eqf8H8ePx2t3uD13o53qTqf6500vIJm38ggLyu4R',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiI1RnZ6ejF6ellla3l5M25CeTFBbmlwNUVmaE12cHdEa1FLZzNXdkM1IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP3ByaWNlPXVuZGVyLTUwMCZzaG9wPWZlYXR1cmVkJnNvcnQ9ZmVhdHVyZWQmc3RvY2s9aW4tc3RvY2smd2lzaGxpc3Q9MSIsInJvdXRlIjoicHJvZHVjdHMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788775248),('EUZCGiH68uWNGVKZtC8F9eO96TjnuHk2VxAY8gOf',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +searchbot@anthropic.com)','eyJfdG9rZW4iOiI5aVZodHZ5VjAwaUxRMUNieGNidEFTUjFEOXdubm5BTlpCcTBLYTRNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9qZXdlbGxlcnktYWNjZXNzb3JpZXMiLCJyb3V0ZSI6ImRlcGFydG1lbnQuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788776747),('ew5NyxjQ9VfXkrryhs518uBbb2kR40IEEaxheO8q',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +searchbot@anthropic.com)','eyJfdG9rZW4iOiJOMFR6YktFZkp0ZFp0Z3cwQTl1SHdjSG9MdHE4YzR1dHNHZXpnSHFpIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3NpdGVtYXAueG1sIiwicm91dGUiOiJzaXRlbWFwIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788772210),('FDOiVzQIIYmxUwMyrnsP2OidRHYI0n4GuyK4JqT3',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJqRzNFYm1jODFwcjFBZG9zNW9JendqR2R2azVqendrODBsekhlRXFEIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzXC8zMSIsInJvdXRlIjoicHJvZHVjdHMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788776118),('FqZQT5LHkZKLIGP54x5aI26GGgm2alSV8GR3wCUZ',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiIzM2taWE9xR2RLTmptWXlINFQ3OVhBbm1UYThGZXZmeWVjM25sdUJCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Nob3A/c29ydD1uZXdlc3QiLCJyb3V0ZSI6InNob3AifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776453),('fsLhNsTutKjJjmkKzUgpaOmahRlvBT8dXKjxNFbo',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJWMGRHbk5oWVl3d0l4aG9ZeUN6QVlNQ2YxRk9ETm4yUXU1SW01NFZWIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3NlYXJjaD9zdG9jaz1pbi1zdG9jayIsInJvdXRlIjoic2VhcmNoIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788770228),('gqkLjNQlXGueUxAgTmuhWXelk5xFKhCHqFy5Qxj4',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJBdVM1QzRaTUJQUUFPdUkydXFhcklFNTNSdEpuVjNWdExwRmJaQk1HIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC93b21lbnMtY2xvdGhpbmc/cHJpY2U9dW5kZXItNTAwJnNvcnQ9ZmVhdHVyZWQmc3RvY2s9aW4tc3RvY2siLCJyb3V0ZSI6ImRlcGFydG1lbnQuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788776356),('gTpHmw5gM6dwPTayT7ZmENMxdMqXgS4aybwYnf3e',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJMZmxRQ3NOVDRZbkV4MDNPQjJMdlRDNnZQbzFSNmNUQk54T01pNWVNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3NlbGxlclwvZm9yZ290LXBhc3N3b3JkIiwicm91dGUiOiJzZWxsZXIucGFzc3dvcmQucmVxdWVzdCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788772821),('GuLGBvMHn2bpE7XdYkhECJZjvJKkN9hEtHzOYgKe',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiI3b25LTWNJTmpYRUp6a1o0TjlETG1VQmlCb1lIalloVlJTWmR4ZGl3IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9ob21lLW1hZGUtaGVhbHRoLW1peD9zaG9wPW5ldyIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788770366),('gWYGB2h0rhrs7Ve0ZcEzakBePqFcMGYdqhbLPJgj',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiI2OWRYVXhBNkFteDE0Z0hWcXNMUjZ1eXpaNEpPNTlUUmxUd0RqaUlZIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWdlbmVyYWwmcHJpY2U9NTAwLTk5OSZzaG9wPWZlYXR1cmVkJnNvcnQ9ZmVhdHVyZWQmc3RvY2s9aW4tc3RvY2siLCJyb3V0ZSI6InByb2R1Y3RzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788774395),('h6ZxcAYmNXlkTuFe5gDOlk5Y13P3mcVO0UAP9Sba',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJoYTBoRm0wR0ZZcG9weHZIMUlYU0NpUVhxUmNuOGNTck1xNFhFNUhwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWdlbmVyYWwmcHJpY2U9MTAwMC00OTk5JnNob3A9bmV3JnNvcnQ9cHJpY2UtaGlnaC1sb3cmc3RvY2s9aW4tc3RvY2siLCJyb3V0ZSI6InByb2R1Y3RzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788772401),('HGROyc0RkNzO5qC6Z6VkwNfNQvCIDmPCowZOiZQ6',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJvY2d6ZEtiZXpBNERHSDlsUWpwRE0wN1hsbERveE1haFVheURFSnowIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzXC8zMSIsInJvdXRlIjoicHJvZHVjdHMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788775967),('hpRFxkdbK5NUXBOzLrHrkwr2cGSQRjjNImUUVDqj',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Amzn-SearchBot/0.1) Chrome/119.0.6045.214 Safari/537.36','eyJfdG9rZW4iOiJaMnQxaDR5bWZpM2NadTZCTzl3NjhOWHFCRDkyVGliSFAyOVNvWUVMIiwicHJvZHVjdF92aWV3ZWQiOnsiMTA5IjoiMjAyNi0wOS0wN1QxMzowNDozNyswMDowMCJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cHM6XC9cL3Nob3Auc3VzaGFrby5pblwvcHJvZHVjdHNcL2V2a2FydHByby1wcmVtaXVtLWJvZHktcGFuZWwtdS1jbGlwcy1mb3ItZWxlY3RyaWMtc2Nvb3RlcnMtcGFjay1vZi0xMC1oZWF2eS1kdXR5LW01bTYtc3BlZWQtbnV0LXRoaW1ibGVzLWZvci1iYXR0ZXJ5LWxvY2stY2hhc3Npcy1mYWlyaW5ncyIsInJvdXRlIjoicHJvZHVjdHMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788786277),('hSlpo0s7kX7993sqRKhOKxoslMibu4iuM11gULKR',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJzdEdzek8zQ2s5V0hpOHF3VzRnTW56Wk1JNXcwc0Vkd3lpZzI1VkRJIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWdlbmVyYWwmcHJpY2U9NTAwLTk5OSZzaG9wPW5ldyZzb3J0PXByaWNlLWxvdy1oaWdoJnN0b2NrPWluLXN0b2NrJndpc2hsaXN0PTEiLCJyb3V0ZSI6InByb2R1Y3RzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788774551),('HWYKvvQzFa2nX1lkmKa18rS4hdmYEtNrYcPIJHlH',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJkbWVMVTBzcVEzcjR4ODlVWWY5ZU0xVnFJNmNrS1ZZTTlmelhXZTJzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3JldHVybi1yZWZ1bmQtcG9saWN5Iiwicm91dGUiOiJwb2xpY2llcy5yZXR1cm4tcmVmdW5kIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788783697),('IG05V8WSUQi0mV7S3HxdyTGUOQRh6uufuK6umnFS',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Amzn-SearchBot/0.1) Chrome/119.0.6045.214 Safari/537.36','eyJfdG9rZW4iOiJFeU9zMTlhN0hxRE12QTZYWGxMbTA5eENnRUlXRHJsNkkxNTVxV3VYIiwicHJvZHVjdF92aWV3ZWQiOnsiMTE3IjoiMjAyNi0wOS0wN1QxMDowMzoyNyswMDowMCJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cHM6XC9cL3Nob3Auc3VzaGFrby5pblwvcHJvZHVjdHNcL29sYS1zaWRlLXN0YW5kIiwicm91dGUiOiJwcm9kdWN0cy5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788775407),('jg3nRtXk9XEqOeExhh0FgMmuC7vrcuA20nOD7Dn5',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJGcHNiRGVPQ0hpM2gwVHhhN1RvN09aOUZmaE9ZdnhZbzdvWkd6d09SIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC90ZXN0LXByb2R1Y3RzIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788774902),('jGddj6hLjK0nC6hKjjQDOn4dhzO0tgjjvzW2cWXq',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJLdGxVaUtTdGJWZkhVZDFWR2Z5bWdpQ2RJdmR6YWVrVjBLVXkwQW9UIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWVsZWN0cm9uaWNzJnByaWNlPTEwMDAtNDk5OSZzaG9wPW5ldyZzb3J0PW5hbWUtYS16JnN0b2NrPWluLXN0b2NrIiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788769893),('jMyDtVFaHQJmOGG4J5TkILDSpnPtfXnDEEGH4vtP',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJyRHY5Qnh5SnFqSXloOVhZdEdnUDNndk82eXF3dEVTWkc1R3R0Y1JHIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9tYXNhbGEtcG93ZGVycyIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788775498),('JZ6b8bfavLb6aRmDu9sJc1f7b7JP0GSIsXYorOo2',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJ6M2t5ejlZeXRQeW10OFhOVkZKbWtDbDJ1dkpGTk94OUhSOEVxamRzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW4iLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788775887),('k6qfFLejvim71f2IVypJzmxrTtWwgn84DMXxK5TV',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiI4OVNmRVpieXl2UUZmTXE2NGNYYnZGMWJvamgzb2FjYlJNQUxZblZPIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9tYXNhbGEtcG93ZGVycz9wcmljZT01MDAtOTk5Iiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788774266),('Kht1ZoXMycaRUqFX6xjRUkdfALLRh198FmLBFiAS',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJ5U1NtUVFGdm5Ba1RjeEJhQ0syemVqVUN3QTlBZDVNRVdsdlhPMldXIiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjE3OjM2KzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776256),('l0nLLuOR9BCx988kq3Kr5LWV41XUjhkZIvwR4Amk',NULL,'172.18.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:154.0) Gecko/20100101 Firefox/154.0','eyJfdG9rZW4iOiJ0Sks0c2VHRWRIeEduYWxXc2FEMkxCZ0dWQ0oxSVJBcU40UmxFVEl0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Nob3AiLCJyb3V0ZSI6InNob3AifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788783104),('lrx4JjpCOlaA7VvMVZovmpOL7HJ0JW3QzKEiOlJG',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJGcVc4bjJaMUlhanFtTVFpaFlpUEQ5S01qNHZwSEpVUVhiS1owdUVHIiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjE3OjI2KzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776246),('ML6bwmeo9Do42lrGsnpMhpctB6agGIspLzEdEpAn',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJtbUdxVzhWV0NZZWFydDNaOGFaVHl3TlRtZFU4VTR5Q0xSUmdtV3ZzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PXRlc3QtcHJvZHVjdHMmcHJpY2U9MTAwMC00OTk5JnNob3A9ZmVhdHVyZWQmc29ydD1wcmljZS1oaWdoLWxvdyZzdG9jaz1pbi1zdG9jayZ3aXNobGlzdD0xIiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788771074),('mX29TGGv1N8PXqGdY6z8cNTTCTr4vWbDG94c6fu6',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +searchbot@anthropic.com)','eyJfdG9rZW4iOiJzRThXelA2enJKQW1QVWh6M2lpcVBFa3ZiUXNzMkNCbmtabURMajhpIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9lbGVjdHJvbmljcy1lbGVjdHJvbmljLWFjY2Vzc29yaWVzIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776235),('mYSOILTbOxOcsnoZYoW2HUgQlaSDsnMV5opkYF5D',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJISG5PRU1xWUQ2UUV6Q3NoMGlMZEZLd3FmM1VKYmFOWk96anNjTXBRIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9ob21lLW1hZGUtaGVhbHRoLW1peD9zb3J0PXByaWNlLWhpZ2gtbG93Iiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788770564),('n04alshZA7mBiHuLApsaa35TxsCYKsiUOmzZb7no',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJrMnYxWFNHM0VQeTF5VU5DVjBseU9MVnVSRWhVWVVzYmo0QnBJQ1FhIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9mYXNoaW9uIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788773726),('N8u0VjE1YRtSliRnCYedK610yJvKsuQ1yuEoMYpA',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJTOFFSUFkwT1BWOVpySlN5Rzc0MU9iZTRBd2tYcW9EdGZCRlM1VmhQIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Nob3A/c29ydD1uYW1lLWEteiZ3aXNobGlzdD0xIiwicm91dGUiOiJzaG9wIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788773617),('nBJ2OWjr2ThmORhBiNTtjI4JSDbHn3JdMvD3SSVz',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJYM2UyZFE3V01xU0Qwc0xvNTkweGJicnJpOE9PamFZcmFWZmNWTlM3IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9lbGVjdHJvbmljcz9wcmljZT01MDAtOTk5JnNvcnQ9bmV3ZXN0Iiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788772146),('nFyA9xRra1XyAuEHil0TzVT7Kp9DsZV1q942Su9y',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiI3UHpqVGtrRmFHZ0dMeUNqdlhkTExzYjFZclo1OWY0S3M5QTI2UkViIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWVsZWN0cm9uaWNzJndpc2hsaXN0PTEiLCJyb3V0ZSI6InByb2R1Y3RzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788774667),('NGkc8iPJHBlk2ErThLLyZylhdj9iRcpdOVYezDoo',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJxWTZwUWY0UEFJd0Jpemc1SjBtV3FtRmd2d21udkhiV3YxaXdBNEJ0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3NlYXJjaD9zaG9wPWZlYXR1cmVkIiwicm91dGUiOiJzZWFyY2gifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788772619),('O06xt7gztwbDMA80I9PPkzQLb0VOMj6oJhQqlaa3',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiI0ek90eGRBMXdXN2FucjlLZlZmNGtubFhVeUZBNlRqNjZZMFFjUnVnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW4iLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788772952),('o5Wlg26bn9ermy2DARuOMEJxQoWqjay8VqSkKOOW',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJFWENKcWFvanJZTmNjcWpiQzdNZmlTcndvTGcwd2hZbG1IZ0w1ZWVNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWVsZWN0cm9uaWNzJnByaWNlPTUwMDAtcGx1cyZzb3J0PW5hbWUtYS16Iiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788769604),('Obup4HYvqKy1UPizbdTcBWRlCF1zWR85p9J23JKu',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJaa2RIMXptWGRzc3dPSm9oUHBkQWZTZ051NE1UcVVFUjlBeEJ1YkZGIiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjE2OjI2KzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776186),('OJOsTzP2yAlZO5MXFnePQJF0HqMqXO663AgtjLbh',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJlQWhzajNuOTU0WEFCY0htMTB6cUVIZUVZSHdYZjMwWHdlM0I2ZjJnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzIiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788783696),('oLl3VPFhsPEGdAZIJgSNnpcwIg8Va1UlDwxFRYHp',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +searchbot@anthropic.com)','eyJfdG9rZW4iOiJPOWVyellrZmlzOUp4U3JIcTk3QVpCY1VEaTdGd2FVMUlMRERpTGNPIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC90b29scy1oYXJkd2FyZS1lbGVjdHJpY2FsIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776176),('OoyiTu6CPbatOb89dyeZuLxP2bUpvrvxnRnBpHer',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJvYTV4a3l3UVlyZTVKTlNtMlpVcjVBQ0Q1dnVwOXppVmNDYUtDbkI2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW4iLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788772982),('OreZFkXfPvBenkBaHO9yf7H0IrsFv78fz9buPair',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJXWmhGM3h6RDdjYmd4Yk5CODZpd0hxSGdid3ZGWUNtNTdrOWZGUkQxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWVsZWN0cm9uaWNzJnByaWNlPTUwMDAtcGx1cyZzaG9wPW5ldyZzb3J0PXByaWNlLWxvdy1oaWdoJnN0b2NrPWluLXN0b2NrJndpc2hsaXN0PTEiLCJyb3V0ZSI6InByb2R1Y3RzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788772386),('OyIk4S95dbtBQSvf2CQzLDKAmnIR3ZTjBdsxRPoW',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJSQXRRNW83d3BqYlpVd1JSRnlMdFhwREFUNjZvaGxSNGl2YTFRUVZpIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PXRlc3QtcHJvZHVjdHMmcHJpY2U9NTAwLTk5OSZzaG9wPWZlYXR1cmVkJnNvcnQ9bmV3ZXN0JnN0b2NrPWluLXN0b2NrJndpc2hsaXN0PTEiLCJyb3V0ZSI6InByb2R1Y3RzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788771077),('p7O4DsUw7biPQQ3sVccr3LPbbLehX1qdvM6f8kC1',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJCUXFLVjVQYXZjdGZ2UmtBRlVyQUgyUXdaTTVwYjk4bkloZE1UWkZoIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC93b21lbnMtY2xvdGhpbmc/c29ydD1uYW1lLWEteiIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788772329),('pDHu8C95jjpeaK7KCOQqDb29zzMLnXwAiQbSUgX5',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJjaEtVOWwzZ2RjSlg5V2lZWUFKd3ZJZDIwVDdEQWNwZFFyOGZXM3dkIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9mYXNoaW9uP3ByaWNlPTEwMDAtNDk5OSZzdG9jaz1pbi1zdG9jayIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788773599),('pKcjsKrVeCT1EgMzjSoS9vzpi3arDt5BacgrdrZF',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJtWW05UEZXMmQydU5IcnNsQUg3dDZFQjFpTDA5cXFjUFZObEFBdkpzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3RyYWNrLW9yZGVyIiwicm91dGUiOiJvcmRlcnMudHJhY2sifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788775496),('pqmQEViRLByFY9jT1EwIV7sBDdrfe9HZ2eoZ0VHe',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJjSG9ObzdxMFhFZ3VZSlRhSkRYbDAzVW9hckpMRjZhb1lvN0M1ZHFtIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Nob3A/c2hvcD1mZWF0dXJlZCZ3aXNobGlzdD0xIiwicm91dGUiOiJzaG9wIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788783508),('pZFYdGXMs5AAXl86pB0eEqTLxIGwsDqJLiJdKqAK',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJtZWZvanpUWXZObUI4TEZuOEROZXpBUkQweEZtOHlmRnFwNlE1ZUJGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWVsZWN0cm9uaWNzJnByaWNlPXVuZGVyLTUwMCZzaG9wPW5ldyZzb3J0PWZlYXR1cmVkJnN0b2NrPWluLXN0b2NrIiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788769981),('qfwpYYqerKwWlMPqGkiqRxo0Sxg1WbRXvzMdbk74',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ChatGPT-User/1.0; +https://openai.com/bot','eyJfdG9rZW4iOiI5SFFDVFBCeGFFVHg5clpsQlVWTEdqdFBYRkpDdndKUjRSNUxEekkwIiwicHJvZHVjdF92aWV3ZWQiOnsiODQiOiIyMDI2LTA5LTA3VDEwOjA5OjQ3KzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby0xNy1lbGl0ZS1oaWdoLXNwZWVkLTE1MDB3LWh1Yi1tb3Rvci02MHYtNzJ2Iiwicm91dGUiOiJwcm9kdWN0cy5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788775787),('QIzsVbUrnGIeBVH1CaIbssLdlv5P3p1lo4q26IQN',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +searchbot@anthropic.com)','eyJfdG9rZW4iOiJjaklBZVZPUk9FTWp1SDZhOWdPc1lNT3ZJQk9kVUsydThHRlhCdUNUIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9lbGVjdHJvbmljcy1zbWFydC13YXRjaGVzIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776513),('QOYUxSaQE6LG8wewN5fnMpW3mr3ObBjGy9mKo4Ln',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJhOThrWXNxRXlRNDFZQWhxT2Y1R2FWaHdYZ1lFZ2F3ZkdJUm9vQnNnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP3ByaWNlPTUwMC05OTkmc2hvcD1mZWF0dXJlZCIsInJvdXRlIjoicHJvZHVjdHMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788773839),('Qpne5KuW00FbVgf8zKY9V7b3TkGgS5uY9jnanQND',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJJRVN2N2xzdjFTRDRRT2kxNmZkQmlrOG91WDlUZXhWUEdoWldKQkNsIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9lbGVjdHJvbmljcz9zb3J0PW5ld2VzdCIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788770465),('QsJPQHqBckgjEZvCZcYh0n6xBb5vngcyTDF6WaaC',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJuSjRVclJ0Y3g3S0F5THlFUE1Vbk5PeUxycXlid1BpN1AycnBnMTgxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9ldi1hY2Nlc3NvcmllcyIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788773722),('RaekUxDtqJ3p2mmOGKMXdEP5tHdxcq0RHjsepCHX',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJHWEJuNkF6SWp0bFR6S2ViSFI2UG9YdWVMNm5YYU1kMzM3akFiVGo0IiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjE2OjQ2KzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776206),('RpVH14YLSwn4lHBRzcSbs0b1B9UFZVHy0zIHenPG',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJ3SHpzaTZyQTY0a0FvbExxbjc1UzE4UnpxWjV1M0hQY1d3dkZ2Y1dVIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW4iLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788786270),('rYyGxDxJkladrae7J8ajFVrDhr6I7Z6xjEYk1hKO',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJCMFpHNjZ5OTQyRE1ReXdHb3dCZEdjeWtkRzVqYVlsTklSZkZXbWxWIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PXRlc3QtcHJvZHVjdHMmcHJpY2U9NTAwMC1wbHVzJnNob3A9ZmVhdHVyZWQmc29ydD1uYW1lLWEteiZzdG9jaz1pbi1zdG9jayZ3aXNobGlzdD0xIiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788770387),('SAx74YRxkkMnVTgHLGYMPpwNl7LdjLJ5wtlOhm2Q',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJBMlYxdEkydXBjR3U5V2VMWGZGYVRIRnUzN2M4SjlGdDV3Y2t3cFJzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9nZW5lcmFsP3N0b2NrPWluLXN0b2NrIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788769839),('SCFP1LEKqgX4SisJi06bIQ9bSAiDjd45AhPiP27k',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiIyNFVjY1lZZmEzRzhqSmxvbXJ3Y0FnZDVlcE9lY25zU0c5R204dGxtIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWdlbmVyYWwmcHJpY2U9NTAwLTk5OSZzaG9wPW5ldyZzb3J0PXByaWNlLWhpZ2gtbG93Iiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788771201),('sGVkDVECLbaqtfapTDMYb5gzu2MF37tDkvKqxbwv',NULL,'172.18.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJUWGdVU1hQSXdXUEp1WVhzWmNocUMweWdvcTY2UXN1YnJ3Z2lJaElBIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzXC9vbGEtYnJha2UtcGFkLXJlYXIiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJwcm9kdWN0X3ZpZXdlZCI6eyIzMSI6IjIwMjYtMDktMDdUMTA6MjE6NTIrMDA6MDAiLCIxMTgiOiIyMDI2LTA5LTA3VDEwOjI0OjAwKzAwOjAwIn19',1788776641),('T1DYurXVeRvP5daGhKBkdqstDbEhtZpiAF0GaJMf',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJSRkdQMjhPYTRJMXhpUVBZN0x2cXl5NmlqOExPNmZ2dEJpYmZJN1RsIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9lbGVjdHJvbmljcz9wcmljZT11bmRlci01MDAiLCJyb3V0ZSI6ImRlcGFydG1lbnQuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788769780),('tBzLg6wnq1FtlfXo2kGnzHsWKJ9tvdDVy8nr6igD',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJhbGJoZVB3aGpoS3ljY3JhS2lQNU5vYWtnb3ZwNFBtdjBlcEhrYWhVIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC93b21lbnMtY2xvdGhpbmc/c29ydD1wcmljZS1sb3ctaGlnaCIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788773364),('TTh8Uc5jM4BXRfBEF8oL7Ye5yF8IOjk63RkKfKZd',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJQdnJsdVJEV21ZNFhIRHlxWk0za2IwQm5PRUpxR2xadktFTzBLR2xTIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC93b21lbnMtY2xvdGhpbmc/cHJpY2U9NTAwLTk5OSIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788772077),('tvF1dYrYzxWdZQSClGso6Pwl2LZaHfvSVvLJkkQu',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJQbVJTaHluSmxCWUlqNkZDazJxV0h1SWtWUUxpUFNseGtxM2s3RmdDIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9mYXNoaW9uP3ByaWNlPTUwMDAtcGx1cyZzb3J0PW5ld2VzdCIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788774357),('TVZ6M9g3c0yq5m7vRCMp7Ehc1nMrWqzQZEesJQ1I',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +searchbot@anthropic.com)','eyJfdG9rZW4iOiJpbEF4eThQNkpHMTZtT0dBQkhiWUNIVHhRaXpZeTZNaHdZRjNKTEZBIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9hdXRvbW90aXZlLXRvb2xzIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776656),('uCq72IEZBcEOJzJ17hD8cQU5YK9QB0n68Q0vtzUC',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJzcWR5Z1BsdG92VXFmelppckpMZ2JBZVJNeXFuWG1HT0RXRmUwenA4IiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjExOjQwKzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788775900),('uEX9ZiCvE8POXwtUUs9vb0Ki8Si6vKskSypRwSaI',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJwUUhBdnBBWVpvUTFjY0E4ZXFlZ0drOGdHMHAzN0tINEczc2MzNEZCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzXC8zMSIsInJvdXRlIjoicHJvZHVjdHMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788775963),('USlmmDpwNLTSpTl1mJpt7R1eWCnNIuBGY3khEF78',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiI2ODM3MWxyRmxyNlpSeGJ0TWhJd01UZTIzVGpsRjJ3cXFUOVNzYllkIiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjE5OjA5KzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776349),('v5MLrmqcdLE1VPRqaHtzW7csfmi3WGnHO2i7EtDl',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +searchbot@anthropic.com)','eyJfdG9rZW4iOiJnMHJiT2NNeERHNjFjVFpJZDY4c3k1NWJpRWtjQ3pxMDNXMjcxMmVNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC90b29scy1oYXJkd2FyZS1ob21lLXJlcGFpciIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788776706),('VGxpP7E1ZqK0jFTPaJyyHi3YQKcXC1w0DcqJSDJV',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJXdWxKWDE0anhBamFOeWpndHFOU0Mwb0JLYWpCRXJjd3RkbGhobWhzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9lbGVjdHJvbmljcz9zdG9jaz1pbi1zdG9jayIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788775330),('wb7CM7WXPIaLCiEUAINJ3AxWkepZBEVQEINF5F2f',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJNVmp1c3E0NUdEcGo0aE11dTVTbXpCVlVjM3V1Q2VWRVJ0WXZTMTYyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3NlYXJjaD9zb3J0PXByaWNlLWhpZ2gtbG93Iiwicm91dGUiOiJzZWFyY2gifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788771115),('We18pFfcwvyV1lYHY3OeOP8nT1QSJvIP1co8CTOI',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJaVTlTd285dVdEWk1waXVqcFhTekE4U2dNVUI0NThxVUJQYzFnelhYIiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjIwOjI2KzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776426),('WhgbdUSLMRHy5gbbp8kP3NPtXKJ8yqK0DGqv3KdA',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJrYVRmM0g0MGF3VG5aNXU1Z0ZPNkt5THpZY0cxcVVITnpGMk9xVHFsIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Nob3AiLCJyb3V0ZSI6InNob3AifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788772654),('wIZ5Kqz44SJnKbsr3ZIyzL3XlQ6fHYcIbO4MokAm',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJTbEc5VnRtY0c3bTFRYWxxSFdkODZBRFlkSjdKSURyVG1CYW1tRHFUIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9ldi1tb3RvcnMtbW90b3ItYWNjZXNzb3JpZXMiLCJyb3V0ZSI6ImRlcGFydG1lbnQuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788775497),('wqJMSLmz43SliXpMgwXjHGjbhsJ3xMUAwrgGYD8i',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJ6Mkh5SVBCQjJNd0FkeWd3TXk4b0JHR1pMelFITDl3eTdVQTN4SmkzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWVsZWN0cm9uaWNzJnByaWNlPTUwMDAtcGx1cyZzb3J0PW5hbWUtYS16Iiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788769894),('wTPCuXFsH80A5B4WVVb1GewpmsdmotIgHuRa1maZ',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJXMWU3a0JDRlJtV2dvWFQwY0l6bnBJUDk0WHd6S3EwSGswWGo2SHpCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzXC8zMSIsInJvdXRlIjoicHJvZHVjdHMuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788776143),('x7MheRG19SjZRJrz9BaK8bYOiwkXrYStDnl0fGjK',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJNbzlXM3Z6d2x5OWQ2aXRsOHBuVHpvUXdoMzRMUHFHWFdRWWlCV2NkIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC90ZXN0LXByb2R1Y3RzP3ByaWNlPTUwMDAtcGx1cyZzaG9wPW5ldyZzb3J0PW5hbWUtYS16JnN0b2NrPWluLXN0b2NrIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788771400),('X7zdJHPSEax9PQBdzBTeF4tsJsXo9NyH90hH5o3X',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJTbmFUVlN4UjVUUHMwajF6TXZpR1FtRDl2VXRuaXhuYjhvaTlnQVNxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP3Nob3A9ZmVhdHVyZWQiLCJyb3V0ZSI6InByb2R1Y3RzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788783381),('xBk1P4BWgb0pBrXGb48tqKNss0PfREBp4nLKz7O6',NULL,'172.18.0.1','curl/8.5.0','eyJfdG9rZW4iOiJKam5Mdm1oemxONGxYb3ZXUlp5a2ZxZHZGYnk2SG5HWWYxNmtVMkdGIiwicHJvZHVjdF92aWV3ZWQiOnsiMzEiOiIyMDI2LTA5LTA3VDEwOjE5OjAyKzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby1zZXJpZXMtdGhyb3R0bGUtd2F0ZXJwcm9vZi0xLTItMy1yZXZlcnNlLWZvcndhcmQiLCJyb3V0ZSI6InByb2R1Y3RzLnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788776342),('xpK2FoBm3c7flmbRa0qbB7liCsuf37MUBrz5BW2v',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Claude-SearchBot/1.0; +searchbot@anthropic.com)','eyJfdG9rZW4iOiJ6RHJGVGxaY3ZOWm05QWNyVEhkUFRiY2FqVFdLTWFOU3ZuUVRZbk0zIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9hcHBsaWFuY2VzLXNtYWxsLWFwcGxpYW5jZXMiLCJyb3V0ZSI6ImRlcGFydG1lbnQuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788776684),('XpxLuKZHzp2GC56YOVb9zQBADRA4WmcOvQNiGhBE',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko); compatible; ChatGPT-User/1.0; +https://openai.com/bot','eyJfdG9rZW4iOiJYcm9XRmFZOVRPT3RTZ3UzdzZMaDZmNVZTbGlYT2ZsSWY0QWE4MWJXIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW4iLCJyb3V0ZSI6ImhvbWUifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788775784),('XztoOtOGdQwgfkj4DflZQzI01a69ZOaSbwHDyKp1',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiI1NU1ZNzNXVktCZTBjS010Z0FhdDlHc0J1WlpENDFnaFd0UEUyeWFyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Nob3A/Y2F0ZWdvcnk9TmV3JTIwQXJyaXZhbHMmcHJpY2U9NTAwLTk5OSZzb3J0PXByaWNlLWxvdy1oaWdoIiwicm91dGUiOiJzaG9wIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788769504),('y7WeCDBeDFQZWnG8bhGBp8Ioxo1LLzC98s8OGrg7',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJONlI3dDd4dE1kOUZwV0ZqMU1md0VVcnRZY2puZlY5SXBwY0ZTOHY3IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9mYXNoaW9uP3ByaWNlPTEwMDAtNDk5OSZzaG9wPWZlYXR1cmVkJnN0b2NrPWluLXN0b2NrIiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788775084),('YfcHNz3EfEDCWCO1OQG0vXHg97mcMKwYiQ0jqfx4',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJPakRFRmM3MGFHRjRDQXJiczBqWUtWT2lOeEM4U1pYS3FKdHVCZ3hKIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Nob3A/Y2F0ZWdvcnk9TmV3JTIwQXJyaXZhbHMmcHJpY2U9NTAwMC1wbHVzJnNvcnQ9cHJpY2UtaGlnaC1sb3ciLCJyb3V0ZSI6InNob3AifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788771625),('ysmEVbWCEK3soZIu39e5zTHNumyU6gkHSdnsQsxs',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJSVUUzd0xubFNVSVkxWlgwQVVhQTdCbXIwbmZsNG0zYlI2Z3JicjNCIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP3NvcnQ9bmFtZS1hLXoiLCJyb3V0ZSI6InByb2R1Y3RzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788771439),('YsRQiqLfzCroJfP0eRjKf1uzJ4Bts28xrwNuhipP',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiIxYUFPOWQybG9LYzY0N24yelo5OVdvVkVZUHVNTVlrNFUzNVROa2hIIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9ob21lLW1hZGUtaGVhbHRoLW1peCIsInJvdXRlIjoiZGVwYXJ0bWVudC5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788772652),('YtI32hBbzKi8WR6hPncF7B2qT1obW4ZmH5SKnN9V',NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJzbmF5c003WGJpWE5vU0UyNE5lMzVhdzVUNFF3djg1cm1NVndMY0lyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3NlYXJjaCIsInJvdXRlIjoic2VhcmNoIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788783692),('ZakPa3bZoh75lZme8xgQN0OrRnMUhCodD5yAJfof',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJFMG9Na2FuSExhSDVwQTNtM3pDeXFoRWdEZm9nUEs4VzFTcXA3ZUZNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9mYXNoaW9uP3Nob3A9bmV3JnNvcnQ9bmFtZS1hLXoiLCJyb3V0ZSI6ImRlcGFydG1lbnQuc2hvdyJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788772788),('ZEBSOrav1cQFyziC9dudzn7OuNs3H5AGUC6Q1jek',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36','eyJfdG9rZW4iOiJablRwSGtqSFNGRVR0VkpIb2U5cGhpbXBHYURRbUJUekhJM2RXU3hrIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL2RlcGFydG1lbnRzXC9nZW5lcmFsP3ByaWNlPTUwMDAtcGx1cyZzb3J0PW5hbWUtYS16Iiwicm91dGUiOiJkZXBhcnRtZW50LnNob3cifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1788773857),('zm5oYi3qmbI7KDh6l3anjyeQHEe0eVdffekNwOMu',NULL,'172.18.0.1','Mozilla/5.0 (compatible; SemrushBot/7~bl; +http://www.semrush.com/bot.html)','eyJfdG9rZW4iOiJrMjh1U3V6SVpzYWpBY0pqckhnOHR6U3lFMXRIa2VKamo0TzQ0S1YwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHBzOlwvXC9zaG9wLnN1c2hha28uaW5cL3Byb2R1Y3RzP2NhdGVnb3J5PWdlbmVyYWwmcHJpY2U9NTAwMC1wbHVzJnNob3A9bmV3JnNvcnQ9bmV3ZXN0Iiwicm91dGUiOiJwcm9kdWN0cy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1788770114),('ZSOgvnSVe4Ec45amV0H6S2uebHRadE9bfWavmIVa',NULL,'172.18.0.1','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Amzn-SearchBot/0.1) Chrome/119.0.6045.214 Safari/537.36','eyJfdG9rZW4iOiJzR2VNcElORENDaEhWdTlGbnh3SDFBalVYTmdCdHUxTkJoUG1zZ3pBIiwicHJvZHVjdF92aWV3ZWQiOnsiNzkiOiIyMDI2LTA5LTA3VDEyOjExOjU3KzAwOjAwIn0sIl9wcmV2aW91cyI6eyJ1cmwiOiJodHRwczpcL1wvc2hvcC5zdXNoYWtvLmluXC9wcm9kdWN0c1wvZXZrYXJ0LXByby13cnVseS1pbnRlbGxpZ2VudC1jb250cm9sbGVyLTQ4di03MnYtMzBhIiwicm91dGUiOiJwcm9kdWN0cy5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1788783117);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settlement_adjustments`
--

DROP TABLE IF EXISTS `settlement_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settlement_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seller_settlement_id` bigint unsigned DEFAULT NULL,
  `vendor_id` bigint unsigned NOT NULL,
  `adjustment_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `settlement_adjustments_seller_settlement_id_foreign` (`seller_settlement_id`),
  KEY `settlement_adjustments_vendor_id_foreign` (`vendor_id`),
  KEY `settlement_adjustments_admin_user_id_foreign` (`admin_user_id`),
  CONSTRAINT `settlement_adjustments_admin_user_id_foreign` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `settlement_adjustments_seller_settlement_id_foreign` FOREIGN KEY (`seller_settlement_id`) REFERENCES `seller_settlements` (`id`) ON DELETE SET NULL,
  CONSTRAINT `settlement_adjustments_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settlement_adjustments`
--

LOCK TABLES `settlement_adjustments` WRITE;
/*!40000 ALTER TABLE `settlement_adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `settlement_adjustments` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Sushako Super Admin','superadmin@sushako.test','9000000001',0,0,0,NULL,NULL,NULL,'$2y$12$oejiCfsSHmyjRRTM8vwoHOrx18NPMSda4JKluEWQYWfrtAWgRzuza','super_admin','active',0,'2026-08-31 00:13:03',NULL,NULL,NULL,NULL,NULL,'2026-07-23 12:50:35','2026-08-31 00:13:03'),(2,'Sushako Admin','admin@sushako.test','9000000002',0,0,0,NULL,NULL,NULL,'$2y$12$Lo2yMAPQN7rnTiaMITlHX.uz598fsDDwcPIG/4dGzBQYy8tylU/em','admin','inactive',0,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-23 12:50:36','2026-07-27 17:15:12'),(5,'Sushako Customer','customer@sushako.test','9000000005',0,0,0,NULL,NULL,NULL,'$2y$12$.P0bOrPKrsz30BWA7z.4U.hYYNwAAJ8JHz4pJws8/UP43IlopSPdu','customer','active',0,'2026-07-23 13:46:45',NULL,NULL,NULL,NULL,NULL,'2026-07-23 12:50:38','2026-07-27 17:15:13'),(6,'Koteeswaran Koteeswaran','kotiairport@gmail.com','9445712235',0,1,1,'2026-07-26 19:14:32','Admin verified consent','2026-07-23 19:43:08','$2y$12$J399./Wr6yKPZJwSdCsbDurjHb/Veyvq28uqYapZGxY6SZo2XKJeG','customer','active',0,'2026-07-25 19:29:04','2026-09-07 06:08:59','106769944664787550675','https://lh3.googleusercontent.com/a/ACg8ocLvXSX7sPXp9RsBiZfO7t0aK-KU7nk4yBO5aUABXV0l9i2rDw=s96-c',NULL,NULL,'2026-07-23 19:43:08','2026-09-07 06:08:59'),(25,'Subasri Koteeswaran','subasrikoteeswaran@gmail.com','9000000000',0,0,0,NULL,NULL,'2026-07-27 17:40:38','$2y$12$TKzcHq0CIbwU9XMmsp.ZFu7dCuoRSNzFfRZcu/xw.wl7hqmsQa86S','vendor','active',0,'2026-08-19 21:07:23',NULL,'113258255565437341525','https://lh3.googleusercontent.com/a/ACg8ocJ_bXjzMWTiYMtoEiGbnankVs9pSBDxqdSoKqXasVUgLwIj4w=s96-c',NULL,NULL,'2026-07-27 17:40:38','2026-08-19 21:07:23'),(26,'Nagarajan A','mailmerajan15@gmail.com','9003213624',1,1,0,NULL,'Admin verified consent',NULL,'$2y$12$ffZHq04Lo4Rp7A6FJwIbreVhsfaTw/bilmIbx9X8jtD8f945WFrBW','customer','active',0,NULL,'2026-07-28 12:11:56',NULL,NULL,NULL,NULL,'2026-07-28 12:11:18','2026-08-08 21:54:17'),(27,'Sharmila V','vsharmilaui@gmail.com','9176000052',0,0,0,NULL,NULL,'2026-07-29 09:44:44','$2y$12$G10Q7.sLZaQVKzzL7sslJOxtM3UXd0zSO1ofSBfGXjKHKSPNW2f5K','vendor','active',0,'2026-08-08 20:03:43',NULL,'113215432553098276413','https://lh3.googleusercontent.com/a/ACg8ocK3Wd-Q0iWxUW43FW2Yhq-61I_bYWsbpKFnFvTPwG8tcdD2tA=s96-c',NULL,NULL,'2026-07-29 09:44:44','2026-08-08 20:03:43'),(28,'Koteeswaran','koteeswaran.pm@gmail.com',NULL,0,0,0,NULL,NULL,'2026-07-29 10:46:34','$2y$12$immWheF54j3.nTLVlOajMuNotTGFb4B.23poUaBnsD8jBtxwiBjnW','vendor','active',0,'2026-07-29 10:48:17',NULL,'106095729001291900319','https://lh3.googleusercontent.com/a/ACg8ocKyNAXKQNHxuwvHl7OO_eVqP_G3kd0Oiot9RMoYcH1GmClueg=s96-c',NULL,NULL,'2026-07-29 10:46:34','2026-07-29 10:48:17'),(115,'Sindhujha E','sindhujhaelango94@gmail.com','7299099957',0,0,0,NULL,NULL,'2026-07-29 21:08:25','$2y$12$XBtYGhWez2qneE8Hmg8xp.HvsUb7jcsjWtd1v3koi1HkMduTQwod.','vendor','active',0,'2026-07-29 21:18:40',NULL,NULL,NULL,NULL,NULL,'2026-07-29 21:08:25','2026-07-29 21:18:40'),(116,'Kotees waran','kotees7418@gmail.com',NULL,0,0,0,NULL,NULL,'2026-08-04 01:11:10','$2y$12$W1Ef.20dTuow0vf9Bw6I/.dFBwsIlb4sgoJ09FgihcQg4NbvmIGti','vendor','active',0,'2026-08-04 01:11:18',NULL,'109147345481302058580','https://lh3.googleusercontent.com/a/ACg8ocK8yz22pFV3AsROge6z3buOgghuWRdT8CPclxJbFtcnxBoodQ=s96-c',NULL,NULL,'2026-08-04 01:11:10','2026-08-04 01:11:18'),(117,'Zaafi','zaafihairoil@gmail.com','9384595122',0,0,0,NULL,NULL,'2026-08-04 16:48:28','$2y$12$1axfD/dh62VTo/b2bPAuxeMqHwGMiGjSBlbwWC4m8KK55D9yIEIse','vendor','active',0,'2026-08-23 11:31:39',NULL,NULL,NULL,NULL,NULL,'2026-08-04 16:48:28','2026-08-23 11:31:39'),(118,'Saranya Ravi','diyavamshi@gmail.com',NULL,0,0,0,NULL,NULL,'2026-08-04 17:12:04','$2y$12$9yP3hccjrs00XZPAhVL2PuhgQ4HyXHEeSjBOW7qApBOq/yAvXRDU6','vendor','active',0,'2026-08-04 17:12:06',NULL,NULL,NULL,NULL,NULL,'2026-08-04 17:12:04','2026-08-04 17:12:06'),(119,'Sushako','sushako.admin@gmail.com',NULL,0,0,0,NULL,NULL,'2026-08-20 04:52:43','$2y$12$z/ueR8WYeO4HYC9XbunBvuUm0NVkj52aNbXZBNjyJOlWNPGUk4ADi','vendor','active',0,'2026-08-20 07:22:59',NULL,'108123241095546592504','https://lh3.googleusercontent.com/a/ACg8ocL9fPii60_Yh-UYP231BPTdWTyyd5Xe_4YddBRZlULXJLA5mQ=s96-c',NULL,NULL,'2026-08-20 04:52:43','2026-08-20 07:22:59'),(120,'Evpro','evpro.in@gmail.com','6381751740',0,0,0,NULL,NULL,'2026-08-27 10:48:24','$2y$12$DmXrt/thz.XoR10ghrB0je.Pv0PT2av53O2iVVt0VRI4Wn0YrNu1y','vendor','active',0,'2026-08-27 11:34:34',NULL,'104572372910601197233','https://lh3.googleusercontent.com/a/ACg8ocKV2CTQFNgFdoseqO7WJsJuo0x4kvXKB9NvJw7soq0niHy7=s96-c',NULL,NULL,'2026-08-27 10:48:24','2026-08-27 11:34:34');
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
  `pickup_address_line_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_postal_code` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `returns_accepted` tinyint(1) NOT NULL DEFAULT '0',
  `return_window_days` smallint unsigned DEFAULT NULL,
  `return_policy_summary` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'No Returns',
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
  `business_name_edit_count` tinyint unsigned NOT NULL DEFAULT '0',
  `tax_preference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'registered',
  `approval_rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `store_visibility` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `setup_completed_at` timestamp NULL DEFAULT NULL,
  `published_by` bigint unsigned DEFAULT NULL,
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
  KEY `vendors_tax_preference_index` (`tax_preference`),
  KEY `vendors_approval_status_index` (`approval_status`),
  KEY `vendors_store_visibility_index` (`store_visibility`),
  KEY `vendors_published_by_foreign` (`published_by`),
  CONSTRAINT `vendors_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendors_published_by_foreign` FOREIGN KEY (`published_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendors_selected_plan_id_foreign` FOREIGN KEY (`selected_plan_id`) REFERENCES `seller_plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
INSERT INTO `vendors` VALUES (6,25,'Subasri Koteeswaran Store','Subasri Koteeswaran','subasri-koteeswaran-store','subasrikoteeswaran@gmail.com','9000000000',NULL,NULL,NULL,NULL,'Chennai',NULL,'Chennai','Tamilnadu','600052','India',NULL,'active','complete',NULL,NULL,NULL,'2026-07-27 17:40:38','2026-08-19 21:13:47','Subasri Koteeswaran Store','dasdaklsdmaklsfdklasjfklsdjlfjsklafjklsadf',NULL,NULL,NULL,NULL,'Chennai',NULL,'Chennai',NULL,NULL,NULL,NULL,'Chennai',NULL,NULL,NULL,NULL,NULL,100,'km','Asia/Kolkata','self_shipping',1000.00,1999.00,0,NULL,'No Returns',0,NULL,1,'live','free','active','2026-07-27 18:07:33',NULL,NULL,NULL,3,'2026-07-27 17:59:46','2026-08-19 21:13:47',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'{\"delivery_radius\": 100, \"shipment_method\": \"self_shipping\", \"flat_shipping_charge\": 1000, \"free_shipping_threshold\": \"1999\"}',NULL,NULL,NULL,NULL,'not_required','free',1,'free','{\"id\": 1, \"key\": \"free\", \"name\": \"Free\", \"slug\": \"free\", \"price\": 0, \"amount\": 0, \"is_paid\": false, \"currency\": \"INR\", \"features\": [\"Unlimited time\", \"Rs 1 commission on every product unit sold\", \"Self shipping\", \"Draft to published workflow\", \"Professional dashboard\"], \"commission\": \"Rs 1 commission on every product unit sold\", \"grace_days\": 0, \"order_limit\": \"Unlimited\", \"product_limit\": 25, \"validity_days\": null, \"billing_period\": \"none\", \"commission_type\": \"flat\", \"supporting_text\": \"Start free for unlimited time with a simple Rs 1 unit commission.\", \"commission_value\": 1}','2026-07-27 17:59:46','not_required',1,0,NULL,'onboarding_completed',NULL,'published',NULL,NULL),(7,27,'Sharmila V Store','Sharmila V','sharmila-v-store','vsharmilaui@gmail.com','9176000052',NULL,'individual',NULL,NULL,'Mettu Street',NULL,'Chengalpattu','Tamil Nadu','600052','India',NULL,'active','complete',NULL,NULL,NULL,'2026-07-29 09:44:44','2026-07-29 15:20:45','Sharmila V Store','kmklklsdgkledjklsvdfklvjsdklgjsd;fklgjkd;fklgsdfkljgkldfjgsdfljgkldfjgklfjsgkljdfklgjdfl',NULL,NULL,NULL,NULL,'Chengalpattu',NULL,'Mettu Street, Chengalpattu, Tamil Nadu, 600052',NULL,NULL,NULL,NULL,'Mettu Street, Chengalpattu, Tamil Nadu, 600052',NULL,NULL,NULL,NULL,NULL,5,'km','Asia/Kolkata','self_shipping',20.00,1000.00,0,NULL,'No Returns',0,NULL,1,'setup_required','free','active','2026-07-29 15:20:45',NULL,NULL,NULL,3,'2026-07-29 15:20:45',NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'{\"pickup_city\": \"Chengalpattu\", \"pickup_state\": \"Tamil Nadu\", \"delivery_radius\": 5, \"pickup_landmark\": null, \"shipment_method\": \"self_shipping\", \"pickup_postal_code\": \"600052\", \"flat_shipping_charge\": 20, \"use_business_address\": true, \"pickup_address_line_1\": \"Mettu Street\", \"pickup_address_line_2\": null, \"free_shipping_threshold\": \"1000\"}',NULL,NULL,NULL,NULL,'not_required','free',1,'free','{\"id\": 1, \"key\": \"free\", \"name\": \"Free\", \"slug\": \"free\", \"price\": 0, \"amount\": 0, \"is_paid\": false, \"currency\": \"INR\", \"features\": [\"Unlimited time\", \"Rs 1 commission on every product unit sold\", \"Self shipping\", \"Draft to published workflow\", \"Professional dashboard\"], \"commission\": \"Rs 1 commission on every product unit sold\", \"grace_days\": 0, \"order_limit\": \"Unlimited\", \"product_limit\": 25, \"validity_days\": null, \"billing_period\": \"none\", \"commission_type\": \"flat\", \"supporting_text\": \"Start free for unlimited time with a simple Rs 1 unit commission.\", \"commission_value\": 1}','2026-07-29 15:20:45','not_required',1,0,NULL,'onboarding_completed',NULL,'draft',NULL,NULL),(8,28,'Koteeswaran Store','Koteeswaran','koteeswaran-store','koteeswaran.pm@gmail.com','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'India',NULL,'active','draft',NULL,NULL,NULL,'2026-07-29 10:46:34','2026-07-29 10:48:03','Koteeswaran Store',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Asia/Kolkata',NULL,NULL,NULL,0,NULL,'No Returns',0,NULL,1,'setup_required',NULL,'pending_payment',NULL,NULL,NULL,NULL,3,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,'pending','growth',2,'growth','{\"id\": 2, \"key\": \"growth\", \"name\": \"Growth\", \"slug\": \"growth\", \"price\": 999, \"amount\": 999, \"is_paid\": true, \"currency\": \"INR\", \"features\": [\"Up to 100 products\", \"Unlimited orders\", \"Zero commission on orders\", \"Advanced seller dashboard\", \"Professional storefront\", \"Sales and order reports\", \"Marketing tools\", \"Seller labelling features available as an add-on\", \"Priority support\", \"One-month plan validity\"], \"commission\": \"Zero commission on orders\", \"grace_days\": 0, \"order_limit\": \"Unlimited\", \"product_limit\": 100, \"validity_days\": 30, \"billing_period\": \"monthly\", \"commission_type\": \"none\", \"supporting_text\": \"Built for growing sellers who want predictable monthly pricing and no commission on orders.\", \"commission_value\": 0}','2026-07-29 10:48:02','pending',0,0,NULL,'registered',NULL,'draft',NULL,NULL),(9,1,'Sushako Official Store','Sushako Shopping','sushako-official-store','superadmin@sushako.test','9000000001',NULL,'marketplace_official_store',NULL,NULL,'Sushako Operations Center',NULL,'Chennai','Tamil Nadu','600001','India',NULL,'active','complete','2026-07-29 13:30:20',NULL,NULL,'2026-07-29 13:30:20','2026-07-29 13:30:20','Sushako Official Store','Verified official Sushako marketplace store managed by the Super Admin team.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,'No Returns',0,NULL,0,'live','enterprise','active',NULL,NULL,NULL,NULL,1,NULL,'2026-07-29 13:30:20',NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,'enterprise',NULL,'enterprise',NULL,NULL,'not_required',1,0,NULL,'approved',NULL,'published','2026-07-29 13:30:20',1),(65,115,'Angels Boutique','Sindhujha E','sindhujha-e-store','sindhujhaelango94@gmail.com','7299099957',NULL,'individual',NULL,NULL,'Kummalamman koil street','Tondiarpet','Chennai','Tamil Nadu','600081','India',NULL,'active','complete',NULL,NULL,NULL,'2026-07-29 21:08:25','2026-08-05 04:13:04','Angels Boutique','All high quality cotton sarees','seller-logos/07dcec29-0e4e-4172-905f-4c802cde60ba.jpg',NULL,NULL,NULL,'Chennai',NULL,'Kummalamman koil street, Tondiarpet, Chennai, Tamil Nadu, 600081',NULL,NULL,NULL,NULL,'Kummalamman koil street, Tondiarpet, Chennai, Tamil Nadu, 600081',NULL,NULL,NULL,NULL,NULL,33,'km','Asia/Kolkata','self_shipping',50.00,1000.00,0,NULL,'No Returns',0,NULL,1,'live','free','active','2026-07-29 21:18:40',NULL,NULL,NULL,3,'2026-07-29 21:18:40',NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'{\"pickup_city\": \"Chennai\", \"pickup_state\": \"Tamil Nadu\", \"delivery_radius\": 33, \"pickup_landmark\": null, \"shipment_method\": \"self_shipping\", \"pickup_postal_code\": \"600081\", \"flat_shipping_charge\": 50, \"use_business_address\": true, \"pickup_address_line_1\": \"Kummalamman koil street\", \"pickup_address_line_2\": \"Tondiarpet\", \"free_shipping_threshold\": \"1000\"}',NULL,NULL,NULL,NULL,'not_required','free',1,'free','{\"id\": 1, \"key\": \"free\", \"name\": \"Free\", \"slug\": \"free\", \"price\": 0, \"amount\": 0, \"is_paid\": false, \"currency\": \"INR\", \"features\": [\"Unlimited time\", \"Rs 1 commission on every product unit sold\", \"Self shipping\", \"Draft to published workflow\", \"Professional dashboard\"], \"commission\": \"Rs 1 commission on every product unit sold\", \"grace_days\": 0, \"order_limit\": \"Unlimited\", \"product_limit\": 25, \"validity_days\": null, \"billing_period\": \"none\", \"commission_type\": \"flat\", \"supporting_text\": \"Start free for unlimited time with a simple Rs 1 unit commission.\", \"commission_value\": 1}','2026-07-29 21:18:39','not_required',1,0,NULL,'onboarding_completed',NULL,'draft',NULL,NULL),(66,116,'Kotees waran Store','Kotees waran','kotees-waran-store','kotees7418@gmail.com','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'India',NULL,'active','draft',NULL,NULL,NULL,'2026-08-04 01:11:11','2026-08-04 01:11:11','Kotees waran Store',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Asia/Kolkata',NULL,NULL,NULL,0,NULL,'No Returns',0,NULL,1,'setup_required',NULL,'pending_payment',NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,'registered',NULL,'draft',NULL,NULL),(67,117,'Zaafi Store','Zaafi','zaafi-store','zaafihairoil@gmail.com','9384595122',NULL,'home_business',NULL,NULL,'SSn thiruporur','Thiruporur','Chennai','Tamil Nadu','603110','India',NULL,'active','complete',NULL,NULL,NULL,'2026-08-04 16:48:28','2026-08-05 04:13:24','Zaafi Store','Zaafi Rosemary Oil is a proudly Indian brand dedicated to creating premium, homemade hair care products using carefully selected natural ingredients. Our mission is to help people achieve stronger, healthier, and more beautiful hair through chemical-free formulations inspired by nature.\r\nWe offer a range of products including Zaafi Rosemary Oil, Zaafi Rosemary Premium Oil, Zaafi Hair Growth Serum, Zaafi Dandruff Oil, and Beard Oil, each crafted to support healthy hair growth, reduce hair fall, nourish the scalp, and improve overall hair health.\r\nAt Zaafi, we believe that healthy hair starts with quality ingredients and consistent care. Our products are made with a focus on purity, effectiveness, and customer satisfaction.\r\nWhy Choose Zaafi?\r\n🌿 100% Natural Ingredients\r\n🧴 Homemade Formulations\r\n❌ No Harsh Chemicals\r\n💚 Supports Healthy Hair Growth\r\n✨ Helps Reduce Hair Fall & Dandruff\r\n🇮🇳 Made in India\r\nZaafi – Nature Meets Perfection.','seller-logos/58b1028c-f721-4473-99d2-94c810855fb4.jpg',NULL,NULL,NULL,'Chennai',NULL,'SSn thiruporur, Thiruporur, Chennai, Tamil Nadu, 603110',NULL,NULL,NULL,NULL,'SSn thiruporur, Thiruporur, Chennai, Tamil Nadu, 603110','[\"mon\", \"tue\", \"wed\", \"thu\", \"fri\", \"sat\"]','09:00:00','18:30:00',NULL,NULL,24,'km','Asia/Kolkata','self_shipping',60.00,799.00,0,NULL,'No Returns',0,NULL,1,'setup_required','free','active','2026-08-04 16:56:38',NULL,NULL,NULL,3,'2026-08-04 16:56:38',NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'{\"pickup_city\": \"Chennai\", \"pickup_state\": \"Tamil Nadu\", \"delivery_radius\": 24, \"pickup_landmark\": null, \"shipment_method\": \"self_shipping\", \"pickup_postal_code\": \"603110\", \"flat_shipping_charge\": 60, \"use_business_address\": true, \"pickup_address_line_1\": \"SSn thiruporur\", \"pickup_address_line_2\": \"Thiruporur\", \"free_shipping_threshold\": \"799\"}',NULL,NULL,NULL,NULL,'not_required','free',1,'free','{\"id\": 1, \"key\": \"free\", \"name\": \"Free\", \"slug\": \"free\", \"price\": 0, \"amount\": 0, \"is_paid\": false, \"currency\": \"INR\", \"features\": [\"Unlimited time\", \"Rs 1 commission on every product unit sold\", \"Self shipping\", \"Draft to published workflow\", \"Professional dashboard\"], \"commission\": \"Rs 1 commission on every product unit sold\", \"grace_days\": 0, \"order_limit\": \"Unlimited\", \"product_limit\": 25, \"validity_days\": null, \"billing_period\": \"none\", \"commission_type\": \"flat\", \"supporting_text\": \"Start free for unlimited time with a simple Rs 1 unit commission.\", \"commission_value\": 1}','2026-08-04 16:56:38','not_required',1,0,'gst_excluded','onboarding_completed',NULL,'hidden',NULL,NULL),(68,118,'Saranya Ravi Store','Saranya Ravi','saranya-ravi-store','diyavamshi@gmail.com','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'India',NULL,'active','draft',NULL,NULL,NULL,'2026-08-04 17:12:04','2026-08-04 17:12:04','Saranya Ravi Store',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Asia/Kolkata',NULL,NULL,NULL,0,NULL,'No Returns',0,NULL,1,'setup_required',NULL,'pending_payment',NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,'registered',NULL,'draft',NULL,NULL),(69,119,'Sushako Store','Sushako','sushako-store','sushako.admin@gmail.com','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'India',NULL,'active','draft',NULL,NULL,NULL,'2026-08-20 04:52:43','2026-08-20 04:52:43','Sushako Store',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Asia/Kolkata',NULL,NULL,NULL,0,NULL,'No Returns',0,NULL,1,'setup_required',NULL,'pending_payment',NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,NULL,'registered',NULL,'draft',NULL,NULL),(70,120,'Evkartpro','Evpro','evpro-store','evpro.in@gmail.com','6381751740',NULL,'individual',NULL,NULL,'4/123 Old Otteri,\r\nGST Road,\r\nVandalur',NULL,'Vandalur','Tamil Nadu','600048','India',NULL,'active','complete',NULL,NULL,NULL,'2026-08-27 10:48:24','2026-08-27 10:51:04','Evkartpro','Local Sushako seller.',NULL,'CRGPK1588J',NULL,NULL,'Vandalur',NULL,'4/123 Old Otteri,\r\nGST Road,\r\nVandalur, Vandalur, Tamil Nadu, 600048','4/123 Old Otteri,\r\nGST Road,\r\nVandalur','Vandalur','Tamil Nadu','600048','4/123 Old Otteri,\r\nGST Road,\r\nVandalur, Vandalur, Tamil Nadu, 600048',NULL,NULL,NULL,NULL,NULL,10,'km','Asia/Kolkata','self_shipping',40.00,2999.00,1,1,'Returns accepted within 1 day from delivery',0,NULL,1,'live','free','active','2026-08-27 10:51:04',NULL,NULL,NULL,3,'2026-08-27 10:51:04','2026-08-27 10:51:04',NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'not_submitted',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'not_registered','2026-08-27 10:51:04',NULL,NULL,NULL,NULL,NULL,NULL,0,0,'{\"pickup_city\": \"Vandalur\", \"pickup_state\": \"Tamil Nadu\", \"delivery_radius\": 10, \"shipment_method\": \"self_shipping\", \"pickup_postal_code\": \"600048\", \"flat_shipping_charge\": 40, \"use_business_address\": true, \"delivery_radius_label\": \"10 km\", \"pickup_address_line_1\": \"4/123 Old Otteri,\\r\\nGST Road,\\r\\nVandalur\", \"return_policy_summary\": \"Returns accepted within 1 day from delivery\", \"free_shipping_threshold\": \"2999\"}',NULL,NULL,NULL,NULL,'not_required','free',1,'free','{\"id\": 1, \"key\": \"free\", \"name\": \"Free\", \"slug\": \"free\", \"price\": 0, \"amount\": 0, \"is_paid\": false, \"currency\": \"INR\", \"features\": [\"Unlimited time\", \"Rs 1 commission on every product unit sold\", \"Self shipping\", \"Draft to published workflow\", \"Professional dashboard\"], \"commission\": \"Rs 1 commission on every product unit sold\", \"grace_days\": 0, \"order_limit\": \"Unlimited\", \"product_limit\": 25, \"validity_days\": null, \"billing_period\": \"none\", \"commission_type\": \"flat\", \"supporting_text\": \"Start free for unlimited time with a simple Rs 1 unit commission.\", \"commission_value\": 1}','2026-08-27 10:51:04','not_required',1,0,NULL,'onboarding_completed',NULL,'published',NULL,NULL);
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

-- Dump completed on 2026-09-07 13:16:11
