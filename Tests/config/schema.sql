-- MySQL dump 10.13  Distrib 5.6.25, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: test
-- ------------------------------------------------------
-- Server version	5.6.25-0ubuntu0.15.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `dag_node`
--

DROP TABLE IF EXISTS `dag_node`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dag_node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dag_node`
--

LOCK TABLES `dag_node` WRITE;
/*!40000 ALTER TABLE `dag_node` DISABLE KEYS */;
INSERT INTO `dag_node` VALUES (131,'Node 0'),(132,'Node 1'),(133,'Node 2'),(134,'Node 3'),(135,'Node 4'),(136,'Node 5'),(137,'Node 6'),(138,'Node 7'),(139,'Node 8'),(140,'Node 9');
/*!40000 ALTER TABLE `dag_node` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

--
-- Table structure for table `dag_edge`
--

DROP TABLE IF EXISTS `dag_edge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dag_edge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `incoming_edge_id` int(11) DEFAULT NULL,
  `direct_edge_id` int(11) DEFAULT NULL,
  `outgoing_edge_id` int(11) DEFAULT NULL,
  `start_node_id` int(11) DEFAULT NULL,
  `end_node_id` int(11) DEFAULT NULL,
  `hops` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E7B25DFB6CBC52DC` (`incoming_edge_id`),
  KEY `IDX_E7B25DFBCBC6DED2` (`direct_edge_id`),
  KEY `IDX_E7B25DFB10FCD9D7` (`outgoing_edge_id`),
  KEY `IDX_E7B25DFBB6C8C304` (`start_node_id`),
  KEY `IDX_E7B25DFBD3C2CE3E` (`end_node_id`),
  CONSTRAINT `FK_E7B25DFB10FCD9D7` FOREIGN KEY (`outgoing_edge_id`) REFERENCES `dag_edge` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E7B25DFB6CBC52DC` FOREIGN KEY (`incoming_edge_id`) REFERENCES `dag_edge` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E7B25DFBB6C8C304` FOREIGN KEY (`start_node_id`) REFERENCES `dag_node` (`id`),
  CONSTRAINT `FK_E7B25DFBCBC6DED2` FOREIGN KEY (`direct_edge_id`) REFERENCES `dag_edge` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E7B25DFBD3C2CE3E` FOREIGN KEY (`end_node_id`) REFERENCES `dag_node` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=511 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dag_edge`
--

LOCK TABLES `dag_edge` WRITE;
/*!40000 ALTER TABLE `dag_edge` DISABLE KEYS */;
INSERT INTO `dag_edge` VALUES (481,481,481,481,131,132,0),(482,482,482,482,131,133,0),(483,483,483,483,131,134,0),(484,484,484,484,132,133,0),(485,481,484,484,131,133,1),(486,486,486,486,133,134,0),(487,482,486,486,131,134,1),(488,484,486,486,132,134,1),(489,485,486,486,131,134,2),(490,490,490,490,134,135,0),(491,483,490,490,131,135,1),(492,486,490,490,133,135,1),(493,487,490,490,131,135,2),(494,488,490,490,132,135,2),(495,489,490,490,131,135,3),(496,496,496,496,136,137,0),(497,497,497,497,136,138,0),(498,498,498,498,136,139,0),(499,499,499,499,137,138,0),(500,496,499,499,136,138,1),(501,501,501,501,138,139,0),(502,497,501,501,136,139,1),(503,499,501,501,137,139,1),(504,500,501,501,136,139,2),(505,505,505,505,139,140,0),(506,498,505,505,136,140,1),(507,501,505,505,138,140,1),(508,502,505,505,136,140,2),(509,503,505,505,137,140,2),(510,504,505,505,136,140,3);
/*!40000 ALTER TABLE `dag_edge` ENABLE KEYS */;
UNLOCK TABLES;

-- Test database insert
SELECT COUNT(*) FROM `dag_node`;
SELECT * FROM `dag_node`;

SELECT COUNT(*) FROM `dag_edge` WHERE hops = 0;
SELECT * FROM `dag_edge` WHERE hops = 0;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-08-21 19:14:39
