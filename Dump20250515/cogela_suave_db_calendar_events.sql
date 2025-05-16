-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: localhost    Database: cogela_suave_db
-- ------------------------------------------------------
-- Server version	8.0.39

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text,
  `event_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `color` varchar(20) DEFAULT '#7C9A92',
  `tipo_evento` enum('personal','terapia','medicamento','ejercicio','otro') DEFAULT 'personal',
  `recordatorio` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_events_user_date` (`user_id`,`event_date`),
  CONSTRAINT `calendar_events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calendar_events`
--

LOCK TABLES `calendar_events` WRITE;
/*!40000 ALTER TABLE `calendar_events` DISABLE KEYS */;
INSERT INTO `calendar_events` VALUES (4,1,'me comi unas papas',NULL,'2025-05-17 19:39:00',NULL,'#E57373','personal',0,'2025-05-09 19:41:58'),(7,1,'me comi unas papas',NULL,'2025-05-23 19:45:00',NULL,'#7C9A92','personal',0,'2025-05-09 19:45:27'),(8,1,'me comi unas papas',NULL,'2025-05-23 19:45:00',NULL,'#7C9A92','personal',0,'2025-05-09 19:48:36'),(9,1,'me comi unas papas',NULL,'2025-05-23 19:45:00',NULL,'#7C9A92','personal',0,'2025-05-09 19:50:46'),(10,1,'ds',NULL,'2025-05-31 19:53:00',NULL,'#64B5F6','personal',0,'2025-05-09 19:51:09'),(11,1,'ds',NULL,'2025-05-31 19:53:00',NULL,'#64B5F6','personal',0,'2025-05-09 19:55:24'),(15,1,'jdjd',NULL,'2025-05-22 19:55:00',NULL,'#7C9A92','personal',0,'2025-05-09 20:03:37'),(16,1,'jdjd',NULL,'2025-05-22 19:55:00',NULL,'#7C9A92','personal',0,'2025-05-09 20:04:17'),(17,1,'jdjd',NULL,'2025-05-22 19:55:00',NULL,'#7C9A92','personal',0,'2025-05-09 20:05:35'),(18,1,'jdjd',NULL,'2025-05-22 19:55:00',NULL,'#7C9A92','personal',0,'2025-05-09 20:09:08'),(19,1,'jdjd',NULL,'2025-05-22 19:55:00',NULL,'#7C9A92','personal',0,'2025-05-09 20:11:57');
/*!40000 ALTER TABLE `calendar_events` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-15 19:39:16
