-- MySQL dump 10.13  Distrib 5.1.66, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: test_operator
-- ------------------------------------------------------
-- Server version	5.1.66

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
-- Table structure for table `tarifs_saas`
--

DROP TABLE IF EXISTS `tarifs_saas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifs_saas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('public','archive') NOT NULL DEFAULT 'public',
  `description` varchar(100) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL DEFAULT '',
  `period` enum('month') CHARACTER SET latin1 DEFAULT 'month',
  `currency` enum('USD','RUR') NOT NULL DEFAULT 'RUR',
  `price` decimal(13,4) NOT NULL DEFAULT '0.0000',
  `num_ports` int(4) NOT NULL DEFAULT '0',
  `overrun_per_port` decimal(13,4) NOT NULL DEFAULT '0.0000',
  `space` int(4) NOT NULL DEFAULT '0',
  `overrun_per_mb` decimal(13,4) DEFAULT '0.0000',
  `is_record` tinyint(4) NOT NULL DEFAULT '0',
  `is_fax` tinyint(4) NOT NULL DEFAULT '0',
  `edit_user` int(11) NOT NULL DEFAULT '0',
  `edit_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=koi8r;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifs_saas`
--

LOCK TABLES `tarifs_saas` WRITE;
/*!40000 ALTER TABLE `tarifs_saas` DISABLE KEYS */;
INSERT INTO `tarifs_saas` VALUES (1,'public','Виртуальная АТС пакет Лайт','month','RUR','507.6271',10,'100.0000',250,'1.0000',0,0,48,'2013-07-25 15:44:15'),(2,'public','Виртуальная АТС пакет Бизнес','month','RUR','1694.0678',50,'50.0000',500,'1.0000',1,1,48,'2013-07-25 15:34:10'),(3,'public','Виртуальная АТС пакет Бизнес Про','month','RUR','2541.5254',100,'10.0000',1000,'1.0000',1,1,48,'2013-07-25 15:34:34');
/*!40000 ALTER TABLE `tarifs_saas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-07-25 15:46:13
