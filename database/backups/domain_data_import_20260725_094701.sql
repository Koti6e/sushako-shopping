-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: sushako_shopping
-- ------------------------------------------------------
-- Server version	8.0.46-0ubuntu0.24.04.3

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
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `phone_is_whatsapp`, `email_verified_at`, `password`, `role`, `status`, `must_change_password`, `last_login_at`, `google_id`, `google_avatar`, `avatar_path`, `remember_token`, `created_at`, `updated_at`) VALUES (1,'Sushako Super Admin','superadmin@sushako.test','9000000001',0,NULL,'$2y$12$kRdCRjjHXRwXZzyt1oNbuu8624VV40XLeRtlr2gf5vxUNEcjES6bu','super_admin','active',0,'2026-07-24 04:43:51',NULL,NULL,NULL,NULL,'2026-07-23 12:50:35','2026-07-24 05:13:28'),(2,'Sushako Admin','admin@sushako.test','9000000002',0,NULL,'$2y$12$Lo2yMAPQN7rnTiaMITlHX.uz598fsDDwcPIG/4dGzBQYy8tylU/em','admin','inactive',0,NULL,NULL,NULL,NULL,NULL,'2026-07-23 12:50:36','2026-07-24 05:13:26'),(3,'Approved Vendor Owner','vendor.approved@sushako.test','9000000003',0,NULL,'$2y$12$Fyeku3nf2S9nv29eoOdpf.U1ACff4WKl5QiUYGOcsN1v2os1ajZZK','vendor','inactive',0,NULL,NULL,NULL,NULL,NULL,'2026-07-23 12:50:36','2026-07-24 05:13:26'),(4,'Pending Vendor Owner','vendor.pending@sushako.test','9000000004',0,NULL,'$2y$12$bRYL6Q8gmSZqc4MNV/.o5.R5ApY7Y2erChc7/eQNCX4qfM8SMmSVu','vendor','inactive',0,NULL,NULL,NULL,NULL,NULL,'2026-07-23 12:50:37','2026-07-24 05:13:26'),(5,'Sushako Customer','customer@sushako.test','9000000005',0,NULL,'$2y$12$QUvOxawT5omaP6LZArWuiO6t7jFcbh70JuR1Kmmbf3HI0t09rkY9W','customer','active',0,'2026-07-23 13:46:45',NULL,NULL,NULL,NULL,'2026-07-23 12:50:38','2026-07-24 05:13:30'),(6,'Koteeswaran T','kotiairport@gmail.com','9445712235',0,'2026-07-23 19:43:08','$2y$12$tEXRlevLbKvQG3Usn0wGr.v6MfeTV5oglP5FMZg1oN4QEMtgDTPs2','customer','active',0,'2026-07-24 04:10:41','106769944664787550675','https://lh3.googleusercontent.com/a/ACg8ocLvXSX7sPXp9RsBiZfO7t0aK-KU7nk4yBO5aUABXV0l9i2rDw=s96-c',NULL,NULL,'2026-07-23 19:43:08','2026-07-24 04:10:41');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
INSERT INTO `vendors` (`id`, `user_id`, `business_name`, `legal_name`, `slug`, `email`, `phone`, `alternate_phone`, `business_type`, `tax_number`, `registration_number`, `address_line_1`, `address_line_2`, `city`, `state`, `postal_code`, `country`, `logo`, `status`, `onboarding_status`, `approved_at`, `approved_by`, `rejection_reason`, `created_at`, `updated_at`) VALUES (1,3,'Approved Demo Store','Approved Demo Store Private Limited','approved-demo-store','vendor.approved@sushako.test','9000000003',NULL,'Fashion',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'India',NULL,'inactive','inactive','2026-07-23 12:50:37',1,NULL,'2026-07-23 12:50:37','2026-07-23 12:50:37'),(2,4,'Pending Demo Store','Pending Demo Store','pending-demo-store','vendor.pending@sushako.test','9000000004',NULL,'Electronics',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'India',NULL,'inactive','inactive',NULL,NULL,NULL,'2026-07-23 12:50:37','2026-07-23 12:50:37');
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `tax_slabs`
--

LOCK TABLES `tax_slabs` WRITE;
/*!40000 ALTER TABLE `tax_slabs` DISABLE KEYS */;
INSERT INTO `tax_slabs` (`id`, `name`, `rate`, `is_active`, `created_at`, `updated_at`) VALUES (1,'GST 0%',0.00,1,'2026-07-24 05:13:27','2026-07-24 05:13:27'),(2,'GST 5%',5.00,1,'2026-07-24 05:13:27','2026-07-24 05:13:27'),(3,'GST 12%',12.00,1,'2026-07-24 05:13:27','2026-07-24 05:13:27'),(4,'GST 18%',18.00,1,'2026-07-24 05:13:27','2026-07-24 05:13:27'),(5,'GST 28%',28.00,1,'2026-07-24 05:13:27','2026-07-24 05:13:27');
/*!40000 ALTER TABLE `tax_slabs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id`, `name`, `slug`, `tagline`, `headline`, `description`, `image`, `accent`, `tax_slab_id`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES (2,'Home Made Health Mix','home-made-health-mix','Homemade nutrition','Traditional health mix for daily strength','Fresh, home-style health mix powders prepared for family nutrition, breakfast routines and everyday wellness.','assets/banners/home-made-health-mix.jpg','#496d47',2,1,1,'2026-07-23 21:14:10','2026-07-24 05:13:30'),(3,'Masala Powders','masala-powders','Fresh aroma','Small-batch masala powders','Aromatic spice powders for everyday Indian cooking, prepared with a focus on freshness, flavour and trusted ingredients.','assets/banners/masala-powders.jpg','#9b3a1d',2,1,2,'2026-07-23 21:14:10','2026-07-24 05:13:30'),(4,'Women\'s Clothing','womens-clothing','Curated style','Women\'s clothing for every occasion','Elegant women\'s clothing selections with polished styling, clear details and Sushako support.','assets/banners/womens-clothing.jpg','#a54569',2,1,4,'2026-07-23 21:14:10','2026-07-24 05:13:30'),(5,'Electronics','electronics','Smart essentials','Mobiles, laptops and everyday electronics','Useful electronics for work, entertainment and daily life, including laptops, mobiles, headsets and smart watches.','assets/banners/electronics.jpg','#263e68',4,1,3,'2026-07-24 02:50:00','2026-07-24 05:13:30');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`id`, `category_id`, `tax_slab_id`, `name`, `slug`, `collection`, `subcategory`, `brand`, `badge`, `short_description`, `full_description`, `fabric`, `fit`, `sleeve`, `pattern`, `occasion`, `country_of_origin`, `return_policy`, `mrp`, `selling_price`, `rating`, `reviews`, `is_published`, `is_new`, `is_best_seller`, `local_delivery`, `fulfillment_scope`, `sort_order`, `created_at`, `updated_at`) VALUES (6,5,NULL,'Lenovo 100e Celeron Laptop','lenovo-100e-celeron-laptop','Sushako Budget Tech','Budgeted Laptop','Lenovo','Budget Pick','Lenovo 100e Celeron laptop with 4GB RAM and a choice of 500GB HDD or 500GB SSD storage.','A practical Lenovo 100e Celeron laptop for students, browsing, online classes, billing work and everyday office use. Choose the 500GB HDD model for the lowest price or the 500GB SSD model for faster startup and smoother daily performance.','Intel Celeron','4GB RAM','500GB HDD or 500GB SSD','Lenovo 100e compact laptop','Students, browsing and office basics','India','Sushako support with invoice after order',11000,9000,4.7,8,1,1,1,1,'Sushako Electronics Fulfillment',10,'2026-07-24 03:02:46','2026-07-24 03:02:46'),(8,2,NULL,'Sushako Staging Sample Product','sushako-razorpay-test-product','Payment Testing','Staging Sample','Sushako','Staging','A one rupee staging product for checking cart, order creation and payment gateway flow before launch.','This staging sample product is kept only to confirm checkout, order ID, invoice and payment gateway behavior before real products are published.','Staging Sample','Standard','Not Applicable','Sample','Gateway Check','India','Not for customer sale',1,1,5.0,1,1,1,0,1,'Sushako Fulfillment',999,'2026-07-24 05:13:30','2026-07-24 05:13:30');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` (`id`, `product_id`, `path`, `label`, `sort_order`, `created_at`, `updated_at`) VALUES (25,8,'assets/banners/home-made-health-mix.jpg','Primary View',1,'2026-07-24 05:13:30','2026-07-24 05:13:30'),(26,8,'assets/banners/masala-powders.jpg','Ingredient View',2,'2026-07-24 05:13:30','2026-07-24 05:13:30'),(27,8,'assets/banners/womens-clothing.jpg','Store View',3,'2026-07-24 05:13:30','2026-07-24 05:13:30'),(28,6,'assets/products/lenovo-100e/lenovo-100e-01.jpg','Open front view',1,'2026-07-24 05:13:30','2026-07-24 05:13:30'),(29,6,'assets/products/lenovo-100e/lenovo-100e-02.jpg','Side ports view',2,'2026-07-24 05:13:30','2026-07-24 05:13:30'),(30,6,'assets/products/lenovo-100e/lenovo-100e-03.jpg','Touch display angle',3,'2026-07-24 05:13:30','2026-07-24 05:13:30'),(31,6,'assets/products/lenovo-100e/lenovo-100e-04.jpg','Hinge profile view',4,'2026-07-24 05:13:30','2026-07-24 05:13:30'),(32,6,'assets/products/lenovo-100e/lenovo-100e-05.jpg','Top lid view',5,'2026-07-24 05:13:30','2026-07-24 05:13:30');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `colour`, `colour_hex`, `size`, `option_name`, `option_value`, `price`, `stock`, `created_at`, `updated_at`) VALUES (6,6,'SS-LEN100E-HDD','Standard','#d8ccb9','500GB HDD','Storage','500GB HDD',9000,5,'2026-07-24 03:02:46','2026-07-24 05:13:30'),(7,6,'SS-LEN100E-SSD','Standard','#d8ccb9','500GB SSD','Storage','500GB SSD',11000,5,'2026-07-24 03:02:46','2026-07-24 03:02:46'),(9,8,'SS-STAGE-001','Standard','#d8ccb9','Standard',NULL,NULL,NULL,99,'2026-07-24 05:13:30','2026-07-24 05:13:30');
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `company_settings`
--

LOCK TABLES `company_settings` WRITE;
/*!40000 ALTER TABLE `company_settings` DISABLE KEYS */;
INSERT INTO `company_settings` (`id`, `company_name`, `legal_business_name`, `logo_path`, `gstin`, `pan`, `cin`, `address_line_1`, `address_line_2`, `city`, `state`, `pincode`, `country`, `support_email`, `support_phone`, `website`, `currency`, `timezone`, `business_hours`, `created_at`, `updated_at`) VALUES (1,'Sushako Shopping','Sushako Shopping',NULL,NULL,NULL,NULL,'Chennai',NULL,'Chennai','Tamil Nadu','600001','India','support@sushako.test','919876543210','http://127.0.0.1:8000','INR','Asia/Kolkata','Monday to Saturday, 10 AM to 7 PM','2026-07-24 05:13:27','2026-07-24 05:13:27');
/*!40000 ALTER TABLE `company_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `invoice_settings`
--

LOCK TABLES `invoice_settings` WRITE;
/*!40000 ALTER TABLE `invoice_settings` DISABLE KEYS */;
INSERT INTO `invoice_settings` (`id`, `invoice_prefix`, `next_invoice_number`, `invoice_footer`, `terms_conditions`, `authorized_signatory_name`, `authorized_signatory_designation`, `created_at`, `updated_at`) VALUES (1,'INV',1,'Thank you for shopping with Sushako.','Goods once delivered are governed by the Sushako return and support policies.','Sushako Store Admin','Authorized Signatory','2026-07-24 05:13:27','2026-07-24 05:13:27');
/*!40000 ALTER TABLE `invoice_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `shipping_settings`
--

LOCK TABLES `shipping_settings` WRITE;
/*!40000 ALTER TABLE `shipping_settings` DISABLE KEYS */;
INSERT INTO `shipping_settings` (`id`, `free_shipping_enabled`, `free_shipping_threshold`, `shipping_policy_text`, `weight_based_shipping_enabled`, `courier_integration_enabled`, `zone_based_shipping_enabled`, `created_at`, `updated_at`) VALUES (1,1,1000,'Delivery charges applicable for eligible orders below the free shipping threshold. Our team will contact the customer after order confirmation regarding delivery charges and logistics.',0,0,0,'2026-07-24 05:13:27','2026-07-24 05:13:27');
/*!40000 ALTER TABLE `shipping_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `payment_settings`
--

LOCK TABLES `payment_settings` WRITE;
/*!40000 ALTER TABLE `payment_settings` DISABLE KEYS */;
INSERT INTO `payment_settings` (`id`, `provider`, `label`, `enabled`, `environment`, `key_placeholder`, `webhook_url_placeholder`, `is_future_provider`, `created_at`, `updated_at`) VALUES (1,'cod','Cash on Delivery',1,'sandbox',NULL,NULL,0,'2026-07-24 05:13:27','2026-07-24 05:13:27'),(2,'razorpay','Razorpay',0,'sandbox','RAZORPAY_KEY_ID','RAZORPAY_WEBHOOK_URL',0,'2026-07-24 05:13:27','2026-07-24 05:13:27'),(3,'future_provider','Future Provider',0,'sandbox','PROVIDER_KEY','PROVIDER_WEBHOOK_URL',1,'2026-07-24 05:13:27','2026-07-24 05:13:27');
/*!40000 ALTER TABLE `payment_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `customer_addresses`
--

LOCK TABLES `customer_addresses` WRITE;
/*!40000 ALTER TABLE `customer_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` (`id`, `order_number`, `invoice_number`, `invoice_sequence`, `invoiced_at`, `user_id`, `customer_name`, `customer_phone`, `customer_email`, `address_line_1`, `address_line_2`, `city`, `pincode`, `landmark`, `delivery_location_url`, `subtotal`, `shipping_amount`, `shipping_status`, `tax_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `discount_amount`, `total_amount`, `status`, `payment_method`, `payment_status`, `shipping_provider`, `shipping_provider_other`, `tracking_number`, `packed_at`, `shipped_at`, `delivered_at`, `razorpay_order_id`, `razorpay_payment_id`, `placed_at`, `created_at`, `updated_at`) VALUES (6,'SS20260700001',NULL,NULL,NULL,6,'Koteeswaran T','9445712235','kotiairport@gmail.com','fksdkfsdjkfnjkasjk','jdbnfksdjkfnsdjkn','Chengalpattu','603001',NULL,'https://www.google.com/maps?q=13.0514944,80.2455552',1,0,'free',0,0,0,0,0,1,'payment_pending','unselected','pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-23 20:22:58','2026-07-23 20:22:58'),(7,'SS20260700002',NULL,NULL,NULL,6,'Koteeswaran T','9445712235','kotiairport@gmail.com','36e/99','mettu street','Chengalpattu','603001',NULL,NULL,36000,0,'free',0,0,0,0,0,36000,'placed','cod','pending',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 04:16:00','2026-07-24 04:14:31','2026-07-24 04:16:00');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_variant_id`, `product_name`, `product_slug`, `colour`, `size`, `quantity`, `unit_price`, `line_total`, `gst_rate`, `tax_amount`, `cgst_amount`, `sgst_amount`, `igst_amount`, `image`, `created_at`, `updated_at`) VALUES (6,6,NULL,NULL,'Sushako Razorpay Test Product','sushako-razorpay-test-product','Sushako Blue','Test Unit',1,1,1,0.00,0,0,0,0,'http://127.0.0.1:8000/assets/products/sushako-razorpay-test-product.svg','2026-07-23 20:22:58','2026-07-23 20:22:58'),(7,7,6,6,'Lenovo 100e Celeron Laptop','lenovo-100e-celeron-laptop','Standard','500GB HDD',4,9000,36000,0.00,0,0,0,0,'http://127.0.0.1:8000/assets/products/lenovo-100e/lenovo-100e-01.jpg','2026-07-24 04:14:31','2026-07-24 04:14:31');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-25  9:47:47
