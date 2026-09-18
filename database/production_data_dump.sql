-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: localhost    Database: lab_utilization
-- ------------------------------------------------------
-- Server version	8.0.44

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
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('lab_utilization_system_cache_fe5dbbcea5ce7e2988b8c69bcfdfde8904aabc1f','i:4;',1789702714),('lab_utilization_system_cache_fe5dbbcea5ce7e2988b8c69bcfdfde8904aabc1f:timer','i:1789702714;',1789702714);
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
  `expiration` int NOT NULL,
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
-- Table structure for table `faculties`
--

DROP TABLE IF EXISTS `faculties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faculties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faculties_department_index` (`department`),
  KEY `faculties_name_index` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faculties`
--

LOCK TABLES `faculties` WRITE;
/*!40000 ALTER TABLE `faculties` DISABLE KEYS */;
INSERT INTO `faculties` VALUES (1,'Alcantara, Randolph','M','College of Science','rgalcantara@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(2,'Bastes, Angelo','M','College of Science','masbastes@gmail.com',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(3,'Antipolo, Enrick','M','Department of Civil and Railway Engineering Technology','eantipolo@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(4,'Chico Iii, Eligio R.','M','Department of Civil and Railway Engineering Technology','erchicoiii@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(5,'Gonzales, Ezekiel A.','M','Department of Civil and Railway Engineering Technology','eagonzales@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(6,'Lumbang, Aldrin G.','M','Department of Civil and Railway Engineering Technology','aglumbang@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(7,'Mateo, Leo Chris','M','Department of Civil and Railway Engineering Technology','lcmateo@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(8,'Mutuc, Jermaine Benjch N.','F','Department of Civil and Railway Engineering Technology','jbnmutuc@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(9,'Sibayan, Ayllinea France G.','F','Department of Civil and Railway Engineering Technology','afgsibayan@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(10,'Tarawi, Allan B.','M','Department of Civil and Railway Engineering Technology','abtarawi@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(11,'Urbano, Vince David F.','M','Department of Civil and Railway Engineering Technology','vdfurbano@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(12,'Cruz, Ramir Malit','M','Department of Civil and Railway Engineering Technology','rm.cruz@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(13,'Fernando, Ronald D','M','Department of Computer and Electronics Engineering Technology','rdfernando@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(14,'Lequigan, Joseph','M','Department of Computer and Electronics Engineering Technology','jblequigan@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(15,'Manarang, Jonathan C.','M','Department of Computer and Electronics Engineering Technology','jcmanarang@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(16,'Alday, Aaron Charles Regis','M','Department of Computer and Electronics Engineering Technology','acralday@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(17,'Andaya, Isaiah Nikkolai M.','M','Department of Computer and Electronics Engineering Technology','inmandaya@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(18,'Cunanan, Carlo O.','M','Department of Computer and Electronics Engineering Technology','cocunanan@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(19,'De Guzman, Jerome','M','Department of Computer and Electronics Engineering Technology','jtdeguzman@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(20,'Hulipas, Patrick Jiorgen U.','M','Department of Computer and Electronics Engineering Technology','pjuhulipas@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(21,'Macalos, Roste Mae','F','Department of Computer and Electronics Engineering Technology','rostemaemacalos@gmail.com',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(22,'Martinez, Tanya','F','Department of Computer and Electronics Engineering Technology','tsmartinez@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(23,'Montano, Juan','M','Department of Computer and Electronics Engineering Technology','juanmontano@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(24,'Nudalo, Charmaine Chrescel','F','Department of Computer and Electronics Engineering Technology','ccd.nudalo@iskolarngbayan.pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(25,'Tiburcio, Jess Rhyan A.','M','Department of Computer and Electronics Engineering Technology','jratiburcio@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(26,'Moscare, Jayson','M','Department of Computer and Electronics Engineering Technology','jaymoscare19@gmail.com',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(27,'Legaspi, John Michael V.','M','Department of Computer and Electronics Engineering Technology','jmvlegaspi@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(28,'Cabrera, Roel D.','M','Department of Computer and Electronics Engineering Technology','rdcabrera@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(29,'Dazon, Kenneth','M','Department of Computer and Electronics Engineering Technology','kpdazon@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(30,'Dipay, Jose Marie B.','M','Department of Computer and Electronics Engineering Technology','jmbdipay@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(31,'Libed, Jake M.','M','Department of Computer and Electronics Engineering Technology','jmlibed@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(32,'Rios, Remegio C.','M','Department of Computer and Electronics Engineering Technology','rcrios@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(33,'Ruiz, Frescian C.','F','Department of Computer and Electronics Engineering Technology','fcruiz@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(34,'Ruiz, Jomar B.','M','Department of Computer and Electronics Engineering Technology','jbruiz@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(35,'Del Rosario, Ruben','M','Department of Computer and Electronics Engineering Technology','rubencayetanodelrosario@gmail.com',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(36,'Yague, Mark Andrew','M','Department of Computer and Electronics Engineering Technology','markandrewyague@gmail.com',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(37,'Amarille, John Arnie','M','Department of Electrical and Mechanical Engineering Technology','janamarille@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(38,'Amul, Mark Anthony Q.','M','Department of Electrical and Mechanical Engineering Technology','maqamul@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(39,'Cabrillas, Victor Alfredo C.','M','Department of Electrical and Mechanical Engineering Technology','vaccabrillas@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(40,'Dacles, Jomar J.','M','Department of Electrical and Mechanical Engineering Technology','jjdacles@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(41,'Glori, Jonathan','M','Department of Electrical and Mechanical Engineering Technology','jdglori@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(42,'Limkian, Jason D.','M','Department of Electrical and Mechanical Engineering Technology','jdlimkian@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(43,'Mananghaya, Andrei','M','Department of Electrical and Mechanical Engineering Technology','acmananghaya@gmail.com',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(44,'Marcaida, Jay Ar D.','M','Department of Electrical and Mechanical Engineering Technology','jadmarcaida@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(45,'Santos, Pablo','M','Department of Electrical and Mechanical Engineering Technology','prtsantos@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(46,'Alfonso, Raymond L.','M','Department of Electrical and Mechanical Engineering Technology','rlalfonso@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(47,'David, Aina M.','F','Department of Electrical and Mechanical Engineering Technology','amdavid@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(48,'Evangelista, Arturo P.','M','Department of Electrical and Mechanical Engineering Technology','apevangelista@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(49,'Lacdang, Clint Michael','M','Department of Electrical and Mechanical Engineering Technology','cmflacdang@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(50,'Legaspi, Jefferson N.','M','Department of Electrical and Mechanical Engineering Technology','jnlegaspi@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(51,'Tindogan, Paulo E.','M','Department of Electrical and Mechanical Engineering Technology','petindogan@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(52,'Forio, Arjay','M','Department of Electrical and Mechanical Engineering Technology','arforio@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(53,'Francisco, Vic Joseph','M','Department of Electrical and Mechanical Engineering Technology','franciscovicjoseph@gmail.com',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(54,'Solosa, Ma. Joepe V.','F','Department of Office Management and Information Technology','mjasolosa@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(55,'Esparas, Natividad Taduan','F','Department of Office Management and Information Technology','ntesparas@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(56,'Lacson, Marigel Nicholle','F','Department of Office Management and Information Technology','mnmlacson@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(57,'Padilla, Jannet S.','F','Department of Office Management and Information Technology','jspadilla@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(58,'Payra, Marisol','F','Department of Office Management and Information Technology','mdpayra@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(59,'Reyes, Nino','M','Department of Office Management and Information Technology','ncreyes@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(60,'Simacon, Rein Ryan','M','Department of Office Management and Information Technology','simaconrein@gmail.com',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(61,'Abogado, Camilo P.','M','Department of Office Management and Information Technology','cpabogado@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(62,'Huceña, Maria Aida B.','F','Department of Office Management and Information Technology','mabhucena@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(63,'Lim, Gina S.','F','Department of Office Management and Information Technology','gslim@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(64,'Lipardo Jr., Fernando V.','M','Department of Office Management and Information Technology','fvlipardojr@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(65,'Macapagal, Diana M.','F','Department of Office Management and Information Technology','dmmacapagal@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(66,'Salamatin, Ofelia','F','Department of Office Management and Information Technology','odsalamatin@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(67,'Salazar, Raquel G.','F','Department of Office Management and Information Technology','rgsalazar@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(68,'Villegas, May Rose M.','F','Department of Office Management and Information Technology','mrmvillegas@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(69,'Atencio, Mylo D.p.','M','Department of Office Management and Information Technology','mdatencio@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(70,'Austria, Adrian','M','Department of Office Management and Information Technology','ajdaustria@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(71,'Bien, Maria Azalea J.','F','Department of Office Management and Information Technology','majbien@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(72,'Cruz, Regie','M','Department of Office Management and Information Technology','rscruz@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(73,'Dela Isla, Jonard John M.','M','Department of Office Management and Information Technology','jjmdelaisla@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(74,'Dela Isla, Josephine',NULL,'Department of Office Management and Information Technology','jmdelaisla@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(75,'Apsay, Jonnalyn B.','F','Department of Office Management and Information Technology','jbapsay@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(76,'Bautista, Marc Anthony S','M','Department of Office Management and Information Technology','msbautista@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(77,'Blasquino, Edrian G.','M','Department of Office Management and Information Technology','egblasquino@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(78,'Bonaobra, Zenaida S.','F','Department of Office Management and Information Technology','zsbonaobra@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(79,'Boniol, Jean O.','F','Department of Office Management and Information Technology','joboniol@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(80,'Crasco, Melania Melanie M','F','Department of Office Management and Information Technology','mmmcrasco@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(81,'Belista, Paula Grace Ann','F','Department of Office Management and Information Technology','pgabbelista@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(82,'Dela Cruz, Rudolf','M','Department of Office Management and Information Technology','rldelacruz@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06'),(83,'Gacute, Natan','M','Department of Electrical and Mechanical Engineering Technology','nfgacute@pup.edu.ph',1,'2026-09-16 17:44:06','2026-09-16 17:44:06');
/*!40000 ALTER TABLE `faculties` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2024_01_01_000000_create_users_table',1),(2,'2024_01_01_000001_create_rooms_table',1),(3,'2024_01_01_000002_create_tools_table',1),(4,'2024_01_01_000003_add_role_and_telegram_to_users_table',1),(5,'2024_01_01_000004_create_transactions_table',1),(6,'2024_01_01_000005_create_notification_logs_table',1),(7,'2026_09_16_154242_create_cache_table',2),(8,'2026_09_16_174000_create_faculties_table',3),(9,'2026_09_16_183000_create_subjects_table',4),(10,'2026_09_16_210000_create_transaction_items_table',5),(11,'2026_09_17_150000_add_department_to_tools_table',6),(12,'2026_09_17_163000_add_department_to_transactions_table',7),(13,'2026_09_17_170000_add_department_to_rooms_table',8),(14,'2026_09_17_173000_add_laboratory_offices_to_rooms_table',9),(15,'2026_09_18_103000_add_software_utilized_to_transactions_table',10);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_logs`
--

DROP TABLE IF EXISTS `notification_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned NOT NULL,
  `channel` enum('telegram','email') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sent_at` timestamp NOT NULL,
  `success` tinyint(1) DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_logs_transaction_id_channel_index` (`transaction_id`,`channel`),
  CONSTRAINT `notification_logs_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_logs`
--

LOCK TABLES `notification_logs` WRITE;
/*!40000 ALTER TABLE `notification_logs` DISABLE KEYS */;
INSERT INTO `notification_logs` VALUES (1,22,'telegram','-1003201567471','2026-09-17 16:59:30',1,'{\"text\": \"🏫 *LAB ROOM CHECKOUT*\\n─────────────────────────\\n👤 *Borrower:* Alfonso, Raymond L.\\n🏢 *Department:* DEMET\\n🚪 *Room:* LAB 208\\n📚 *Purpose / Subject:* Project Development\\n🕐 *Time Out:* Sep 17, 2026 12:26 PM\\n⏰ *Expected Return:* 3:26 PM\\n\"}',NULL,'2026-09-17 16:59:30','2026-09-17 16:59:30'),(2,31,'telegram','-1003201567471','2026-09-17 17:02:17',1,'{\"text\": \"🔧 *EQUIPMENT BORROWED*\\n─────────────────────────\\n👤 *Borrower:* Ruiz, Jomar B.\\n🏢 *Department:* DCEET\\n📦 *Equipment Items:*\\n   • ARDUINO UNO STARTING KIT (×50)\\n🕐 *Time Out:* Sep 17, 2026 5:01 PM\\n⏰ *Expected Return:* 8:01 PM\\n\"}',NULL,'2026-09-17 17:02:17','2026-09-17 17:02:17'),(3,31,'telegram','-1003201567471','2026-09-17 17:25:38',1,'{\"text\": \"✅ *ITEM RETURNED*\\n─────────────────────────\\n👤 *Borrower:* Ruiz, Jomar B.\\n🏢 *Department:* DCEET\\n📦 *Item:* Tools: ARDUINO UNO STARTING KIT (×50)\\n🕐 *Returned At:* Sep 17, 2026 5:25 PM\\n\"}',NULL,'2026-09-17 17:25:38','2026-09-17 17:25:38'),(4,22,'telegram','-1003201567471','2026-09-17 17:45:33',1,'{\"text\": \"🏫 *ROOM VACATED*\\n─────────────────────────\\n👤 *Borrower:* Alfonso, Raymond L.\\n🏢 *Department:* DEMET\\n🚪 *Room:* LAB 208\\n🕐 *Returned At:* Sep 17, 2026 5:45 PM\\n⚠️ *Note:* Returned 2 hours after expected return time.\\n\"}',NULL,'2026-09-17 17:45:33','2026-09-17 17:45:33'),(5,32,'telegram','-1003201567471','2026-09-17 17:57:12',1,'{\"text\": \"🏫 *LAB ROOM CHECKOUT*\\n─────────────────────────\\n👤 *Borrower:* Alfonso, Raymond L.\\n🏢 *Department:* DEMET\\n🚪 *Room:* LEC 213\\n📚 *Purpose / Subject:* Project Development 2\\n🕐 *Time Out:* Sep 17, 2026 5:56 PM\\n⏰ *Expected Return:* 8:56 PM\\n\"}',NULL,'2026-09-17 17:57:12','2026-09-17 17:57:12'),(6,33,'telegram','-1003201567471','2026-09-17 17:59:32',1,'{\"text\": \"🏫 *LAB ROOM CHECKOUT*\\n─────────────────────────\\n👤 *Borrower:* Prof. Rochelle Cajayon\\n🏢 *Department:* DOMIT\\n🚪 *Room:* LEC 303\\n📚 *Purpose / Subject:* CWTS\\n🕐 *Time Out:* Sep 17, 2026 5:57 PM\\n⏰ *Expected Return:* 8:57 PM\\n📝 *Notes:* Borrower (Student) - Joe Emmanuel Panagdato\\n\"}',NULL,'2026-09-17 17:59:32','2026-09-17 17:59:32'),(7,33,'telegram','-1003201567471','2026-09-17 18:35:40',1,'{\"text\": \"🏫 *ROOM VACATED*\\n─────────────────────────\\n👤 *Borrower:* Prof. Rochelle Cajayon\\n🏢 *Department:* DOMIT\\n🚪 *Room:* LEC 303\\n🕐 *Returned At:* Sep 17, 2026 6:35 PM\\n\"}',NULL,'2026-09-17 18:35:40','2026-09-17 18:35:40'),(8,32,'telegram','-1003201567471','2026-09-17 19:12:56',1,'{\"text\": \"🏫 *ROOM VACATED*\\n─────────────────────────\\n👤 *Borrower:* Alfonso, Raymond L.\\n🏢 *Department:* DEMET\\n🚪 *Room:* LEC 213\\n🕐 *Returned At:* Sep 17, 2026 7:12 PM\\n\"}',NULL,'2026-09-17 19:12:56','2026-09-17 19:12:56'),(9,34,'telegram','-1003201567471','2026-09-17 19:30:08',1,'{\"text\": \"🔧 *EQUIPMENT BORROWED*\\n─────────────────────────\\n👤 *Borrower:* MICHAEL LOREMIA\\n🏢 *Department:* DEMET\\n📦 *Equipment Items:*\\n   • DIGITAL MULTITESTER (×1)\\n📚 *Purpose / Subject:* ELECTRICAL CIRCUITS 1\\n🕐 *Time Out:* Sep 17, 2026 6:00 PM\\n⏰ *Expected Return:* 8:00 PM\\n\"}',NULL,'2026-09-17 19:30:08','2026-09-17 19:30:08'),(10,34,'telegram','-1003201567471','2026-09-17 19:53:39',1,'{\"text\": \"✅ *ITEM RETURNED*\\n─────────────────────────\\n👤 *Borrower:* MICHAEL LOREMIA\\n🏢 *Department:* DEMET\\n📦 *Item:* Tools: DIGITAL MULTITESTER (×1)\\n🕐 *Returned At:* Sep 17, 2026 7:53 PM\\n\"}',NULL,'2026-09-17 19:53:39','2026-09-17 19:53:39'),(11,28,'telegram','-1003201567471','2026-09-17 20:31:14',1,'{\"text\": \"🏫 *ROOM VACATED*\\n─────────────────────────\\n👤 *Borrower:* Tindogan, Paulo E.\\n🏢 *Department:* DEMET\\n🚪 *Room:* LEC 210\\n🕐 *Returned At:* Sep 17, 2026 8:31 PM\\n⚠️ *Note:* Returned 2 hours after expected return time.\\n\"}',NULL,'2026-09-17 20:31:14','2026-09-17 20:31:14'),(12,35,'telegram','-1003201567471','2026-09-18 07:35:20',0,'{\"text\": \"🏫 *LAB ROOM CHECKOUT*\\n─────────────────────────\\n👤 *Borrower:* Tindogan, Paulo E.\\n🏢 *Department:* DEMET\\n🚪 *Room:* LAB 208\\n📚 *Purpose / Subject:* EETE 204 - Photovoltaic (PV) Installation Design and Maintenance\\n🕐 *Time Out:* Sep 18, 2026 7:31 AM\\n⏰ *Expected Return:* 10:31 AM\\n📝 *Notes:* Borrower presented ID\\n\"}','cURL error 6: Could not resolve host: api.telegram.org (see https://curl.se/libcurl/c/libcurl-errors.html) for https://api.telegram.org/bot8375004820:AAFEqfIxsR5gqbthXSrq6_-76ZpB57nCF_M/sendMessage','2026-09-18 07:35:20','2026-09-18 07:35:20'),(13,36,'telegram','-1003201567471','2026-09-18 07:38:04',1,'{\"text\": \"🏫 *LAB ROOM CHECKOUT*\\n─────────────────────────\\n👤 *Borrower:* CARLOS, AUGUSTINE JOHN A.\\n🏢 *Department:* DEMET\\n🚪 *Room:* LEC 209\\n📚 *Purpose / Subject:* PHYS 013 - Physics for Engineers (Calculus-based)\\n🕐 *Time Out:* Sep 18, 2026 7:35 AM\\n⏰ *Expected Return:* 10:35 AM\\n📝 *Notes:* Borrower presented ID\\n\"}',NULL,'2026-09-18 07:38:04','2026-09-18 07:38:04'),(14,37,'telegram','-1003201567471','2026-09-18 08:08:12',0,'{\"text\": \"🏫 *LAB ROOM CHECKOUT*\\n─────────────────────────\\n👤 *Borrower:* Marcaida, Jay Ar D.\\n🏢 *Department:* DEMET\\n🚪 *Room:* LEC 213\\n📚 *Purpose / Subject:* ENSC 013 - Engineering Drawing\\n🕐 *Time Out:* Sep 18, 2026 8:07 AM\\n⏰ *Expected Return:* 1:07 PM\\n📝 *Notes:* Borrower presented ID\\n\"}','cURL error 6: Could not resolve host: api.telegram.org (see https://curl.se/libcurl/c/libcurl-errors.html) for https://api.telegram.org/bot8375004820:AAFEqfIxsR5gqbthXSrq6_-76ZpB57nCF_M/sendMessage','2026-09-18 08:08:12','2026-09-18 08:08:12'),(15,38,'telegram','-1003201567471','2026-09-18 08:09:50',1,'{\"text\": \"🔧 *EQUIPMENT BORROWED*\\n─────────────────────────\\n👤 *Borrower:* MORANO, ERICA ANN G.\\n🏢 *Department:* DEMET\\n📦 *Equipment Items:*\\n   • COMBINATION PLIERS (×1)\\n🕐 *Time Out:* Sep 18, 2026 8:09 AM\\n⏰ *Expected Return:* 11:09 AM\\n\"}',NULL,'2026-09-18 08:09:50','2026-09-18 08:09:50'),(16,36,'telegram','-1003201567471','2026-09-18 09:31:18',0,'{\"text\": \"🏫 *ROOM VACATED*\\n─────────────────────────\\n👤 *Borrower:* CARLOS, AUGUSTINE JOHN A.\\n🏢 *Department:* DEMET\\n🚪 *Room:* LEC 209\\n🕐 *Returned At:* Sep 18, 2026 9:31 AM\\n\"}','cURL error 6: Could not resolve host: api.telegram.org (see https://curl.se/libcurl/c/libcurl-errors.html) for https://api.telegram.org/bot8375004820:AAFEqfIxsR5gqbthXSrq6_-76ZpB57nCF_M/sendMessage','2026-09-18 09:31:18','2026-09-18 09:31:18'),(17,35,'telegram','-1003201567471','2026-09-18 10:02:29',0,'{\"text\": \"🏫 *LAB ROOM CHECKOUT*\\n─────────────────────────\\n👤 *Borrower:* Tindogan, Paulo E.\\n🏢 *Department:* DEMET\\n🚪 *Room:* LAB 208\\n📚 *Purpose / Subject:* EETE 204 - Photovoltaic (PV) Installation Design and Maintenance\\n🕐 *Time Out:* Sep 18, 2026 7:31 AM\\n⏰ *Expected Return:* 10:31 AM\\n📝 *Notes:* Borrower presented ID\\n\"}','cURL error 6: Could not resolve host: api.telegram.org (see https://curl.se/libcurl/c/libcurl-errors.html) for https://api.telegram.org/bot8375004820:AAFEqfIxsR5gqbthXSrq6_-76ZpB57nCF_M/sendMessage','2026-09-18 10:02:29','2026-09-18 10:02:29'),(18,37,'telegram','-1003201567471','2026-09-18 10:02:29',0,'{\"text\": \"🏫 *LAB ROOM CHECKOUT*\\n─────────────────────────\\n👤 *Borrower:* Marcaida, Jay Ar D.\\n🏢 *Department:* DEMET\\n🚪 *Room:* LEC 213\\n📚 *Purpose / Subject:* ENSC 013 - Engineering Drawing\\n🕐 *Time Out:* Sep 18, 2026 8:07 AM\\n⏰ *Expected Return:* 1:07 PM\\n📝 *Notes:* Borrower presented ID\\n\"}','cURL error 6: Could not resolve host: api.telegram.org (see https://curl.se/libcurl/c/libcurl-errors.html) for https://api.telegram.org/bot8375004820:AAFEqfIxsR5gqbthXSrq6_-76ZpB57nCF_M/sendMessage','2026-09-18 10:02:29','2026-09-18 10:02:29'),(19,39,'telegram','-1003201567471','2026-09-18 10:08:56',1,'{\"text\": \"🏫 *LAB ROOM CHECKOUT*\\n─────────────────────────\\n👤 *Borrower:* Ruiz, Jomar B.\\n🏢 *Department:* DCEET\\n🚪 *Room:* LAB 105\\n📚 *Purpose / Subject:* CMPEPC1 - CPE Professional Course 1\\n🕐 *Time Out:* Sep 18, 2026 10:08 AM\\n⏰ *Expected Return:* 1:08 PM\\n\"}',NULL,'2026-09-18 10:08:56','2026-09-18 10:08:56'),(22,38,'telegram','-1003201567471','2026-09-18 11:26:25',1,'{\"text\": \"✅ *ITEM RETURNED*\\n─────────────────────────\\n👤 *Borrower:* MORANO, ERICA ANN G.\\n🏢 *Department:* DEMET\\n📦 *Item:* Tools: COMBINATION PLIERS (×1)\\n🕐 *Returned At:* Sep 18, 2026 11:26 AM\\n⚠️ *Note:* Returned 17 minutes after expected return time.\\n\"}',NULL,'2026-09-18 11:26:25','2026-09-18 11:26:25'),(23,41,'telegram','-1003201567471','2026-09-18 11:28:14',1,'{\"text\": \"🏫 *LAB ROOM CHECKOUT*\\n─────────────────────────\\n👤 *Borrower:* Ruiz, Frescian C.\\n🏢 *Department:* DECET\\n🚪 *Room:* LAB 204\\n💻 *Software Utilized:* Adobe Creative Cloud, Adobe Animate\\n📚 *Purpose / Subject:* CPET 201 - 2D Animation\\n🕐 *Time Out:* Sep 18, 2026 11:27 AM\\n⏰ *Expected Return:* 2:27 PM\\n\"}',NULL,'2026-09-18 11:28:14','2026-09-18 11:28:14'),(24,39,'telegram','-1003201567471','2026-09-18 11:37:38',1,'{\"text\": \"🏫 *ROOM VACATED*\\n─────────────────────────\\n👤 *Borrower:* Ruiz, Jomar B.\\n🏢 *Department:* DECET\\n🚪 *Room:* LAB 105\\n🕐 *Returned At:* Sep 18, 2026 11:37 AM\\n\"}',NULL,'2026-09-18 11:37:38','2026-09-18 11:37:38');
/*!40000 ALTER TABLE `notification_logs` ENABLE KEYS */;
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
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` smallint unsigned DEFAULT NULL,
  `has_wifi` tinyint(1) NOT NULL DEFAULT '0',
  `wifi_notes` text COLLATE utf8mb4_unicode_ci,
  `manual_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rooms_department_index` (`department`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,'LAB 104','Department of Office Management and Information Technology','1st Floor',NULL,1,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(2,'LAB 105','Department of Office Management and Information Technology','1st Floor',NULL,1,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(3,'LAB 109B','Department of Electrical and Mechanical Engineering Technology','1st Floor',NULL,1,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(4,'LAB 109C','Department of Electrical and Mechanical Engineering Technology','1st Floor',NULL,1,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(5,'LAB 203','Department of Office Management and Information Technology','2nd Floor',NULL,1,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(6,'LAB 204','Department of Computer and Electronics Engineering Technology','2nd Floor',NULL,1,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(7,'LAB 205','Department of Computer and Electronics Engineering Technology','2nd Floor',NULL,1,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(8,'LAB 208','Department of Electrical and Mechanical Engineering Technology','2nd Floor',NULL,1,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(9,'LEC 200','Department of Computer and Electronics Engineering Technology','2nd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(10,'LEC 201','Department of Computer and Electronics Engineering Technology','2nd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(11,'LEC 209','Department of Electrical and Mechanical Engineering Technology','2nd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(12,'LEC 210','Department of Electrical and Mechanical Engineering Technology','2nd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(13,'LEC 211','Department of Electrical and Mechanical Engineering Technology','2nd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(14,'LEC 212',NULL,'2nd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(15,'LEC 213','Department of Electrical and Mechanical Engineering Technology','2nd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(16,'LEC 301',NULL,'3rd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(17,'LEC 303','Department of Office Management and Information Technology','3rd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(18,'LEC 304','Department of Office Management and Information Technology','3rd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(19,'LEC 305','Department of Office Management and Information Technology','3rd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(20,'LEC 306',NULL,'3rd Floor',NULL,0,NULL,NULL,NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(21,'LAB 109','Department of Electrical and Mechanical Engineering Technology','1st Floor',NULL,1,NULL,NULL,NULL,'2026-09-17 16:47:03','2026-09-17 16:47:03'),(22,'Laboratory Office 109A','DEMET & DOMIT','1st Floor',NULL,1,NULL,NULL,NULL,'2026-09-17 17:16:17','2026-09-17 17:19:09'),(23,'Laboratory Office 203','Department of Computer and Electronics Engineering Technology','2nd Floor',NULL,1,NULL,NULL,NULL,'2026-09-17 17:16:17','2026-09-17 17:19:09');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
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
INSERT INTO `sessions` VALUES ('2KRJs1CskeZfqdtEmp8UAxLXdzbg6lm3DYtwI2uM',1,'127.0.0.1','Symfony','YTo0OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoiX3Rva2VuIjtzOjQwOiJ2TnJOMlpITWtJaE9Tc3JRYWpjdFc4eGxtV0RLTzFHeVRxekxMaGdHIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MzoiaHR0cDovL2xvY2FsaG9zdC9hZG1pbi90cmFuc2FjdGlvbnMvNDEvZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789702332),('CeIMdGfqwKfmUu3779LRn1sMuTOZ0vY9i12X1lNp',1,'127.0.0.1','Symfony','YTo0OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoiX3Rva2VuIjtzOjQwOiIzYVdUYWk3czNaQmdER29iSmNGT3hiOUl5bWF2M09mUUlDQnVDZ09tIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozMDoiaHR0cDovL2xvY2FsaG9zdC9hZG1pbi9yZXBvcnRzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1789701968),('F9Skm9zNmcQkVdTBrCD08A0m0yD6TRYyT4QZ5vce',1,'127.0.0.1','Symfony','YTo0OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoiX3Rva2VuIjtzOjQwOiJaWlFQNmpSRkt0dmx0aGVYNVhFU1JFR1ZHM2lkcnplalF5d1lnTk83IjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozNToiaHR0cDovL2xvY2FsaG9zdC9hZG1pbi90cmFuc2FjdGlvbnMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1789702396),('j7FxOTcmn01kT3KfxsioBr35202epVRqw2xH4Vf8',1,'127.0.0.1','Symfony','YTo0OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoiX3Rva2VuIjtzOjQwOiJ0OXBnMU9yMlBmR2xxaEtENjFNeXFQUGIyc3dmekNGUzNYbWNPT2FFIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MzoiaHR0cDovL2xvY2FsaG9zdC9hZG1pbi90cmFuc2FjdGlvbnMvNDEvZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789702372),('jalQYbf4ZXhVuXdWn9tTrzH6l6wjVtk0hs9fUyf6',1,'127.0.0.1','Symfony','YTo0OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoiX3Rva2VuIjtzOjQwOiJIZ2ZpM1ZZN2JzUndMNmZNdGFTak12TDFRdnhnQmhJSnBvUjREbW81IjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MzoiaHR0cDovL2xvY2FsaG9zdC9hZG1pbi90cmFuc2FjdGlvbnMvMzgvZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789702378),('kRvMLgFjr6rNzBGGIMyK9H5jNAneEFYsIo7Kou3O',1,'172.24.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRTNVMXo5eEM1MUtkYUMyQ3BqQkxEZEllSkpsaU5NNFVUUUtzMzhqcSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTE6Imh0dHA6Ly8xOTIuMTY4LjAuMTY3OjgwODgvYWRtaW4vdHJhbnNhY3Rpb25zL2NyZWF0ZSI7fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=',1789702167),('oxp3m9o2Tcxwy8flQ3bTrrUYoZhXdI7qZcttA4KE',1,'127.0.0.1','Symfony','YTo0OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoiX3Rva2VuIjtzOjQwOiJvUTd4RXZUYVBRT0ZLZmJxU3MzOGpFeDBpOEpVWTA5SW5nOGFvS0RrIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0MzoiaHR0cDovL2xvY2FsaG9zdC9hZG1pbi90cmFuc2FjdGlvbnMvNDEvZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789702337),('RpkmrFfh9zXgjLQjTbLs6YJhkCcyK3A3IKmIwDSZ',8,'172.24.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRnNMWWFmS3NRQ1h3aElaSWhKTnNVejF1YVowbnFEQ3h6cXhDODZmdiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODA4OC9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6ODt9',1789703545),('rwRrp2FSs5HmqvBksokzf2ntHSt6V1uYhryvLSOa',1,'127.0.0.1','Symfony','YTozOntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoiX3Rva2VuIjtzOjQwOiJudW5BMmtvZ0hPVWlIQ2lhZ0U4Q1dvaEFkMTl0bjB3aHZJaFZKcDBoIjtzOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1789702383),('vrI2KN1aWibrsml9OceJOO6SS4EpMhsTJ6ruip97',1,'127.0.0.1','Symfony','YTo0OntzOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6NjoiX3Rva2VuIjtzOjQwOiJ5am1HaDFvaThkeVBBQlNlaVVWUDJnT01EVmdpaXM0cENrRnNaS0M4IjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo3OToiaHR0cDovL2xvY2FsaG9zdC9hZG1pbi9yZXBvcnRzP2JvcnJvd2VyPUp1YW4mZGVwYXJ0bWVudD1ERUNFVCZwcmVzZXQ9dGhpc19tb250aCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1789701973);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year_level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subjects_department_index` (`department`),
  KEY `subjects_code_index` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,'COMP 001','Introduction to Computing','Department of Office Management and Information Technology','1st Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(2,'COMP 002','Computer Programming 1','Department of Office Management and Information Technology','1st Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(3,'ITEC 101','Keyboarding and Documents Processing with Laboratory','Department of Office Management and Information Technology','1st Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(4,'ITEC 102','Basic Computer Hardware Servicing','Department of Office Management and Information Technology','1st Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(5,'COMP 006','Data Structures and Algorithms','Department of Office Management and Information Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(6,'COMP 007','Operating Systems','Department of Office Management and Information Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(7,'COMP 008','Data Communications and Networking','Department of Office Management and Information Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(8,'INTE 201','Programming 3 (Structured Programming)','Department of Office Management and Information Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(9,'INTE 202','Integrative Programming and Technologies 1','Department of Office Management and Information Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(10,'COMP 015','Fundamentals of Research','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(11,'COMP 017','Multimedia','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(12,'COMP 018','Database Administration','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(13,'COMP 019','Applications Development and Emerging Technologies','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(14,'COMP 025','Project Management','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(15,'COMP 027','Mobile Application Development (SMP PLUS)','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(16,'INTE 351','Systems Analysis and Design','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(17,'ITEC 301','Advance Programming','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(18,'OFAD 101','Keyboard and Documents Processing','Department of Office Management and Information Technology','1st Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(19,'OFAD 102','Foundation of Shorthand','Department of Office Management and Information Technology','1st Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(20,'OFAD 103','Administrative Office Procedures and Records Management','Department of Office Management and Information Technology','1st Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(21,'OFAD 361','Legal Terminology with Transcription for Court Stenographers and Legal Office Associates','Department of Office Management and Information Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(22,'OFAD 351','Legal Office Procedures','Department of Office Management and Information Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(23,'LAW 015','Business Law (Obligations and Contracts)','Department of Office Management and Information Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(24,'OFAD 105','Principles of Public and Customer Relations','Department of Office Management and Information Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(25,'COMP 106','Introduction to Database Management System','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(26,'OFAD 371','Filipino Stenography','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(27,'OFAD 202','Web Design for Business','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(28,'OFAD 301','Events Management','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(29,'OFAD 451','Machine Shorthand 2','Department of Office Management and Information Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(30,'CMPE 102','Programming Logic and Design','Department of Computer and Electronics Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(31,'CMPE 105','Computer Hardware Fundamentals','Department of Computer and Electronics Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(32,'CPET 102','Web Technology and Programming','Department of Computer and Electronics Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(33,'ENSC 013','Engineering Drawing','Department of Computer and Electronics Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(34,'CMPE 101','Computer Engineering as a Discipline','Department of Computer and Electronics Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(35,'CMPE 201','Data Structures and Algorithms','Department of Computer and Electronics Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(36,'CMPEPC1','CPE Professional Course 1','Department of Computer and Electronics Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(37,'CPET 201','2D Animation','Department of Computer and Electronics Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(38,'ECEN 011','Fundamentals of Electronic Circuits','Department of Computer and Electronics Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(39,'ELEN 012','Fundamentals of Electrical Circuits','Department of Computer and Electronics Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(40,'CMPE 303','Computer Engineering Drafting and Design','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(41,'CMPE 305','Data and Digital Communications','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(42,'CMPE 308','CPE Laws and Professional Practice','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(43,'CMPE 311','Microprocessors','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(44,'CMPEPC3','CPE Professional Course 3','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(45,'CPET 301','CPET Project Development 1','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(46,'CPET 302','Database Management System 2','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(47,'ENGL 012','Technical Communication','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(48,'ECEN 101','Basic Electronics 1','Department of Computer and Electronics Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(49,'ECET 101','Consumer Electronics Servicing 1','Department of Computer and Electronics Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(50,'CMPE 012','Computer Programming','Department of Computer and Electronics Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(51,'ECEN 202','Communications 1: Principles of Communication Systems','Department of Computer and Electronics Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(52,'ECEN 204','Electronics 2: Electronic Circuit Analysis and Design','Department of Computer and Electronics Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(53,'ECET 201','Computer Maintenance and Repair','Department of Computer and Electronics Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(54,'ELEN 013','Circuits 1','Department of Computer and Electronics Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(55,'ECEN 206','Communications 2: Modulation & Coding Techniques','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(56,'ECET 301','Mechatronics','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(57,'ECET 302','Industrial Electronics','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(58,'ECET 303','Photovoltaic (PV) Installation Design and Maintenance','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(59,'ECET 304','ECET Project Development 2','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(60,'ELEN 014','Circuits 2','Department of Computer and Electronics Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(61,'EETE 101','Basic Electrical Instruments','Department of Electrical and Mechanical Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(62,'EETE 102','Philippine Electrical Code','Department of Electrical and Mechanical Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(63,'ENSC 013','Engineering Drawing','Department of Electrical and Mechanical Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(64,'CMPE 012','Computer Programming','Department of Electrical and Mechanical Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(65,'ECEN 015','Electromagnetics','Department of Electrical and Mechanical Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(66,'EETE 201','Design, Layout and Estimates','Department of Electrical and Mechanical Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(67,'ELEN 201','Electrical Circuits 1','Department of Electrical and Mechanical Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(68,'ELEN 205','EE Laws, Codes and Professional Ethics','Department of Electrical and Mechanical Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(69,'ENSC 018','Engineering Mechanics','Department of Electrical and Mechanical Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(70,'ECEN 013','Industrial Electronics','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(71,'EETE 301','Illumination Engineering Design','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(72,'EETE 302','Electrical Engineering Technology Project Development 2','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(73,'ELEN 202','Mechatronics','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(74,'ELEN 303','Electrical Apparatus and Devices','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(75,'ELEN 411','Management of Engineering Projects','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(76,'MEEN 101','Mechanical Engineering Orientation','Department of Electrical and Mechanical Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(77,'MEEN 202','Workshop Theory and Practice','Department of Electrical and Mechanical Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(78,'METE 101','Basic Automotive','Department of Electrical and Mechanical Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(79,'MEEN 414','ME Laws, Ethics, Contracts, Codes and Standards','Department of Electrical and Mechanical Engineering Technology',NULL,1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(80,'ELEN 016','Basic Electrical Engineering','Department of Electrical and Mechanical Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(81,'CMPE 011','Computer Fundamentals and Programming','Department of Electrical and Mechanical Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(82,'METE 202','Auto Engine Rebuilding 1','Department of Electrical and Mechanical Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(83,'METE 201','CAD/CAM Operation 1 (2D)','Department of Electrical and Mechanical Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(84,'METE 203','RAC Servicing 2 (NC II)','Department of Electrical and Mechanical Engineering Technology','2nd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(85,'MEEN 301','DC and AC Machinery','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(86,'METE 301','Fundamentals of Driving and Practice','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(87,'METE 302','MET Project Development 2','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(88,'METE 303','Gas Metal Arc Welding','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(89,'METE 304','Shielded Metal Arc Welding 2','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59'),(90,'METE 305','Combustion Engineering','Department of Electrical and Mechanical Engineering Technology','3rd Year',1,'2026-09-16 18:31:59','2026-09-16 18:31:59');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tools`
--

DROP TABLE IF EXISTS `tools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tools` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `total_quantity` smallint unsigned NOT NULL DEFAULT '1',
  `room_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tools_room_id_foreign` (`room_id`),
  KEY `tools_category_is_active_index` (`category`,`is_active`),
  KEY `tools_department_index` (`department`),
  CONSTRAINT `tools_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tools`
--

LOCK TABLES `tools` WRITE;
/*!40000 ALTER TABLE `tools` DISABLE KEYS */;
INSERT INTO `tools` VALUES (1,'Digital Multimeter Fluke 101','Testing Equipment',NULL,'Handheld digital multimeter for electronics lab',5,NULL,1,'2026-09-16 18:39:15','2026-09-16 17:13:50','2026-09-16 18:39:15'),(2,'Digital Oscilloscope Rigol DS1054Z','Testing Equipment',NULL,'4-channel 50MHz digital oscilloscope',2,NULL,1,'2026-09-16 18:39:27','2026-09-16 17:13:58','2026-09-16 18:39:27'),(3,'STENOGRAPH LUMINEX CSE STUDENT SHORTHAND MACHINE WRITER (NEW)','EQUIPMENTS','Department of Office Management and Information Technology',NULL,6,NULL,1,NULL,'2026-09-16 17:15:08','2026-09-16 17:38:21'),(4,'WATT METER – SEU,0.2A/1A,120-240V','MEASURING','Department of Electrical and Mechanical Engineering Technology',NULL,1,NULL,1,NULL,'2026-09-16 17:27:28','2026-09-17 15:01:07'),(5,'MULTITESTER - SANWA','MEASURING','Department of Computer and Electronics Engineering Technology',NULL,6,NULL,1,NULL,'2026-09-16 17:28:39','2026-09-17 16:36:11'),(6,'BENCH METER','MEASURING','Department of Computer and Electronics Engineering Technology',NULL,3,NULL,1,NULL,'2026-09-16 17:29:08','2026-09-16 17:29:08'),(7,'VOLTMETER','MEASURING','Department of Electrical and Mechanical Engineering Technology',NULL,1,NULL,1,NULL,'2026-09-16 17:29:17','2026-09-17 15:00:42'),(8,'DIGITAL MULTITESTER','MEASURING','Department of Computer and Electronics Engineering Technology',NULL,6,NULL,1,NULL,'2026-09-16 17:29:35','2026-09-17 16:36:11'),(9,'METER','MEASURING','Department of Computer and Electronics Engineering Technology',NULL,2,NULL,1,NULL,'2026-09-16 17:29:53','2026-09-17 16:36:11'),(10,'DIE HEAD COMPLETE','TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,2,NULL,1,NULL,'2026-09-16 17:30:13','2026-09-17 14:56:53'),(11,'FLARING TOOLS','TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,3,NULL,1,NULL,'2026-09-16 17:30:26','2026-09-17 14:57:14'),(12,'DESOLDERING','HAND TOOLS','Department of Computer and Electronics Engineering Technology',NULL,3,NULL,1,NULL,'2026-09-16 17:30:36','2026-09-17 17:33:01'),(13,'WIRE STRIPPER','HAND TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,4,NULL,1,NULL,'2026-09-16 17:30:50','2026-09-17 17:35:58'),(14,'SOLDERING IRON','HAND TOOLS','Department of Computer and Electronics Engineering Technology',NULL,6,NULL,1,NULL,'2026-09-16 17:31:11','2026-09-17 17:34:40'),(15,'CUTTER','HAND TOOLS','Department of Computer and Electronics Engineering Technology',NULL,5,NULL,1,NULL,'2026-09-16 17:31:42','2026-09-17 17:28:50'),(16,'COMBINATION PLIERS','HAND TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,5,NULL,1,NULL,'2026-09-16 17:32:03','2026-09-17 17:35:09'),(17,'LONG NOSE PLIERS','HAND TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,8,NULL,1,NULL,'2026-09-16 17:32:17','2026-09-17 17:37:01'),(18,'SIDE CUTTING PLIERS','HAND TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,6,NULL,1,NULL,'2026-09-16 17:32:34','2026-09-17 17:37:29'),(19,'ADJUSTABLE WRENCH','HAND TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,2,NULL,1,NULL,'2026-09-16 17:32:47','2026-09-17 17:32:50'),(20,'SLIP JOINT PLIERS','HAND TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,2,NULL,1,NULL,'2026-09-16 17:33:17','2026-09-17 17:37:12'),(21,'PHILLIPS SCREWDRIVER','HAND TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,2,NULL,1,NULL,'2026-09-16 17:33:44','2026-09-17 17:37:21'),(22,'FLATHEAD SCREWDRIVER','HAND TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,2,NULL,1,NULL,'2026-09-16 17:34:05','2026-09-17 17:35:40'),(23,'HACKSAW','HAND TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,3,NULL,1,NULL,'2026-09-16 17:34:24','2026-09-17 17:33:39'),(24,'HAMMER','HAND TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,1,NULL,1,NULL,'2026-09-16 17:34:34','2026-09-17 17:36:30'),(25,'BALL HAMMER','HAND TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,2,NULL,1,NULL,'2026-09-16 17:34:43','2026-09-17 17:33:50'),(26,'EXTENSION CORD ( Not PUP Property)','ELECTRICAL DEVICE/ACCESSORY','Department of Electrical and Mechanical Engineering Technology',NULL,6,22,1,NULL,'2026-09-16 17:35:22','2026-09-17 17:23:49'),(27,'STENOGRAPH WAVE STUDENT WRITER (OLD)','EQUIPMENTS','Department of Office Management and Information Technology',NULL,2,NULL,1,NULL,'2026-09-16 17:38:03','2026-09-16 17:38:03'),(28,'EPSON PROJECTOR','EQUIPMENTS (DOMIT)','Department of Office Management and Information Technology',NULL,2,NULL,1,NULL,'2026-09-16 17:38:36','2026-09-17 14:51:55'),(29,'LAPTOP FOR STENOGRAPHY','EQUIPMENTS (DOMIT)','Department of Office Management and Information Technology',NULL,6,NULL,1,NULL,'2026-09-16 17:38:51','2026-09-17 15:17:12'),(30,'MAKITA DRILL MACHINE','POWER TOOLS','Department of Electrical and Mechanical Engineering Technology','13mm (1/2\")',2,NULL,1,NULL,'2026-09-16 17:39:36','2026-09-17 17:34:28'),(31,'SMD REWORK STATION','EQUIPMENTS','Department of Computer and Electronics Engineering Technology',NULL,1,NULL,1,NULL,'2026-09-16 17:39:46','2026-09-16 17:39:46'),(32,'WELDING AND CUTTING TORCH-MASTER','EQUIPMENTS','Department of Electrical and Mechanical Engineering Technology',NULL,1,NULL,1,NULL,'2026-09-16 17:40:03','2026-09-16 17:40:03'),(33,'BATTERY','EQUIPMENTS (DEMET)','Department of Electrical and Mechanical Engineering Technology',NULL,2,NULL,1,NULL,'2026-09-16 17:40:14','2026-09-17 15:01:49'),(34,'LPG TORCH','EQUIPMENTS','Department of Electrical and Mechanical Engineering Technology',NULL,2,NULL,1,NULL,'2026-09-16 17:42:03','2026-09-16 17:42:03'),(35,'INDUSTRIAL GRINDER','POWER TOOLS','Department of Electrical and Mechanical Engineering Technology',NULL,1,NULL,1,NULL,'2026-09-16 17:42:17','2026-09-17 17:34:08'),(36,'CONDUIT BENDER','EQUIPMENTS (DEMET)','Department of Electrical and Mechanical Engineering Technology',NULL,6,NULL,1,NULL,'2026-09-16 17:42:29','2026-09-17 15:02:05'),(37,'SOCKET WRENCH SET 3','EQUIPMENTS (DEMET)','Department of Electrical and Mechanical Engineering Technology',NULL,3,NULL,1,NULL,'2026-09-16 17:42:44','2026-09-17 14:53:35'),(38,'START CAPACITOR MOTOR (SINGLE PHASE)','EQUIPMENTS (DEMET)','Department of Electrical and Mechanical Engineering Technology',NULL,1,NULL,1,NULL,'2026-09-16 17:43:01','2026-09-17 14:53:46'),(39,'ELECTRODE HOLDER','EQUIPMENTS (DEMET)','Department of Electrical and Mechanical Engineering Technology',NULL,1,NULL,1,NULL,'2026-09-16 17:43:11','2026-09-17 14:51:24'),(40,'WELDING HELMET','EQUIPMENTS','Department of Electrical and Mechanical Engineering Technology',NULL,5,NULL,1,NULL,'2026-09-16 17:43:22','2026-09-16 17:43:22'),(41,'TV REMOTE','EQUIPMENTS','Department of Office Management and Information Technology',NULL,7,NULL,1,NULL,'2026-09-16 17:43:34','2026-09-17 16:36:11'),(42,'DANGLE','SMART TV ACCESORY','Department of Electrical and Mechanical Engineering Technology',NULL,1,8,1,NULL,'2026-09-16 17:43:55','2026-09-17 17:28:05'),(43,'DANGLE','SMART TV ACCESORY','Department of Office Management and Information Technology',NULL,1,2,1,NULL,'2026-09-16 17:44:15','2026-09-17 17:47:46'),(44,'PEN FOR INTERACTIVE TV','SMART TV ACCESORY','Department of Electrical and Mechanical Engineering Technology',NULL,1,8,1,NULL,'2026-09-16 17:44:31','2026-09-17 17:25:27'),(45,'PEN FOR INTERACTIVE TV','SMART TV ACCESORY','Department of Office Management and Information Technology',NULL,1,2,1,NULL,'2026-09-16 17:44:40','2026-09-17 17:47:58'),(46,'ARDUINO UNO STARTING KIT','EQUIPMENTS (DOMIT)','Department of Office Management and Information Technology',NULL,100,NULL,1,NULL,'2026-09-16 17:45:08','2026-09-16 17:45:08'),(47,'ARDUINO UNO STARTING KIT','EQUIPMENTS (DEMET)','Department of Electrical and Mechanical Engineering Technology',NULL,100,NULL,1,NULL,'2026-09-16 17:45:22','2026-09-16 17:45:22');
/*!40000 ALTER TABLE `tools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_items`
--

DROP TABLE IF EXISTS `transaction_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaction_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned NOT NULL,
  `tool_id` bigint unsigned NOT NULL,
  `quantity_borrowed` smallint unsigned NOT NULL DEFAULT '1',
  `quantity_returned` smallint unsigned NOT NULL DEFAULT '0',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'borrowed',
  `returned_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_items_transaction_id_tool_id_index` (`transaction_id`,`tool_id`),
  KEY `transaction_items_tool_id_status_index` (`tool_id`,`status`),
  CONSTRAINT `transaction_items_tool_id_foreign` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transaction_items_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_items`
--

LOCK TABLES `transaction_items` WRITE;
/*!40000 ALTER TABLE `transaction_items` DISABLE KEYS */;
INSERT INTO `transaction_items` VALUES (4,31,46,50,50,'returned','2026-09-17 17:25:37','all returned in good condition','2026-09-17 17:02:15','2026-09-17 17:25:37'),(5,34,8,1,1,'returned','2026-09-17 19:53:38',NULL,'2026-09-17 19:30:07','2026-09-17 19:53:38'),(6,38,16,1,1,'returned','2026-09-18 11:26:23',NULL,'2026-09-18 08:09:48','2026-09-18 11:26:23');
/*!40000 ALTER TABLE `transaction_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `tool_id` bigint unsigned DEFAULT NULL,
  `quantity` smallint unsigned NOT NULL DEFAULT '1',
  `borrower_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `borrower_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `software_utilized` json DEFAULT NULL,
  `checked_out_at` timestamp NOT NULL,
  `expected_return_at` timestamp NULL DEFAULT NULL,
  `returned_at` timestamp NULL DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'google_form',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_user_id_foreign` (`user_id`),
  KEY `transactions_status_index` (`status`),
  KEY `transactions_checked_out_at_index` (`checked_out_at`),
  KEY `transactions_room_id_status_index` (`room_id`,`status`),
  KEY `transactions_tool_id_status_index` (`tool_id`,`status`),
  KEY `transactions_borrower_email_index` (`borrower_email`),
  KEY `transactions_department_index` (`department`),
  CONSTRAINT `transactions_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_tool_id_foreign` FOREIGN KEY (`tool_id`) REFERENCES `tools` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (6,1,1,NULL,1,'Alcantara, Randolph','rgalcantara@pup.edu.ph','College of Science','Physics 101 Experiment',NULL,'2026-09-16 17:51:16',NULL,'2026-09-16 17:54:15','returned',NULL,'qr_scan','2026-09-16 17:51:16','2026-09-16 17:54:15'),(7,1,6,NULL,1,'Ruiz, Frescian C.','fcruiz@pup.edu.ph','Department of Computer and Electronics Engineering Technology','2D Animation',NULL,'2026-09-16 17:53:27','2026-09-16 20:53:27','2026-09-16 18:13:46','returned',NULL,'dashboard','2026-09-16 17:53:27','2026-09-16 18:13:46'),(8,1,NULL,36,2,'Lanze Ardreen Jabal',NULL,'Department of Electrical and Mechanical Engineering Technology','EMTR',NULL,'2026-09-16 18:37:23','2026-09-16 21:37:23','2026-09-16 18:37:47','returned',NULL,'dashboard','2026-09-16 18:37:23','2026-09-16 18:37:47'),(9,4,8,NULL,1,'Alfonso, Raymond L.','rlalfonso@pup.edu.ph','Department of Electrical and Mechanical Engineering Technology','Basic Electrical Instrument',NULL,'2026-09-16 18:44:35','2026-09-16 20:44:35','2026-09-16 19:23:04','returned',NULL,'dashboard','2026-09-16 18:44:35','2026-09-16 19:23:04'),(10,1,1,NULL,1,'Ruiz, Jomar B.','jbruiz@pup.edu.ph','Department of Computer and Electronics Engineering Technology','CMPE 102 - Programming Logic and Design',NULL,'2026-09-15 08:00:00','2026-09-15 11:30:00','2026-09-15 11:30:00','returned',NULL,'dashboard','2026-09-16 19:05:50','2026-09-16 19:05:50'),(11,1,2,NULL,1,'Fernando, Ronald D','rdfernando@pup.edu.ph','Department of Computer and Electronics Engineering Technology','ECEN 101 - Basic Electronics 1',NULL,'2026-09-16 14:00:00','2026-09-16 17:00:00','2026-09-16 20:42:12','returned',NULL,'dashboard','2026-09-16 19:05:50','2026-09-16 20:42:12'),(13,1,15,NULL,1,'Francisco, Vic Joseph','franciscovicjoseph@gmail.com','Department of Electrical and Mechanical Engineering Technology','ENSC 013 - Engineering Drawing',NULL,'2026-09-16 07:30:00','2026-09-16 12:03:00','2026-09-16 12:03:00','returned',NULL,'dashboard','2026-09-16 19:11:29','2026-09-16 19:11:29'),(14,1,8,NULL,1,'Legaspi, Jefferson N.','jnlegaspi@pup.edu.ph','Department of Electrical and Mechanical Engineering Technology','ENSC 018 - Engineering Mechanics',NULL,'2026-09-16 07:35:00','2026-09-16 10:16:00','2026-09-16 10:16:00','returned',NULL,'dashboard','2026-09-16 19:13:37','2026-09-16 19:13:37'),(15,1,2,NULL,1,'Legaspi, John Michael V.','jmvlegaspi@pup.edu.ph','Department of Computer and Electronics Engineering Technology','COMP 025 - Project Management',NULL,'2026-09-16 07:56:00','2026-09-16 10:20:00','2026-09-16 10:20:00','returned',NULL,'dashboard','2026-09-16 19:17:19','2026-09-16 19:17:19'),(16,1,11,NULL,1,'Lacdang, Clint Michael','cmflacdang@pup.edu.ph','Department of Electrical and Mechanical Engineering Technology','EETE 301 - Illumination Engineering Design',NULL,'2026-09-16 09:54:00','2026-09-16 12:20:00','2026-09-16 12:20:00','returned',NULL,'dashboard','2026-09-16 19:20:09','2026-09-16 19:20:09'),(17,4,4,NULL,1,'Glori, Jonathan','jdglori@pup.edu.ph','Department of Electrical and Mechanical Engineering Technology','SMAW',NULL,'2026-09-16 09:45:00','2026-09-16 11:45:00','2026-09-16 20:42:05','returned',NULL,'dashboard','2026-09-16 19:25:40','2026-09-16 20:42:05'),(19,4,15,NULL,1,'Alfonso, Raymond L.',NULL,'Department of Electrical and Mechanical Engineering Technology','ENSC 013 - Engineering Drawing',NULL,'2026-09-17 07:17:00','2026-09-17 10:17:00','2026-09-17 11:23:31','returned',NULL,'dashboard','2026-09-17 07:18:03','2026-09-17 11:23:31'),(20,1,2,NULL,1,'Legaspi, John Michael V.','jmvlegaspi@pup.edu.ph','Department of Computer and Electronics Engineering Technology','COMP 025 - Project Management',NULL,'2026-09-17 08:25:00','2026-09-17 11:25:00','2026-09-17 11:20:21','returned',NULL,'dashboard','2026-09-17 08:27:12','2026-09-17 11:20:21'),(21,1,1,NULL,1,'Bonaobra, Zenaida S.','zsbonaobra@pup.edu.ph','Department of Office Management and Information Technology','OFAD 351 - Legal Office Procedures',NULL,'2026-09-17 08:30:00','2026-09-17 11:30:00','2026-09-17 12:42:49','returned',NULL,'dashboard','2026-09-17 08:31:17','2026-09-17 12:42:49'),(22,4,8,NULL,1,'Alfonso, Raymond L.',NULL,'Department of Electrical and Mechanical Engineering Technology','Project Development',NULL,'2026-09-17 12:26:00','2026-09-17 15:26:00','2026-09-17 17:45:32','returned',NULL,'dashboard','2026-09-17 12:34:38','2026-09-17 17:45:32'),(23,4,11,NULL,1,'Lacdang, Clint Michael',NULL,'Department of Electrical and Mechanical Engineering Technology','ELEN 303 - Electrical Apparatus and Devices',NULL,'2026-09-17 13:00:00','2026-09-17 16:00:00','2026-09-17 16:27:16','returned',NULL,'dashboard','2026-09-17 13:51:11','2026-09-17 16:27:16'),(24,4,NULL,41,1,'Raya Margaret Junio',NULL,'Department of Electrical and Mechanical Engineering Technology','ELEN 303 - Electrical Apparatus and Devices',NULL,'2026-09-17 13:00:00','2026-09-17 16:00:00','2026-09-17 16:27:08','returned',NULL,'dashboard','2026-09-17 13:52:44','2026-09-17 16:27:08'),(28,4,12,NULL,1,'Tindogan, Paulo E.','petindogan@pup.edu.ph','Department of Electrical and Mechanical Engineering Technology','ELEN 201 - Electrical Circuits 1',NULL,'2026-09-17 14:38:00','2026-09-17 17:38:00','2026-09-17 20:31:13','returned',NULL,'dashboard','2026-09-17 14:39:06','2026-09-17 20:31:13'),(30,1,6,NULL,1,'Ruiz, Jomar B.','jbruiz@pup.edu.ph','Department of Computer and Electronics Engineering Technology','CMPE 305 - Data and Digital Communications',NULL,'2026-09-17 07:55:00','2026-09-17 09:56:00','2026-09-17 09:56:00','returned',NULL,'dashboard','2026-09-17 17:01:20','2026-09-17 17:01:20'),(31,1,NULL,46,50,'Ruiz, Jomar B.','jbruiz@pup.edu.ph','Department of Computer and Electronics Engineering Technology',NULL,NULL,'2026-09-17 17:01:00','2026-09-17 20:01:00','2026-09-17 17:25:37','returned',NULL,'dashboard','2026-09-17 17:02:15','2026-09-17 17:25:37'),(32,4,15,NULL,1,'Alfonso, Raymond L.','rlalfonso@pup.edu.ph','Department of Electrical and Mechanical Engineering Technology','Project Development 2',NULL,'2026-09-17 17:56:00','2026-09-17 20:56:00','2026-09-17 19:12:55','returned',NULL,'dashboard','2026-09-17 17:57:11','2026-09-17 19:12:55'),(33,4,17,NULL,1,'Prof. Rochelle Cajayon',NULL,'Department of Office Management and Information Technology','CWTS',NULL,'2026-09-17 17:57:00','2026-09-17 20:57:00','2026-09-17 18:35:40','returned','Borrower (Student) - Joe Emmanuel Panagdato','dashboard','2026-09-17 17:59:31','2026-09-17 18:35:40'),(34,4,NULL,8,1,'MICHAEL LOREMIA',NULL,'Department of Electrical and Mechanical Engineering Technology','ELECTRICAL CIRCUITS 1',NULL,'2026-09-17 18:00:00','2026-09-17 20:00:00','2026-09-17 19:53:38','returned',NULL,'dashboard','2026-09-17 19:30:07','2026-09-17 19:53:38'),(35,7,8,NULL,1,'Tindogan, Paulo E.','petindogan@pup.edu.ph','Department of Electrical and Mechanical Engineering Technology','EETE 204 - Photovoltaic (PV) Installation Design and Maintenance',NULL,'2026-09-18 07:31:00','2026-09-18 10:31:00',NULL,'open','Borrower presented ID','dashboard','2026-09-18 07:35:15','2026-09-18 07:35:15'),(36,7,11,NULL,1,'CARLOS, AUGUSTINE JOHN A.',NULL,'Department of Electrical and Mechanical Engineering Technology','PHYS 013 - Physics for Engineers (Calculus-based)',NULL,'2026-09-18 07:35:00','2026-09-18 10:35:00','2026-09-18 09:31:18','returned','Borrower presented ID','dashboard','2026-09-18 07:38:02','2026-09-18 09:31:18'),(37,7,15,NULL,1,'Marcaida, Jay Ar D.','jadmarcaida@pup.edu.ph','Department of Electrical and Mechanical Engineering Technology','ENSC 013 - Engineering Drawing',NULL,'2026-09-18 08:07:00','2026-09-18 13:07:00',NULL,'open','Borrower presented ID','dashboard','2026-09-18 08:08:12','2026-09-18 08:08:12'),(38,7,NULL,16,1,'MORANO, ERICA ANN G.',NULL,'Department of Electrical and Mechanical Engineering Technology',NULL,NULL,'2026-09-18 08:09:00','2026-09-18 11:09:00','2026-09-18 11:26:23','returned',NULL,'dashboard','2026-09-18 08:09:48','2026-09-18 11:26:23'),(39,8,2,NULL,1,'Ruiz, Jomar B.','jbruiz@pup.edu.ph','Department of Computer and Electronics Engineering Technology','CMPEPC1 - CPE Professional Course 1',NULL,'2026-09-18 10:08:00','2026-09-18 13:08:00','2026-09-18 11:37:37','returned',NULL,'dashboard','2026-09-18 10:08:55','2026-09-18 11:37:37'),(41,8,6,NULL,1,'Ruiz, Frescian C.','fcruiz@pup.edu.ph','Department of Computer and Electronics Engineering Technology','CPET 201 - 2D Animation','[\"Adobe Creative Cloud\", \"Adobe Animate\"]','2026-09-18 11:27:00',NULL,NULL,'open',NULL,'dashboard','2026-09-18 11:28:13','2026-09-18 11:33:08');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
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
  `role` enum('student_assistant','faculty','lab_head') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'faculty',
  `telegram_chat_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Jomar Ruiz','labhead@pup.edu.ph','lab_head',NULL,NULL,'$2y$12$HoI8FMtmcLZm84rGbLlbru.cOtyQf2uVZkzn/2ttcgcWq2TQuFVnW','wfIM1kXZDeVkQKlVM2IuRG9v5JL1hvQg5DJkkw7ycCLpKaraNxzASzLVHG9y','2026-09-15 11:36:21','2026-09-16 18:34:23'),(2,'Student Assistant','assistant@pup.edu.ph','student_assistant',NULL,NULL,'$2y$12$HNt9uMWIaAls3o4aMFb1ou8nL7e19fNEp50tTxWQYJioOSWN6uJha',NULL,'2026-09-15 11:36:21','2026-09-15 11:36:21'),(4,'Hannah Frances','hannahfrancesgnino@iskolarngbayan.pup.edu.ph','student_assistant',NULL,NULL,'$2y$12$h6NcfwBiI7comP5CwtifXu2x83LpgopCWF9/NhgRGkaMCmrWv1Noi','Oj3HqOh5YYqV8AqhA6cXqjGFR7qQl0skBeTL4SxAu4QMdUXalDsWXaGpZX3E','2026-09-16 18:29:26','2026-09-16 18:29:26'),(5,'Remegio Rios','rcrios@pup.edu.ph','lab_head',NULL,NULL,'$2y$12$YpCTHSRwVyeAPZetev7wIuoM4meAFyoBY6lDULrGYF73HvZxXyEj2',NULL,'2026-09-16 19:26:04','2026-09-16 19:26:04'),(6,'Aina David','amdavid@pup.edu.ph','lab_head',NULL,NULL,'$2y$12$XwUINRnuRH0MF.ySxqzRPehwepC9xQNb1Zk0cqWA93q0LcRNBhf92',NULL,'2026-09-16 19:26:42','2026-09-16 19:26:42'),(7,'Lanze Ardreen L. Jabal','lanzeardreenljabal@iskolarngbayan.pup.edu.ph','student_assistant',NULL,NULL,'$2y$12$OUZ/jH6yAyaQTji0l80sh.B8.DORe1Zf3Zb8Vxb.JfJ84HnOn/eye',NULL,'2026-09-16 19:28:46','2026-09-16 19:28:46'),(8,'Jomar Ruiz','jbruiz@pup.edu.ph','lab_head',NULL,NULL,'$2y$12$.Z.NYo.gc4l0H6DVzHNu0OGSygzmDi17uFmT4k79CMR9AJLoLXFSC',NULL,'2026-09-16 19:33:51','2026-09-17 14:48:01'),(9,'Third Aboc','thirdmaboc@iskolarngbayan.pup.edu.ph','student_assistant',NULL,NULL,'$2y$12$bgJfubUslZA82igZp4SYuu2KdFhHYfGYn8ocrgPQhfLE6Go69tCqK','rbn3V98tRlGNX9HduRzCwn06vfP89uAlZdxmhd48hVxfXCmM4ZSOYuTPSFy3','2026-09-16 20:51:34','2026-09-16 20:51:34');
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

-- Dump completed on 2026-09-18  3:55:18
