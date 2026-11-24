-- MySQL dump 10.13  Distrib 9.1.0, for Win64 (x86_64)
--
-- Host: localhost    Database: maha_eseva
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.28-MariaDB

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
-- Table structure for table `application_custom_values`
--

DROP TABLE IF EXISTS `application_custom_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_custom_values` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `application_id` bigint(20) unsigned NOT NULL,
  `field_id` bigint(20) unsigned NOT NULL,
  `field_key` varchar(50) NOT NULL,
  `field_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_custom_values_tenant_id_foreign` (`tenant_id`),
  KEY `application_custom_values_field_id_foreign` (`field_id`),
  KEY `application_custom_values_application_id_field_key_index` (`application_id`,`field_key`),
  CONSTRAINT `application_custom_values_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_custom_values_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `service_custom_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_custom_values_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `application_custom_values`
--

LOCK TABLES `application_custom_values` WRITE;
/*!40000 ALTER TABLE `application_custom_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_custom_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `application_field_histories`
--

DROP TABLE IF EXISTS `application_field_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_field_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `application_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `field_name` varchar(50) NOT NULL,
  `field_label` varchar(100) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `old_display` text DEFAULT NULL,
  `new_display` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `application_field_histories_user_id_foreign` (`user_id`),
  KEY `application_field_histories_application_id_created_at_index` (`application_id`,`created_at`),
  KEY `application_field_histories_application_id_field_name_index` (`application_id`,`field_name`),
  KEY `application_field_histories_tenant_id_field_name_index` (`tenant_id`,`field_name`),
  CONSTRAINT `application_field_histories_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_field_histories_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_field_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `application_field_histories`
--

LOCK TABLES `application_field_histories` WRITE;
/*!40000 ALTER TABLE `application_field_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_field_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `application_status_histories`
--

DROP TABLE IF EXISTS `application_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `application_status_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `application_id` bigint(20) unsigned NOT NULL,
  `old_status` varchar(255) DEFAULT NULL,
  `new_status` varchar(255) NOT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `application_status_histories_tenant_id_foreign` (`tenant_id`),
  KEY `application_status_histories_changed_by_foreign` (`changed_by`),
  KEY `application_status_histories_application_id_created_at_index` (`application_id`,`created_at`),
  CONSTRAINT `application_status_histories_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_status_histories_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `application_status_histories`
--

LOCK TABLES `application_status_histories` WRITE;
/*!40000 ALTER TABLE `application_status_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `application_status_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `applications`
--

DROP TABLE IF EXISTS `applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `family_member_id` bigint(20) unsigned DEFAULT NULL,
  `service_id` bigint(20) unsigned NOT NULL,
  `sub_service` varchar(100) DEFAULT NULL,
  `application_number` varchar(40) NOT NULL,
  `application_date` date NOT NULL,
  `assigned_employee_id` bigint(20) unsigned DEFAULT NULL,
  `work_status` varchar(255) NOT NULL DEFAULT 'NEW',
  `payment_status` varchar(255) NOT NULL DEFAULT 'PENDING',
  `base_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `govt_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `additional_charges` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `received_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_mode` varchar(50) DEFAULT 'Cash',
  `external_acknowledgement_no` varchar(255) DEFAULT NULL,
  `external_portal_login_user` varchar(255) DEFAULT NULL,
  `submit_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `expected_completion_date` date DEFAULT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `delivery_status` varchar(255) NOT NULL DEFAULT 'PENDING',
  `delivery_date` timestamp NULL DEFAULT NULL,
  `work_details` text DEFAULT NULL,
  `payment_remarks` text DEFAULT NULL,
  `documents_list` text DEFAULT NULL,
  `pending_remarks` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `applications_tenant_id_application_number_unique` (`tenant_id`,`application_number`),
  UNIQUE KEY `applications_uuid_unique` (`uuid`),
  KEY `applications_branch_id_foreign` (`branch_id`),
  KEY `applications_customer_id_foreign` (`customer_id`),
  KEY `applications_service_id_foreign` (`service_id`),
  KEY `applications_assigned_employee_id_foreign` (`assigned_employee_id`),
  KEY `applications_created_by_foreign` (`created_by`),
  KEY `applications_updated_by_foreign` (`updated_by`),
  KEY `applications_tenant_id_branch_id_work_status_index` (`tenant_id`,`branch_id`,`work_status`),
  KEY `applications_tenant_id_customer_id_index` (`tenant_id`,`customer_id`),
  KEY `applications_tenant_id_assigned_employee_id_index` (`tenant_id`,`assigned_employee_id`),
  KEY `applications_tenant_id_payment_status_index` (`tenant_id`,`payment_status`),
  KEY `applications_tenant_id_application_date_index` (`tenant_id`,`application_date`),
  KEY `applications_family_member_id_foreign` (`family_member_id`),
  CONSTRAINT `applications_assigned_employee_id_foreign` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `applications_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `applications_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_family_member_id_foreign` FOREIGN KEY (`family_member_id`) REFERENCES `family_members` (`id`) ON DELETE SET NULL,
  CONSTRAINT `applications_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `applications`
--

LOCK TABLES `applications` WRITE;
/*!40000 ALTER TABLE `applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(50) NOT NULL,
  `entity_type` varchar(100) DEFAULT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `audit_logs_branch_id_foreign` (`branch_id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_tenant_id_event_index` (`tenant_id`,`event`),
  KEY `audit_logs_tenant_id_user_id_index` (`tenant_id`,`user_id`),
  KEY `audit_logs_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `branch_code` varchar(30) NOT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `aadhaar_number` varchar(20) DEFAULT NULL,
  `shop_licence_number` varchar(50) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_mobile` varchar(20) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `state` varchar(255) NOT NULL DEFAULT 'Maharashtra',
  `pincode` varchar(10) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'ACTIVE',
  `is_main_branch` tinyint(1) NOT NULL DEFAULT 0,
  `settings_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branches_tenant_id_branch_code_unique` (`tenant_id`,`branch_code`),
  UNIQUE KEY `branches_uuid_unique` (`uuid`),
  KEY `branches_tenant_id_status_index` (`tenant_id`,`status`),
  CONSTRAINT `branches_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'4955dae5-5253-4b02-bba2-cb1fea096e1a',1,'Main Head Office','HO-01',NULL,NULL,NULL,NULL,'Vinod Kadam','9876543210','admin@mahaeseva.com','Shop No. 1, Main Market Road','Pune','Pune','Maharashtra','411001','ACTIVE',1,NULL,'2026-11-24 00:27:26','2026-11-24 01:31:31',NULL);
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
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
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
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
-- Table structure for table `customer_categories`
--

DROP TABLE IF EXISTS `customer_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `discount_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_categories_tenant_id_name_unique` (`tenant_id`,`name`),
  CONSTRAINT `customer_categories_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_categories`
--

LOCK TABLES `customer_categories` WRITE;
/*!40000 ALTER TABLE `customer_categories` DISABLE KEYS */;
INSERT INTO `customer_categories` VALUES (1,1,'Regular Citizen','REG',0.00,'2026-11-24 00:27:27','2026-11-24 00:27:27'),(2,1,'Senior Citizen','SR',10.00,'2026-11-24 00:27:27','2026-11-24 00:27:27'),(3,1,'Student','STU',15.00,'2026-11-24 00:27:27','2026-11-24 00:27:27'),(4,1,'BPL Cardholder','BPL',20.00,'2026-11-24 00:27:27','2026-11-24 00:27:27');
/*!40000 ALTER TABLE `customer_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `customer_code` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `gender` varchar(255) NOT NULL DEFAULT 'MALE',
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `state` varchar(255) NOT NULL DEFAULT 'Maharashtra',
  `pincode` varchar(10) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `alternate_mobile` varchar(20) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `star_rating` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `is_blacklisted` tinyint(1) NOT NULL DEFAULT 0,
  `blacklist_reason` varchar(500) DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_tenant_id_customer_code_unique` (`tenant_id`,`customer_code`),
  UNIQUE KEY `customers_uuid_unique` (`uuid`),
  KEY `customers_branch_id_foreign` (`branch_id`),
  KEY `customers_category_id_foreign` (`category_id`),
  KEY `customers_created_by_foreign` (`created_by`),
  KEY `customers_updated_by_foreign` (`updated_by`),
  KEY `customers_tenant_id_mobile_index` (`tenant_id`,`mobile`),
  KEY `customers_tenant_id_branch_id_index` (`tenant_id`,`branch_id`),
  KEY `customers_tenant_id_name_index` (`tenant_id`,`name`),
  CONSTRAINT `customers_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customers_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `customer_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `customers_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `application_id` bigint(20) unsigned DEFAULT NULL,
  `document_type_name` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `storage_path` varchar(255) NOT NULL,
  `disk` varchar(255) NOT NULL DEFAULT 'local',
  `mime_type` varchar(100) NOT NULL,
  `file_size_bytes` bigint(20) unsigned NOT NULL,
  `file_hash` varchar(64) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'RECEIVED',
  `rejection_reason` text DEFAULT NULL,
  `verified_by` bigint(20) unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `uploaded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documents_uuid_unique` (`uuid`),
  KEY `documents_branch_id_foreign` (`branch_id`),
  KEY `documents_customer_id_foreign` (`customer_id`),
  KEY `documents_application_id_foreign` (`application_id`),
  KEY `documents_verified_by_foreign` (`verified_by`),
  KEY `documents_uploaded_by_foreign` (`uploaded_by`),
  KEY `documents_tenant_id_customer_id_index` (`tenant_id`,`customer_id`),
  KEY `documents_tenant_id_application_id_index` (`tenant_id`,`application_id`),
  KEY `documents_tenant_id_status_index` (`tenant_id`,`status`),
  CONSTRAINT `documents_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `documents_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_services`
--

DROP TABLE IF EXISTS `employee_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_services` (
  `employee_id` bigint(20) unsigned NOT NULL,
  `service_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`employee_id`,`service_id`),
  KEY `employee_services_service_id_foreign` (`service_id`),
  CONSTRAINT `employee_services_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_services_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_services`
--

LOCK TABLES `employee_services` WRITE;
/*!40000 ALTER TABLE `employee_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_tasks`
--

DROP TABLE IF EXISTS `employee_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `application_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` varchar(255) NOT NULL DEFAULT 'MEDIUM',
  `status` varchar(255) NOT NULL DEFAULT 'PENDING',
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_tasks_branch_id_foreign` (`branch_id`),
  KEY `employee_tasks_employee_id_foreign` (`employee_id`),
  KEY `employee_tasks_application_id_foreign` (`application_id`),
  KEY `employee_tasks_created_by_foreign` (`created_by`),
  KEY `employee_tasks_tenant_id_employee_id_status_index` (`tenant_id`,`employee_id`,`status`),
  KEY `employee_tasks_tenant_id_due_date_index` (`tenant_id`,`due_date`),
  CONSTRAINT `employee_tasks_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_tasks_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_tasks_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_tasks_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_tasks`
--

LOCK TABLES `employee_tasks` WRITE;
/*!40000 ALTER TABLE `employee_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `employee_code` varchar(30) NOT NULL,
  `designation` varchar(255) NOT NULL DEFAULT 'Service Operator',
  `joining_date` date DEFAULT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_proof_type` varchar(255) DEFAULT NULL,
  `id_proof_number` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_tenant_id_employee_code_unique` (`tenant_id`,`employee_code`),
  UNIQUE KEY `employees_uuid_unique` (`uuid`),
  KEY `employees_branch_id_foreign` (`branch_id`),
  KEY `employees_user_id_foreign` (`user_id`),
  KEY `employees_tenant_id_branch_id_status_index` (`tenant_id`,`branch_id`,`status`),
  CONSTRAINT `employees_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `family_members`
--

DROP TABLE IF EXISTS `family_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `family_members` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `relation` varchar(50) NOT NULL DEFAULT 'OTHER',
  `gender` varchar(20) NOT NULL DEFAULT 'MALE',
  `birth_date` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `family_members_customer_id_foreign` (`customer_id`),
  KEY `family_members_tenant_id_customer_id_index` (`tenant_id`,`customer_id`),
  CONSTRAINT `family_members_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `family_members_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `family_members`
--

LOCK TABLES `family_members` WRITE;
/*!40000 ALTER TABLE `family_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `family_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `follow_ups`
--

DROP TABLE IF EXISTS `follow_ups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `follow_ups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `application_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_employee_id` bigint(20) unsigned DEFAULT NULL,
  `follow_up_date` date NOT NULL,
  `follow_up_time` time DEFAULT NULL,
  `reason` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'PENDING',
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `follow_ups_branch_id_foreign` (`branch_id`),
  KEY `follow_ups_customer_id_foreign` (`customer_id`),
  KEY `follow_ups_application_id_foreign` (`application_id`),
  KEY `follow_ups_assigned_employee_id_foreign` (`assigned_employee_id`),
  KEY `follow_ups_created_by_foreign` (`created_by`),
  KEY `follow_ups_tenant_id_follow_up_date_status_index` (`tenant_id`,`follow_up_date`,`status`),
  KEY `follow_ups_tenant_id_assigned_employee_id_index` (`tenant_id`,`assigned_employee_id`),
  KEY `follow_ups_tenant_id_customer_id_index` (`tenant_id`,`customer_id`),
  CONSTRAINT `follow_ups_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `follow_ups_assigned_employee_id_foreign` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `follow_ups_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `follow_ups_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `follow_ups_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `follow_ups_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `follow_ups`
--

LOCK TABLES `follow_ups` WRITE;
/*!40000 ALTER TABLE `follow_ups` DISABLE KEYS */;
/*!40000 ALTER TABLE `follow_ups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `service_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `tax_percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  KEY `invoice_items_service_id_foreign` (`service_id`),
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `application_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_number` varchar(40) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'UNPAID',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_tenant_id_invoice_number_unique` (`tenant_id`,`invoice_number`),
  UNIQUE KEY `invoices_uuid_unique` (`uuid`),
  KEY `invoices_branch_id_foreign` (`branch_id`),
  KEY `invoices_customer_id_foreign` (`customer_id`),
  KEY `invoices_application_id_foreign` (`application_id`),
  KEY `invoices_created_by_foreign` (`created_by`),
  KEY `invoices_tenant_id_branch_id_status_index` (`tenant_id`,`branch_id`,`status`),
  KEY `invoices_tenant_id_customer_id_index` (`tenant_id`,`customer_id`),
  KEY `invoices_tenant_id_invoice_date_index` (`tenant_id`,`invoice_date`),
  CONSTRAINT `invoices_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
/*!50503 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `service_id` bigint(20) unsigned DEFAULT NULL,
  `sub_service` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `source` varchar(255) NOT NULL DEFAULT 'WEBSITE',
  `status` varchar(255) NOT NULL DEFAULT 'NEW',
  `next_follow_up_date` date DEFAULT NULL,
  `next_follow_up_time` varchar(20) DEFAULT NULL,
  `assigned_employee_id` bigint(20) unsigned DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `converted_customer_id` bigint(20) unsigned DEFAULT NULL,
  `converted_application_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leads_branch_id_foreign` (`branch_id`),
  KEY `leads_service_id_foreign` (`service_id`),
  KEY `leads_assigned_employee_id_foreign` (`assigned_employee_id`),
  KEY `leads_converted_customer_id_foreign` (`converted_customer_id`),
  KEY `leads_converted_application_id_foreign` (`converted_application_id`),
  KEY `leads_tenant_id_status_index` (`tenant_id`,`status`),
  KEY `leads_tenant_id_mobile_index` (`tenant_id`,`mobile`),
  CONSTRAINT `leads_assigned_employee_id_foreign` FOREIGN KEY (`assigned_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_converted_application_id_foreign` FOREIGN KEY (`converted_application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_converted_customer_id_foreign` FOREIGN KEY (`converted_customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leads_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_30_000001_create_tenants_branches_and_roles_tables',1),(5,'2026_09_30_000002_create_customers_services_and_applications_tables',1),(6,'2026_09_30_000003_create_documents_billing_and_crm_tables',1),(7,'2026_09_30_000004_create_employee_permissions_and_services_tables',1),(8,'2026_10_12_000001_add_occupation_and_star_rating_to_customers_table',1),(9,'2026_10_12_000002_add_fast_entry_fields_to_applications_table',1),(10,'2026_10_13_000001_make_category_id_nullable_in_services_table',1),(11,'2026_10_21_000004_sync_existing_applications_to_follow_ups',1),(12,'2026_10_22_000001_create_application_field_histories_table',1),(13,'2026_10_23_000001_add_payment_remarks_to_applications_table',1),(14,'2026_10_26_000001_add_blacklist_fields_to_customers_table',1),(15,'2026_10_26_000002_add_owner_and_licence_fields_to_branches_table',1),(16,'2026_10_27_000001_create_family_members_table',1),(17,'2026_10_29_000001_add_payment_mode_to_applications_table',1),(18,'2026_11_01_000001_add_branch_id_to_services_table',1),(19,'2026_11_07_000001_add_aadhaar_card_test_service',1),(20,'2026_11_16_000001_add_followup_and_sub_service_to_leads_table',1),(21,'2026_11_17_000001_sync_converted_leads_branches',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications_log`
--

DROP TABLE IF EXISTS `notifications_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `channel` varchar(255) NOT NULL DEFAULT 'SYSTEM',
  `recipient` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'SENT',
  `provider_response` text DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_log_user_id_foreign` (`user_id`),
  KEY `notifications_log_customer_id_foreign` (`customer_id`),
  KEY `notifications_log_tenant_id_channel_status_index` (`tenant_id`,`channel`,`status`),
  KEY `notifications_log_tenant_id_created_at_index` (`tenant_id`,`created_at`),
  CONSTRAINT `notifications_log_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `notifications_log_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications_log`
--

LOCK TABLES `notifications_log` WRITE;
/*!40000 ALTER TABLE `notifications_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otp_verifications`
--

DROP TABLE IF EXISTS `otp_verifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `otp_verifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `mobile` varchar(20) NOT NULL,
  `application_number` varchar(40) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `tracking_token` varchar(64) DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `otp_verifications_tracking_token_unique` (`tracking_token`),
  KEY `otp_verifications_tenant_id_foreign` (`tenant_id`),
  KEY `otp_verifications_mobile_application_number_index` (`mobile`,`application_number`),
  KEY `otp_verifications_tracking_token_index` (`tracking_token`),
  CONSTRAINT `otp_verifications_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
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
/*!50503 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `payment_refunds`
--

DROP TABLE IF EXISTS `payment_refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_refunds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned NOT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `refund_mode` varchar(255) NOT NULL DEFAULT 'CASH',
  `reason` text DEFAULT NULL,
  `refunded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_refunds_payment_id_foreign` (`payment_id`),
  KEY `payment_refunds_refunded_by_foreign` (`refunded_by`),
  CONSTRAINT `payment_refunds_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_refunds_refunded_by_foreign` FOREIGN KEY (`refunded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_refunds`
--

LOCK TABLES `payment_refunds` WRITE;
/*!40000 ALTER TABLE `payment_refunds` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `branch_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `application_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `receipt_number` varchar(40) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_mode` varchar(255) NOT NULL DEFAULT 'CASH',
  `transaction_reference` varchar(255) DEFAULT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'SUCCESS',
  `notes` text DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_tenant_id_receipt_number_unique` (`tenant_id`,`receipt_number`),
  UNIQUE KEY `payments_uuid_unique` (`uuid`),
  KEY `payments_branch_id_foreign` (`branch_id`),
  KEY `payments_customer_id_foreign` (`customer_id`),
  KEY `payments_application_id_foreign` (`application_id`),
  KEY `payments_invoice_id_foreign` (`invoice_id`),
  KEY `payments_received_by_foreign` (`received_by`),
  KEY `payments_tenant_id_branch_id_payment_date_index` (`tenant_id`,`branch_id`,`payment_date`),
  KEY `payments_tenant_id_customer_id_index` (`tenant_id`,`customer_id`),
  KEY `payments_tenant_id_payment_mode_index` (`tenant_id`,`payment_mode`),
  CONSTRAINT `payments_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
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
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_slug_unique` (`slug`),
  KEY `permissions_module_index` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'View Customers','customers.view','customers',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(2,'Create Customers','customers.create','customers',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(3,'Edit Customers','customers.edit','customers',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(4,'Delete Customers','customers.delete','customers',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(5,'View Applications','applications.view','applications',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(6,'Create Applications','applications.create','applications',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(7,'Edit Applications','applications.edit','applications',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(8,'Delete Applications','applications.delete','applications',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(9,'Assign Applications','applications.assign','applications',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(10,'View Documents','documents.view','documents',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(11,'Upload Documents','documents.upload','documents',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(12,'Verify Documents','documents.verify','documents',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(13,'Delete Documents','documents.delete','documents',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(14,'View Payments','payments.view','payments',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(15,'Create Payments','payments.create','payments',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(16,'Edit Payments','payments.edit','payments',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(17,'Refund Payments','payments.refund','payments',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(18,'View Employees','employees.view','employees',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(19,'Create Employees','employees.create','employees',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(20,'Edit Employees','employees.edit','employees',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(21,'View Branches','branches.view','branches',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(22,'Create Branches','branches.create','branches',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(23,'Edit Branches','branches.edit','branches',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(24,'View Services','services.view','services',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(25,'Create Services','services.create','services',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(26,'Edit Services','services.edit','services',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(27,'Delete Services','services.delete','services',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(28,'View Reports','reports.view','reports',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(29,'Export Reports','reports.export','reports',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(30,'Manage Settings','settings.manage','settings',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(31,'Manage Subscription','subscription.manage','subscription',NULL,'2026-11-24 00:27:25','2026-11-24 00:27:25');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `role_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(2,8),(2,9),(2,10),(2,11),(2,12),(2,13),(2,14),(2,15),(2,16),(2,17),(2,18),(2,19),(2,20),(2,21),(2,22),(2,23),(2,24),(2,25),(2,26),(2,27),(2,28),(2,29),(2,30),(2,31),(3,1),(3,2),(3,3),(3,5),(3,6),(3,7),(3,8),(3,9),(3,10),(3,11),(3,12),(3,13),(3,14),(3,15),(3,16),(3,17),(3,18),(3,19),(3,20),(3,21),(3,23),(3,24),(3,25),(3,26),(3,28),(3,29),(3,30),(4,1),(4,2),(4,3),(4,5),(4,6),(4,7),(4,10),(4,11),(4,14),(4,15),(4,24);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Platform Super Admin','PLATFORM_SUPER_ADMIN','Platform super administrator with global system access',1,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(2,'Business Owner','BUSINESS_OWNER','Business tenant administrator with complete organization access',1,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(3,'Branch Admin','BRANCH_ADMIN','Branch administrator with full access to branch operations',1,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(4,'Employee / Operator','EMPLOYEE','Service counter operator restricted to assigned branch',1,'2026-11-24 00:27:25','2026-11-24 00:27:25'),(5,'Customer','CUSTOMER','End customer for application tracking',1,'2026-11-24 00:27:25','2026-11-24 00:27:25');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_categories`
--

DROP TABLE IF EXISTS `service_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL DEFAULT 'folder',
  `description` text DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_categories_tenant_id_slug_index` (`tenant_id`,`slug`),
  CONSTRAINT `service_categories_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_categories`
--

LOCK TABLES `service_categories` WRITE;
/*!40000 ALTER TABLE `service_categories` DISABLE KEYS */;
INSERT INTO `service_categories` VALUES (1,1,'Identity & Civil Documents','identity-civil','id-card',NULL,1,1,'2026-11-24 00:27:27','2026-11-24 00:27:27'),(2,1,'Revenue, Land & Domicile','revenue-land','building-bank',NULL,2,1,'2026-11-24 00:27:27','2026-11-24 00:27:27'),(3,1,'Police, Legal & Verification','police-legal','shield-check',NULL,3,1,'2026-11-24 00:27:27','2026-11-24 00:27:27'),(4,1,'Business, GST & Licences','business-gst','briefcase',NULL,4,1,'2026-11-24 00:27:27','2026-11-24 00:27:27'),(5,1,'Welfare, Ration & Gazette','welfare-ration','receipt',NULL,5,1,'2026-11-24 00:27:27','2026-11-24 00:27:27');
/*!40000 ALTER TABLE `service_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_custom_fields`
--

DROP TABLE IF EXISTS `service_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_custom_fields` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `service_id` bigint(20) unsigned NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_key` varchar(50) NOT NULL,
  `field_type` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `placeholder` varchar(255) DEFAULT NULL,
  `default_value` text DEFAULT NULL,
  `options_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options_json`)),
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `validation_rules` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_custom_fields_tenant_id_foreign` (`tenant_id`),
  KEY `service_custom_fields_service_id_is_active_index` (`service_id`,`is_active`),
  CONSTRAINT `service_custom_fields_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_custom_fields_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_custom_fields`
--

LOCK TABLES `service_custom_fields` WRITE;
/*!40000 ALTER TABLE `service_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `service_code` varchar(50) DEFAULT NULL,
  `main_service_name` varchar(255) NOT NULL,
  `sub_service_name` varchar(255) NOT NULL,
  `service_variant` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `govt_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `additional_charge_rules_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_charge_rules_json`)),
  `required_documents_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_documents_json`)),
  `service_portal_link` varchar(255) DEFAULT NULL,
  `work_portal_link` varchar(255) DEFAULT NULL,
  `video_instruction_link` varchar(255) DEFAULT NULL,
  `portal_username_encrypted` text DEFAULT NULL,
  `portal_password_encrypted` text DEFAULT NULL,
  `expected_processing_days` int(10) unsigned NOT NULL DEFAULT 7,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `services_category_id_foreign` (`category_id`),
  KEY `services_tenant_id_service_code_index` (`tenant_id`,`service_code`),
  KEY `services_tenant_id_is_active_index` (`tenant_id`,`is_active`),
  KEY `services_branch_id_foreign` (`branch_id`),
  KEY `services_tenant_id_branch_id_index` (`tenant_id`,`branch_id`),
  CONSTRAINT `services_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `services_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `services_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `business_type` varchar(255) NOT NULL DEFAULT 'MAHA_E_SEVA',
  `contact_name` varchar(255) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_mobile` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `state` varchar(255) NOT NULL DEFAULT 'Maharashtra',
  `pincode` varchar(10) DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'ACTIVE',
  `subscription_plan` varchar(255) NOT NULL DEFAULT 'PREMIUM',
  `subscription_expires_at` date DEFAULT NULL,
  `max_branches` int(10) unsigned NOT NULL DEFAULT 5,
  `max_users` int(10) unsigned NOT NULL DEFAULT 25,
  `max_storage_mb` int(10) unsigned NOT NULL DEFAULT 5120,
  `settings_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings_json`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenants_uuid_unique` (`uuid`),
  UNIQUE KEY `tenants_slug_unique` (`slug`),
  KEY `tenants_status_index` (`status`),
  KEY `tenants_contact_mobile_index` (`contact_mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
INSERT INTO `tenants` VALUES (1,'409b5f1a-e46a-4fb8-b21d-aa3a5cc63d79','Maha E-Seva Kendra & Digital Services','maha-e-seva','MAHA_E_SEVA','Vinod Kadam','admin@mahaeseva.com','9876543210','Shop No. 1, Main Market Road','Pune','Pune','Maharashtra','411001',NULL,'ACTIVE','ENTERPRISE','2031-11-24',10,50,20480,'{\"sms_sender_id\":\"MHSEVA\",\"currency\":\"INR\",\"thermal_receipt_header\":\"MAHA E-SEVA SERVICES\",\"whatsapp_default_template_lang\":\"mr\",\"whatsapp_broadcast_default_service\":\"\\u0936\\u093e\\u0938\\u0915\\u0940\\u092f \\u0938\\u0947\\u0935\\u093e \\u0935 \\u092f\\u094b\\u091c\\u0928\\u093e\",\"whatsapp_broadcast_default_status\":\"\\u0928\\u0935\\u0940\\u0928 \\u0928\\u094b\\u0902\\u0926\\u0923\\u0940 \\u0938\\u0941\\u0930\\u0942 \\u0906\\u0939\\u0947\",\"whatsapp_broadcast_default_app_no\":\"MAHA-ALERT\",\"advertising_banner_enabled\":false}','2026-11-24 00:27:26','2026-11-24 01:31:31',NULL);
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_permissions` (
  `user_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `user_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `user_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_permissions`
--

LOCK TABLES `user_permissions` WRITE;
/*!40000 ALTER TABLE `user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `user_roles_role_id_foreign` (`role_id`),
  CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,1),(2,2);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `branch_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'ACTIVE',
  `failed_login_attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `two_factor_secret` text DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_branch_id_foreign` (`branch_id`),
  KEY `users_tenant_id_branch_id_index` (`tenant_id`,`branch_id`),
  KEY `users_status_index` (`status`),
  KEY `users_mobile_index` (`mobile`),
  CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,NULL,NULL,'Platform Super Administrator','superadmin@mahaeseva.gov.in','9000000000',NULL,'$2y$12$UsxihDxmKlb4CElli0WHOuyx5CDUpHb17wn4l3G6xP7uVyPEuUWOG','ACTIVE',0,NULL,NULL,NULL,NULL,NULL,0,NULL,'2026-11-24 00:27:26','2026-11-24 00:27:26',NULL),(2,1,1,'Vinod Kadam','admin@mahaeseva.com','9876543210',NULL,'$2y$12$HA0On1kBUK0UnNltDpsB6eGBXnai0ZnzfvPrCS3y.5m13/sMp3K2a','ACTIVE',0,NULL,NULL,NULL,NULL,NULL,0,NULL,'2026-11-24 00:27:27','2026-11-24 01:31:31',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-24 12:31:47
