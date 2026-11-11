/*M!999999\- enable the sandbox mode */ 
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;
DROP TABLE IF EXISTS `api_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `key_prefix` varchar(12) NOT NULL,
  `key_hash` varchar(64) NOT NULL,
  `abilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`abilities`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_keys_key_hash_unique` (`key_hash`),
  KEY `api_keys_user_id_is_active_index` (`user_id`,`is_active`),
  CONSTRAINT `api_keys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `api_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_requests` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `request_id` varchar(200) NOT NULL,
  `device_token` varchar(255) DEFAULT NULL,
  `country_code` varchar(5) GENERATED ALWAYS AS (json_unquote(json_extract(`plumber_request`,'$.country'))) STORED,
  `lat` decimal(10,6) GENERATED ALWAYS AS (json_unquote(json_extract(`plumber_request`,'$.lat'))) STORED,
  `lon` decimal(10,6) GENERATED ALWAYS AS (json_unquote(json_extract(`plumber_request`,'$.lon'))) STORED,
  `full_names` varchar(255) GENERATED ALWAYS AS (coalesce(json_unquote(json_extract(`droid_request`,'$.userInfo.userName')),nullif(trim(concat_ws(' ',json_unquote(json_extract(`droid_request`,'$.user_info.first_name')),json_unquote(json_extract(`droid_request`,'$.user_info.last_name')))),''))) STORED,
  `phone_number` varchar(50) GENERATED ALWAYS AS (coalesce(json_unquote(json_extract(`droid_request`,'$.userInfo.mobileNumber')),nullif(json_unquote(json_extract(`droid_request`,'$.user_info.phone_number')),'null'))) STORED,
  `gender` varchar(50) GENERATED ALWAYS AS (coalesce(json_unquote(json_extract(`droid_request`,'$.userInfo.gender')),json_unquote(json_extract(`droid_request`,'$.user_info.gender')))) STORED,
  `fr` tinyint(1) GENERATED ALWAYS AS (convert(json_unquote(json_extract(`plumber_request`,'$.FR')) using utf8mb4) = 'true') STORED,
  `ic` tinyint(1) GENERATED ALWAYS AS (convert(json_unquote(json_extract(`plumber_request`,'$.IC')) using utf8mb4) = 'true') STORED,
  `pp` tinyint(1) GENERATED ALWAYS AS (convert(json_unquote(json_extract(`plumber_request`,'$.PP')) using utf8mb4) = 'true') STORED,
  `sph` tinyint(1) GENERATED ALWAYS AS (convert(json_unquote(json_extract(`plumber_request`,'$.SPH')) using utf8mb4) = 'true') STORED,
  `spp` tinyint(1) GENERATED ALWAYS AS (convert(json_unquote(json_extract(`plumber_request`,'$.SPP')) using utf8mb4) = 'true') STORED,
  `excluded` tinyint(1) NOT NULL DEFAULT 0,
  `use_case` varchar(10) GENERATED ALWAYS AS (case when `fr` = 1 then 'FR' when `pp` = 1 then 'PP' when `ic` = 1 then 'IC' when `spp` = 1 or `sph` = 1 then 'SPHS' else 'NA' end) STORED,
  `gender_code` varchar(2) GENERATED ALWAYS AS (case when `gender` in ('Male','Mwanaume','M') then 'M' when `gender` in ('Female','Mwanamke','F') then 'F' else 'NA' end) STORED,
  `droid_request` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`droid_request`)),
  `plumber_request` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`plumber_request`)),
  `request_started_at` timestamp NULL DEFAULT NULL,
  `request_duration_ms` int(10) unsigned DEFAULT NULL,
  `plumber_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '{}' CHECK (json_valid(`plumber_response`)),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `api_requests_device_token_index` (`device_token`),
  KEY `api_requests_country_code_index` (`country_code`),
  KEY `api_requests_lat_lon_index` (`lat`,`lon`),
  KEY `api_requests_full_names_index` (`full_names`),
  KEY `api_requests_phone_number_index` (`phone_number`),
  KEY `api_requests_gender_index` (`gender`),
  KEY `api_requests_fr_ic_pp_sph_spp_index` (`fr`,`ic`,`pp`,`sph`,`spp`),
  KEY `api_requests_created_at_idx` (`created_at`),
  KEY `api_requests_date_excluded_idx` (`created_at`,`excluded`),
  KEY `api_requests_use_case_idx` (`use_case`),
  KEY `api_requests_gender_code_idx` (`gender_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `app_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_report` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `device_token` varchar(255) DEFAULT NULL,
  `country_code` varchar(4) DEFAULT NULL,
  `lat` decimal(10,6) DEFAULT NULL,
  `lon` decimal(10,6) DEFAULT NULL,
  `full_names` varchar(150) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `excluded` bit(1) DEFAULT b'0',
  `user_type` varchar(20) DEFAULT NULL,
  `fr` bit(1) DEFAULT b'0',
  `ic` bit(1) DEFAULT b'0',
  `pp` bit(1) DEFAULT b'0',
  `spp` bit(1) DEFAULT b'0',
  `sph` bit(1) DEFAULT b'0',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `device_token_idx` (`device_token`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `authorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `authorities` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `authority` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `authority-username-uk` (`username`,`authority`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `available_fertilizer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `available_fertilizer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `n_content` int(11) NOT NULL,
  `p_content` int(11) NOT NULL,
  `k_content` int(11) NOT NULL,
  `weight` int(11) NOT NULL DEFAULT 50,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `country` varchar(5) NOT NULL DEFAULT 'ALL',
  `use_case` varchar(5) DEFAULT 'ALL',
  `available` bit(1) DEFAULT b'1',
  `custom` bit(1) DEFAULT b'1',
  `sort_order` int(11) DEFAULT 999,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE,
  UNIQUE KEY `type` (`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cassava_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cassava_prices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `country` varchar(4) NOT NULL,
  `min_local_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_local_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_usd` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_usd` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_price` bit(1) NOT NULL DEFAULT b'0',
  `max_price` bit(1) NOT NULL DEFAULT b'0',
  `price_active` bit(1) DEFAULT b'1',
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cassava_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cassava_units` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `unit_weight` double NOT NULL,
  `label` varchar(50) NOT NULL,
  `sort_order` double NOT NULL DEFAULT 0 COMMENT 'Sort order for display',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `countries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(2) NOT NULL COMMENT 'ISO 3166-1 alpha-2 country code',
  `name` varchar(100) NOT NULL COMMENT 'Country name',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(5) unsigned DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL COMMENT 'Country centroid latitude',
  `longitude` decimal(11,8) DEFAULT NULL COMMENT 'Country centroid longitude',
  `min_latitude` decimal(10,8) DEFAULT NULL,
  `max_latitude` decimal(10,8) DEFAULT NULL,
  `min_longitude` decimal(11,8) DEFAULT NULL,
  `max_longitude` decimal(11,8) DEFAULT NULL,
  `boundary` geometry NOT NULL DEFAULT st_geometryfromtext('GEOMETRYCOLLECTION EMPTY') COMMENT 'Country boundary polygon or multipolygon',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countries_code_unique` (`code`),
  KEY `idx_country_coordinates` (`latitude`,`longitude`),
  KEY `idx_country_bbox` (`min_latitude`,`max_latitude`,`min_longitude`,`max_longitude`),
  SPATIAL KEY `idx_country_boundary` (`boundary`),
  KEY `countries_name_index` (`name`),
  KEY `countries_active_index` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `coverage_areas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `coverage_areas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) NOT NULL,
  `geojson` longtext NOT NULL,
  `n_cells` int(10) unsigned DEFAULT NULL,
  `min_lon` double NOT NULL,
  `min_lat` double NOT NULL,
  `max_lon` double NOT NULL,
  `max_lat` double NOT NULL,
  `checksum` char(64) NOT NULL,
  `fetched_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coverage_areas_country_code_unique` (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `crops`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `crops` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `crop_key` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `advice_available` tinyint(1) NOT NULL DEFAULT 0,
  `countries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`countries`)),
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `crops_crop_key_unique` (`crop_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `currencies` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(4) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `currency_name` varchar(80) DEFAULT NULL,
  `currency_code` varchar(50) DEFAULT NULL,
  `currency_symbol` varchar(50) DEFAULT NULL,
  `currency_native_symbol` varchar(50) DEFAULT NULL,
  `name_plural` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `default_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `default_prices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country` varchar(2) NOT NULL,
  `item` varchar(50) NOT NULL,
  `price` double NOT NULL,
  `unit` varchar(15) NOT NULL DEFAULT 'per_bag',
  `currency` varchar(3) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `country_item_uk` (`country`,`item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
DROP TABLE IF EXISTS `fertilizer_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fertilizer_prices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `country` varchar(4) DEFAULT NULL,
  `fertilizer_key` varchar(50) DEFAULT NULL,
  `min_price` decimal(10,2) NOT NULL,
  `max_price` decimal(10,2) NOT NULL,
  `price_per_bag` decimal(10,2) NOT NULL,
  `price_active` bit(1) DEFAULT b'0',
  `sort_order` int(11) NOT NULL DEFAULT 999,
  `desc` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fertilizers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fertilizers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fertilizer_label` varchar(255) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `fertilizer_key` varchar(50) DEFAULT NULL,
  `weight` int(11) NOT NULL DEFAULT 50,
  `country` varchar(3) NOT NULL,
  `sort_order` int(11) DEFAULT 1,
  `use_case` varchar(10) NOT NULL DEFAULT 'ALL',
  `cis` tinyint(1) NOT NULL DEFAULT 1,
  `cim` tinyint(1) NOT NULL DEFAULT 1,
  `available` bit(1) DEFAULT b'1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `country-fertilizer` (`fertilizer_key`) USING BTREE,
  KEY `fertilizers_cim_index` (`cim`),
  KEY `fertilizers_cis_index` (`cis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `investment_amount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `investment_amount` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `country` varchar(4) DEFAULT NULL,
  `investment_amount` decimal(10,2) NOT NULL,
  `area_unit` varchar(10) DEFAULT 'acre',
  `price_active` bit(1) DEFAULT b'0',
  `sort_order` int(11) NOT NULL DEFAULT 999,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
DROP TABLE IF EXISTS `maize_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `maize_prices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `country` varchar(4) NOT NULL,
  `min_local_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_local_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_usd` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_usd` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_price` bit(1) NOT NULL DEFAULT b'0',
  `max_price` bit(1) NOT NULL DEFAULT b'0',
  `price_active` bit(1) DEFAULT b'1',
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  `produce_type` varchar(10) NOT NULL DEFAULT 'grain',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitored_scheduled_task_log_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitored_scheduled_task_log_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `monitored_scheduled_task_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_scheduled_task_id` (`monitored_scheduled_task_id`),
  CONSTRAINT `fk_scheduled_task_id` FOREIGN KEY (`monitored_scheduled_task_id`) REFERENCES `monitored_scheduled_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitored_scheduled_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitored_scheduled_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `cron_expression` varchar(255) NOT NULL,
  `timezone` varchar(255) DEFAULT NULL,
  `ping_url` varchar(255) DEFAULT NULL,
  `last_started_at` datetime DEFAULT NULL,
  `last_finished_at` datetime DEFAULT NULL,
  `last_failed_at` datetime DEFAULT NULL,
  `last_skipped_at` datetime DEFAULT NULL,
  `registered_on_oh_dear_at` datetime DEFAULT NULL,
  `last_pinged_at` datetime DEFAULT NULL,
  `grace_time_in_minutes` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `operation_costs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `operation_costs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `operation_name` varchar(100) NOT NULL,
  `operation_type` varchar(100) NOT NULL,
  `country_code` char(2) NOT NULL,
  `min_cost` decimal(20,3) NOT NULL,
  `max_cost` decimal(20,3) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx-op-type-op-country` (`operation_type`,`operation_name`,`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `potato_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `potato_prices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `country` varchar(4) NOT NULL,
  `min_local_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_local_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_usd` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_usd` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_price` bit(1) NOT NULL DEFAULT b'0',
  `max_price` bit(1) NOT NULL DEFAULT b'0',
  `price_active` bit(1) DEFAULT b'1',
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `produce_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `produce_prices` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `country` varchar(4) NOT NULL,
  `produce_name` varchar(20) NOT NULL,
  `min_price` decimal(10,3) NOT NULL DEFAULT 0.000,
  `max_price` decimal(10,3) NOT NULL DEFAULT 0.000,
  `is_min_price` tinyint(1) NOT NULL DEFAULT 0,
  `is_max_price` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `request_fertilizer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `request_fertilizer` (
  `fertilizer_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `request_id` bigint(20) DEFAULT NULL,
  `fertilizer_type` varchar(100) NOT NULL,
  `available` bit(1) DEFAULT b'0',
  `price` decimal(10,2) NOT NULL,
  `weight` decimal(10,2) NOT NULL,
  PRIMARY KEY (`fertilizer_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
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
DROP TABLE IF EXISTS `starch_factories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `starch_factories` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `factory_name` varchar(100) NOT NULL,
  `factory_label` varchar(120) NOT NULL,
  `country` varchar(4) NOT NULL,
  `factory_active` bit(1) DEFAULT b'0',
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `starch_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `starch_prices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `starch_factory_id` bigint(20) NOT NULL,
  `price_class` int(11) NOT NULL,
  `min_starch` double NOT NULL,
  `range_starch` text DEFAULT NULL,
  `price` double NOT NULL,
  `currency` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `starch_factory_id_price_class_unique` (`starch_factory_id`,`price_class`),
  CONSTRAINT `starch_prices_starch_factory_id_foreign` FOREIGN KEY (`starch_factory_id`) REFERENCES `starch_factories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `traffic_exclusions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `traffic_exclusions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `field` varchar(20) NOT NULL,
  `match_type` varchar(20) NOT NULL,
  `value` varchar(191) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL,
  `en` text NOT NULL COMMENT 'base language',
  `sw` text DEFAULT NULL,
  `rw` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `translations_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_feedback` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `akilimo_usage` mediumtext NOT NULL,
  `use_case` varchar(5) DEFAULT NULL,
  `user_type` varchar(25) NOT NULL DEFAULT 'OTHER',
  `akilimo_rec_rating` int(11) NOT NULL,
  `akilimo_useful_rating` int(11) NOT NULL,
  `language` varchar(5) DEFAULT NULL,
  `device_token` mediumtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('admin','partner','user') NOT NULL DEFAULT 'user',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `yield_request`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `yield_request` (
  `id` bigint(20) NOT NULL,
  `map_lat` decimal(12,8) NOT NULL,
  `map_long` decimal(12,8) NOT NULL,
  `cassava_unit_weight` decimal(10,2) DEFAULT NULL,
  `cassava_unit_price` decimal(10,2) DEFAULT NULL,
  `max_investment` decimal(10,2) DEFAULT NULL,
  `field_area` decimal(10,2) DEFAULT NULL,
  `planting_date` datetime NOT NULL,
  `harvest_date` datetime NOT NULL,
  `country` varchar(3) NOT NULL,
  `client` varchar(18) DEFAULT 'android',
  `area_units` varchar(18) DEFAULT NULL,
  `user_name` varchar(18) DEFAULT NULL,
  `user_phone_code` varchar(5) DEFAULT NULL,
  `user_phone_number` varchar(18) DEFAULT NULL,
  `cassava_pd` varchar(18) DEFAULT NULL,
  `field_description` varchar(255) DEFAULT NULL,
  `user_email` varchar(50) DEFAULT NULL,
  `processed` bit(1) DEFAULT b'0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `recommendation_text` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

/*M!999999\- enable the sandbox mode */ 
SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2025_04_01_140811_create_app_report_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2025_04_01_140811_create_authorities_table ',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2025_04_01_140811_create_app_report_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_04_01_140811_create_authorities_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_04_01_140811_create_available_fertilizer_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_04_01_140811_create_cassava_prices_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_04_01_140811_create_countries_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_04_01_140811_create_currencies_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_04_01_140811_create_fertilizer_price_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_04_01_140811_create_fertilizers_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_04_01_140811_create_investment_amount_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_04_01_140811_create_maize_prices_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_04_01_140811_create_operation_costs_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_04_01_140811_create_potato_prices_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_04_01_140811_create_request_fertilizer_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_04_01_140811_create_request_response_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_04_01_140811_create_starch_factory_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_04_01_140811_create_user_feedback_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_04_01_140811_create_yield_request_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_04_01_140812_create_v_app_request_stats_view_view',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_04_01_140813_create_exclusion_flag_evaluation_proc',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_04_01_140813_create_process_rec_request_proc',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_04_16_000002_create_jobs_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_04_17_081036_rename_request_response_to_api_requests',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_04_17_081230_rename_starch_factory_to_starch_factories',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_04_29_051435_rename_operation_costs_table_to_operation_costs_old',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_04_29_051449_create_operation_costs_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_04_29_052838_drop_operation_costs_old_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_05_21_053527_create_cache_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_05_21_113041_add_fertilizer_label_to_fertilizers_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_04_16_000000_create_users_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_05_22_062954_change_api_request_columns_to_json',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_10_27_075019_drop_fertilizer_prices_table',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_10_27_075129_rename_fertilizer_price_to_fertilizer_prices',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_10_29_122432_create_cassava_unit_of_sale_table',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_10_31_214215_create_produce_prices_table',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_01_08_080329_add_use_case_column_to_user_feedback_table',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_03_26_000001_add_request_tracking_to_api_requests_table',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_04_07_195619_add_cis_and_cim_boolean_columns_to_fertilizers_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_04_08_062915_create_translations_table',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_04_08_063545_create_starch_prices_table',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_04_08_063603_create_default_prices_table',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_04_08_093844_create_api_keys_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_04_08_093844_create_personal_access_tokens_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_04_08_094604_add_abilities_to_api_keys_table',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_04_09_081130_recreate_countries_table',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_04_09_081203_improve_api_requests_table',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_04_09_120751_switch_stats_view_to_api_requests',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_04_09_120812_drop_legacy_app_report_procedures',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_04_09_121704_drop_unused_triggers_for_app_report',50);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_04_09_122400_drop_stats_view_add_computed_columns',51);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_05_06_000001_add_role_to_users_table',52);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_05_06_120100_drop_stale_tables',53);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_05_07_064836_alter_api_keys_user_id_column',54);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_05_07_064837_add_user_id_fk_to_api_keys',55);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_05_07_085600_rename_playground_role_to_user_in_users_table',56);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_06_08_115318_create_schedule_monitor_tables',57);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_10_28_000001_create_coverage_areas_table',58);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_10_29_000001_create_crops_table',59);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_11_02_000001_create_traffic_exclusions_table',60);
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
