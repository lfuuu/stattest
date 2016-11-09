-- MySQL dump 10.13  Distrib 5.7.16, for Linux (x86_64)
--
-- Host: localhost    Database: nispd_test
-- ------------------------------------------------------
-- Server version	5.7.16-0ubuntu0.16.04.1

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
-- Table structure for table `_client_contract_business_process_status`
--

DROP TABLE IF EXISTS `_client_contract_business_process_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_client_contract_business_process_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_process_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `sort` tinyint(4) NOT NULL DEFAULT '0',
  `oldstatus` varchar(20) NOT NULL DEFAULT '',
  `color` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_client_contract_business_process_status`
--

LOCK TABLES `_client_contract_business_process_status` WRITE;
/*!40000 ALTER TABLE `_client_contract_business_process_status` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `_client_contract_business_process_status` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `_voip_numbers`
--

DROP TABLE IF EXISTS `_voip_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `_voip_numbers` (
  `number` varchar(11) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `status` enum('notsell','instock','reserved','active','hold') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'instock',
  `reserve_from` datetime DEFAULT NULL,
  `reserve_till` datetime DEFAULT NULL,
  `hold_from` datetime DEFAULT NULL,
  `beauty_level` tinyint(4) NOT NULL DEFAULT '0',
  `price` int(11) DEFAULT '0',
  `region` smallint(6) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `usage_id` int(11) DEFAULT NULL,
  `reserved_free_date` datetime DEFAULT NULL,
  `used_until_date` datetime DEFAULT NULL,
  `edit_user_id` int(11) DEFAULT NULL,
  `site_publish` enum('N','Y') NOT NULL DEFAULT 'N',
  `city_id` int(11) NOT NULL,
  `did_group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`number`),
  KEY `region` (`region`),
  KEY `fk_voip_number__city_id` (`city_id`),
  KEY `fk_voip_number__did_group_id` (`did_group_id`),
  CONSTRAINT `_voip_numbers_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `_voip_numbers_ibfk_2` FOREIGN KEY (`did_group_id`) REFERENCES `did_group` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `_voip_numbers`
--

LOCK TABLES `_voip_numbers` WRITE;
/*!40000 ALTER TABLE `_voip_numbers` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `_voip_numbers` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `actual_call_chat`
--

DROP TABLE IF EXISTS `actual_call_chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actual_call_chat` (
  `client_id` int(11) NOT NULL,
  `usage_id` int(11) NOT NULL,
  `tarif_id` int(11) NOT NULL,
  UNIQUE KEY `client_id__usage_id` (`client_id`,`usage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actual_call_chat`
--

LOCK TABLES `actual_call_chat` WRITE;
/*!40000 ALTER TABLE `actual_call_chat` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `actual_call_chat` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `actual_number`
--

DROP TABLE IF EXISTS `actual_number`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actual_number` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `number` char(16) NOT NULL,
  `region` int(11) NOT NULL DEFAULT '99',
  `call_count` int(11) NOT NULL,
  `number_type` enum('vnumber','nonumber','number') NOT NULL DEFAULT 'number',
  `is_blocked` tinyint(4) NOT NULL DEFAULT '0',
  `is_disabled` tinyint(4) NOT NULL DEFAULT '0',
  `number7800` char(13) NOT NULL DEFAULT '',
  `biller_version` int(11) NOT NULL DEFAULT '4',
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21880 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actual_number`
--

LOCK TABLES `actual_number` WRITE;
/*!40000 ALTER TABLE `actual_number` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `actual_number` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `actual_virtpbx`
--

DROP TABLE IF EXISTS `actual_virtpbx`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actual_virtpbx` (
  `usage_id` int(11) NOT NULL DEFAULT '0',
  `client_id` int(11) NOT NULL DEFAULT '0',
  `tarif_id` int(11) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `biller_version` int(11) NOT NULL DEFAULT '4',
  PRIMARY KEY (`usage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actual_virtpbx`
--

LOCK TABLES `actual_virtpbx` WRITE;
/*!40000 ALTER TABLE `actual_virtpbx` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `actual_virtpbx` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `adsl_speed`
--

DROP TABLE IF EXISTS `adsl_speed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `adsl_speed` (
  `value` varchar(11) NOT NULL DEFAULT '',
  `name` varchar(11) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adsl_speed`
--

LOCK TABLES `adsl_speed` WRITE;
/*!40000 ALTER TABLE `adsl_speed` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `adsl_speed` VALUES ('128|128','128/128'),('128|160','128/160'),('128|256','128/256'),('256|320','256/320'),('128|512','128/512'),('256|512','256/512'),('256|1024','256/1024'),('768|1024','768/1024'),('256|2048','256/2048'),('512|2048','512/2048'),('768|2048','768/2048'),('256|3072','256/3072'),('768|3072','768/3072'),('512|4096','512/4096'),('512|5120','512/5120'),('768|6144','768/6144'),('768|10000','768/10000'),('1024|15360','1024/15360');
/*!40000 ALTER TABLE `adsl_speed` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `agent_interests`
--

DROP TABLE IF EXISTS `agent_interests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agent_interests` (
  `client_id` int(11) NOT NULL,
  `interest` enum('bills','prebills') NOT NULL,
  `per_bill_sum` decimal(12,2) NOT NULL,
  `per_abon` decimal(12,2) NOT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agent_interests`
--

LOCK TABLES `agent_interests` WRITE;
/*!40000 ALTER TABLE `agent_interests` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `agent_interests` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bik`
--

DROP TABLE IF EXISTS `bik`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bik` (
  `bik` varchar(9) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `corr_acc` varchar(20) NOT NULL DEFAULT '',
  `bank_name` varchar(50) NOT NULL DEFAULT '',
  `bank_city` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`bik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bik`
--

LOCK TABLES `bik` WRITE;
/*!40000 ALTER TABLE `bik` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bik` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bill_monthlyadd`
--

DROP TABLE IF EXISTS `bill_monthlyadd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_monthlyadd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '2004-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `client` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  `amount` varchar(100) NOT NULL DEFAULT '',
  `price` varchar(100) NOT NULL DEFAULT '',
  `period` enum('day','week','month','year','once') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'day',
  `enabled` tinyint(4) DEFAULT '1',
  `date_last_writeoff` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `credit_usd` decimal(7,2) NOT NULL DEFAULT '0.00',
  `status` enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=2054 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_monthlyadd`
--

LOCK TABLES `bill_monthlyadd` WRITE;
/*!40000 ALTER TABLE `bill_monthlyadd` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bill_monthlyadd` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bill_monthlyadd_log`
--

DROP TABLE IF EXISTS `bill_monthlyadd_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_monthlyadd_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_service` int(11) NOT NULL,
  `actual_from` date NOT NULL DEFAULT '2004-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `client` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  `amount` varchar(100) NOT NULL DEFAULT '',
  `price` varchar(100) NOT NULL DEFAULT '',
  `period` enum('day','week','month','year','once') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'day',
  `enabled` tinyint(4) DEFAULT '1',
  `date_last_writeoff` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `credit_usd_del` decimal(7,2) NOT NULL DEFAULT '0.00',
  `status` enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working',
  `who` int(11) NOT NULL,
  `ts` datetime DEFAULT NULL,
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_monthlyadd_log`
--

LOCK TABLES `bill_monthlyadd_log` WRITE;
/*!40000 ALTER TABLE `bill_monthlyadd_log` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bill_monthlyadd_log` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bill_monthlyadd_reference`
--

DROP TABLE IF EXISTS `bill_monthlyadd_reference`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_monthlyadd_reference` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `price` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `only_one_time` tinyint(1) DEFAULT '0',
  `period` enum('day','week','month','year','once') CHARACTER SET utf8 NOT NULL DEFAULT 'day',
  `currency` char(3) COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  `edit_user` int(11) DEFAULT NULL,
  `edit_time` datetime DEFAULT NULL,
  `status` enum('public','special','archive') COLLATE utf8_bin NOT NULL DEFAULT 'public',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_monthlyadd_reference`
--

LOCK TABLES `bill_monthlyadd_reference` WRITE;
/*!40000 ALTER TABLE `bill_monthlyadd_reference` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bill_monthlyadd_reference` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `city`
--

DROP TABLE IF EXISTS `city`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `city` (
  `id` int(10) NOT NULL,
  `name` varchar(50) NOT NULL,
  `country_id` int(11) NOT NULL,
  `connection_point_id` int(11) DEFAULT NULL,
  `voip_number_format` varchar(50) DEFAULT NULL,
  `in_use` int(1) NOT NULL DEFAULT '0',
  `billing_method_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_city__country_id` (`country_id`),
  KEY `fk-city-billing_method` (`billing_method_id`),
  CONSTRAINT `fk-city-billing_method` FOREIGN KEY (`billing_method_id`) REFERENCES `city_billing_methods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_city__country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `city`
--

LOCK TABLES `city` WRITE;
/*!40000 ALTER TABLE `city` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `city` VALUES (49,'Германия',276,82,'49 0000 000-000-000',0,NULL),(361,'Budapest',348,81,'36 1 000-0000',0,NULL),(3621,'LIECS numbers',348,81,'36 21 000-000',0,NULL),(3646,'Miskolc',348,81,'36 46 000-000',0,NULL),(3652,'Debrecen',348,81,'36 52 000-000',0,NULL),(3662,'Szeged',348,81,'36 62 000-000',0,NULL),(3672,'Pécs',348,81,'36 72 000-000',0,NULL),(3696,'Győr',348,81,'36 96 000-000',0,NULL),(7342,'Пермь',643,92,'7 342 000-00-00',0,NULL),(7343,'Екатеринбург',643,95,'7 343 000-00-00',0,NULL),(7347,'Уфа',643,84,'7 347 000-00-00',0,NULL),(7351,'Челябинск',643,90,'7 351 000-00-00',0,NULL),(7383,'Новосибирск',643,94,'7 383 000-00-00',0,NULL),(7473,'Воронеж',643,86,'7 473 000-00-00',0,NULL),(7495,'Москва',643,99,'7 495 000-00-00',1,NULL),(7812,'Санкт-Петербург',643,98,'7 812 000-00-00',0,NULL),(7831,'Нижний Новгород',643,88,'7 831 000-00-00',0,NULL),(7843,'Казань',643,93,'7 843 000-00-00',0,NULL),(7846,'Самара',643,96,'7 846 000-00-00',0,NULL),(7861,'Краснодар',643,97,'7 861 000-00-00',0,NULL),(7863,'Ростов-на-Дону',643,87,'7 863 000-00-00',0,NULL),(74212,'Хабаровск',643,83,'7 4212 00-00-00',0,NULL),(74232,'Владивосток',643,89,'7 4232 00-00-00',0,NULL),(74832,'Брянск',643,85,'7 4832 00-00-00',0,NULL),(78442,'Волгоград',643,91,'7 8442 00-00-00',0,NULL);
/*!40000 ALTER TABLE `city` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `city_billing_methods`
--

DROP TABLE IF EXISTS `city_billing_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `city_billing_methods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `city_billing_methods`
--

LOCK TABLES `city_billing_methods` WRITE;
/*!40000 ALTER TABLE `city_billing_methods` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `city_billing_methods` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_account_options`
--

DROP TABLE IF EXISTS `client_account_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_account_options` (
  `client_account_id` int(11) NOT NULL,
  `option` varchar(150) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`client_account_id`,`option`,`value`),
  CONSTRAINT `client_account_options__account_id` FOREIGN KEY (`client_account_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_account_options`
--

LOCK TABLES `client_account_options` WRITE;
/*!40000 ALTER TABLE `client_account_options` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_account_options` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contact_personal`
--

DROP TABLE IF EXISTS `client_contact_personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contact_personal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-client_contact_type-id` (`type_id`),
  KEY `fk-client_contract-id` (`contract_id`),
  CONSTRAINT `fk-client_contact_type-id` FOREIGN KEY (`type_id`) REFERENCES `client_contact_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-client_contract-id` FOREIGN KEY (`contract_id`) REFERENCES `client_contract` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contact_personal`
--

LOCK TABLES `client_contact_personal` WRITE;
/*!40000 ALTER TABLE `client_contact_personal` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_contact_personal` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contact_type`
--

DROP TABLE IF EXISTS `client_contact_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contact_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-client_contact_type-code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=204 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contact_type`
--

LOCK TABLES `client_contact_type` WRITE;
/*!40000 ALTER TABLE `client_contact_type` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `client_contact_type` VALUES (1,'phone','Телефон'),(2,'email','Email'),(3,'fax','Факс'),(150,'sms','СМС'),(201,'email_invoice','Email - Invoice'),(202,'email_rate','Email - Rate'),(203,'email_support','Email - Support');
/*!40000 ALTER TABLE `client_contact_type` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contacts`
--

DROP TABLE IF EXISTS `client_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `type` enum('email','phone','fax','sms','email_invoice','email_rate','email_support') NOT NULL,
  `data` text NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ts` datetime DEFAULT NULL,
  `comment` text NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `is_official` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `type_data` (`type`,`data`(32),`user_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=104529 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contacts`
--

LOCK TABLES `client_contacts` WRITE;
/*!40000 ALTER TABLE `client_contacts` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_contacts` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contract`
--

DROP TABLE IF EXISTS `client_contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contract` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `super_id` int(11) DEFAULT NULL,
  `contragent_id` int(11) DEFAULT NULL,
  `number` varchar(100) NOT NULL,
  `organization_id` int(11) NOT NULL DEFAULT '0',
  `manager` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `account_manager` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `business_id` tinyint(4) NOT NULL DEFAULT '0',
  `business_process_id` int(11) NOT NULL DEFAULT '0',
  `business_process_status_id` int(11) NOT NULL DEFAULT '0',
  `contract_type_id` tinyint(4) NOT NULL DEFAULT '0',
  `state` enum('unchecked','checked_copy','checked_original','offer') NOT NULL DEFAULT 'unchecked',
  `financial_type` enum('','profitable','consumables','yield-consumable') NOT NULL DEFAULT '',
  `federal_district` set('cfd','sfd','nwfd','dfo','sfo','ufo','pfo') NOT NULL DEFAULT '',
  `is_external` enum('internal','external') NOT NULL DEFAULT 'internal',
  `lk_access` enum('full','readonly','noaccess') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contragent_id` (`contragent_id`),
  KEY `super_id` (`super_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35802 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contract`
--

LOCK TABLES `client_contract` WRITE;
/*!40000 ALTER TABLE `client_contract` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_contract` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contract_business`
--

DROP TABLE IF EXISTS `client_contract_business`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contract_business` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `sort` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contract_business`
--

LOCK TABLES `client_contract_business` WRITE;
/*!40000 ALTER TABLE `client_contract_business` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `client_contract_business` VALUES (2,'Телеком-клиент',2),(3,'Межоператорка',3),(4,'Поставщик',4),(5,'Интернет-магазин',5),(6,'Внутренний офис',6),(7,'Партнер',7),(8,'Welltime Клиент',8),(9,'ИТ-аутсорсинг',9);
/*!40000 ALTER TABLE `client_contract_business` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contract_business_process`
--

DROP TABLE IF EXISTS `client_contract_business_process`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contract_business_process` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_id` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `show_as_status` enum('0','1') NOT NULL DEFAULT '1',
  `sort` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contract_business_process`
--

LOCK TABLES `client_contract_business_process` WRITE;
/*!40000 ALTER TABLE `client_contract_business_process` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `client_contract_business_process` VALUES (1,2,'Сопровождение','1',2),(2,2,'Продажи','0',1),(3,5,'Заказы магазина','1',1),(4,5,'Сопровождение','1',2),(5,4,'Заказы поставщиков','0',1),(6,4,'Сопровождение','1',2),(8,7,'Сопровождение','1',1),(9,1,'Входящие','1',1),(10,6,'Внутренний офис','1',1),(11,3,'Операторы','1',1),(12,3,'Клиенты','1',2),(13,3,'Инфраструктура','1',3),(15,8,'Cопровождение','1',1),(16,2,'Отчеты','0',0),(17,9,'Сопровождение','1',1);
/*!40000 ALTER TABLE `client_contract_business_process` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contract_business_process_status`
--

DROP TABLE IF EXISTS `client_contract_business_process_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contract_business_process_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_process_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `sort` tinyint(4) NOT NULL DEFAULT '0',
  `oldstatus` varchar(20) NOT NULL DEFAULT '',
  `color` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contract_business_process_status`
--

LOCK TABLES `client_contract_business_process_status` WRITE;
/*!40000 ALTER TABLE `client_contract_business_process_status` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `client_contract_business_process_status` VALUES (8,1,'Подключаемые',1,'connecting','#F49AC1'),(9,1,'Включенные',2,'work',''),(10,1,'Отключенные',3,'closed','#FFFFCC'),(15,6,'Действующий',3,'distr','yellow'),(16,4,'Действующий',0,'once','silver'),(19,1,'Заказ услуг',0,'negotiations','#C4DF9B'),(22,1,'Мусор',4,'trash','#a5e934'),(27,1,'Техотказ',5,'tech_deny','#996666'),(28,1,'Отказ',6,'deny','#A0A0A0'),(29,1,'Дубликат',7,'double','#60a0e0'),(30,9,'Заказ магазина',0,'income','#CCFFFF'),(33,3,'Заказ магазина',0,'once','silver'),(34,10,'Внутренний офис',0,'',''),(35,8,'Действующий',1,'',''),(37,11,'Входящий',0,'income','#CCFFFF'),(38,11,'Переговоры',1,'negotiations','#C4DF9B'),(39,11,'Тестирование',2,'testing','#6DCFF6'),(40,11,'Действующий',3,'work',''),(41,11,'Приостановлен',5,'suspended','#C4a3C0'),(42,11,'Расторгнут',6,'closed','#FFFFCC'),(43,11,'Фрод блокировка',7,'blocked','silver'),(44,11,'Отказ',8,'tech_deny','#996666'),(47,12,'Входящий',0,'income','#CCFFFF'),(48,12,'Переговоры',1,'negotiations','#C4DF9B'),(49,12,'Тестирование',2,'testing','#6DCFF6'),(50,12,'Действующий',3,'work',''),(51,12,'Приостановлен',5,'suspended','#C4a3C0'),(52,12,'Расторгнут',6,'closed','#FFFFCC'),(53,12,'Фрод блокировка',7,'blocked','silver'),(54,12,'Отказ',8,'tech_deny','#996666'),(56,12,'JiraSoft',4,'work',''),(62,13,'Входящий',0,'income','#CCFFFF'),(63,13,'Переговоры',1,'negotiations','#C4DF9B'),(64,13,'Тестирование',2,'testing','#6DCFF6'),(65,13,'Действующий',3,'work',''),(66,13,'Приостановлен',5,'suspended','#C4a3C0'),(67,13,'Расторгнут',6,'closed','#FFFFCC'),(68,13,'Фрод блокировка',7,'blocked','silver'),(69,13,'Отказ',8,'tech_deny','#996666'),(77,14,'Входящий',0,'income','#CCFFFF'),(78,14,'Переговоры',1,'negotiations','#C4DF9B'),(79,14,'Тестирование',2,'testing','#6DCFF6'),(80,14,'Действующий',3,'work',''),(81,14,'Приостановлен',4,'suspended','#C4a3C0'),(82,14,'Расторгнут',5,'closed','#FFFFCC'),(83,14,'Фрод блокировка',6,'blocked','silver'),(84,14,'Отказ',7,'tech_deny','#996666'),(92,6,'Закрытый',4,'closed',''),(93,6,'Самозакупки',5,'distr',''),(94,6,'Разовый',6,'distr',''),(95,15,'Пуско-наладка',0,'connecting',''),(96,15,'Техобслуживание',1,'work',''),(97,15,'Без Техобслуживания',2,'work',''),(98,15,'Приостановленные',3,'suspended',''),(99,15,'Отказ',4,'deny',''),(100,15,'Мусор',5,'trash',''),(107,11,'Ручной счет',4,'','#CCFFFF'),(108,6,'GPON',0,'distr',''),(109,6,'ВОЛС',1,'distr',''),(110,6,'Сервисный',2,'distr',''),(111,10,'Закрытые',1,'',''),(121,11,'Мусор',9,'trash','#996666'),(122,12,'Мусор',9,'trash','#996666'),(123,13,'Мусор',9,'trash','#996666'),(124,14,'Мусор',8,'trash','#996666'),(125,11,'Формальные',4,'',''),(126,8,'Переговоры',0,'',''),(127,8,'Ручной счет',2,'',''),(128,8,'Приостановлен',3,'',''),(129,8,'Расторгнут',4,'',''),(130,8,'Отказ',5,'',''),(131,8,'Мусор',6,'',''),(132,17,'Входящие',0,'income',''),(133,17,'В стадии переговоров',1,'negotiations',''),(134,17,'Проверка документов',2,'connecting',''),(135,17,'Подключаемые',3,'connecting',''),(136,17,'На обслуживании',4,'work',''),(137,17,'Приостановленные',5,'suspended',''),(138,17,'Отказ',6,'tech_deny',''),(139,17,'Мусор',7,'trash',''),(140,13,'Формальные',4,'','');
/*!40000 ALTER TABLE `client_contract_business_process_status` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contract_comment`
--

DROP TABLE IF EXISTS `client_contract_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contract_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `user` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `ts` datetime DEFAULT NULL,
  `is_publish` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_client` (`contract_id`)
) ENGINE=InnoDB AUTO_INCREMENT=117422 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contract_comment`
--

LOCK TABLES `client_contract_comment` WRITE;
/*!40000 ALTER TABLE `client_contract_comment` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_contract_comment` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contract_reward`
--

DROP TABLE IF EXISTS `client_contract_reward`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contract_reward` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contract_id` int(10) unsigned NOT NULL,
  `usage_type` varchar(60) DEFAULT NULL,
  `once_only` smallint(5) unsigned NOT NULL,
  `percentage_of_fee` smallint(5) unsigned NOT NULL,
  `percentage_of_over` smallint(5) unsigned NOT NULL,
  `period_type` enum('always','month') NOT NULL DEFAULT 'always',
  `period_month` tinyint(3) unsigned DEFAULT NULL,
  `actual_from` date NOT NULL DEFAULT '2000-01-01',
  `percentage_of_margin` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `insert_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_id-usage_type-actual_from` (`contract_id`,`usage_type`,`actual_from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contract_reward`
--

LOCK TABLES `client_contract_reward` WRITE;
/*!40000 ALTER TABLE `client_contract_reward` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_contract_reward` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contract_type`
--

DROP TABLE IF EXISTS `client_contract_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contract_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `business_process_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contract_type`
--

LOCK TABLES `client_contract_type` WRITE;
/*!40000 ALTER TABLE `client_contract_type` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `client_contract_type` VALUES (1,'Местное присоединение',11),(2,'Агентский на МГ МН',11),(3,'Присоединение Зоновых сетей',11),(4,'Присоединение МГ-сетей',11),(5,'Присоединение МН-сетей',11),(6,'Присоединение Зоны МСН к МГ-сети Оператора',11),(7,'Присоединение МГ-сети МСН к Зоне Оператора',11),(8,'Межоператорский VoIP',11),(9,'Абонентский на услуги связи',11),(10,'Другой',11),(11,'Размещение',13),(12,'Каналы связи',13),(13,'Кроссировки',13),(14,'Интернет / СПД',13),(15,'Бронирование ресурсов',13),(16,'Выдача ТУ',13),(17,'Аренда ресурсов',13),(18,'Абонентский на услуги связи',12),(19,'Присоединение сетей',12),(20,'Местное присоединение',12),(21,'Агентский на МГ МН',12),(22,'Другой',12),(23,'Другой',13),(24,'Договор на СОРМ',13),(25,'Разовый',8),(26,'Постоянный',8),(27,'Субоператорский',8),(28,'Агентский на 8 800',11),(29,'Клиентский на 8 800',11),(30,'Межоператорский VoIP',12);
/*!40000 ALTER TABLE `client_contract_type` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contragent`
--

DROP TABLE IF EXISTS `client_contragent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contragent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `super_id` int(11) NOT NULL,
  `country_id` int(4) DEFAULT '643',
  `name` varchar(255) NOT NULL,
  `legal_type` enum('person','ip','legal') NOT NULL DEFAULT 'legal',
  `name_full` varchar(255) NOT NULL,
  `address_jur` varchar(255) NOT NULL DEFAULT '',
  `inn` varchar(16) NOT NULL DEFAULT '',
  `inn_euro` varchar(16) NOT NULL DEFAULT '',
  `kpp` varchar(16) NOT NULL DEFAULT '',
  `position` varchar(128) NOT NULL DEFAULT '',
  `fio` varchar(128) NOT NULL DEFAULT '',
  `positionV` varchar(128) NOT NULL DEFAULT '',
  `fioV` varchar(128) NOT NULL DEFAULT '',
  `signer_passport` varchar(20) NOT NULL DEFAULT '',
  `tax_regime` enum('undefined','OCH-VAT18','YCH-VAT0') NOT NULL DEFAULT 'OCH-VAT18',
  `opf_id` int(11) NOT NULL DEFAULT '0',
  `okpo` varchar(16) NOT NULL DEFAULT '',
  `okvd` varchar(16) NOT NULL DEFAULT '',
  `ogrn` varchar(16) NOT NULL,
  `comment` text NOT NULL,
  `sale_channel_id` int(10) unsigned NOT NULL DEFAULT '0',
  `partner_contract_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `super_client_id` (`super_id`)
) ENGINE=InnoDB AUTO_INCREMENT=79289 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contragent`
--

LOCK TABLES `client_contragent` WRITE;
/*!40000 ALTER TABLE `client_contragent` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_contragent` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_contragent_person`
--

DROP TABLE IF EXISTS `client_contragent_person`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contragent_person` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `contragent_id` int(11) NOT NULL,
  `last_name` varchar(64) DEFAULT '',
  `first_name` varchar(64) DEFAULT '',
  `middle_name` varchar(64) DEFAULT '',
  `passport_date_issued` date DEFAULT '1970-01-02',
  `passport_serial` varchar(6) DEFAULT '',
  `passport_number` varchar(10) DEFAULT '',
  `passport_issued` varchar(1024) DEFAULT '',
  `registration_address` varchar(255) DEFAULT '',
  `mother_maiden_name` varchar(64) DEFAULT NULL,
  `birthplace` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `other_document` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contragent_id` (`contragent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2309 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_contragent_person`
--

LOCK TABLES `client_contragent_person` WRITE;
/*!40000 ALTER TABLE `client_contragent_person` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_contragent_person` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_counters`
--

DROP TABLE IF EXISTS `client_counters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_counters` (
  `client_id` int(11) NOT NULL,
  `amount_sum` decimal(12,2) NOT NULL,
  `amount_day_sum` decimal(12,2) NOT NULL,
  `amount_month_sum` decimal(12,2) NOT NULL,
  `subscription_rt_balance` decimal(12,2) NOT NULL,
  `subscription_rt_last_month` decimal(12,2) NOT NULL,
  `subscription_rt` decimal(12,2) NOT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_counters`
--

LOCK TABLES `client_counters` WRITE;
/*!40000 ALTER TABLE `client_counters` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_counters` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_document`
--

DROP TABLE IF EXISTS `client_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `contract_no` varchar(100) NOT NULL,
  `contract_date` date NOT NULL,
  `contract_dop_date` date NOT NULL DEFAULT '2012-01-01',
  `contract_dop_no` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ts` datetime DEFAULT NULL,
  `comment` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `type` enum('blank','agreement','contract') NOT NULL DEFAULT 'contract',
  PRIMARY KEY (`id`),
  KEY `client_id` (`contract_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35860 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_document`
--

LOCK TABLES `client_document` WRITE;
/*!40000 ALTER TABLE `client_document` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_document` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_files`
--

DROP TABLE IF EXISTS `client_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ts` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `comment` text NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`contract_id`)
) ENGINE=InnoDB AUTO_INCREMENT=86520 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_files`
--

LOCK TABLES `client_files` WRITE;
/*!40000 ALTER TABLE `client_files` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_files` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_inn`
--

DROP TABLE IF EXISTS `client_inn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_inn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `inn` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ts` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `comment` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1365 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_inn`
--

LOCK TABLES `client_inn` WRITE;
/*!40000 ALTER TABLE `client_inn` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_inn` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_pay_acc`
--

DROP TABLE IF EXISTS `client_pay_acc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_pay_acc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL DEFAULT '0',
  `pay_acc` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `who` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `k_pay_acc` (`pay_acc`),
  KEY `k_client` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=974 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_pay_acc`
--

LOCK TABLES `client_pay_acc` WRITE;
/*!40000 ALTER TABLE `client_pay_acc` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_pay_acc` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_sum_traf`
--

DROP TABLE IF EXISTS `client_sum_traf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_sum_traf` (
  `client` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `traff` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_sum_traf`
--

LOCK TABLES `client_sum_traf` WRITE;
/*!40000 ALTER TABLE `client_sum_traf` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_sum_traf` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_super`
--

DROP TABLE IF EXISTS `client_super`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_super` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `financial_manager_id` int(11) NOT NULL DEFAULT '0',
  `is_lk_exists` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79276 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_super`
--

LOCK TABLES `client_super` WRITE;
/*!40000 ALTER TABLE `client_super` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_super` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `super_id` int(4) NOT NULL DEFAULT '0',
  `contract_id` int(4) NOT NULL DEFAULT '0',
  `country_id` int(4) NOT NULL DEFAULT '643',
  `password` varchar(16) NOT NULL,
  `password_type` enum('plaintext','MD5') NOT NULL DEFAULT 'plaintext',
  `comment` varchar(250) NOT NULL DEFAULT '',
  `status` enum('negotiations','testing','connecting','work','closed','tech_deny','telemarketing','income','deny','debt','double','trash','move','already','denial','once','reserved','suspended','operator','distr','blocked') NOT NULL DEFAULT 'income',
  `usd_rate_percent` decimal(4,1) NOT NULL DEFAULT '0.0',
  `address_post` varchar(128) NOT NULL DEFAULT '',
  `address_post_real` varchar(128) NOT NULL DEFAULT '',
  `support` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `login` varchar(32) NOT NULL DEFAULT '',
  `bik` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `bank_properties` varchar(255) NOT NULL DEFAULT '',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB',
  `currency_bill` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB',
  `stamp` enum('0','1') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0',
  `nal` enum('nal','beznal','prov') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'beznal',
  `telemarketing` varchar(30) NOT NULL DEFAULT '',
  `sale_channel` int(11) NOT NULL,
  `uid` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `site_req_no` varchar(20) NOT NULL DEFAULT '',
  `hid_rtsaldo_date` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `hid_rtsaldo_RUB` decimal(11,2) NOT NULL DEFAULT '0.00',
  `hid_rtsaldo_USD` decimal(11,2) NOT NULL DEFAULT '0.00',
  `credit_USD` int(11) NOT NULL DEFAULT '0',
  `credit_RUB` int(11) NOT NULL DEFAULT '0',
  `credit` int(11) NOT NULL DEFAULT '-1',
  `user_impersonate` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'client',
  `address_connect` varchar(128) NOT NULL DEFAULT '',
  `phone_connect` varchar(128) NOT NULL DEFAULT '',
  `id_all4net` int(11) NOT NULL DEFAULT '0',
  `dealer_comment` varchar(255) NOT NULL DEFAULT '',
  `form_type` enum('manual','payment','bill') NOT NULL DEFAULT 'manual',
  `metro_id` int(4) NOT NULL DEFAULT '0',
  `previous_reincarnation` int(11) DEFAULT NULL,
  `cli_1c` varchar(36) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `con_1c` varchar(36) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `corr_acc` varchar(64) DEFAULT NULL,
  `pay_acc` varchar(64) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_city` varchar(255) DEFAULT NULL,
  `sync_1c` enum('no','yes') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  `price_type` varchar(60) DEFAULT '',
  `voip_credit_limit` int(11) NOT NULL DEFAULT '0',
  `voip_disabled` int(1) NOT NULL DEFAULT '0',
  `voip_credit_limit_day` int(11) NOT NULL DEFAULT '0',
  `balance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `balance_usd` decimal(12,2) NOT NULL DEFAULT '0.00',
  `voip_is_day_calc` int(1) NOT NULL DEFAULT '1',
  `region` smallint(6) DEFAULT '99',
  `last_account_date` datetime DEFAULT NULL,
  `last_payed_voip_month` date DEFAULT NULL,
  `mail_print` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'yes',
  `mail_who` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `head_company` varchar(255) NOT NULL DEFAULT '',
  `head_company_address_jur` varchar(255) NOT NULL DEFAULT '',
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `bill_rename1` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'no',
  `nds_calc_method` tinyint(4) NOT NULL DEFAULT '1',
  `admin_contact_id` int(11) NOT NULL DEFAULT '0',
  `admin_is_active` tinyint(4) NOT NULL DEFAULT '1',
  `is_agent` varchar(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'N',
  `is_bill_only_contract` smallint(1) NOT NULL DEFAULT '0',
  `is_bill_with_refund` smallint(1) NOT NULL DEFAULT '0',
  `is_with_consignee` smallint(1) NOT NULL DEFAULT '0',
  `consignee` varchar(255) NOT NULL,
  `is_upd_without_sign` smallint(1) NOT NULL DEFAULT '0',
  `price_include_vat` tinyint(1) DEFAULT '1',
  `is_active` tinyint(4) NOT NULL DEFAULT '1',
  `is_blocked` tinyint(4) NOT NULL DEFAULT '0',
  `is_closed` tinyint(4) NOT NULL DEFAULT '0',
  `timezone_name` varchar(50) NOT NULL DEFAULT 'Europe/Moscow',
  `lk_balance_view_mode` enum('old','new') NOT NULL DEFAULT 'old',
  `anti_fraud_disabled` tinyint(4) NOT NULL DEFAULT '0',
  `site_name` varchar(128) NOT NULL DEFAULT '',
  `account_version` int(1) unsigned DEFAULT '4',
  `is_postpaid` int(11) NOT NULL DEFAULT '0',
  `voip_limit_mn_day` int(11) NOT NULL DEFAULT '0',
  `voip_is_mn_day_calc` int(1) NOT NULL DEFAULT '1',
  `type_of_bill` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_1c_uk` (`cli_1c`,`con_1c`),
  KEY `client` (`client`),
  KEY `status` (`status`),
  KEY `super_id` (`super_id`),
  KEY `contract_id` (`contract_id`),
  KEY `clients__is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=35800 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `clients_contracts_yota`
--

DROP TABLE IF EXISTS `clients_contracts_yota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients_contracts_yota` (
  `client_id` int(10) unsigned NOT NULL,
  `json_data` text,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients_contracts_yota`
--

LOCK TABLES `clients_contracts_yota` WRITE;
/*!40000 ALTER TABLE `clients_contracts_yota` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `clients_contracts_yota` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `code_opf`
--

DROP TABLE IF EXISTS `code_opf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `code_opf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `code_opf`
--

LOCK TABLES `code_opf` WRITE;
/*!40000 ALTER TABLE `code_opf` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `code_opf` VALUES (1,'1 22 47','Публичные акционерные общества'),(2,'1 23 00','Общества с ограниченной ответственностью'),(3,'5 01 02','Индивидуальные предприниматели'),(4,'7 04 00','Фонды'),(5,'7 50 00','Учреждения');
/*!40000 ALTER TABLE `code_opf` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `contract`
--

DROP TABLE IF EXISTS `contract`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contract` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` enum('blank','agreement','contract') NOT NULL DEFAULT 'contract',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contract`
--

LOCK TABLES `contract` WRITE;
/*!40000 ALTER TABLE `contract` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `contract` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `core_sync_ids`
--

DROP TABLE IF EXISTS `core_sync_ids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_sync_ids` (
  `id` int(4) NOT NULL,
  `type` enum('account','contragent','admin_email','super_client') NOT NULL DEFAULT 'account',
  `external_id` varchar(32) NOT NULL,
  KEY `type_id` (`id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_sync_ids`
--

LOCK TABLES `core_sync_ids` WRITE;
/*!40000 ALTER TABLE `core_sync_ids` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `core_sync_ids` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `counter_interop_trunk`
--

DROP TABLE IF EXISTS `counter_interop_trunk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `counter_interop_trunk` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `income_sum` decimal(12,2) DEFAULT NULL,
  `outcome_sum` decimal(12,2) DEFAULT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `counter_interop_trunk`
--

LOCK TABLES `counter_interop_trunk` WRITE;
/*!40000 ALTER TABLE `counter_interop_trunk` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `counter_interop_trunk` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `country`
--

DROP TABLE IF EXISTS `country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `country` (
  `code` int(4) NOT NULL DEFAULT '0',
  `alpha_3` varchar(3) DEFAULT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `in_use` tinyint(4) NOT NULL DEFAULT '0',
  `lang` varchar(5) DEFAULT 'ru-RU',
  `currency_id` char(3) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `prefix` int(11) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `order` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`code`),
  KEY `in_use` (`in_use`),
  KEY `fk-country-currency_id` (`currency_id`),
  CONSTRAINT `fk-country-currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `country`
--

LOCK TABLES `country` WRITE;
/*!40000 ALTER TABLE `country` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `country` VALUES (4,'AFG','АФГАНИСТАН',0,'ru-RU',NULL,NULL,NULL,0),(8,'ALB','АЛБАНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(10,'ATA','АНТАРКТИДА',0,'ru-RU',NULL,NULL,NULL,0),(12,'DZA','АЛЖИР',0,'ru-RU',NULL,NULL,NULL,0),(16,'ASM','АМЕРИКАНСКОЕ САМОА',0,'ru-RU',NULL,NULL,NULL,0),(20,'AND','АНДОРРА',0,'ru-RU',NULL,NULL,NULL,0),(24,'AGO','АНГОЛА',0,'ru-RU',NULL,NULL,NULL,0),(28,'ATG','АНТИГУА И БАРБУДА',0,'ru-RU',NULL,NULL,NULL,0),(31,'AZE','АЗЕРБАЙДЖАН',0,'ru-RU',NULL,NULL,NULL,0),(32,'ARG','АРГЕНТИНА',0,'ru-RU',NULL,NULL,NULL,0),(36,'AUS','АВСТРАЛИЯ',0,'ru-RU',NULL,NULL,NULL,0),(40,'AUT','АВСТРИЯ',0,'ru-RU',NULL,NULL,NULL,0),(44,'BHS','БАГАМЫ',0,'ru-RU',NULL,NULL,NULL,0),(48,'BHR','БАХРЕЙН',0,'ru-RU',NULL,NULL,NULL,0),(50,'BGD','БАНГЛАДЕШ',0,'ru-RU',NULL,NULL,NULL,0),(51,'ARM','АРМЕНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(52,'BRB','БАРБАДОС',0,'ru-RU',NULL,NULL,NULL,0),(56,'BEL','БЕЛЬГИЯ',0,'ru-RU',NULL,NULL,NULL,0),(60,'BMU','БЕРМУДЫ',0,'ru-RU',NULL,NULL,NULL,0),(64,'BTN','БУТАН',0,'ru-RU',NULL,NULL,NULL,0),(68,'BOL','БОЛИВИЯ',0,'ru-RU',NULL,NULL,NULL,0),(70,'BIH','БОСНИЯ И ГЕРЦЕГОВИНА',0,'ru-RU',NULL,NULL,NULL,0),(72,'BWA','БОТСВАНА',0,'ru-RU',NULL,NULL,NULL,0),(74,'BVT','ОСТРОВ БУВЕ',0,'ru-RU',NULL,NULL,NULL,0),(76,'BRA','БРАЗИЛИЯ',0,'ru-RU',NULL,NULL,NULL,0),(84,'BLZ','БЕЛИЗ',0,'ru-RU',NULL,NULL,NULL,0),(86,'IOT','БРИТАНСКАЯ ТЕРРИТОРИЯ В ИНДИЙСКОМ ОКЕАНЕ',0,'ru-RU',NULL,NULL,NULL,0),(90,'SLB','СОЛОМОНОВЫ ОСТРОВА',0,'ru-RU',NULL,NULL,NULL,0),(92,'VGB','ВИРГИНСКИЕ ОСТРОВА, БРИТАНСКИЕ',0,'ru-RU',NULL,NULL,NULL,0),(96,'BRN','БРУНЕЙ - ДАРУССАЛАМ',0,'ru-RU',NULL,NULL,NULL,0),(100,'BGR','БОЛГАРИЯ',0,'ru-RU',NULL,NULL,NULL,0),(104,'MMR','МЬЯНМА',0,'ru-RU',NULL,NULL,NULL,0),(108,'BDI','БУРУНДИ',0,'ru-RU',NULL,NULL,NULL,0),(112,'BLR','БЕЛАРУСЬ',0,'ru-RU',NULL,NULL,NULL,0),(116,'KHM','КАМБОДЖА',0,'ru-RU',NULL,NULL,NULL,0),(120,'CMR','КАМЕРУН',0,'ru-RU',NULL,NULL,NULL,0),(124,'CAN','КАНАДА',0,'ru-RU',NULL,NULL,NULL,0),(132,'CPV','КАБО - ВЕРДЕ',0,'ru-RU',NULL,NULL,NULL,0),(136,'CYM','ОСТРОВА КАЙМАН',0,'ru-RU',NULL,NULL,NULL,0),(140,'CAF','ЦЕНТРАЛЬНО - АФРИКАНСКАЯ РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(144,'LKA','ШРИ - ЛАНКА',0,'ru-RU',NULL,NULL,NULL,0),(148,'TCD','ЧАД',0,'ru-RU',NULL,NULL,NULL,0),(152,'CHL','ЧИЛИ',0,'ru-RU',NULL,NULL,NULL,0),(156,'CHN','КИТАЙ',0,'ru-RU',NULL,NULL,NULL,0),(158,'TWN','ТАЙВАНЬ (КИТАЙ)',0,'ru-RU',NULL,NULL,NULL,0),(162,'CXR','ОСТРОВ РОЖДЕСТВА',0,'ru-RU',NULL,NULL,NULL,0),(166,'CCK','КОКОСОВЫЕ (КИЛИНГ) ОСТРОВА',0,'ru-RU',NULL,NULL,NULL,0),(170,'COL','КОЛУМБИЯ',0,'ru-RU',NULL,NULL,NULL,0),(174,'COM','КОМОРЫ',0,'ru-RU',NULL,NULL,NULL,0),(175,'MYT','МАЙОТТА',0,'ru-RU',NULL,NULL,NULL,0),(178,'COG','КОНГО',0,'ru-RU',NULL,NULL,NULL,0),(180,'COD','КОНГО, ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(184,'COK','ОСТРОВА КУКА',0,'ru-RU',NULL,NULL,NULL,0),(188,'CRI','КОСТА - РИКА',0,'ru-RU',NULL,NULL,NULL,0),(191,'HRV','ХОРВАТИЯ',0,'ru-RU',NULL,NULL,NULL,0),(192,'CUB','КУБА',0,'ru-RU',NULL,NULL,NULL,0),(196,'CYP','КИПР',0,'ru-RU',NULL,NULL,NULL,0),(203,'CZE','ЧЕШСКАЯ РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(204,'BEN','БЕНИН',0,'ru-RU',NULL,NULL,NULL,0),(208,'DNK','ДАНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(212,'DMA','ДОМИНИКА',0,'ru-RU',NULL,NULL,NULL,0),(214,'DOM','ДОМИНИКАНСКАЯ РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(218,'ECU','ЭКВАДОР',0,'ru-RU',NULL,NULL,NULL,0),(222,'SLV','САЛЬВАДОР',0,'ru-RU',NULL,NULL,NULL,0),(226,'GNQ','ЭКВАТОРИАЛЬНАЯ ГВИНЕЯ',0,'ru-RU',NULL,NULL,NULL,0),(231,'ETH','ЭФИОПИЯ',0,'ru-RU',NULL,NULL,NULL,0),(232,'ERI','ЭРИТРЕЯ',0,'ru-RU',NULL,NULL,NULL,0),(233,'EST','ЭСТОНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(234,'FRO','ФАРЕРСКИЕ ОСТРОВА',0,'ru-RU',NULL,NULL,NULL,0),(238,'FLK','ФОЛКЛЕНДСКИЕ ОСТРОВА (МАЛЬВИНСКИЕ)',0,'ru-RU',NULL,NULL,NULL,0),(239,'SGS','ЮЖНАЯ ДЖОРДЖИЯ И ЮЖНЫЕ САНДВИЧЕВЫ ОСТРОВА',0,'ru-RU',NULL,NULL,NULL,0),(242,'FJI','ФИДЖИ',0,'ru-RU',NULL,NULL,NULL,0),(246,'FIN','ФИНЛЯНДИЯ',0,'ru-RU',NULL,NULL,NULL,0),(250,'FRA','ФРАНЦИЯ',0,'ru-RU',NULL,NULL,NULL,0),(254,'GUF','ФРАНЦУЗСКАЯ ГВИАНА',0,'ru-RU',NULL,NULL,NULL,0),(258,'PYF','ФРАНЦУЗСКАЯ ПОЛИНЕЗИЯ',0,'ru-RU',NULL,NULL,NULL,0),(260,'ATF','ФРАНЦУЗСКИЕ ЮЖНЫЕ ТЕРРИТОРИИ',0,'ru-RU',NULL,NULL,NULL,0),(262,'DJI','ДЖИБУТИ',0,'ru-RU',NULL,NULL,NULL,0),(266,'GAB','ГАБОН',0,'ru-RU',NULL,NULL,NULL,0),(268,'GEO','ГРУЗИЯ',0,'ru-RU',NULL,NULL,NULL,0),(270,'GMB','ГАМБИЯ',0,'ru-RU',NULL,NULL,NULL,0),(275,'PSE','ПАЛЕСТИНСКАЯ ТЕРРИТОРИЯ, ОККУПИРОВАННАЯ',0,'ru-RU',NULL,NULL,NULL,0),(276,'DEU','ГЕРМАНИЯ',1,'de-DE','EUR',49,NULL,3),(288,'GHA','ГАНА',0,'ru-RU',NULL,NULL,NULL,0),(292,'GIB','ГИБРАЛТАР',0,'ru-RU',NULL,NULL,NULL,0),(296,'KIR','КИРИБАТИ',0,'ru-RU',NULL,NULL,NULL,0),(300,'GRC','ГРЕЦИЯ',0,'ru-RU',NULL,NULL,NULL,0),(304,'GRL','ГРЕНЛАНДИЯ',0,'ru-RU',NULL,NULL,NULL,0),(308,'GRD','ГРЕНАДА',0,'ru-RU',NULL,NULL,NULL,0),(312,'GLP','ГВАДЕЛУПА',0,'ru-RU',NULL,NULL,NULL,0),(316,'GUM','ГУАМ',0,'ru-RU',NULL,NULL,NULL,0),(320,'GTM','ГВАТЕМАЛА',0,'ru-RU',NULL,NULL,NULL,0),(324,'GIN','ГВИНЕЯ',0,'ru-RU',NULL,NULL,NULL,0),(328,'GUY','ГАЙАНА',0,'ru-RU',NULL,NULL,NULL,0),(332,'HTI','ГАИТИ',0,'ru-RU',NULL,NULL,NULL,0),(334,'HMD','ОСТРОВ ХЕРД И ОСТРОВА МАКДОНАЛЬД',0,'ru-RU',NULL,NULL,NULL,0),(336,'VAT','ПАПСКИЙ ПРЕСТОЛ (ГОСУДАРСТВО - ГОРОД ВАТИКАН)',0,'ru-RU',NULL,NULL,NULL,0),(340,'HND','ГОНДУРАС',0,'ru-RU',NULL,NULL,NULL,0),(344,'HKG','ГОНКОНГ',0,'ru-RU',NULL,NULL,NULL,0),(348,'HUN','ВЕНГРИЯ',1,'hu-HU','HUF',36,NULL,2),(352,'ISL','ИСЛАНДИЯ',0,'ru-RU',NULL,NULL,NULL,0),(356,'IND','ИНДИЯ',0,'ru-RU',NULL,NULL,NULL,0),(360,'IDN','ИНДОНЕЗИЯ',0,'ru-RU',NULL,NULL,NULL,0),(364,'IRN','ИРАН, ИСЛАМСКАЯ РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(368,'IRQ','ИРАК',0,'ru-RU',NULL,NULL,NULL,0),(372,'IRL','ИРЛАНДИЯ',0,'ru-RU',NULL,NULL,NULL,0),(376,'ISR','ИЗРАИЛЬ',0,'ru-RU',NULL,NULL,NULL,0),(380,'ITA','ИТАЛИЯ',0,'ru-RU',NULL,NULL,NULL,0),(384,'CIV','КОТ Д\'ИВУАР',0,'ru-RU',NULL,NULL,NULL,0),(388,'JAM','ЯМАЙКА',0,'ru-RU',NULL,NULL,NULL,0),(392,'JPN','ЯПОНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(398,'KAZ','КАЗАХСТАН',0,'ru-RU',NULL,NULL,NULL,0),(400,'JOR','ИОРДАНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(404,'KEN','КЕНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(408,'PRK','КОРЕЯ, НАРОДНО - ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(410,'KOR','КОРЕЯ, РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(414,'KWT','КУВЕЙТ',0,'ru-RU',NULL,NULL,NULL,0),(417,'KGZ','КИРГИЗИЯ',0,'ru-RU',NULL,NULL,NULL,0),(418,'LAO','ЛАОССКАЯ НАРОДНО - ДЕМОКРАТИЧЕСКАЯ РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(422,'LBN','ЛИВАН',0,'ru-RU',NULL,NULL,NULL,0),(426,'LSO','ЛЕСОТО',0,'ru-RU',NULL,NULL,NULL,0),(428,'LVA','ЛАТВИЯ',0,'ru-RU',NULL,NULL,NULL,0),(430,'LBR','ЛИБЕРИЯ',0,'ru-RU',NULL,NULL,NULL,0),(434,'LBY','ЛИВИЙСКАЯ АРАБСКАЯ ДЖАМАХИРИЯ',0,'ru-RU',NULL,NULL,NULL,0),(438,'LIE','ЛИХТЕНШТЕЙН',0,'ru-RU',NULL,NULL,NULL,0),(440,'LTU','ЛИТВА',0,'ru-RU',NULL,NULL,NULL,0),(442,'LUX','ЛЮКСЕМБУРГ',0,'ru-RU',NULL,NULL,NULL,0),(446,'MAC','МАКАО',0,'ru-RU',NULL,NULL,NULL,0),(450,'MDG','МАДАГАСКАР',0,'ru-RU',NULL,NULL,NULL,0),(454,'MWI','МАЛАВИ',0,'ru-RU',NULL,NULL,NULL,0),(458,'MYS','МАЛАЙЗИЯ',0,'ru-RU',NULL,NULL,NULL,0),(462,'MDV','МАЛЬДИВЫ',0,'ru-RU',NULL,NULL,NULL,0),(466,'MLI','МАЛИ',0,'ru-RU',NULL,NULL,NULL,0),(470,'MLT','МАЛЬТА',0,'ru-RU',NULL,NULL,NULL,0),(474,'MTQ','МАРТИНИКА',0,'ru-RU',NULL,NULL,NULL,0),(478,'MRT','МАВРИТАНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(480,'MUS','МАВРИКИЙ',0,'ru-RU',NULL,NULL,NULL,0),(484,'MEX','МЕКСИКА',0,'ru-RU',NULL,NULL,NULL,0),(492,'MCO','МОНАКО',0,'ru-RU',NULL,NULL,NULL,0),(496,'MNG','МОНГОЛИЯ',0,'ru-RU',NULL,NULL,NULL,0),(498,'MDA','МОЛДОВА, РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(500,'MSR','МОНТСЕРРАТ',0,'ru-RU',NULL,NULL,NULL,0),(504,'MAR','МАРОККО',0,'ru-RU',NULL,NULL,NULL,0),(508,'MOZ','МОЗАМБИК',0,'ru-RU',NULL,NULL,NULL,0),(512,'OMN','ОМАН',0,'ru-RU',NULL,NULL,NULL,0),(516,'NAM','НАМИБИЯ',0,'ru-RU',NULL,NULL,NULL,0),(520,'NRU','НАУРУ',0,'ru-RU',NULL,NULL,NULL,0),(524,'NPL','НЕПАЛ',0,'ru-RU',NULL,NULL,NULL,0),(528,'NLD','НИДЕРЛАНДЫ',0,'ru-RU',NULL,NULL,NULL,0),(530,'ANT','НИДЕРЛАНДСКИЕ АНТИЛЫ',0,'ru-RU',NULL,NULL,NULL,0),(533,'ABW','АРУБА',0,'ru-RU',NULL,NULL,NULL,0),(540,'NCL','НОВАЯ КАЛЕДОНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(548,'VUT','ВАНУАТУ',0,'ru-RU',NULL,NULL,NULL,0),(554,'NZL','НОВАЯ ЗЕЛАНДИЯ',0,'ru-RU',NULL,NULL,NULL,0),(558,'NIC','НИКАРАГУА',0,'ru-RU',NULL,NULL,NULL,0),(562,'NER','НИГЕР',0,'ru-RU',NULL,NULL,NULL,0),(566,'NGA','НИГЕРИЯ',0,'ru-RU',NULL,NULL,NULL,0),(570,'NIU','НИУЭ',0,'ru-RU',NULL,NULL,NULL,0),(574,'NFK','ОСТРОВ НОРФОЛК',0,'ru-RU',NULL,NULL,NULL,0),(578,'NOR','НОРВЕГИЯ',0,'ru-RU',NULL,NULL,NULL,0),(580,'MNP','СЕВЕРНЫЕ МАРИАНСКИЕ ОСТРОВА',0,'ru-RU',NULL,NULL,NULL,0),(581,'UMI','МАЛЫЕ ТИХООКЕАНСКИЕ ОТДАЛЕННЫЕ ОСТРОВА СОЕДИНЕННЫХ ШТАТОВ',0,'ru-RU',NULL,NULL,NULL,0),(583,'FSM','МИКРОНЕЗИЯ, ФЕДЕРАТИВНЫЕ ШТАТЫ',0,'ru-RU',NULL,NULL,NULL,0),(584,'MHL','МАРШАЛЛОВЫ ОСТРОВА',0,'ru-RU',NULL,NULL,NULL,0),(585,'PLW','ПАЛАУ',0,'ru-RU',NULL,NULL,NULL,0),(586,'PAK','ПАКИСТАН',0,'ru-RU',NULL,NULL,NULL,0),(591,'PAN','ПАНАМА',0,'ru-RU',NULL,NULL,NULL,0),(598,'PNG','ПАПУА - НОВАЯ ГВИНЕЯ',0,'ru-RU',NULL,NULL,NULL,0),(600,'PRY','ПАРАГВАЙ',0,'ru-RU',NULL,NULL,NULL,0),(604,'PER','ПЕРУ',0,'ru-RU',NULL,NULL,NULL,0),(608,'PHL','ФИЛИППИНЫ',0,'ru-RU',NULL,NULL,NULL,0),(612,'PCN','ПИТКЕРН',0,'ru-RU',NULL,NULL,NULL,0),(616,'POL','ПОЛЬША',0,'ru-RU',NULL,NULL,NULL,0),(620,'PRT','ПОРТУГАЛИЯ',0,'ru-RU',NULL,NULL,NULL,0),(624,'GNB','ГВИНЕЯ - БИСАУ',0,'ru-RU',NULL,NULL,NULL,0),(626,'TLS','ВОСТОЧНЫЙ ТИМОР',0,'ru-RU',NULL,NULL,NULL,0),(630,'PRI','ПУЭРТО - РИКО',0,'ru-RU',NULL,NULL,NULL,0),(634,'QAT','КАТАР',0,'ru-RU',NULL,NULL,NULL,0),(638,'REU','РЕЮНЬОН',0,'ru-RU',NULL,NULL,NULL,0),(642,'ROU','РУМЫНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(643,'RUS','РОССИЯ',1,'ru-RU','RUB',7,NULL,1),(646,'RWA','РУАНДА',0,'ru-RU',NULL,NULL,NULL,0),(654,'SHN','СВЯТАЯ ЕЛЕНА',0,'ru-RU',NULL,NULL,NULL,0),(659,'KNA','СЕНТ - КИТС И НЕВИС',0,'ru-RU',NULL,NULL,NULL,0),(660,'AIA','АНГИЛЬЯ',0,'ru-RU',NULL,NULL,NULL,0),(662,'LCA','СЕНТ - ЛЮСИЯ',0,'ru-RU',NULL,NULL,NULL,0),(666,'SPM','СЕН - ПЬЕР И МИКЕЛОН',0,'ru-RU',NULL,NULL,NULL,0),(670,'VCT','СЕНТ - ВИНСЕНТ И ГРЕНАДИНЫ',0,'ru-RU',NULL,NULL,NULL,0),(674,'SMR','САН - МАРИНО',0,'ru-RU',NULL,NULL,NULL,0),(678,'STP','САН - ТОМЕ И ПРИНСИПИ',0,'ru-RU',NULL,NULL,NULL,0),(682,'SAU','САУДОВСКАЯ АРАВИЯ',0,'ru-RU',NULL,NULL,NULL,0),(686,'SEN','СЕНЕГАЛ',0,'ru-RU',NULL,NULL,NULL,0),(690,'SYC','СЕЙШЕЛЫ',0,'ru-RU',NULL,NULL,NULL,0),(694,'SLE','СЬЕРРА - ЛЕОНЕ',0,'ru-RU',NULL,NULL,NULL,0),(702,'SGP','СИНГАПУР',0,'ru-RU',NULL,NULL,NULL,0),(703,'SVK','Slovensko',1,'sk-SK','EUR',42,NULL,4),(704,'VNM','ВЬЕТНАМ',0,'ru-RU',NULL,NULL,NULL,0),(705,'SVN','СЛОВЕНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(706,'SOM','СОМАЛИ',0,'ru-RU',NULL,NULL,NULL,0),(710,'ZAF','ЮЖНАЯ АФРИКА',0,'ru-RU',NULL,NULL,NULL,0),(716,'ZWE','ЗИМБАБВЕ',0,'ru-RU',NULL,NULL,NULL,0),(724,'ESP','ИСПАНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(732,'ESH','ЗАПАДНАЯ САХАРА',0,'ru-RU',NULL,NULL,NULL,0),(740,'SUR','СУРИНАМ',0,'ru-RU',NULL,NULL,NULL,0),(744,'SJM','ШПИЦБЕРГЕН И ЯН МАЙЕН',0,'ru-RU',NULL,NULL,NULL,0),(748,'SWZ','СВАЗИЛЕНД',0,'ru-RU',NULL,NULL,NULL,0),(752,'SWE','ШВЕЦИЯ',0,'ru-RU',NULL,NULL,NULL,0),(756,'CHE','ШВЕЙЦАРИЯ',0,'ru-RU',NULL,NULL,NULL,0),(760,'SYR','СИРИЙСКАЯ АРАБСКАЯ РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(762,'TJK','ТАДЖИКИСТАН',0,'ru-RU',NULL,NULL,NULL,0),(764,'THA','ТАИЛАНД',0,'ru-RU',NULL,NULL,NULL,0),(768,'TGO','ТОГО',0,'ru-RU',NULL,NULL,NULL,0),(772,'TKL','ТОКЕЛАУ',0,'ru-RU',NULL,NULL,NULL,0),(776,'TON','ТОНГА',0,'ru-RU',NULL,NULL,NULL,0),(780,'TTO','ТРИНИДАД И ТОБАГО',0,'ru-RU',NULL,NULL,NULL,0),(784,'ARE','ОБЪЕДИНЕННЫЕ АРАБСКИЕ ЭМИРАТЫ',0,'ru-RU',NULL,NULL,NULL,0),(788,'TUN','ТУНИС',0,'ru-RU',NULL,NULL,NULL,0),(792,'TUR','ТУРЦИЯ',0,'ru-RU',NULL,NULL,NULL,0),(795,'TKM','ТУРКМЕНИЯ',0,'ru-RU',NULL,NULL,NULL,0),(796,'TCA','ОСТРОВА ТЕРКС И КАЙКОС',0,'ru-RU',NULL,NULL,NULL,0),(798,'TUV','ТУВАЛУ',0,'ru-RU',NULL,NULL,NULL,0),(800,'UGA','УГАНДА',0,'ru-RU',NULL,NULL,NULL,0),(804,'UKR','УКРАИНА',0,'ru-RU',NULL,NULL,NULL,0),(807,'MKD','МАКЕДОНИЯ, БЫВШАЯ ЮГОСЛАВСКАЯ РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(818,'EGY','ЕГИПЕТ',0,'ru-RU',NULL,NULL,NULL,0),(826,'GBR','СОЕДИНЕННОЕ КОРОЛЕВСТВО',0,'ru-RU',NULL,NULL,NULL,0),(834,'TZA','ТАНЗАНИЯ, ОБЪЕДИНЕННАЯ РЕСПУБЛИКА',0,'ru-RU',NULL,NULL,NULL,0),(840,'USA','СОЕДИНЕННЫЕ ШТАТЫ',0,'ru-RU',NULL,NULL,NULL,0),(850,'VIR','ВИРГИНСКИЕ ОСТРОВА, США',0,'ru-RU',NULL,NULL,NULL,0),(854,'BFA','БУРКИНА - ФАСО',0,'ru-RU',NULL,NULL,NULL,0),(858,'URY','УРУГВАЙ',0,'ru-RU',NULL,NULL,NULL,0),(860,'UZB','УЗБЕКИСТАН',0,'ru-RU',NULL,NULL,NULL,0),(862,'VEN','ВЕНЕСУЭЛА',0,'ru-RU',NULL,NULL,NULL,0),(876,'WLF','УОЛЛИС И ФУТУНА',0,'ru-RU',NULL,NULL,NULL,0),(882,'WSM','САМОА',0,'ru-RU',NULL,NULL,NULL,0),(887,'YEM','ЙЕМЕН',0,'ru-RU',NULL,NULL,NULL,0),(891,'YUG','ЮГОСЛАВИЯ',0,'ru-RU',NULL,NULL,NULL,0),(894,'ZMB','ЗАМБИЯ',0,'ru-RU',NULL,NULL,NULL,0),(895,'---','АБХАЗИЯ',0,'ru-RU',NULL,NULL,NULL,0),(896,'---','ЮЖНАЯ ОСЕТИЯ',0,'ru-RU',NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `country` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `courier`
--

DROP TABLE IF EXISTS `courier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `all4geo` varchar(10) NOT NULL DEFAULT '',
  `is_used` smallint(1) NOT NULL DEFAULT '0',
  `enabled` enum('yes','no') NOT NULL DEFAULT 'yes',
  `depart` varchar(64) NOT NULL DEFAULT 'Курьер' COMMENT 'отдел Курьер/Инженер',
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `courier`
--

LOCK TABLES `courier` WRITE;
/*!40000 ALTER TABLE `courier` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `courier` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `currency`
--

DROP TABLE IF EXISTS `currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currency` (
  `id` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `name` varchar(50) NOT NULL,
  `symbol` varchar(16) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currency`
--

LOCK TABLES `currency` WRITE;
/*!40000 ALTER TABLE `currency` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `currency` VALUES ('EUR','Евро','€'),('HUF','Форинт','Ft.'),('RUB','Российский рубль','руб.'),('USD','Доллар США','$');
/*!40000 ALTER TABLE `currency` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `currency_rate`
--

DROP TABLE IF EXISTS `currency_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currency_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL DEFAULT '1970-01-02',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  `rate` decimal(10,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`),
  KEY `date` (`date`,`currency`)
) ENGINE=InnoDB AUTO_INCREMENT=8408 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currency_rate`
--

LOCK TABLES `currency_rate` WRITE;
/*!40000 ALTER TABLE `currency_rate` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `currency_rate` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `datacenter`
--

DROP TABLE IF EXISTS `datacenter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `datacenter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) DEFAULT NULL,
  `address` varchar(256) DEFAULT NULL,
  `comment` varchar(256) DEFAULT NULL,
  `region` int(4) NOT NULL DEFAULT '99',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `datacenter`
--

LOCK TABLES `datacenter` WRITE;
/*!40000 ALTER TABLE `datacenter` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `datacenter` VALUES (4,'Москва','г. Москва, Бутлерова д.7','ММТС-9',99),(5,'Краснодарская','г. Краснодар, Рашпилевская 22','стойка 25',97),(6,'Новосибирская','г. Новосибирск, ул Серебренниковская д 14','',94),(7,'Санкт-Петербург','г. Санкт-Петербург ул.Большая Морская, 18, к.124','',98),(8,'Екатеринбург','г. Екатеринбург ул. Сыромолотова, 27, ЛАЦ, АТС347/348, 2э, п124','',95),(9,'Самара','г. Самара ул. Ново-Вокзальная, д.112А, ОПТС-93/95, 1э, к.1, р.2, м.2','',96),(10,'Ростов-на-Дону','г.Ростов-на-Дону, ул.Волкова, 9, клиентский зал, 4 этаж, ряд 2, место 1. и 1U на АТС-233, 3 этаж, 23 ряд.','',87),(11,'Казань','г. Казань ул.Лаврентьева, 3','',93),(12,'Владивосток','г. Владивосток, ул.Пушкинская, 53, ком.507, 22 ряд, место 4','',89),(13,'Hungary, Budapest','Hungary, Budaors, Ipartelep utca','',81),(14,'Нижний новгород','Нижний новгород','',88);
/*!40000 ALTER TABLE `datacenter` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `did_group`
--

DROP TABLE IF EXISTS `did_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `did_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `city_id` int(11) NOT NULL,
  `beauty_level` int(11) NOT NULL DEFAULT '0',
  `number_type_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_did_group__city_id` (`city_id`),
  KEY `fk-number_type_id` (`number_type_id`),
  CONSTRAINT `fk-number_type_id` FOREIGN KEY (`number_type_id`) REFERENCES `voip_number_type` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_did_group__city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `did_group`
--

LOCK TABLES `did_group` WRITE;
/*!40000 ALTER TABLE `did_group` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `did_group` VALUES (1,'Стандартные 495',7495,0,1),(2,'Стандартные 499',7495,0,1),(3,'Платиновые',7495,1,1),(4,'Золотые',7495,2,1),(5,'Серебряные',7495,3,1),(6,'Бронзовые',7495,4,1),(7,'Стандартные',7812,0,1),(8,'Платиновые',7812,1,1),(9,'Золотые',7812,2,1),(10,'Серебряные',7812,3,1),(11,'Бронзовые',7812,4,1),(12,'Стандартные',7861,0,1),(13,'Платиновые',7861,1,1),(14,'Золотые',7861,2,1),(15,'Серебряные',7861,3,1),(16,'Бронзовые',7861,4,1),(17,'Стандартные',7846,0,1),(18,'Платиновые',7846,1,1),(19,'Золотые',7846,2,1),(20,'Серебряные',7846,3,1),(21,'Бронзовые',7846,4,1),(22,'Стандартные',7343,0,1),(23,'Платиновые',7343,1,1),(24,'Золотые',7343,2,1),(25,'Серебряные',7343,3,1),(26,'Бронзовые',7343,4,1),(27,'Стандартные',7383,0,1),(28,'Платиновые',7383,1,1),(29,'Золотые',7383,2,1),(30,'Серебряные',7383,3,1),(31,'Бронзовые',7383,4,1),(32,'Стандартные',7843,0,1),(33,'Платиновые',7843,1,1),(34,'Золотые',7843,2,1),(35,'Серебряные',7843,3,1),(36,'Бронзовые',7843,4,1),(37,'Стандартные',74232,0,1),(38,'Платиновые',74232,1,1),(39,'Золотые',74232,2,1),(40,'Серебряные',74232,3,1),(41,'Бронзовые',74232,4,1),(42,'Стандартные',7831,0,1),(43,'Платиновые',7831,1,1),(44,'Золотые',7831,2,1),(45,'Серебряные',7831,3,1),(46,'Бронзовые',7831,4,1),(47,'Стандартные',7863,0,1),(48,'Платиновые',7863,1,1),(49,'Золотые',7863,2,1),(50,'Серебряные',7863,3,1),(51,'Бронзовые',7863,4,1),(52,'Стандартные',361,0,1),(53,'Стандартные',3646,0,1),(54,'Стандартные',3652,0,1),(55,'Стандартные',3662,0,1),(56,'Стандартные',3672,0,1),(57,'Стандартные',3696,0,1),(58,'Стандартные',3621,0,1),(59,'Платиновые',361,1,1),(60,'Золотые',361,2,1),(61,'Серебряные',361,3,1),(62,'Бронзовые',361,4,1),(63,'Платиновые',3646,1,1),(64,'Золотые',3646,2,1),(65,'Серебряные',3646,3,1),(66,'Бронзовые',3646,4,1),(67,'Платиновые',3652,1,1),(68,'Золотые',3652,2,1),(69,'Серебряные',3652,3,1),(70,'Бронзовые',3652,4,1),(71,'Платиновые',3662,1,1),(72,'Золотые',3662,2,1),(73,'Серебряные',3662,3,1),(74,'Бронзовые',3662,4,1),(75,'Платиновые',3672,1,1),(76,'Золотые',3672,2,1),(77,'Серебряные',3672,3,1),(78,'Бронзовые',3672,4,1),(79,'Платиновые',3696,1,1),(80,'Золотые',3696,2,1),(81,'Серебряные',3696,3,1),(82,'Бронзовые',3696,4,1),(83,'Платиновые',3621,1,1),(84,'Золотые',3621,2,1),(85,'Серебряные',3621,3,1),(86,'Бронзовые',3621,4,1),(87,'Стандартные',49,0,1),(88,'Платиновые',49,1,1),(89,'Золотые',49,2,1),(90,'Серебряные',49,3,1),(91,'Бронзовые',49,4,1),(92,'Стандартные',74212,0,1),(93,'Платиновые',74212,1,1),(94,'Золотые',74212,2,1),(95,'Серебряные',74212,3,1),(96,'Бронзовые',74212,4,1);
/*!40000 ALTER TABLE `did_group` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `document_folder`
--

DROP TABLE IF EXISTS `document_folder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_folder` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `sort` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `default_for_business_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id_default_for_business_id` (`parent_id`,`default_for_business_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_folder`
--

LOCK TABLES `document_folder` WRITE;
/*!40000 ALTER TABLE `document_folder` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `document_folder` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `document_template`
--

DROP TABLE IF EXISTS `document_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `folder_id` int(11) DEFAULT NULL,
  `content` mediumtext NOT NULL,
  `type` enum('contract','agreement','blank') NOT NULL DEFAULT 'contract',
  `sort` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_template`
--

LOCK TABLES `document_template` WRITE;
/*!40000 ALTER TABLE `document_template` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `document_template` VALUES (13,'Zakaz_Uslug',3,'<p><span style=\"font-size: 8pt;\"><strong>Заказ на услуги&nbsp; № <strong>{$contract_dop_no}</strong></strong></span></p>\n<p><span style=\"font-size: 8pt;\"><strong>К договору №{$contract_no} от {$contract_date|mdate:\'\"d\" месяца Y\'} г.</strong></span></p>\n<p><span style=\"font-size: 8pt;\"><strong>заключенному между {$organization_name} и {$name_full}{if $contract_dop_no gt 1}</strong></span></p>\n<p><span style=\"font-size: 8pt;\"><strong>Прекращает действие Заказа №{$contract_dop_no-1}{/if}</strong></span></p>\n<table style=\"width: 100%;\" border=\"1\" cellspacing=\"0\" cellpadding=\"5\">\n<tbody>\n<tr>\n<td>\n<p><span style=\"font-size: 8pt;\"><em>Лицевой счет </em></span></p>\n</td>\n<td colspan=\"3\">\n<p><span style=\"font-size: 8pt;\">{$account_id}</span></p>\n</td>\n</tr>\n<tr>\n<td>\n<p><span style=\"font-size: 8pt;\"><em>Адрес для доставки бухгалтерских документов</em></span></p>\n</td>\n<td colspan=\"3\">\n<p><span style=\"font-size: 8pt;\">{$address_post_real}</span></p>\n</td>\n</tr>\n<tr>\n<td>\n<p><span style=\"font-size: 8pt;\"><em>E-mail для уведомлений и бухгалтерских документов</em></span></p>\n</td>\n<td colspan=\"3\">\n<p><span style=\"font-size: 10.6666669845581px;\">{$emails}</span></p>\n</td>\n</tr>\n<tr>\n<td>\n<p><span style=\"font-size: 8pt;\"><em>Кредитный лимит, руб/мес</em></span></p>\n</td>\n<td colspan=\"3\">\n<p><span style=\"font-size: 8pt;\">{if $credit == -1}-------------{else} {$credit} руб.&nbsp;{/if}</span></p>\n</td>\n</tr>\n</tbody>\n</table>\n<p><span style=\"font-size: 8pt;\"><strong><em>&nbsp;</em></strong></span></p>\n<p><span style=\"font-size: 8pt;\"><strong><em>Параметры Услуги:</em></strong></span></p>\n<p><span style=\"font-size: 8pt;\">&nbsp;&nbsp;&nbsp;{*#blank_zakaz#*}</span></p>\n<p><span style=\"font-size: 8pt;\">&nbsp;</span></p>\n<p><span style=\"font-size: 8pt;\">Услуги связи проверены представителем АБОНЕНТА, функционируют нормально и&nbsp;удовлетворяют требованиям Договора.</span></p>\n<p><span style=\"font-size: 8pt;\">&nbsp;</span></p>\n<table style=\"width: 100%;\">\n<tbody>\n<tr>\n<td>\n<p><span style=\"font-size: 8pt;\">ОПЕРАТОР</span><br /> <br /> <br /> <br /><span style=\"font-size: 8pt;\"> __________________________</span><br /><span style=\"font-size: 8pt;\"> {$organization_director_post} {$organization_director}</span></p>\n</td>\n<td>\n<p><span style=\"font-size: 8pt;\">АБОНЕНТ</span><br /> <br /> <br /> <br /><span style=\"font-size: 8pt;\"> ________________________</span><br /><span style=\"font-size: 8pt;\"> {$position} {$fio}</span></p>\n</td>\n</tr>\n</tbody>\n</table>\n<p><span style=\"font-size: 8pt;\">&nbsp;</span></p>','blank',0),(41,'DC_telefonia',3,'<p style=\"text-align: center;\"><span style=\"font-size: 8pt;\"><strong>Дополнительное соглашение № {$contract_dop_no}<br /></strong></span></p>\n<p style=\"text-align: center;\"><span style=\"font-size: 8pt;\"><strong>К договору №{$contract_no} от {$contract_date|mdate:\'\"d\" месяца Y\'} г.</strong></span></p>\n<p><span style=\"font-size: 8pt;\"><strong>&nbsp;</strong></span></p>\n<table style=\"width: 100%;\">\n<tbody>\n<tr>\n<td>\n<p><span style=\"font-size: 8pt;\"><strong>г. Москва </strong></span></p>\n</td>\n<td>\n<p style=\"text-align: right;\"><span style=\"font-size: 8pt;\"><strong>{$contract_dop_date|mdate:\'\"d\" месяца Y\'} г.</strong></span></p>\n</td>\n</tr>\n</tbody>\n</table>\n<p style=\"text-align: justify;\"><span style=\"font-size: 8pt;\">{$name_full}, {if $old_legal_type == \"org\"}именуемое в дальнейшем АБОНЕНТ, в качестве исполнительного органа и уполномоченного лица выступает {$position} {$fio}, действующий(ая) на основании Устава{else}именуемый(ая) в дальнейшем АБОНЕНТ,{/if} с одной стороны, и {$organization_name}, именуемое в дальнейшем ОПЕРАТОР,&nbsp;в качестве исполнительного органа и уполномоченного лица выступает {$organization_director_post} {$organization_director}, действующий(ая) на основании Устава, с другой стороны, именуемые в дальнейшем Стороны, заключили Дополнительное соглашение (далее Соглашение) о нижеследующем:</span></p>\n<ol>\n<li><span style=\"font-size: 8pt;\"><strong> Описание Услуги</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">1.1.&nbsp; ОПЕРАТОР предоставляет АБОНЕНТУ доступ к сети местной телефонной связи, возможность доступа к услугам внутризоновой связи и к сети оператора(ов) связи, оказывающего(их) услуги междугородной и международной телефонной связи междугородной и международной телефонной связи, в соответствии с условиями Договора, настоящего Соглашения, со стандартами и техническими нормами, установленными уполномоченными государственными органами РФ и условиями лицензий ОПЕРАТОРА и Заказов на Услугу.</span></p>\n<p><span style=\"font-size: 8pt;\">1.2.&nbsp; ОПЕРАТОР, на основании обращения АБОНЕНТА, оказывает также иные услуги, технологически</span><br /><span style=\"font-size: 8pt;\"> неразрывно связанные с услугами телефонной связи: услуги связи по передаче данных для целей передачи&nbsp; голосовой информации; услуги телематических служб; услуги передачи данных.</span></p>\n<p><span style=\"font-size: 8pt;\">1.3.&nbsp; ОПЕРАТОР при предоставлении услуг телефонного соединения обеспечивает предоставление АБОНЕНТУ: </span><br /><span style=\"font-size: 8pt;\"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - доступа к системе информационно-справочного обслуживания: информация о тарифах на услуги телефонной связи, о состоянии лицевого счета АБОНЕНТА, а также иные, предусмотренные законодательством РФ и Договором, информационно-справочные услуги;</span><br /><span style=\"font-size: 8pt;\"> &nbsp;&nbsp;&nbsp;&nbsp; - возможности бесплатного круглосуточного вызова экстренных оперативных служб.</span></p>\n<p><span style=\"font-size: 8pt;\">1.4.&nbsp; На основании обращения АБОНЕНТА или заказа Услуг через Личный кабинет, ОПЕРАТОР выделяет в пользование АБОНЕНТА один или более номеров с поддержкой соответствующего им количества одновременных соединений, каждое из которых осуществляется на отдельном телефонном порту Оборудования. Конкретные телефонные номера и соответствующее им количество одновременных соединений (линий), закрепленные за АБОНЕНТОМ, указываются в Заказе.</span></p>\n<p><span style=\"font-size: 8pt;\">1.5.&nbsp; Предоставление возможности доступа к услугам внутризоновой, междугородной и международной телефонной связи АБОНЕНТУ осуществляется при согласии АБОНЕНТА на доступ к таким услугам и на предоставление сведений о нем другим операторам связи для оказания таких услуг. Для получения доступа к услугам междугородной и международной телефонной связи АБОНЕНТУ необходимо указать в Заказе на Услугу название выбранного АБОНЕНТОМ оператора услуг междугородной и международной телефонной связи и способ выбора (предварительный выбор, либо выбор при каждом вызове).</span></p>\n<p><span style=\"font-size: 8pt;\">1.6.&nbsp; В зависимости от способа выбора оператора, предоставляющего услуги междугородной и международной телефонной связи, для получения Услуги АБОНЕНТУ необходимо использовать следующий план набора номера:</span></p>\n<p><span style=\"font-size: 8pt;\">&nbsp; (а)&nbsp; При предварительном выборе: </span><br /><span style=\"font-size: 8pt;\"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - при осуществлении междугородних соединений: 8 - код города (или код сети) - номер вызываемого абонента; </span><br /><span style=\"font-size: 8pt;\"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - при осуществлении международных соединений 8 - 10 - код страны - код города (или код сети) - номер вызываемого абонента. </span><br /> <br /><span style=\"font-size: 8pt;\"> (б)&nbsp;&nbsp; При выборе при каждом вызове:</span><br /><span style=\"font-size: 8pt;\"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - при осуществлении междугородных соединений и международных соединений АБОНЕНТ обязуется использовать план набора номера, установленный ОПЕРАТОРОМ.</span></p>\n<p><span style=\"font-size: 8pt;\">1.7.&nbsp; Перечень основных и дополнительных услуг ОПЕРАТОРА, а так же тарифы, действующие на момент подписания настоящего Соглашения, опубликованы на Интернет-сайте <a href=\"http://www.mcn.ru\">www.mcn.ru</a>.</span></p>\n<p><span style=\"font-size: 8pt;\">1.8.&nbsp; Доступ к сети местной телефонной связи ОПЕРАТОРА предоставляется при наличии технической возможности.</span></p>\n<p><span style=\"font-size: 8pt;\">1.9.&nbsp; В рамках Соглашения и соответствующего Заказа, ОПЕРАТОР в целях оказания Услуг АБОНЕНТУ, осуществляет комплекс действий для предоставления АБОНЕНТУ доступа к сети местной телефонной связи ОПЕРАТОРА (далее - &laquo;Подключение к Услугам&raquo;).</span></p>\n<p><span style=\"font-size: 8pt;\">1.10.&nbsp; По требованию АБОНЕНТА абонентская линия может быть сформирована ОПЕРАТОРОМ на имеющихся в пользовании у АБОНЕНТА каналах доступа, организованных ОПЕРАТОРОМ, или каналах связи, организованных АБОНЕНТОМ самостоятельно. ОПЕРАТОР не несет ответственности за техническое состояние и работоспособность каналов связи, организованных АБОНЕНТОМ, в том числе за прерывание оказания Услуг, связанное с техническим состоянием таких каналов связи.</span></p>\n<ol start=\"2\">\n<li><span style=\"font-size: 8pt;\"><strong> Порядок предоставления услуги</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">2.1. После подписания Соглашения АБОНЕНТ в соответствии с условиями Договора и п. 3.1 настоящего Соглашения осуществляет на основании счета ОПЕРАТОРА платеж. В случае если АБОНЕНТ в течение 15 (пятнадцати) календарных дней после получения соответствующего счета не перечислит платеж в полном объеме в соответствии с п. 3.1 настоящего Соглашения, обязательства ОПЕРАТОРА, вытекающие из настоящего Соглашения, не возникают и настоящее Соглашение прекращает свое действие.</span></p>\n<p><span style=\"font-size: 8pt;\">2.2. ОПЕРАТОР осуществляет подключение к Услуге не позднее, чем через 5 рабочих дней со дня оплаты платежа по п. 3.1 настоящего Соглашения, при условии предоставления ОПЕРАТОРУ беспрепятственного доступа в помещения АБОНЕНТА.</span></p>\n<p><span style=\"font-size: 8pt;\">2.3. В случае если АБОНЕНТ в течение 30 (тридцати) рабочих дней со дня оплаты платежа по п. 3.1 настоящего Соглашения, не предоставляет&nbsp; ОПЕРАТОРУ беспрепятственный доступ в это помещение, ОПЕРАТОР вправе отказаться от исполнения своих обязательств по настоящему Соглашению, и настоящее Соглашение прекращает свое действие. В данном случае платеж, уплаченный АБОНЕНТОМ в порядке, установленном п. 2.1. настоящего Соглашения, возврату не подлежит.</span></p>\n<p><span style=\"font-size: 8pt;\">2.4.&nbsp;Дата подключения Услуг указывается Оператором в бланке Заказа. Для подтверждения Абонентом Заказа, изменения состава&nbsp; Услуги и тарифов Оператор высылает Абоненту по электронной почте бланк Заказа, который Абонент должен подписать и вернуть Оператору. Если в течение 5 дней Абонент не предоставит Оператору мотивированный отказ, то Услуги и тарифы, указанные в Заказе, считаются принятыми Абонентом, а Заказ подписанным.</span></p>\n<p><span style=\"font-size: 8pt;\">2.5. ОПЕРАТОР возвращает АБОНЕНТУ осуществленный АБОНЕНТОМ платеж в течение 10 рабочих дней после получения от АБОНЕНТА соответствующего письменного заявления в следующих случаях (при этом настоящее Соглашение прекращает свое действие с момента возврата платежа):</span></p>\n<p><span style=\"font-size: 8pt;\">&nbsp;&nbsp;&nbsp;&nbsp; - в случае одностороннего расторжения АБОНЕНТОМ настоящего Соглашения&nbsp;до подключения к Услуге по п.2.2 настоящего Соглашения; </span><br /><span style=\"font-size: 8pt;\"> &nbsp;&nbsp;&nbsp; - в случае невозможности устранения ОПЕРАТОРОМ причин, вызвавших письменный мотивированный отказ АБОНЕНТА от подписания Заказа.</span></p>\n<ol start=\"3\">\n<li><span style=\"font-size: 8pt;\"><strong> Стоимость услуг и порядок расчетов</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">3.1. Оплата Услуг, предоставляемых АБОНЕНТУ, осуществляется в порядке и размере согласно Заказам, вышеуказанному Договору, настоящему Соглашению и тарифам, опубликованным на Интернет-сайте <a href=\"http://www.mcn.ru\">www.mcn.ru</a>.</span></p>\n<p><span style=\"font-size: 8pt;\">3.2. Ежемесячные платежи, предусмотренные согласно Заказам к настоящему Соглашению и тарифам, опубликованным на Интернет-сайте <a href=\"http://www.mcn.ru\">www.mcn.ru</a>, начинают взиматься с даты подключения Услуги.&nbsp;</span></p>\n<ol start=\"4\">\n<li><span style=\"font-size: 8pt;\"><strong> Дополнительные условия</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">4.1. АБОНЕНТ обязан соблюдать нормативные требования по нагрузке (трафику) на линии связи:</span></p>\n<p><span style=\"font-size: 8pt;\">&nbsp;- нагрузка на один порт не должна превышать 0,8 Эрланга;</span></p>\n<p><span style=\"font-size: 8pt;\">-&nbsp; нагрузка на один порт при безлимитном тарифном плане не должна превышать 5000 минут в месяц.</span></p>\n<p><span style=\"font-size: 8pt;\">В случае невыполнения этих условий ОПЕРАТОР имеет право по своему выбору ограничить предоставление Услуг или&nbsp; пересмотреть их стоимость.</span></p>\n<p><span style=\"font-size: 8pt;\">4.2. ОПЕРАТОР имеет право на полное или частичное прерывание предоставления Услуг, связанное с заменой оборудования, программного обеспечения или проведения других плановых работ, вызванных необходимостью поддержания работоспособности и развития сети, на общий срок не более чем 4 часа в течение месяца, оповестив АБОНЕНТА не менее чем за сутки до данного перерыва.</span></p>\n<p><span style=\"font-size: 8pt;\">4.3. Управление и настройка Оборудования АБОНЕНТА на время действия настоящего Соглашения осуществляется ОПЕРАТОРОМ. При несоблюдении данного условия линия разграничения ответственности между ОПЕРАТОРОМ и АБОНЕНТОМ устанавливается на порту оборудования ОПЕРАТОРА.</span></p>\n<p><span style=\"font-size: 8pt;\">4.4. Настоящее Соглашение вступает в силу с даты его подписания обеими Сторонами.</span></p>\n<p><span style=\"font-size: 8pt;\">4.5. Настоящее Соглашение прекращает свое действие в следующих случаях:</span></p>\n<p><span style=\"font-size: 8pt;\">&nbsp;&nbsp;&nbsp; - по инициативе АБОНЕНТА, с письменным уведомлением ОПЕРАТОРА не позднее, чем за 30 (тридцать) календарных дней до даты прекращения; </span><br /><span style=\"font-size: 8pt;\"> &nbsp;&nbsp;&nbsp; - по инициативе ОПЕРАТОРА в соответствии с условиями пп. 2.1, 2.3, 2.5 и 4.1 настоящего договора, с предварительным уведомлением АБОНЕНТА.</span></p>\n<p><span style=\"font-size: 8pt;\">4.6. В случае нарушения АБОНЕНТОМ сроков оплаты Услуг, ОПЕРАТОР связи имеет право приостановить оказание Услуг до устранения нарушения, уведомив об этом АБОНЕНТА в письменной форме и с использованием средств связи оператора связи (автоинформатора). В случае неустранения такого нарушения в течение 6 (шести) месяцев с даты получения АБОНЕНТОМ от ОПЕРАТОРА связи уведомления (в письменной форме) о намерении приостановить оказание Услуг ОПЕРАТОР в одностороннем порядке вправе расторгнуть настоящее Соглашение.</span></p>\n<table style=\"width: 100%;\">\n<tbody>\n<tr>\n<td>\n<p><span style=\"font-size: 8pt;\">ОПЕРАТОР</span><br /><span style=\"font-size: 8pt;\"> __________________________</span><br /><span style=\"font-size: 8pt;\"> {$organization_director_post} {$organization_director}</span></p>\n</td>\n<td>\n<p><span style=\"font-size: 8pt;\">АБОНЕНТ</span><br /><span style=\"font-size: 8pt;\"> ________________________ </span><br /><span style=\"font-size: 8pt;\"> {$position} {$fio}</span></p>\n</td>\n</tr>\n</tbody>\n</table>\n<p><span style=\"font-size: 8pt;\">&nbsp;</span></p>\n<p><span style=\"font-size: 8pt;\"><strong>&nbsp;</strong></span></p>','agreement',0),(102,'Dog_UslugiSvayzi',3,'<p style=\"text-align: center;\"><span style=\"font-size: 8pt;\"><strong>Договор оказания услуг связи&nbsp; № {$contract_no}</strong></span></p>\n<table width=\"100%\">\n<tbody>\n<tr>\n<td width=\"132\">\n<p><span style=\"font-size: 8pt;\">г. Москва</span></p>\n</td>\n<td width=\"451\">\n<p style=\"text-align: right;\"><span style=\"font-size: 8pt;\">{$contract_date|mdate:\'\"d\" месяца Y\'}г.</span></p>\n</td>\n</tr>\n</tbody>\n</table>\n<p><span style=\"font-size: 8pt;\">{$name_full}, {if $legal_type == \"legal\"}именуемое в дальнейшем АБОНЕНТ, в качестве исполнительного органа и уполномоченного лица выступает {$position} {$fio}, действующий(ая) на основании Устава{else}именуемый(ая) в дальнейшем АБОНЕНТ,{/if} с одной стороны, и {$organization_name}, именуемое в дальнейшем ОПЕРАТОР,&nbsp;в качестве исполнительного органа и уполномоченного лица выступает {$organization_director_post} {$organization_director}, действующий(ая) на основании Устава, с другой стороны, именуемые в дальнейшем Стороны, заключили настоящий Договор о нижеследующем:</span></p>\n<ol>\n<li><span style=\"font-size: 8pt;\"><strong> Определения</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">1.1. Договор - настоящий документ с Приложениями и Заказами, а также все дополнения и изменения, подписанные Сторонами или принятые АБОНЕНТОМ в предусмотренном Договором порядке.</span></p>\n<p><span style=\"font-size: 8pt;\">1.2. Заказ - документ, подписываемый Сторонами в рамках данного Договора с целью приобретения АБОНЕНТОМ Услуг ОПЕРАТОРА, содержащий наименование Услуг, стоимость, метод расчетов (авансовый или кредитный с указанием размера кредитного лимита) и другую информацию, необходимую для реализации Заказа.</span></p>\n<p><span style=\"font-size: 8pt;\">1.3. Отчетный месяц - календарный месяц, в котором АБОНЕНТУ были оказаны Услуги.</span></p>\n<p><span style=\"font-size: 8pt;\">1.4. Лицевой счет&nbsp;- регистр аналитического счета в биллинговой системе ОПЕРАТОРА, предназначенный для отражения в учете операций по оказанию Услуг АБОНЕНТУ и их оплате.&nbsp;</span></p>\n<p><span style=\"font-size: 8pt;\">1.5. Баланс Лицевого счета &ndash; разность между суммой денежных средств, внесенных на Лицевой счет и суммой денежных средств, списанных с Лицевого счета.</span></p>\n<p><span style=\"font-size: 8pt;\">1.6. Авансовый метод&nbsp; расчетов &ndash; Услуги оказываются ОПЕРАТОРОМ на основании авансового платежа, произведенного АБОНЕНТОМ до начала пользования Услугами, при условии наличия положительно баланса денежных средств на&nbsp;Лицевом счете АБОНЕНТА в&nbsp;размере, достаточном для&nbsp;пользования Услугами.</span></p>\n<p><span style=\"font-size: 8pt;\">1.7. Кредитный метод расчетов &ndash; ОПЕРАТОР оказывает АБОНЕНТУ Услуги в кредит в размере суммы всех кредитных лимитов, указанных в Заказах (далее Общий кредитный лимит). Общий кредитный лимит равен разрешенному ОПЕРАТОРОМ минусу Баланса Лицевого счета АБОНЕНТА.</span></p>\n<p><span style=\"font-size: 8pt;\">1.8.&nbsp;Личный кабинет &ndash; индивидуальная страница АБОНЕНТА, создаваемая и поддерживаемая ОПЕРАТОРОМ на Интернет-сайте <a href=\"http://www.mcn.ru\">www.mcn.ru</a>, содержащая статистическую информацию об объеме полученных Услуг и текущем Балансе Лицевого счета. На данной странице ОПЕРАТОРОМ размещаются счета на оплату Услуг, Универсальные передаточные документы или Акты и Счета-фактуры, информация о подключенных услугах, специальные уведомления ОПЕРАТОРА в адрес АБОНЕНТА, а также иная информация, размещенная в соответствии с условиями Договора.&nbsp;Действия, совершенные с использованием Личного кабинета Абонента, считаются совершенными от имени Абонента, при этом Абонент самостоятельно несет риск возможных&nbsp;неблагоприятных последствий изменений и настроек в Личном кабинете. Доступ к Личному кабинету Абонента осуществляется после регистрации Абонента на Интернет-сайте Оператора. &nbsp;</span></p>\n<p>{if $organization_firma == \'mcn_telekom\' || $organization_firma == \'mcm_telekom\'}</p>\n<p><span style=\"font-size: 8pt;\">1.9. Услуги:</span></p>\n<p>{if $organization_firma == \'mcn_telekom\'}</p>\n<ul>\n<li><span style=\"font-size: 8pt;\">Услуги связи по передаче данных для целей передачи голосовой информации (Лицензия № 117137, сроком действия с 02.09.2011г. до 02.09.2016г.);</span></li>\n<li><span style=\"font-size: 8pt;\">Услуги внутризоновой связи (Лицензия № 117140, сроком действия с 02.09.2011г. до 02.09.2016г.)</span></li>\n<li><span style=\"font-size: 8pt;\">Услуги связи по передаче данных, за исключением услуг связи по передаче данных для целей передачи голосовой информации (Лицензия № 117139, сроком действия с 02.09.2011г. до 02.09.2016г.)</span></li>\n<li><span style=\"font-size: 8pt;\">Телематические услуги связи (Лицензия № 117138, сроком действия с 02.09.2011г. до 02.09.2016г.)</span></li>\n<li><span style=\"font-size: 8pt;\">Услуги местной телефонной связи, за исключением услуг местной телефонной связи с использованием таксофонов и средств коллективного доступа (Лицензия № 117141, сроком действия с 02.09.2011 до 02.09.2016г.)</span></li>\n</ul>\n<p>{else}</p>\n<ul>\n<li><span style=\"font-size: 8pt;\">Услуги связи по передаче данных для целей передачи голосовой информации (Лицензия № 131874, сроком действия с 18.06.2015г. до 18.06.2020г.);</span></li>\n<li><span style=\"font-size: 8pt;\">Услуги связи по передаче данных, за исключением услуг связи по передаче данных для целей передачи голосовой информации (Лицензия № 131877, сроком действия с 18.06.2015г. до 18.06.2020г.)</span></li>\n<li><span style=\"font-size: 8pt;\">Телематические услуги связи (Лицензия № 131875, сроком действия с 18.06.2015г. до 18.06.2020г.)</span></li>\n<li><span style=\"font-size: 8pt;\">Услуги местной телефонной связи, за исключением услуг местной телефонной связи с использованием таксофонов и средств коллективного доступа (Лицензия № 131876, сроком действия с 18.06.2015г. до 18.06.2020г.)</span></li>\n</ul>\n<p>{/if}</p>\n<p><span style=\"font-size: 8pt;\">оказываются ОПЕРАТОРОМ АБОНЕНТУ в рамках отдельного Заказа к настоящему Договору. Описание, условия и порядок оказания каждой из Услуг, а также порядок взаимодействия Сторон в рамках оказания Услуг описываются в соответствующих Дополнительных соглашениях к Договору.</span></p>\n<p>{/if}</p>\n<ol start=\"2\">\n<li><span style=\"font-size: 8pt;\"><strong> Предмет Договора</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">В соответствии с имеющимися лицензиями, условиями настоящего Договора на основании Заказов ОПЕРАТОР оказывает АБОНЕНТУ Услуги, а АБОНЕНТ принимает и оплачивает их. Описание, порядок и условия оказания конкретных Услуг содержатся в соответствующих Дополнительных соглашениях к Договору.</span></p>\n<ol start=\"3\">\n<li><span style=\"font-size: 8pt;\"><strong> Срок действия, вступление в силу</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">3.1. Договор вступает в силу после его подписания последней из Сторон (&laquo;Дата вступления Договора в силу&raquo;) и действует до&nbsp;конца текущего календарного года. Ежегодно, в случае если ни&nbsp;одна Сторона до 1-го декабря текущего года не&nbsp;оповестила другую о&nbsp;желании расторгнуть или пересмотреть Договор, его действие автоматически продлевается на&nbsp;следующий календарный&nbsp;год.</span></p>\n<p><span style=\"font-size: 8pt;\">3.2. Стороны признают равную юридическую силу собственноручной подписи и факсимиле подписи (воспроизведение личной подписи механическим способом) и пришли к соглашению о том, что настоящий Договор, Приложения, Дополнительные соглашения и иные документы, подписанные Сторонами путем простановки факсимиле подписи и печати, имеют силу оригинала.</span></p>\n<ol start=\"4\">\n<li><span style=\"font-size: 8pt;\"><strong> Оказание Услуг</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">4.1. ОПЕРАТОР оказывает Услуги в соответствии с выданными Федеральной службой по надзору в сфере связи лицензиями, указанными в п. 1.9. настоящего Договора.</span></p>\n<p><span style=\"font-size: 8pt;\">4.2. Услуги предоставляются в соответствии с настоящим Договором 24 часа в сутки, 7 дней в неделю. Время реакции ОПЕРАТОРА на аварийную заявку АБОНЕНТА составляет 4 часа в рабочие дни с 9:00-18:00 по местному времени и 8 часов в остальное время.</span></p>\n<p><span style=\"font-size: 8pt;\">4.3. Адрес установки оконечного оборудования АБОНЕНТА, тип оборудования, способ выбора оператора услуг междугородной и международной телефонной связи, перечень обслуживаемых абонентских номеров АБОНЕНТА, а также иная необходимая для оказания Услуг информация, указываются в соответствующем Заказе.</span></p>\n<table>\n<tbody>\n<tr>\n<td>\n<p><span style=\"font-size: 8pt;\">4.4. В случае ухудшения качества Услуг, для получения консультаций, связанных с&nbsp;настройкой и&nbsp;использованием предоставляемых Услуг,&nbsp; АБОНЕНТ может обращаться в службу технической поддержки ОПЕРАТОРА:&nbsp;&nbsp;</span></p>\n<ul>\n<li><span style=\"font-size: 8pt;\">Москва: +7 (495) 105-99-95</span></li>\n<li><span style=\"font-size: 8pt;\">Санкт-Петербург: +7 (812) 372-6999</span></li>\n<li><span style=\"font-size: 8pt;\">Краснодар: +7 (861) 204-0099</span></li>\n<li><span style=\"font-size: 8pt;\">Екатеринбург: +7 (343) 302-0099</span></li>\n<li><span style=\"font-size: 8pt;\">Новосибирск: +7 (383) 312-0099</span></li>\n<li><span style=\"font-size: 8pt;\">Самара: +7 (846) 215-0099</span></li>\n<li><span style=\"font-size: 8pt;\">Ростов-на-Дону: +7 (863) 309-0099</span></li>\n<li><span style=\"font-size: 8pt;\">Казань: +7 (843) 207-0099</span></li>\n<li><span style=\"font-size: 8pt;\">Нижний Новгород:+7 (831) 235-0099</span></li>\n<li><span style=\"font-size: 8pt;\">Владивосток: +7 (423) 206-0099</span></li>\n</ul>\n<p><span style=\"font-size: 8pt;\">&nbsp;&nbsp;&nbsp;&nbsp; E-mail: <a href=\"mailto:support@mcn.ru\">support@mcn.ru</a></span></p>\n</td>\n</tr>\n</tbody>\n</table>\n<p><span style=\"font-size: 8pt;\">&nbsp;</span></p>\n<p><span style=\"font-size: 8pt;\">4.5. Ежемесячно, не позднее 5 (пяти) рабочих дней со дня окончания отчетного месяца, ОПЕРАТОР оформляет и публикует в Личном кабинете АБОНЕНТА документы об оказании Услуг: Счета, Универсальные передаточные документы, Акты, Счета-фактуры и пр. Оператор изготавливает и доставляет Абоненту документы на бумажном носителе, заверенные печатью Оператора (Счета, Универсальные передаточные документы, Акты, Счета-фактуры, Акты сверки, Детализации счета и пр.), по заявкам Абонента.&nbsp;Стоимость доставки документов на бумажном носителе определяется согласно тарифам.&nbsp;</span></p>\n<p><span style=\"font-size: 8pt;\">4.6. ОПЕРАТОР вправе привлекать третьих лиц, в том числе, владеющих собственной или арендуемой сетью связи и имеющих необходимые лицензии на оказание услуг связи на территории РФ, для организации предоставления АБОНЕНТУ Услуг.</span></p>\n<ol start=\"5\">\n<li><span style=\"font-size: 8pt;\"><strong> Обязательства Сторон</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\"><strong>5.1.&nbsp;&nbsp;&nbsp;</strong> <strong>Стороны обязуются:</strong></span></p>\n<p><span style=\"font-size: 8pt;\">5.1.2. При осуществлении деятельности по Договору применять только лицензированное программное обеспечение и исправно работающее оборудование, сертифицированное в установленном в Российской Федерации порядке.</span></p>\n<p><span style=\"font-size: 8pt;\">5.1.3. Самостоятельно оплачивать все расходы, связанные с выполнением своих обязательств по Договору, если иное прямо не предусмотрено в Договоре или не согласовано с другой Стороной иным способом.</span></p>\n<p><span style=\"font-size: 8pt;\">5.1.4. Соблюдать режим конфиденциальности в отношении информации, обозначенной передающей Стороной как &laquo;Конфиденциальная&raquo; и признанной таковой в соответствии с действующим законодательством РФ.</span></p>\n<p><span style=\"font-size: 8pt;\"><strong>5.2.&nbsp;&nbsp;&nbsp;</strong> <strong>ОПЕРАТОР обязуется:</strong></span></p>\n<p><span style=\"font-size: 8pt;\">5.2.2. Обеспечивать ежедневное и круглосуточное функционирование оборудования, к которому подключается АБОНЕНТ, за исключением промежутков времени для проведения профилактических и ремонтных работ, а также времени, необходимого для оперативного устранения отказов или повреждений линейного, кабельного или станционного оборудования.</span></p>\n<p><span style=\"font-size: 8pt;\">5.2.3. Проводить профилактические и регламентные работы в часы наименьшей нагрузки, а также информировать АБОНЕНТА о дате, времени и продолжительности названных работ не менее чем за 24 часа до даты их проведения.</span></p>\n<p><span style=\"font-size: 8pt;\">5.2.4. Обеспечивать выполнение требований по соблюдению тайны связи в соответствии с Федеральным Законом &laquo;О связи&raquo; от 7 июля 2003 г . №126-ФЗ (далее &laquo;ФЗ &laquo;О связи&raquo;).</span></p>\n<p><span style=\"font-size: 8pt;\">5.2.5. При оказании Услуг обеспечивать параметры качества в соответствии с нормативными документами отрасли &laquo;Связь&raquo;.</span></p>\n<p><span style=\"font-size: 8pt;\">5.2.6. Предоставлять АБОНЕНТУ возможность доступа к Личному кабинету. Доступ к Личному кабинету предоставляется АБОНЕНТУ с момента регистрации в Личном кабинете. В случае временного приостановления оказания Услуг, Личный кабинет остается доступным для АБОНЕНТА в течение срока действия Договора.</span></p>\n<p><span style=\"font-size: 8pt;\"><strong>5.3.&nbsp;&nbsp;&nbsp;</strong> <strong>АБОНЕНТ обязуется:</strong></span></p>\n<p><span style=\"font-size: 8pt;\">5.3.1. Принимать оказанные ОПЕРАТОРОМ Услуги в соответствии с условиями Договора и Дополнительных соглашений к нему.</span></p>\n<p><span style=\"font-size: 8pt;\">5.3.2. Полностью и своевременно производить оплату Услуг в соответствии с условиями Договора и Дополнительных соглашений к нему.</span></p>\n<p><span style=\"font-size: 8pt;\">5.3.3. Оперативно предоставлять по запросу ОПЕРАТОРА всю информацию, в том числе техническую, которая может потребоваться ОПЕРАТОРУ для реализации Заказа.</span></p>\n<p><span style=\"font-size: 8pt;\">5.3.4. В случае необходимости, выделить ОПЕРАТОРУ место для установки оборудования в помещении по адресу, указанному в Заказе к Договору и обеспечить к нему доступ специалистов ОПЕРАТОРА; обеспечить получение всех необходимых разрешений и согласований от владельца территории (помещения), на которой расположено оборудование АБОНЕНТА, на проведение работ по прокладке кабеля, строительству кабельной канализации и организации кабельного ввода, а также по размещению и электропитанию оборудования.</span></p>\n<p><span style=\"font-size: 8pt;\">5.3.5. В&nbsp;период временного прекращения предоставления Услуг АБОНЕНТУ по&nbsp;причинам, изложенным в&nbsp;п.&nbsp;п.&nbsp;6.10., 6.16. Договора, оплачивать ежемесячную абонентскую плату в&nbsp;соответствии с&nbsp;действующим тарифным планом.</span></p>\n<p><span style=\"font-size: 8pt;\">5.3.6. Самостоятельно контролировать Баланс Лицевого счета, получение счетов и уведомлений ОПЕРАТОРА в Личном кабинете.</span></p>\n<p><span style=\"font-size: 8pt;\">5.3.7. Предоставлять Оператору полную, достоверную и актуальную информацию о себе, поддерживать актуальность и полноту данной информации посредством Личного кабинета Абонента.</span></p>\n<ol start=\"6\">\n<li><span style=\"font-size: 8pt;\"><strong> Стоимость Услуг и условия оплаты</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">6.1. Ежемесячная стоимость Услуг определяется в соответствии с Заказом и действующими тарифами ОПЕРАТОРА. Тарифы, действующие на момент подписания Договора и/или Дополнительных соглашений к нему, приведены в соответствующих Дополнительных соглашениях к настоящему Договору.</span></p>\n<p><span style=\"font-size: 8pt;\">6.2. ОПЕРАТОР вправе в одностороннем порядке изменить действующие тарифы АБОНЕНТА и сроки оплаты Услуг с предварительным уведомлением АБОНЕНТА за 10 (десять) календарных дней до даты введения в действие таких изменений. Уведомление АБОНЕНТА осуществляется по электронной почте и /или путем публикации информации на&nbsp; Интернет-сайте ОПЕРАТОРА&nbsp;<a href=\"http://www.mcn.ru\">www.mcn.ru</a> (Свидетельство о регистрации СМИ Интернет-сайт <a href=\"http://www.mcn.ru\">www.mcn.ru</a>: Эл № ФС77-61463).</span></p>\n<p><span style=\"font-size: 8pt;\">6.3. В случае несогласия АБОНЕНТА с изменением тарифов ОПЕРАТОРА и изъявлении желания расторгнуть Договор, АБОНЕНТ обязан оплатить счета за оказанные Услуги по Договору до момента расторжения Договора.</span></p>\n<p><span style=\"font-size: 8pt;\">6.4. АБОНЕНТ вправе перейти на любой другой действующий тарифный план ОПЕРАТОРА, доступный для существующих клиентов, предварительно уведомив об этом ОПЕРАТОРА по электронной почте, факсу или через Личный кабинет. Перевод АБОНЕНТА на новый тарифный план осуществляется с первого числа календарного месяца при условии наличия технической возможности и получения ОПЕРАТОРОМ соответствующего уведомления от АБОНЕНТА с указанием даты перевода и названия выбранного тарифного плана не позднее, чем за 1(один) рабочий день до окончания предыдущего календарного месяца.</span></p>\n<p><span style=\"font-size: 8pt;\">6.5. Ежемесячно, не позднее 5 (пяти) рабочих дней со дня начала отчетного месяца, ОПЕРАТОР выставляет счет, размещает его в Личном кабинете, а также направляет АБОНЕНТУ по электронной почте&nbsp;. С момента размещения счета в Личном кабинете, в соответствии п. 5.3.6. Договора, счет считается полученным АБОНЕНТОМ, за исключением случаев недоступности Личного кабинета по вине ОПЕРАТОРА.&nbsp;</span></p>\n<p><span style=\"font-size: 8pt;\">6.6. В случае заключения Сторонами нескольких Заказов на одну Услугу и /или нескольких Заказов на разные виды Услуг для оплаты ежемесячной стоимости Услуг ОПЕРАТОР выставляет единый счет.</span></p>\n<p><span style=\"font-size: 8pt;\">6.7. По инициативе АБОНЕНТА или ОПЕРАТОРА, ОПЕРАТОР имеет право выделить АБОНЕНТУ несколько Лицевых счетов для ведения раздельного учета по оплате разных Заказов на одну Услугу и /или нескольких Заказов на разные виды Услуг. Лицевые счета указываются в соответствующих им Заказах. При заключении Сторонами нескольких Заказов на одну Услугу и /или нескольких Заказов на разные виды Услуг, в случае ведения учета по их оплате в рамках единого Лицевого счета, для расчетов по данным Заказам и Услугам АБОНЕНТА применяется единый метод расчетов: либо авансовый, либо кредитный.</span></p>\n<p><span style=\"font-size: 8pt;\">6.8. Оплата счетов за Услуги производится в российских рублях.&nbsp; <br /></span></p>\n<p><span style=\"font-size: 8pt;\">6.9.&nbsp;АБОНЕНТ оплачивает Услуги на&nbsp;основании счетов, выставляемых ОПЕРАТОРОМ согласно пункту 6.5 настоящего Договора, в течение 10 рабочих дней с момента публикации счета в Личном кабинете. Оплата считается произведённой в момент зачисления денежных средств на расчетный счет ОПЕРАТОРА. Расходы по переводу денежных средств относятся на счет АБОНЕНТА. В случае применения Авансового метода расчетов, при условии отсутствия в момент списания денежных средств за оказанные ОПЕРАТОРОМ Услуги на Лицевом счете АБОНЕНТА суммы, достаточной для оплаты Услуг, оказание Услуг временно приостанавливается. В случае применения Кредитного метода расчетов, при условии, что на момент списания денежных средств за оказанные ОПЕРАТОРОМ Услуги, суммы Баланса Лицевого счета и Общего кредитного лимита АБОНЕНТА недостаточно для оплаты Услуг, оказание Услуг временно приостанавливается.</span></p>\n<p><span style=\"font-size: 8pt;\">6.10. В случае нарушения обязательств по оплате, указанных в п. 5.3.2. настоящего Договора, в соответствии с п. 6.9. настоящего Договора, ОПЕРАТОР имеет право временно приостановить оказание Услуг до&nbsp;полного погашения задолженности АБОНЕНТОМ. В случае приостановления оказания Услуг по причине наличия задолженности, ОПЕРАТОР возобновляет оказание Услуг АБОНЕНТУ в течение следующего рабочего дня со дня поступления денежных средств на расчетный счет ОПЕРАТОРА.</span></p>\n<p><span style=\"font-size: 8pt;\">6.11. &nbsp;В случае несогласия с&nbsp;объемом предоставленной Услуги согласно счетам ОПЕРАТОРА, АБОНЕНТ в&nbsp;течение 5&nbsp;(пяти) рабочих дней с&nbsp;момента получения счета должен представить ОПЕРАТОРУ письменную претензию, в&nbsp;противном случае Услуга считается выполненной. ОПЕРАТОР в&nbsp;течение 10&nbsp;рабочих дней должен представить ответ на&nbsp;претензию.</span></p>\n<p><span style=\"font-size: 8pt;\">6.12. В случае обнаружения ошибок в выставленном ОПЕРАТОРОМ счете соответствующая корректировка проводится в счете за последующий отчетный месяц.</span></p>\n<p><span style=\"font-size: 8pt;\">6.13. При осуществлении расчетов по настоящему Договору АБОНЕНТ обязан указывать в платежных документах следующие сведения: наименование плательщика; наименование получателя платежа и его банковские реквизиты, ИНН, КПП; наименование банка получателя; сумму платежа; документы, на основании которых производится платеж (договор от &hellip;. № &hellip;.; счет&nbsp; от &hellip;. № &hellip;.); вид платежа (единовременная плата, ежемесячная плата, неустойка (пеня, убытки); период, за который производится платеж). В случае если АБОНЕНТ не указал или ненадлежащим образом указал в платежных документах сведения о расчетном периоде, за который произведен платеж, период определяется ОПЕРАТОРОМ самостоятельно. При этом, если существует задолженность предыдущего периода, то ОПЕРАТОР засчитывает вышеуказанную плату в счет погашения задолженности предыдущего периода (с учетом положений п. 6.14. Договора).</span></p>\n<p><span style=\"font-size: 8pt;\">6.14. При неполной оплате счета ОПЕРАТОР вправе по своему усмотрению засчитывать осуществленный АБОНЕНТОМ платеж пропорционально в счет оплаты каждого Заказа или в счет полной оплаты отдельных Заказов &nbsp;в рамках одного Лицевого счета.</span></p>\n<p><span style=\"font-size: 8pt;\">6.15. В&nbsp;случае невозможности оказания качественных Услуг, наступившей из-за повреждений оборудования ОПЕРАТОРА, ежемесячный абонентский платеж за&nbsp;время невозможности качественного предоставления Услуг уменьшается пропорционально времени неисправности.</span></p>\n<p><span style=\"font-size: 8pt;\">6.16. В&nbsp;случаях отказа оборудования АБОНЕНТА или отказа оборудования ОПЕРАТОРА вследствие ненадлежащей эксплуатации его АБОНЕНТОМ, неисправности исправляются ОПЕРАТОРОМ за&nbsp;счет АБОНЕНТА. ОПЕРАТОР не&nbsp;несет ответственности за&nbsp;такие ситуации и&nbsp;ежемесячный абонентский платеж не&nbsp;уменьшается.</span></p>\n<p><span style=\"font-size: 8pt;\">6.17. Если на момент прекращения действия Договора и оплаты всех оказанных ОПЕРАТОРОМ Услуг Баланс Лицевого счета имеет положительное значение, ОПЕРАТОР возвращает неизрасходованный остаток денежных средств на основании письменного заявления об их возврате, направленного АБОНЕНТОМ вместе с уведомлением о расторжении Договора.</span></p>\n<p><span style=\"font-size: 8pt;\">6.18. Счет, выставляемый согласно пункту 6.5. настоящего Договора, включает в себя ежемесячные абонентские платежи за услуги, оказываемые АБОНЕНТУ в Отчетном месяце. В случае неоплаты счета в срок, установленный настоящим Договором, ОПЕРАТОР вправе приостановить оказание Услуг согласно пункту 6.9. настоящего Договора. При этом ежемесячные абонентские платежи за период приостановки оказания Услуг начисляются в полном объеме и подлежат оплате АБОНЕНТОМ, работающим по кредитному или авансовому методу оплаты.</span></p>\n<ol start=\"7\">\n<li><span style=\"font-size: 8pt;\"><strong> Ответственность Сторон</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">7.1. За неисполнение либо ненадлежащее исполнение обязательств по Договору Стороны несут ответственность в соответствии с действующим законодательством Российской Федерации.</span></p>\n<p><span style=\"font-size: 8pt;\">7.2. Стороны не несут ответственности в случаях действия обстоятельств непреодолимой силы, а именно чрезвычайных и непредотвратимых обстоятельств: стихийных бедствий (землетрясений, наводнений и т.д.), обстоятельств общественной жизни (военных действий, крупномасштабных забастовок, эпидемий, аварий на энергоснабжающих предприятиях и т.д.) и запретительных мер государственных органов. О наступлении таких обстоятельств Стороны письменно информируют друг друга в течение пяти дней с момента их наступления.</span></p>\n<p><span style=\"font-size: 8pt;\">7.3. ОПЕРАТОР не несет ответственности перед АБОНЕНТОМ и третьими лицами за прямые и/или косвенные убытки, понесённые АБОНЕНТОМ и/или третьими лицами, посредством использования Услуг или получения доступа к ним.</span></p>\n<p><span style=\"font-size: 8pt;\">7.4. ОПЕРАТОР не несет ответственности в случае сбоев программного обеспечения и/или появление дефектов в оборудовании АБОНЕНТА или любых третьих лиц.</span></p>\n<p><span style=\"font-size: 8pt;\">7.5. ОПЕРАТОР не&nbsp;отвечает за&nbsp;содержание информации, передаваемой и&nbsp;получаемой АБОНЕНТОМ, за&nbsp;исключением случая собственной информации ОПЕРАТОРА.</span></p>\n<ol start=\"8\">\n<li><span style=\"font-size: 8pt;\"><strong> Расторжение Договора</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">8.1. В&nbsp;случае неоплаты АБОНЕНТОМ выставленного ОПЕРАТОРОМ счета до конца месяца, следующего за отчетным, ОПЕРАТОР имеет право произвести окончательное отключение Услуг, при этом настоящий Договор считается расторгнутым ОПЕРАТОРОМ в одностороннем порядке.</span></p>\n<p><span style=\"font-size: 8pt;\">8.2. АБОНЕНТ вправе в одностороннем порядке расторгнуть настоящий Договор, при условии оплаты ОПЕРАТОРУ всех причитающихся сумм по Договору в соответствии со ст. 782 ГК РФ и получения ОПЕРАТОРОМ письменного уведомления от АБОНЕНТА о намерении расторгнуть Договор за 30 (тридцать) календарных дней до даты его расторжения.</span></p>\n<p><span style=\"font-size: 8pt;\">8.3. Обязательства Сторон по п. 5.1.4. настоящего Договора, продолжают действовать и после истечения срока действия или расторжения Договора.</span></p>\n<ol start=\"9\">\n<li><span style=\"font-size: 8pt;\"><strong> Другие Положения</strong></span></li>\n</ol>\n<p><span style=\"font-size: 8pt;\">9.1. Любое изменение Договора оформляется в виде Дополнительного соглашения к Договору, которое вступает в силу только после его подписания Сторонами, если иной порядок изменения не предусмотрен положениями настоящего Договора (включая Приложения, Дополнительные соглашения и Заказы), либо действующего законодательства РФ.</span></p>\n<p><span style=\"font-size: 8pt;\">9.2. Неправильность, недействительность, невыполнимость или незаконность какого-либо положения Договора не влияет на действительность или выполнимость любого другого из остальных положений Договора.</span></p>\n<p><span style=\"font-size: 8pt;\">9.3. Если в Дополнительных соглашениях к Договору условия предоставления Услуг отличаются от условий предоставления Услуг, предусмотренных в Договоре, то положения Дополнительных соглашений будут превалировать над текстом Договора.</span></p>\n<p><span style=\"font-size: 8pt;\">9.4. Все споры, разногласия или требования, возникающие по настоящему Договору или в связи с ним, подлежат разрешению путем переговоров Сторон. В случае если Стороны не пришли к соглашению по спорному вопросу, спор подлежит рассмотрению в Арбитражном суде г. Москвы в соответствии с действующим законодательством РФ.</span></p>\n<p><span style=\"font-size: 8pt;\">9.5. В&nbsp;случае изменения адреса доставки, электронной почты, телефона и /или&nbsp;факса, каждая из&nbsp;Сторон обязуется в&nbsp;течение 5&nbsp;(пяти) дневного срока известить об&nbsp;этом другую Сторону по электронной почте и /или факсу.</span></p>\n<p><span style=\"font-size: 8pt;\">9.6. В случае изменения организационно-правовой формы юридического лица, фирменного наименования, изменения реквизитов компании, изменения адреса предоставления услуг, АБОНЕНТ обязан письменно уведомить об этом ОПЕРАТОРА не менее чем за 1 (один) рабочий день до конца текущего отчетного периода.&nbsp;</span></p>\n<p><span style=\"font-size: 8pt;\">9.7. АБОНЕНТ вправе переоформить Услуги на другое юридическое лицо, письменно уведомив о своем намерении ОПЕРАТОРА. За переоформление договора ОПЕРАТОР взимает единовременную плату согласно действующему тарифу.</span></p>\n<p><span style=\"font-size: 8pt;\">9.8. Ни одна из Сторон не может передавать свои права и обязанности по данному договору какой-либо третьей стороне без согласия другой Стороны.</span></p>\n<p><span style=\"font-size: 8pt;\">9.9. Договор, все Приложения и Дополнительные соглашения к нему, включая Заказы, составляют единое целое. Настоящий Договор подписан Сторонами в двух экземплярах, имеющих одинаковую юридическую силу, по одному для каждой из Сторон.</span></p>\n<ol start=\"10\">\n<li><span style=\"font-size: 8pt;\"><strong> Адреса и реквизиты сторон</strong>:</span></li>\n</ol>\n<table style=\"width: 100%;\">\n<tbody>\n<tr>\n<td>\n<p><span style=\"font-size: 8pt;\">ОПЕРАТОР: {$firm_detail_block}</span></p>\n</td>\n<td>\n<p style=\"font-size: 8pt;\">АБОНЕНТ: {$payment_info}</p>\n</td>\n</tr>\n</tbody>\n</table>\n<ol start=\"11\">\n<li><span style=\"font-size: 8pt;\"><strong>Подписи сторон:</strong></span></li>\n</ol>\n<table style=\"width: 100%;\">\n<tbody>\n<tr>\n<td>\n<p><span style=\"font-size: 8pt;\">ОПЕРАТОР</span></p>\n<p><span style=\"font-size: 8pt;\">__________________________</span><br /><span style=\"font-size: 8pt;\"> {$organization_director_post} {$organization_director}</span></p>\n</td>\n<td>\n<p><span style=\"font-size: 8pt;\">АБОНЕНТ</span></p>\n<p><span style=\"font-size: 8pt;\">__________________________</span><br /><span style=\"font-size: 8pt;\">{if ($legal_type) == \"legal\"}{$position} {$fio}{else}{$name_full}{/if}</span></p>\n</td>\n</tr>\n</tbody>\n</table>','contract',0),(133,'Договор Вегрия',3,'<h1>Egyedi előfizetői szerződ&eacute;s</h1>\n<p><span style=\"font-size: 10pt;\">Jelen<strong> Egyedi Előfizetői Szerződ&eacute;s </strong>l&eacute;trej&ouml;tt</span></p>\n<p><span style=\"font-size: 10pt;\"><strong>a {$organization_name} (</strong>ad&oacute;sz&aacute;m<strong>: </strong>12773246-2-43, bejegyzett c&iacute;m: Budapest 1114, Kemenes u., 8, f&eacute;lemelet 3<strong>), mint MCNtelecom Szolg&aacute;ltat&oacute; &eacute;s </strong></span></p>\n<p><span style=\"font-size: 10pt;\">&nbsp;</span></p>\n<p><span style=\"font-size: 10pt;\"><strong>{$name_full} (</strong>ad&oacute;sz&aacute;m: {$inn} bejegyzett c&iacute;m: {$address_jur}),<strong> mint Előfizető </strong>k&ouml;z&ouml;tt<strong>.</strong></span></p>\n<p><span style=\"font-size: 10pt;\">&nbsp;</span></p>\n<ol>\n<li><span style=\"font-size: 10pt;\"><strong> A Szolg&aacute;ltat&aacute;s le&iacute;r&aacute;sa.</strong> Jelen szerződ&eacute;s t&aacute;rgya a telep&iacute;tett telefon alk&ouml;zpont rendszer&eacute;n &eacute;s informatikai eszk&ouml;z&ouml;k&ouml;n IP alap&uacute; (SIP) helyi, belf&ouml;ldi &eacute;s mobil, valamint nemzetk&ouml;zi vezet&eacute;kes &eacute;s mobil ir&aacute;nyokra ig&eacute;nybe vehető, PSTN-el egyen&eacute;rt&eacute;kű szolg&aacute;ltat&aacute;s (h&iacute;v&aacute;sind&iacute;t&aacute;s &eacute;s v&eacute;gződtet&eacute;s, a tov&aacute;bbiakban \"Szolg&aacute;ltat&aacute;s\") ny&uacute;jt&aacute;sa az Előfizető r&eacute;sz&eacute;re, az eszk&ouml;z&ouml;k rendszeres karbantart&aacute;sa, &uuml;gyeleti rendszer biztos&iacute;t&aacute;sa az esetleges meghib&aacute;sod&aacute;sok bejelent&eacute;se a fogad&aacute;sra, valamint a telefon alk&ouml;zpont rendszer &eacute;n informatikai eszk&ouml;z&ouml;k hib&aacute;inak elh&aacute;r&iacute;t&aacute;sa. A Szolg&aacute;ltat&aacute;s v&eacute;szh&iacute;v&aacute;sok c&eacute;lj&aacute;ra nem alkalmas, noha a seg&eacute;lyh&iacute;v&oacute; sz&aacute;mok h&iacute;vhat&oacute;ak, ugyanis a szolg&aacute;ltat&aacute;s &aacute;ramkimarad&aacute;s &eacute;s az internetkapcsolat hib&aacute;ja eset&eacute;n nem műk&ouml;dik.</span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">Az Előfizető kijelenti, hogy elolvasta &eacute;s elfogadja az &Aacute;ltal&aacute;nos Szerződ&eacute;si Felt&eacute;teleket (&Aacute;SZF). &Aacute;SZF a jelen szerződ&eacute;s r&eacute;sz&eacute;t k&eacute;pezi. Az Előfizető tov&aacute;bb&aacute; kijelenti, hogy megismerte a szolg&aacute;ltat&aacute;sny&uacute;jt&aacute;s felt&eacute;teleivel, a Szolg&aacute;ltat&oacute; &aacute;rjegyz&eacute;kkel &eacute;s a Szolg&aacute;ltat&oacute; &aacute;ltal felk&iacute;n&aacute;lt akci&oacute;s aj&aacute;nlatokkal azok teljes terjedelm&eacute;ben. Mindenkori &Aacute;SZF a Szolg&aacute;ltat&oacute; honlapj&aacute;n &eacute;s irod&aacute;j&aacute;ban &eacute;rhető el.</span></p>\n<ol start=\"2\">\n<li><span style=\"font-size: 10pt;\"><strong> &Uuml;gyf&eacute;lszolg&aacute;lat &eacute;s hibabejelent&eacute;s</strong> el&eacute;rhetős&eacute;ge: info@mcntele.com; <a href=\"http://www.mcntelel.com/\">www.mcntelel.com</a>; &Uuml;gyf&eacute;lszolg&aacute;lat &eacute;s hibabejelent&eacute;s: Budapest, Kemenes utca 8. f&eacute;lemelet 3.; Telefon: +36 (1) 490 0999; Nyitva tart&aacute;s: munkanapokon 09-16h; e-mail: info@mcntele.com.</span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">Előfizető kijelenti, hogy a Szolg&aacute;ltat&oacute; &Aacute;ltal&aacute;nos Szerződ&eacute;si Felt&eacute;teleit (&bdquo;&Aacute;SZF&rdquo;) a megismerte &eacute;s annak felt&eacute;teleit elfogadja. Az &Aacute;SZF mindenkor a szerződ&eacute;s r&eacute;sz&eacute;t k&eacute;pezi, &eacute;s egyben kijelenti, hogy a Szolg&aacute;ltat&oacute; akci&oacute;s felt&eacute;teleit, vonatkoz&oacute; d&iacute;jszab&aacute;s&aacute;t &eacute;s a szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;re vonatkoz&oacute; szerződ&eacute;si felt&eacute;teleket teljes k&ouml;rűen megismerte &eacute;s az &Aacute;SZF kivonatot &aacute;tvette. A mindenkor hat&aacute;lyos &Aacute;SZF el&eacute;rhető a Szolg&aacute;ltat&oacute; weboldal&aacute;n &eacute;s &uuml;gyf&eacute;lszolg&aacute;lat&aacute;n.</span></p>\n<ol start=\"3\">\n<li><span style=\"font-size: 10pt;\"><strong> A Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tele &eacute;s haszn&aacute;lata</strong></span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">A Szolg&aacute;ltat&aacute;s Magyarorsz&aacute;g ter&uuml;let&eacute;n vehető ig&eacute;nybe. A Szolg&aacute;ltat&oacute; az ig&eacute;nyt abban az esetben el&eacute;g&iacute;ti ki, ha az előfizetői v&eacute;gberendez&eacute;s telep&iacute;t&eacute;s&eacute;nek nincsenek műszaki, jogi, hat&oacute;s&aacute;gi korl&aacute;tai, a telep&iacute;t&eacute;s &eacute;sszerű k&ouml;lts&eacute;ghat&aacute;rok mellett megval&oacute;s&iacute;that&oacute; &eacute;s az Előfizetőnek nincs lej&aacute;rt tartoz&aacute;sa a Szolg&aacute;ltat&oacute;val szemben. A Szolg&aacute;ltat&aacute;st az Előfizető k&ouml;teles rendeltet&eacute;sszerűen haszn&aacute;lni.</span></p>\n<ol start=\"4\">\n<li><span style=\"font-size: 10pt;\"><strong> A Szolg&aacute;ltat&aacute;s l&eacute;tes&iacute;t&eacute;s&eacute;vel &eacute;s műk&ouml;dtet&eacute;s&eacute;vel kapcsolatos előfizetői k&ouml;telezetts&eacute;gek</strong></span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">4.1. Az Előfizető k&ouml;teles gondoskodni arr&oacute;l, hogy a Szolg&aacute;ltat&oacute; a ki&eacute;p&iacute;t&eacute;s szempontj&aacute;b&oacute;l &eacute;rintett ingatlanra bejusson &eacute;s a Szolg&aacute;ltat&aacute;s ny&uacute;jt&aacute;s&aacute;hoz sz&uuml;ks&eacute;ges berendez&eacute;seit d&iacute;jmentesen elhelyezhesse. Az Előfizető k&ouml;teless&eacute;ge az ingatlan tulajdonos&aacute;t&oacute;l &iacute;r&aacute;sos hozz&aacute;j&aacute;rul&aacute;st beszerezni, amennyiben az sz&uuml;ks&eacute;ges. Az Előfizető az eszk&ouml;z&ouml;k elhelyez&eacute;s&eacute;hez sz&uuml;ks&eacute;ges helyet ingyenesen biztos&iacute;tja a Szolg&aacute;ltat&oacute; sz&aacute;m&aacute;ra. A helyi informatikai rendszeren a Szolg&aacute;ltat&aacute;s műk&ouml;d&eacute;s&eacute;hez sz&uuml;ks&eacute;ges helyi h&aacute;l&oacute;zat ki&eacute;p&iacute;t&eacute;se nem k&eacute;pezi r&eacute;sz&eacute;t jelen szerződ&eacute;snek.</span></p>\n<p><span style=\"font-size: 10pt;\">A Szolg&aacute;ltat&aacute;s műk&ouml;d&eacute;s&eacute;nek felt&eacute;tele, a műk&ouml;dő internet kapcsolat, amelynek biztos&iacute;t&aacute;sa az Előfizető k&ouml;telezetts&eacute;ge. Amennyiben b&aacute;rmilyen okb&oacute;l ez nem &aacute;ll a rendelkez&eacute;sre, az ebből ad&oacute;d&oacute; szolg&aacute;ltat&aacute;s-kies&eacute;s&eacute;rt a Szolg&aacute;ltat&oacute;t semmilyen felelőss&eacute;g nem terheli. A Szolg&aacute;ltat&oacute; &aacute;ltal a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;hez biztos&iacute;tott valamennyi eszk&ouml;z a Szolg&aacute;ltat&oacute; tulajdon&aacute;t k&eacute;pezi, annak kifog&aacute;stalan &aacute;llapotban t&ouml;rt&eacute;nő megőrz&eacute;s&eacute;&eacute;rt, rendeltet&eacute;sszerű haszn&aacute;lat&aacute;&eacute;rt, a Szerződ&eacute;s megszűn&eacute;s&eacute;vel egyidejűleg t&ouml;rt&eacute;nő visszaszolg&aacute;ltat&aacute;s&aacute;&eacute;rt az Előfizető felel.</span></p>\n<p><span style=\"font-size: 10pt;\">Az Előfizető tulajdon&aacute;ban l&eacute;vő eszk&ouml;z&ouml;k&ouml;n a Szolg&aacute;ltat&oacute; semmilyen be&aacute;ll&iacute;t&aacute;st nem v&eacute;gez, &nbsp;ezen eszk&ouml;z&ouml;k&ouml;n amennyiben sz&uuml;ks&eacute;g van &nbsp;Szolg&aacute;ltat&aacute;shoz konfigur&aacute;ci&oacute;ra, vagy egy&eacute;b m&oacute;dos&iacute;t&aacute;sra, cser&eacute;re, ez minden esetben az Előfizető felelőss&eacute;ge &eacute;s feladata.</span></p>\n<p><span style=\"font-size: 10pt;\">4.2. Amennyiben a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;hez sz&uuml;ks&eacute;ges műszaki, jogi, hat&oacute;s&aacute;gi felt&eacute;telek teljes&uuml;lnek &eacute;s a telep&iacute;t&eacute;s &eacute;sszerű k&ouml;lts&eacute;ghat&aacute;rok mellett megval&oacute;s&iacute;that&oacute;, akkor a Szolg&aacute;ltat&oacute; a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;nek lehetős&eacute;g&eacute;t az &eacute;rv&eacute;nyes Szerződ&eacute;s k&eacute;zhezv&eacute;tel&eacute;től sz&aacute;m&iacute;tott 30 (harminc) napon bel&uuml;l biztos&iacute;tja. Szerződ&eacute;s a Felek k&ouml;z&ouml;tt a Szolg&aacute;ltat&aacute;s műszaki megval&oacute;sul&aacute;s&aacute;val j&ouml;n l&eacute;tre &eacute;s l&eacute;p hat&aacute;lyba. A Szolg&aacute;ltat&aacute;s ny&uacute;jt&aacute;sa a sz&uuml;ks&eacute;ges IP kapcsolat &uuml;zemel&eacute;se első napja ut&aacute;ni munkanapon kezdődik. Amennyiben a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;hez sz&uuml;ks&eacute;ges műszaki, jogi, hat&oacute;s&aacute;gi felt&eacute;telek nem teljes&uuml;lnek, &eacute;s a telep&iacute;t&eacute;s az &eacute;sszerű k&ouml;lts&eacute;ghat&aacute;rok mellett nem megval&oacute;s&iacute;that&oacute;, akkor a Szolg&aacute;ltat&oacute; a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;nek lehetős&eacute;g&eacute;t nem biztos&iacute;tja, a Szerződ&eacute;s nem j&ouml;n l&eacute;tre. Indokolt esetben a Szolg&aacute;ltat&oacute; gondoskodik a megrendel&eacute;s elutas&iacute;t&aacute;s&aacute;r&oacute;l sz&oacute;l&oacute; t&aacute;j&eacute;koztat&aacute;sr&oacute;l. A Szolg&aacute;ltat&oacute; nem z&aacute;rja ki a jelen Előfizetői Szerződ&eacute;s egyező akarattal t&ouml;rt&eacute;nő esetleges m&oacute;dos&iacute;t&aacute;s&aacute;nak lehetős&eacute;g&eacute;t. Az Előfizetői Szerződ&eacute;s m&oacute;dos&iacute;t&aacute;s&aacute;nak tov&aacute;bbi eseteit az &Aacute;SZF 9. fejezete tartalmazza. A Szolg&aacute;ltat&aacute;s sz&aacute;ml&aacute;z&aacute;sa &eacute;s a hat&aacute;rozott időtartam&uacute; elk&ouml;telezetts&eacute;g kezdete a Szolg&aacute;ltat&aacute;s aktiv&aacute;l&aacute;s&aacute;t&oacute;l kezdődik.</span></p>\n<p><span style=\"font-size: 10pt;\">&nbsp;</span></p>\n<ol start=\"5\">\n<li><span style=\"font-size: 10pt;\"><strong> A Szerződ&eacute;s időtartama</strong></span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">Jelen Szerződ&eacute;st a Felek hat&aacute;rozatlan időtartamra k&ouml;tik meg egym&aacute;ssal, mely 30 (harminc) napos felmond&aacute;si idővel, indokol&aacute;s n&eacute;lk&uuml;l felmondhat&oacute; az Előfizető r&eacute;sz&eacute;ről.</span></p>\n<ol start=\"6\">\n<li><span style=\"font-size: 10pt;\"><strong> D&iacute;jak</strong></span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">Az Előfizető a Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;&eacute;rt</span></p>\n<ol>\n<li><span style=\"font-size: 10pt;\">a) egyszeri d&iacute;jat;</span></li>\n<li><span style=\"font-size: 10pt;\">b) havid&iacute;jat;</span></li>\n<li><span style=\"font-size: 10pt;\">c) havi forgalmi d&iacute;jat k&ouml;teles fizetni.</span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">Az egyszeri d&iacute;j a ki&eacute;p&iacute;t&eacute;st első havid&iacute;jjal egy&uuml;tt, a havid&iacute;j havonta előre, a havi forgalom havonta ut&oacute;lag fizetendő az adatlapon meghat&aacute;rozott d&iacute;jszab&aacute;s szerint, a Szolg&aacute;ltat&oacute; &aacute;ltal kibocs&aacute;tott sz&aacute;mla ellen&eacute;ben. Az Előfizető sz&aacute;ml&aacute;j&aacute;t eseti utal&aacute;ssal (a befizet&eacute;s azonos&iacute;t&aacute;s&aacute;ra szolg&aacute;l&oacute; k&ouml;zlem&eacute;ny rovatban a sz&aacute;mla sorsz&aacute;m&aacute;t fel kell t&uuml;ntetni), k&ouml;teles kiegyenl&iacute;teni a sz&aacute;mla ki&aacute;ll&iacute;t&aacute;s&aacute;t&oacute;l sz&aacute;m&iacute;tott 15 (tizen&ouml;t) napon bel&uuml;l.</span></p>\n<p><span style=\"font-size: 10pt;\">Az Előfizető k&ouml;teles a szolg&aacute;ltat&aacute;si d&iacute;jakat a r&eacute;sz&eacute;re megk&uuml;ld&ouml;tt sz&aacute;mla alapj&aacute;n havonta, a sz&aacute;ml&aacute;n felt&uuml;ntetett 15 (tizen&ouml;t) napos fizet&eacute;si hat&aacute;ridővel, banki &aacute;tutal&aacute;ssal megfizetni. K&eacute;sedelmes fizet&eacute;s eset&eacute;n a Szolg&aacute;ltat&oacute; a mindenkori jegybanki alapkamat k&eacute;tszeres&eacute;nek megfelelő k&eacute;sedelmi kamatra, valamint a szerződ&eacute;s azonnali hat&aacute;ly&uacute; felmond&aacute;s&aacute;ra jogosult.</span></p>\n<p><span style=\"font-size: 10pt;\">A Szolg&aacute;ltat&oacute; a Szolg&aacute;ltat&aacute;s d&iacute;jait jogosult a d&iacute;jv&aacute;ltoz&aacute;s hat&aacute;lyba l&eacute;p&eacute;s&eacute;t megelőzően legal&aacute;bb 15 (tizen&ouml;t) egyoldal&uacute;an m&oacute;dos&iacute;tani.</span></p>\n<p><span style=\"font-size: 10pt;\">A Szolg&aacute;ltat&oacute; legk&eacute;sőbb minden h&oacute;nap 5. (&ouml;t&ouml;dik) napj&aacute;n elektronikus d&iacute;jbek&eacute;rőt &aacute;ll&iacute;t ki, melyet &ndash; amennyiben az Előfizető rendelkezik ilyen hozz&aacute;f&eacute;r&eacute;ssel &ndash; el&eacute;rhetőv&eacute; tesz az Előfizető MyMCN oldal&aacute;n &eacute;s/vagy megk&uuml;ldi az Előfizető sz&aacute;m&aacute;ra, az Előfizető &aacute;ltal megadott e-mail c&iacute;mre. A d&iacute;jbek&eacute;rő ki&aacute;ll&iacute;tottnak &eacute;s k&eacute;zbes&iacute;tettnek tekintendő minden h&oacute;nap 5. (&ouml;t&ouml;dik) napj&aacute;t k&ouml;vetően.</span></p>\n<p><span style=\"font-size: 10pt;\">A Szolg&aacute;ltat&oacute; az Előfizető valamennyi ig&eacute;nybevett Szolg&aacute;ltat&aacute;s&aacute;r&oacute;l jogosult egyetlen sz&aacute;ml&aacute;t ki&aacute;ll&iacute;tani.</span></p>\n<p><span style=\"font-size: 10pt;\">Az Előfizető rendelkezhet t&ouml;bb Egy&eacute;ni Sz&aacute;ml&aacute;val is, de egy Egy&eacute;ni Sz&aacute;mla csak előre fizetett, vagy ut&oacute;lag fizetett forgalmi d&iacute;jas szolg&aacute;ltat&aacute;sokat kezelhet.</span></p>\n<p><span style=\"font-size: 10pt;\">Az Előfizető k&ouml;teles a beazonos&iacute;t&aacute;shoz sz&uuml;ks&eacute;ges inform&aacute;ci&oacute;kkal gondoskodni a tartoz&aacute;sa kiegyenl&iacute;t&eacute;s&eacute;ről, ennek hi&aacute;ny&aacute;ban a Szolg&aacute;ltat&oacute; a legk&ouml;zelebbi tartoz&aacute;ssal &eacute;rintett időszakhoz rendeli a be&eacute;rkezett &ouml;sszeget.</span></p>\n<p><span style=\"font-size: 10pt;\">Amennyiben az Előfizető a tartoz&aacute;st csak r&eacute;szben fizeti meg, &uacute;gy a Szolg&aacute;ltat&oacute; jogosult a be&eacute;rkezett &ouml;sszeget ar&aacute;nyosan, vagy egyes Szolg&aacute;ltat&aacute;sokhoz rendelten j&oacute;v&aacute;&iacute;rni.</span></p>\n<p><span style=\"font-size: 10pt;\"><strong>&nbsp;</strong></span></p>\n<ol start=\"7\">\n<li><span style=\"font-size: 10pt;\"><strong> Hibaelh&aacute;r&iacute;t&aacute;s</strong></span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">7.1 Hib&aacute;s teljes&iacute;t&eacute;s eset&eacute;n a Szolg&aacute;ltat&oacute; minden tőle elv&aacute;rhat&oacute;t megtesz annak &eacute;rdek&eacute;ben, hogy a hiba bejelent&eacute;s&eacute;től sz&aacute;m&iacute;tott 72 (hetvenk&eacute;t) &oacute;r&aacute;n bel&uuml;l a hibaforr&aacute;st kik&uuml;sz&ouml;b&ouml;lje, &eacute;s a hib&aacute;tlan teljes&iacute;t&eacute;st biztos&iacute;tsa.</span></p>\n<p><span style=\"font-size: 10pt;\">Az esetleges hib&aacute;t a 2. pont szerinti el&eacute;rhetős&eacute;gek egyik&eacute;n lehet a Szolg&aacute;ltat&oacute; r&eacute;sz&eacute;re bejelenteni. Ha a kivizsg&aacute;l&aacute;s vagy a kijav&iacute;t&aacute;s kiz&aacute;r&oacute;lag a helysz&iacute;nen, az Előfizető helyis&eacute;g&eacute;ben &eacute;s az Előfizető &aacute;ltal meghat&aacute;rozott időpontban lehets&eacute;ges, vagy ha a kijav&iacute;t&aacute;s a Szolg&aacute;ltat&oacute; &eacute;s az Előfizető &aacute;ltal meghat&aacute;rozott időpontban a Szolg&aacute;ltat&oacute; &eacute;rdekk&ouml;r&eacute;n k&iacute;v&uuml;l eső okok miatt nem volt lehets&eacute;ges, a fenti 72 (hetvenk&eacute;t) &oacute;r&aacute;s hat&aacute;ridő a kies&eacute;s időtartam&aacute;val meghosszabbodik. Amennyiben a Szolg&aacute;ltat&aacute;s a hiba bejelent&eacute;s&eacute;től sz&aacute;m&iacute;tott 72 (hetvenk&eacute;t) &oacute;r&aacute;t meghalad&oacute; időtartamban a Szolg&aacute;ltat&oacute;nak felr&oacute;hat&oacute; ok miatt nem vehető ig&eacute;nybe, a Szolg&aacute;ltat&oacute; k&ouml;teles a 73. (hetvenharmadik) &oacute;r&aacute;t&oacute;l a hiba elh&aacute;r&iacute;t&aacute;s&aacute;ig tart&oacute; időszakra k&ouml;tb&eacute;rt fizetni az &Aacute;SZF 6.3 pontja szerint</span></p>\n<p><span style=\"font-size: 10pt;\">7.2 Szolg&aacute;ltat&oacute;t csak a szerződ&eacute;s szerinti szolg&aacute;ltat&aacute;sok ny&uacute;jt&aacute;s&aacute;val &eacute;s saj&aacute;t h&aacute;l&oacute;zat&aacute;val &ouml;sszef&uuml;gg&eacute;sben felmer&uuml;lt hib&aacute;k&eacute;rt terheli felelőss&eacute;g. Nem terheli felelőss&eacute;g a Szolg&aacute;ltat&oacute;t:</span></p>\n<p><span style=\"font-size: 10pt;\">7.2.1 Előfizető műszaki berendez&eacute;s&eacute;nek hib&aacute;ja vagy alkalmatlans&aacute;ga (pl. helyi h&aacute;l&oacute;zati eszk&ouml;z&ouml;k hib&aacute;ja),</span></p>\n<p><span style=\"font-size: 10pt;\">7.2.2 a műszaki berendez&eacute;s vagy a Szolg&aacute;ltat&aacute;s helytelen vagy rendeltet&eacute;sellenes haszn&aacute;lata,</span></p>\n<p><span style=\"font-size: 10pt;\">7.2.3 Előfizető &aacute;ltal a hozz&aacute;f&eacute;r&eacute;sben okozott hiba (pl. k&aacute;belszakad&aacute;s);</span></p>\n<p><span style=\"font-size: 10pt;\">7.2.4 a Szerződ&eacute;sben foglalt k&ouml;telezetts&eacute;g&eacute;nek vagy jogszab&aacute;lyi elő&iacute;r&aacute;sok Előfizető &aacute;ltali megszeg&eacute;se,</span></p>\n<p><span style=\"font-size: 10pt;\">7.2.5 A Szolg&aacute;ltat&aacute;s megszak&iacute;t&aacute;sa vagy korl&aacute;toz&aacute;sa, m&aacute;s Szolg&aacute;ltat&oacute; &aacute;ltal ny&uacute;jtott hozz&aacute;f&eacute;r&eacute;s vagy kapcsol&oacute;d&aacute;s megszakad&aacute;sa miatt,</span></p>\n<p><span style=\"font-size: 10pt;\">7.2.6 t&aacute;pell&aacute;t&aacute;s hib&aacute;ja, vagy</span></p>\n<p><span style=\"font-size: 10pt;\">7.2.7 vis major miatt.</span></p>\n<p><span style=\"font-size: 10pt;\">Előfizető a Szolg&aacute;ltat&aacute;ssal kapcsolatos hib&aacute;kat a Szolg&aacute;ltat&oacute; 2. pontj&aacute;ban ismertetett hibabejelentő el&eacute;rhetős&eacute;geken jelentheti be. A hibabejelent&eacute;s &eacute;s sz&aacute;mlapanasz kezel&eacute;s szab&aacute;lyait &eacute;s a Szolg&aacute;ltat&aacute;s minős&eacute;g&eacute;vel kapcsolatos rendelkez&eacute;seket az &Aacute;SZF 15. illetve 16. fejezete tartalmazza, valamint az &Aacute;SZF 5. sz. mell&eacute;klete</span></p>\n<ol start=\"8\">\n<li><span style=\"font-size: 10pt;\"><strong> A Szolg&aacute;ltat&aacute;s korl&aacute;toz&aacute;s&aacute;nak esetei</strong></span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">A Szolg&aacute;ltat&aacute;s ig&eacute;nybev&eacute;tel&eacute;nek korl&aacute;toz&aacute;sa, &iacute;gy k&uuml;l&ouml;n&ouml;sen az Előfizető &aacute;ltal ind&iacute;tott vagy az Előfizetőn&eacute;l v&eacute;gződtetett forgalom korl&aacute;toz&aacute;s&aacute;ra, a Szolg&aacute;ltat&oacute; minős&eacute;gi vagy m&aacute;s jellemzőkkel cs&ouml;kkent&eacute;s&eacute;re a Szolg&aacute;ltat&oacute; - az Előfizető egyidejű &eacute;rtes&iacute;t&eacute;se mellett - a k&ouml;vetkező esetekben jogosult:</span></p>\n<ol>\n<li><span style=\"font-size: 10pt;\">a) Előfizető akad&aacute;lyozza, vagy vesz&eacute;lyezteti a Szolg&aacute;ltat&oacute; rendszer&eacute;nek rendeltet&eacute;sszerű műk&ouml;d&eacute;s&eacute;t, &iacute;gy k&uuml;l&ouml;n&ouml;sen, ha az Előfizető a rendszerhez megfelelős&eacute;g-tan&uacute;s&iacute;t&aacute;ssal nem rendelkező v&eacute;gberendez&eacute;st csatlakoztat</span></li>\n<li><span style=\"font-size: 10pt;\">b) Előfizetőnek lej&aacute;rt d&iacute;jtartoz&aacute;sa van.</span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">A Szolg&aacute;ltat&oacute; a korl&aacute;toz&aacute;st halad&eacute;ktalanul megsz&uuml;nteti, ha az Előfizető a korl&aacute;toz&aacute;s ok&aacute;t megsz&uuml;nteti &eacute;s erről a Szolg&aacute;ltat&oacute; hitelt &eacute;rdemlő m&oacute;don tudom&aacute;st szerez. A Szolg&aacute;ltat&aacute;s korl&aacute;toz&aacute;sa eset&eacute;n is a Szolg&aacute;ltat&oacute; biztos&iacute;tja:</span></p>\n<ol>\n<li><span style=\"font-size: 10pt;\">a) az Előfizető h&iacute;vhat&oacute;s&aacute;g&aacute;t,</span></li>\n<li><span style=\"font-size: 10pt;\">b) a seg&eacute;lyk&eacute;rő h&iacute;v&aacute;sok tov&aacute;bb&iacute;t&aacute;s&aacute;t,</span></li>\n<li><span style=\"font-size: 10pt;\">c) a Szolg&aacute;ltat&oacute; &uuml;gyf&eacute;lszolg&aacute;lat&aacute;nak (hibabejelentőj&eacute;nek) el&eacute;rhetős&eacute;g&eacute;t.</span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">Az Előfizetői szerződ&eacute;s megszűn&eacute;s&eacute;nek eseteit az &Aacute;SZF 12. pontja tartalmazza. Az Előfizetői szerződ&eacute;s sz&uuml;neteltet&eacute;s&eacute;ről az &Aacute;SZF 7. pontja, a Szolg&aacute;ltat&aacute;s korl&aacute;toz&aacute;s&aacute;nak felt&eacute;teleiről az &Aacute;SZF 9. pontja rendelkezik. Panasz eset&eacute;n az Előfizető az &Aacute;SZF 1. pontban felsorolt hat&oacute;s&aacute;gokhoz fordulhat.</span></p>\n<p><span style=\"font-size: 10pt;\">&nbsp;</span></p>\n<ol start=\"9\">\n<li><span style=\"font-size: 10pt;\"><strong> A Szerződ&eacute;s felmond&aacute;sa</strong></span></li>\n</ol>\n<p><span style=\"font-size: 10pt;\">A szerződ&eacute;s mindk&eacute;t f&eacute;l al&aacute;&iacute;r&aacute;s&aacute;val &eacute;s/vagy egyező akaratnyilv&aacute;n&iacute;t&aacute;s&aacute;val j&ouml;n l&eacute;tre, &eacute;s a szolg&aacute;ltat&aacute;s biztos&iacute;t&aacute;s&aacute;val/ny&uacute;jt&aacute;s&aacute;val l&eacute;p hat&aacute;lyba.</span></p>\n<p><span style=\"font-size: 10pt;\">K&eacute;sedelmes fizet&eacute;s eset&eacute;n a Szolg&aacute;ltat&oacute; a mindenkori jegybanki alapkamat k&eacute;tszeres&eacute;nek megfelelő k&eacute;sedelmi kamatra, valamint a szerződ&eacute;s azonnali hat&aacute;ly&uacute; felmond&aacute;s&aacute;ra jogosult.</span></p>\n<h2><span style=\"font-size: 10pt;\">9.1. A Szolg&aacute;ltat&oacute; r&eacute;sz&eacute;ről t&ouml;rt&eacute;nő rendk&iacute;v&uuml;li felmond&aacute;s</span></h2>\n<h2><span style=\"font-size: 10pt;\">A Szolg&aacute;ltat&oacute; jogosult rendk&iacute;v&uuml;li felmond&aacute;ssal, azonnali hat&aacute;llyal megsz&uuml;ntetni a jelen szerződ&eacute;st, amennyiben:</span></h2>\n<ul>\n<li><span style=\"font-size: 10pt;\">az Előfizető a sz&aacute;ml&aacute;kat - a havi sz&aacute;ml&aacute;n felt&uuml;ntetett fizet&eacute;si hat&aacute;ridőn t&uacute;l - k&eacute;sedelmesen fizeti;</span></li>\n<li><span style=\"font-size: 10pt;\">az Előfizető a Szolg&aacute;ltat&oacute; vonatkoz&oacute; &Aacute;ltal&aacute;nos Szerződ&eacute;si Felt&eacute;teleiben foglaltakat megs&eacute;rti;</span></li>\n<li><span style=\"font-size: 10pt;\">az Előfizető ellen csőd-, felsz&aacute;mol&aacute;si, v&eacute;gelsz&aacute;mol&aacute;si elj&aacute;r&aacute;s indul, ezekben az esetekben a Szolg&aacute;ltat&oacute; rendk&iacute;v&uuml;li felmond&aacute;s&aacute;nak joga a csőd-, felsz&aacute;mol&aacute;si, v&eacute;gelsz&aacute;mol&aacute;si elj&aacute;r&aacute;s &nbsp;kihirdet&eacute;se napj&aacute;n ny&iacute;lik meg.</span></li>\n</ul>\n<h2><span style=\"font-size: 10pt;\">&nbsp;</span></h2>\n<h2><span style=\"font-size: 10pt;\">Egy&eacute;b rendelkez&eacute;sek &eacute;s nyilatkozatok</span></h2>\n<p><span style=\"font-size: 10pt;\">Tekintettel arra, hogy a Szolg&aacute;ltat&oacute; az elektronikus szerződ&eacute;sk&ouml;t&eacute;st &eacute;s kapcsolattart&aacute;st r&eacute;szes&iacute;ti előnyben, &iacute;gy előfizetői sz&aacute;m&aacute;ra szem&eacute;lyes webes fel&uuml;letet biztos&iacute;t MyMCN n&eacute;ven, amennyiben az Előfizető ezt ig&eacute;nyli. A MyMCN haszn&aacute;lata kiz&aacute;r&oacute;lag az arra &eacute;rv&eacute;nyes Felhaszn&aacute;l&aacute;si Felt&eacute;telek elfogad&aacute;sa mellett lehets&eacute;ges.</span></p>\n<p><span style=\"font-size: 10pt;\">A műszaki egyeztet&eacute;sek sor&aacute;n r&ouml;gz&iacute;tett adatok helyess&eacute;g&eacute;&eacute;rt az Előfizető felel, amennyiben a k&eacute;sőbbiekben ezek egy r&eacute;sze vagy eg&eacute;sze nem bizonyul helyt&aacute;ll&oacute;nak, az ebből eredő t&ouml;bbletk&ouml;lts&eacute;gek az Előfizetőt terhelik.</span></p>\n<p><span style=\"font-size: 10pt;\">Az előfizetői szerződ&eacute;st a Felek egyező akarattal, a szerződ&eacute;sk&ouml;t&eacute;s alakis&aacute;g&aacute;nak megfelelő form&aacute;ban m&oacute;dos&iacute;thatj&aacute;k.</span></p>\n<p><span style=\"font-size: 10pt;\">Az ig&eacute;nybevett Szolg&aacute;ltat&aacute;s nem egyetemes szolg&aacute;ltat&aacute;s.</span></p>\n<p><span style=\"font-size: 10pt;\">A szolg&aacute;ltat&oacute;i szerződ&eacute;sszeg&eacute;s jogk&ouml;vetkezm&eacute;nyeit, &iacute;gy k&uuml;l&ouml;n&ouml;sen a Szolg&aacute;ltat&aacute;s minős&eacute;g&eacute;re, sz&uuml;neteltet&eacute;s&eacute;re vonatkoz&oacute; rendelkez&eacute;sek megszeg&eacute;se eset&eacute;n az Előfizetőt megillető jogokat, a d&iacute;jvisszat&eacute;r&iacute;t&eacute;s rendj&eacute;t, az Előfizetőt megillető k&ouml;tb&eacute;r m&eacute;rt&eacute;k&eacute;t az &Aacute;SZF 6.3 pontja tartalmazza.</span></p>\n<p><span style=\"font-size: 10pt;\">A Szolg&aacute;ltat&aacute;s sz&uuml;neteltet&eacute;s&eacute;nek &eacute;s korl&aacute;toz&aacute;s&aacute;nak felt&eacute;teleit az &Aacute;SZF 5., karbantart&aacute;sra vonatkoz&oacute; inform&aacute;ci&oacute;kat az &Aacute;SZF 5.1.1. pontja tartalmazza.</span></p>\n<p><span style=\"font-size: 10pt;\">Az Előfizető d&iacute;jcsomagot a k&ouml;vetkező h&oacute;nap első napj&aacute;t&oacute;l kezdődően v&aacute;lthat, amennyiben az ezir&aacute;ny&uacute; k&eacute;relme a d&iacute;jcsomag v&aacute;lt&aacute;s hat&aacute;lyba l&eacute;p&eacute;s&eacute;t legk&eacute;sőbb az azt megelőző utols&oacute;előtti munkanapon be&eacute;rkezik a Szolg&aacute;ltat&oacute;hoz.</span></p>\n<p><span style=\"font-size: 10pt;\">Az Előfizető kifejezetten hozz&aacute;j&aacute;rul az elektronikus h&iacute;rk&ouml;zl&eacute;sről sz&oacute;l&oacute; 2003. &eacute;vi C. t&ouml;rv&eacute;ny (a tov&aacute;bbiakban: &bdquo;Eht.&rdquo;) 157. &sect; (2) bekezd&eacute;s&eacute;ben nem neves&iacute;tett adatai kezel&eacute;s&eacute;hez, illetve az adatai c&eacute;lhoz k&ouml;t&ouml;tt, Eht.-ban meghat&aacute;rozott c&eacute;lokt&oacute;l elt&eacute;rő m&oacute;don t&ouml;rt&eacute;nő felhaszn&aacute;l&aacute;s&aacute;hoz.</span></p>\n<p><span style=\"font-size: 10pt;\">Az Előfizető az e-mail c&iacute;me megad&aacute;s&aacute;val hozz&aacute;j&aacute;rul ahhoz, hogy a Szolg&aacute;ltat&oacute; hivatalos &eacute;rtes&iacute;t&eacute;st r&eacute;sz&eacute;re az &aacute;ltala megadott e-mail c&iacute;mre k&uuml;ldj&ouml;n elektronikus lev&eacute;l form&aacute;j&aacute;ban.</span></p>\n<p><span style=\"font-size: 10pt;\">Az Előfizető jelen dokumentum elfogad&aacute;s&aacute;val kijelenti, hogy a kapcsolattart&aacute;sra megjel&ouml;lt email-c&iacute;mre &eacute;rkező elektronikus &eacute;rtes&iacute;t&eacute;st (elektronikus dokumentumban vagy az elektronikus lev&eacute;lben foglalt &eacute;rtes&iacute;t&eacute;s) elfogadja &eacute;s v&aacute;llalja a k&eacute;zbes&iacute;t&eacute;si igazol&aacute;s megk&uuml;ld&eacute;s&eacute;t.</span></p>\n<p><span style=\"font-size: 10pt;\">A Szolg&aacute;ltat&oacute; fenntartja mag&aacute;nak a jogot, hogy hűs&eacute;ges előfizetői sz&aacute;m&aacute;ra időszakosan kedvezm&eacute;nyt biztos&iacute;tson.</span></p>\n<p><span style=\"font-size: 10pt;\">Jelen előfizetői szerződ&eacute;s mell&eacute;klete (1. sz&aacute;m&uacute; mell&eacute;klet) a tartalmazza a Szolg&aacute;ltat&aacute;s d&iacute;jait azzal, hogy amely d&iacute;jak nem ker&uuml;ltek felt&uuml;ntet&eacute;sre abban, &uacute;gy azokra a Szolg&aacute;ltat&oacute; mindenkor hat&aacute;lyos &Aacute;SZF-j&eacute;ben foglaltak alkalmazand&oacute;k.</span></p>\n<p><span style=\"font-size: 10pt;\">&nbsp;</span></p>\n<p><span style=\"font-size: 10pt;\">Felek meg&aacute;llapodnak abban, hogy jelen Szerződ&eacute;s &eacute;s a vonatkoz&oacute; &Aacute;SZF elt&eacute;r&eacute;se eset&eacute;n a jelen Szerződ&eacute;sben foglalt szab&aacute;lyok &eacute;rv&eacute;nyesek.</span></p>\n<p><span style=\"font-size: 10pt;\">&nbsp;</span></p>\n<p><span style=\"font-size: 10pt;\">Az Előfizető jelen előfizetői szerződ&eacute;sben szereplő felt&eacute;teleket elolvasta, megismerte &eacute;s &eacute;rtelmezte, &eacute;s azt - mint akarat&aacute;val mindenben megegyezőt -, arra feljogos&iacute;tott k&eacute;pviselője &uacute;tj&aacute;n &iacute;rta al&aacute;.</span></p>\n<p><span style=\"font-size: 10pt;\">&nbsp;</span></p>\n<table>\n<tbody>\n<tr>\n<td width=\"108\">\n<p><span style=\"font-size: 10pt;\">d&aacute;tum</span></p>\n</td>\n<td width=\"192\">\n<p><span style=\"font-size: 10pt;\">&nbsp;{$contract_date}</span></p>\n</td>\n<td width=\"173\">\n<p><span style=\"font-size: 10pt;\">d&aacute;tum</span></p>\n</td>\n<td width=\"169\">\n<p><span style=\"font-size: 10pt;\">_____________________</span></p>\n</td>\n</tr>\n<tr>\n<td width=\"108\">\n<p><span style=\"font-size: 10pt;\">Szolg&aacute;ltat&oacute;</span></p>\n</td>\n<td width=\"192\">\n<p><span style=\"font-size: 10pt;\">_____________________</span></p>\n<p><span style=\"font-size: 10pt;\">{$organization_name}</span></p>\n</td>\n<td width=\"173\">\n<p><span style=\"font-size: 10pt;\">Előfizető</span></p>\n</td>\n<td width=\"169\">\n<p><span style=\"font-size: 10pt;\">{$fio}</span></p>\n</td>\n</tr>\n<tr>\n<td width=\"108\">\n<p><span style=\"font-size: 10pt;\">&nbsp;</span></p>\n</td>\n<td width=\"192\">\n<p><span style=\"font-size: 10pt;\">&nbsp;</span></p>\n</td>\n<td width=\"173\">\n<p><span style=\"font-size: 10pt;\">előfizető (nyomtatott betűkkel)</span></p>\n</td>\n<td width=\"169\">\n<p><span style=\"font-size: 10pt;\">_____________________</span></p>\n</td>\n</tr>\n</tbody>\n</table>','contract',0);
/*!40000 ALTER TABLE `document_template` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `domains`
--

DROP TABLE IF EXISTS `domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '1970-01-02',
  `actual_to` date NOT NULL DEFAULT '1970-01-02',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `client` varchar(32) NOT NULL DEFAULT '',
  `primary_mx` varchar(64) NOT NULL DEFAULT '',
  `registrator` enum('','RUCENTER-REG-RIPN') NOT NULL,
  `rucenter_form_no` decimal(6,0) NOT NULL,
  `dns` varchar(64) NOT NULL DEFAULT '',
  `paid_till` date NOT NULL DEFAULT '1970-01-02',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=349 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domains`
--

LOCK TABLES `domains` WRITE;
/*!40000 ALTER TABLE `domains` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `e164_stat`
--

DROP TABLE IF EXISTS `e164_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `e164_stat` (
  `pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `e164` varchar(11) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `action` varchar(50) NOT NULL,
  `client` int(10) unsigned DEFAULT NULL,
  `user` int(10) unsigned DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `addition` text,
  PRIMARY KEY (`pk`),
  KEY `work` (`e164`)
) ENGINE=InnoDB AUTO_INCREMENT=88678 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `e164_stat`
--

LOCK TABLES `e164_stat` WRITE;
/*!40000 ALTER TABLE `e164_stat` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `e164_stat` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `email_whitelist`
--

DROP TABLE IF EXISTS `email_whitelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_whitelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `local_part` varchar(100) NOT NULL DEFAULT '',
  `domain` varchar(100) NOT NULL DEFAULT '',
  `sender_address` varchar(100) NOT NULL DEFAULT '',
  `sender_address_domain` varchar(100) NOT NULL DEFAULT '',
  `sender_host_address` varchar(100) NOT NULL DEFAULT '',
  `comment` varchar(100) NOT NULL DEFAULT '',
  `rbl_enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=2798 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_whitelist`
--

LOCK TABLES `email_whitelist` WRITE;
/*!40000 ALTER TABLE `email_whitelist` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `email_whitelist` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `emailaliases`
--

DROP TABLE IF EXISTS `emailaliases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailaliases` (
  `local_part` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `to_local_part` varchar(64) NOT NULL DEFAULT '',
  `to_domain` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`local_part`,`domain`,`to_local_part`,`to_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailaliases`
--

LOCK TABLES `emailaliases` WRITE;
/*!40000 ALTER TABLE `emailaliases` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `emailaliases` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `emails`
--

DROP TABLE IF EXISTS `emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `actual_from` date NOT NULL DEFAULT '1970-01-02',
  `actual_to` date NOT NULL DEFAULT '1970-01-02',
  `local_part` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `password` varchar(64) NOT NULL DEFAULT '',
  `client` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `box_size` int(11) NOT NULL DEFAULT '0',
  `box_quota` int(11) NOT NULL DEFAULT '50000',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `spam_act` enum('pass','mark','discard') NOT NULL DEFAULT 'pass',
  `smtp_auth` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working',
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `local_part` (`local_part`,`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=3344 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emails`
--

LOCK TABLES `emails` WRITE;
/*!40000 ALTER TABLE `emails` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `emails` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `event_queue`
--

DROP TABLE IF EXISTS `event_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_queue` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `event` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `param` text,
  `status` enum('plan','ok','error','stop') NOT NULL DEFAULT 'plan',
  `iteration` smallint(6) NOT NULL DEFAULT '0',
  `next_start` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00',
  `log_error` text,
  `code` char(32) NOT NULL,
  `insert_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `is_handled` (`status`) USING BTREE,
  KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=58247 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_queue`
--

LOCK TABLES `event_queue` WRITE;
/*!40000 ALTER TABLE `event_queue` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `event_queue` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `firma_pay_account`
--

DROP TABLE IF EXISTS `firma_pay_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firma_pay_account` (
  `pay_acc` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `firma` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`pay_acc`,`firma`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `firma_pay_account`
--

LOCK TABLES `firma_pay_account` WRITE;
/*!40000 ALTER TABLE `firma_pay_account` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `firma_pay_account` VALUES ('301422002','markomnet'),('301422002','mcn'),('301422002','mcn_telekom'),('301422002','ooomcn'),('301423001','all4net'),('301423002','all4net'),('40702810038000034045','mcm_telekom'),('40702810038000034045','mcn_telekom'),('40702810038110015462','mcm_telekom'),('40702810038110015462','mcn'),('40702810038110015462','mcn_telekom'),('40702810500540000002','all4net'),('40702810700320000882','markomnet'),('40702810700320000882','mcn'),('40702810700320000882','mcn_telekom'),('40702810700320000882','ooomcn'),('40702810800540001507','ooocmc'),('40702810900301423002','all4net');
/*!40000 ALTER TABLE `firma_pay_account` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_bonus`
--

DROP TABLE IF EXISTS `g_bonus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_bonus` (
  `good_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `group` enum('telemarketing','marketing','manager') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'telemarketing',
  `value` decimal(5,2) NOT NULL DEFAULT '0.00',
  `type` enum('fix','%') NOT NULL DEFAULT '%',
  PRIMARY KEY (`good_id`,`group`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_bonus`
--

LOCK TABLES `g_bonus` WRITE;
/*!40000 ALTER TABLE `g_bonus` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_bonus` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_division`
--

DROP TABLE IF EXISTS `g_division`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_division` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `k_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_division`
--

LOCK TABLES `g_division` WRITE;
/*!40000 ALTER TABLE `g_division` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `g_division` VALUES (1,'All4Net'),(2,'Compapa'),(3,'WellTime');
/*!40000 ALTER TABLE `g_division` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_good_description`
--

DROP TABLE IF EXISTS `g_good_description`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_good_description` (
  `id` char(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `good_id` char(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `name` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `k_good` (`good_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_good_description`
--

LOCK TABLES `g_good_description` WRITE;
/*!40000 ALTER TABLE `g_good_description` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_good_description` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_good_price`
--

DROP TABLE IF EXISTS `g_good_price`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_good_price` (
  `good_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `descr_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `price_type_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `price` decimal(11,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`price_type_id`,`good_id`,`descr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_good_price`
--

LOCK TABLES `g_good_price` WRITE;
/*!40000 ALTER TABLE `g_good_price` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_good_price` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_good_store`
--

DROP TABLE IF EXISTS `g_good_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_good_store` (
  `good_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `descr_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `qty_free` int(4) NOT NULL DEFAULT '0',
  `qty_store` int(4) NOT NULL DEFAULT '0',
  `qty_wait` int(4) NOT NULL DEFAULT '0',
  `store_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '8e5c7b22-8385-11df-9af5-001517456eb1',
  PRIMARY KEY (`store_id`,`good_id`,`descr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_good_store`
--

LOCK TABLES `g_good_store` WRITE;
/*!40000 ALTER TABLE `g_good_store` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_good_store` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_goods`
--

DROP TABLE IF EXISTS `g_goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_goods` (
  `id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `num_id` int(4) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
  `name_full` varchar(4096) NOT NULL DEFAULT '',
  `art` varchar(32) NOT NULL DEFAULT '',
  `price` float(7,2) NOT NULL DEFAULT '0.00',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `quantity_store` int(11) NOT NULL DEFAULT '0',
  `producer_id` int(11) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `is_service` int(1) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `division_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_allowpricezero` int(1) NOT NULL DEFAULT '0',
  `is_allowpricechange` int(1) NOT NULL DEFAULT '0',
  `store` enum('','yes','no','remote') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `nds` int(4) NOT NULL DEFAULT '18',
  `unit_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `k_group` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_goods`
--

LOCK TABLES `g_goods` WRITE;
/*!40000 ALTER TABLE `g_goods` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_goods` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_groups`
--

DROP TABLE IF EXISTS `g_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_groups` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `k_parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_groups`
--

LOCK TABLES `g_groups` WRITE;
/*!40000 ALTER TABLE `g_groups` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_groups` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_gtd`
--

DROP TABLE IF EXISTS `g_gtd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_gtd` (
  `id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `code` char(30) NOT NULL,
  `country_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_country` (`country_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_gtd`
--

LOCK TABLES `g_gtd` WRITE;
/*!40000 ALTER TABLE `g_gtd` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_gtd` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_income_document`
--

DROP TABLE IF EXISTS `g_income_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_income_document` (
  `id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `order_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `active` smallint(1) NOT NULL COMMENT 'active: 1 - true, 0 - false',
  `deleted` smallint(1) NOT NULL COMMENT 'deleted: 1 - true, 0 - false',
  `number` varchar(11) NOT NULL,
  `date` datetime NOT NULL,
  `client_card_id` int(11) NOT NULL,
  `organization_id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `store_id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `currency` char(3) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `price_includes_nds` smallint(1) NOT NULL COMMENT 'Is price includes NDS: 1 - true, 0 - false',
  `sum` decimal(15,2) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_currency` (`currency`) USING BTREE,
  KEY `fk_client_card` (`client_card_id`) USING BTREE,
  KEY `fk_store` (`store_id`) USING BTREE,
  KEY `fk_organization` (`organization_id`) USING BTREE,
  KEY `idx_order` (`order_id`) USING BTREE,
  CONSTRAINT `client_card` FOREIGN KEY (`client_card_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `organization` FOREIGN KEY (`organization_id`) REFERENCES `g_organization` (`id`),
  CONSTRAINT `store` FOREIGN KEY (`store_id`) REFERENCES `g_store` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_income_document`
--

LOCK TABLES `g_income_document` WRITE;
/*!40000 ALTER TABLE `g_income_document` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_income_document` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_income_document_lines`
--

DROP TABLE IF EXISTS `g_income_document_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_income_document_lines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `order_id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin DEFAULT NULL,
  `line_code` int(11) NOT NULL,
  `good_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `good_ext_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `price` decimal(15,6) unsigned NOT NULL,
  `amount` decimal(15,3) NOT NULL,
  `sum` decimal(15,2) unsigned NOT NULL,
  `sum_nds` decimal(15,2) unsigned NOT NULL,
  `gtd_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_document` (`document_id`) USING BTREE,
  KEY `order` (`order_id`) USING BTREE,
  CONSTRAINT `document` FOREIGN KEY (`document_id`) REFERENCES `g_income_document` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20979 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_income_document_lines`
--

LOCK TABLES `g_income_document_lines` WRITE;
/*!40000 ALTER TABLE `g_income_document_lines` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_income_document_lines` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_income_order`
--

DROP TABLE IF EXISTS `g_income_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_income_order` (
  `id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `active` smallint(1) NOT NULL COMMENT 'active: 1 - true, 0 - false',
  `ready` smallint(1) NOT NULL,
  `deleted` smallint(1) NOT NULL COMMENT 'deleted: 1 - true, 0 - false',
  `number` varchar(11) NOT NULL,
  `date` datetime NOT NULL,
  `client_card_id` int(11) NOT NULL,
  `external_number` varchar(20) NOT NULL DEFAULT '',
  `external_date` date DEFAULT NULL,
  `status` varchar(25) NOT NULL,
  `organization_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `store_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `price_includes_nds` smallint(1) NOT NULL COMMENT 'Is price includes NDS: 1 - true, 0 - false',
  `sum` decimal(15,2) NOT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `comment` text NOT NULL,
  `is_payed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_currency` (`currency`) USING BTREE,
  KEY `fk_client_card` (`client_card_id`) USING BTREE,
  KEY `fk_store` (`store_id`) USING BTREE,
  KEY `fk_organization` (`organization_id`) USING BTREE,
  KEY `fk_manager` (`manager_id`) USING BTREE,
  KEY `number` (`number`),
  CONSTRAINT `client_card_id` FOREIGN KEY (`client_card_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `fk_manager` FOREIGN KEY (`manager_id`) REFERENCES `user_users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_income_order`
--

LOCK TABLES `g_income_order` WRITE;
/*!40000 ALTER TABLE `g_income_order` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_income_order` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_income_order_lines`
--

DROP TABLE IF EXISTS `g_income_order_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_income_order_lines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `line_code` int(11) DEFAULT NULL,
  `good_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `good_ext_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `price` decimal(15,6) unsigned DEFAULT NULL,
  `amount` decimal(15,3) DEFAULT NULL,
  `sum` decimal(15,2) unsigned DEFAULT NULL,
  `sum_nds` decimal(15,2) unsigned DEFAULT NULL,
  `incoming_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`) USING BTREE,
  CONSTRAINT `fk_order` FOREIGN KEY (`order_id`) REFERENCES `g_income_order` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55483 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_income_order_lines`
--

LOCK TABLES `g_income_order_lines` WRITE;
/*!40000 ALTER TABLE `g_income_order_lines` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_income_order_lines` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_income_store`
--

DROP TABLE IF EXISTS `g_income_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_income_store` (
  `id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `order_id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `active` smallint(1) NOT NULL COMMENT 'active: 1 - true, 0 - false',
  `deleted` smallint(1) NOT NULL COMMENT 'deleted: 1 - true, 0 - false',
  `number` varchar(11) NOT NULL,
  `date` datetime NOT NULL,
  `status` varchar(25) NOT NULL,
  `store_id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `responsible` varchar(50) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_store` (`store_id`) USING BTREE,
  KEY `idx_order` (`order_id`) USING BTREE,
  CONSTRAINT `store_id` FOREIGN KEY (`store_id`) REFERENCES `g_store` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_income_store`
--

LOCK TABLES `g_income_store` WRITE;
/*!40000 ALTER TABLE `g_income_store` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_income_store` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_income_store_lines`
--

DROP TABLE IF EXISTS `g_income_store_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_income_store_lines` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `order_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `good_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `good_ext_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `amount` decimal(15,3) NOT NULL,
  `serial_numbers` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_document` (`document_id`) USING BTREE,
  KEY `idx_order_id` (`order_id`) USING BTREE,
  CONSTRAINT `fk_document` FOREIGN KEY (`document_id`) REFERENCES `g_income_store` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53438 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_income_store_lines`
--

LOCK TABLES `g_income_store_lines` WRITE;
/*!40000 ALTER TABLE `g_income_store_lines` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_income_store_lines` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_notification_limits`
--

DROP TABLE IF EXISTS `g_notification_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_notification_limits` (
  `good_id` char(36) NOT NULL,
  `store_id` char(36) NOT NULL,
  `user_id` int(11) NOT NULL,
  `limit_value` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`good_id`,`store_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_notification_limits`
--

LOCK TABLES `g_notification_limits` WRITE;
/*!40000 ALTER TABLE `g_notification_limits` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_notification_limits` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_order_free_goods`
--

DROP TABLE IF EXISTS `g_order_free_goods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_order_free_goods` (
  `bill_no` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `good_id` varchar(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `descr_id` varchar(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `last_free` int(11) NOT NULL,
  UNIQUE KEY `pk` (`bill_no`,`good_id`,`descr_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_order_free_goods`
--

LOCK TABLES `g_order_free_goods` WRITE;
/*!40000 ALTER TABLE `g_order_free_goods` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_order_free_goods` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_organization`
--

DROP TABLE IF EXISTS `g_organization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_organization` (
  `id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `name` varchar(100) NOT NULL,
  `jur_name` varchar(200) DEFAULT NULL,
  `jur_name_full` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_organization`
--

LOCK TABLES `g_organization` WRITE;
/*!40000 ALTER TABLE `g_organization` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `g_organization` VALUES ('105b3c26-c0c7-11df-9864-001517456eb1','СМ СЕРВИС','ООО \"СМ СЕРВИС\"','Общество с ограниченной ответственностью \"СМ СЕРВИС\"'),('1af08386-8f4f-11df-866d-001517456eb1','МСН','ООО \"МСН\"','Общество с ограниченной ответственностью \"МСН\"'),('323e46bf-4896-11e4-8530-00155d881200','ООО \"НМС сервис\"','',''),('864ac447-4761-11e1-8572-00155d881200','Маркомнет Старый','ООО \"Маркомнет\"','Общество с ограниченной ответственностью \"Маркомнет\"'),('8b50f62c-475f-11e1-8572-00155d881200','МСН Телеком','ООО \"МСН Телеком\"','Общество с ограниченной ответственностью \"МСН Телеком\"'),('8e5c7b1e-8385-11df-9af5-001517456eb1','ЭМ СИ ЭН','ООО \"ЭМ СИ ЭН\"','Общество с ограниченной ответственностью \"ЭМ СИ ЭН\"'),('8e5c7b1f-8385-11df-9af5-001517456eb1','Маркомнет','ООО \"Маркомнет\"','Общество с ограниченной ответственностью \"Маркомнет\"'),('92241963-955d-11e3-9482-00155d881200','Веллстарт','ООО \"Веллстарт\"','Общество с ограниченной ответственностью \"Веллстарт\"'),('9bce961c-b525-11e1-b733-00155d881200','Маркомнет Сервис','ООО \"Маркомнет Сервис\"','Общество с ограниченной ответственностью \"Маркомнет сервис\"'),('9e926a28-6fe1-11e0-a7ed-d485644c7711','НС Системс','ООО \"НС Системс\"','Общество с ограниченной ответственостью \"НС Системс\"'),('a195ccf5-66a3-11e1-8572-00155d881200','МСМ','ООО \"МСМ\"','Общество с ограниченной ответственностью \"МСМ\"'),('a9c3bf57-39e8-11e5-a041-00155d881200','МСМ Телеком','ООО \"МСМ Телеком\"','ООО \"МСМ Телеком\"'),('af714d23-1334-11e0-9c11-d485644c7711','Олфонет','ООО \"Олфонет\"','Общество с ограниченной ответственностью \"Олфонет\"'),('b934cfe9-1338-11e5-b552-00155d881200','ИП Граевский С.В.','Индивидуальный предприниматель Граевский Сергей Владимирович','Индивидуальный предприниматель Граевский Сергей Владимирович'),('c25a7969-50eb-4d49-ade2-3ddf89b5fc14','Управленческая организация','',''),('dcc73554-8a33-11e2-af24-00155d881200','Олфогео','ООО \"Олфогео\"','Общество с ограниченной ответственностью \"Олфогео\"');
/*!40000 ALTER TABLE `g_organization` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_price_type`
--

DROP TABLE IF EXISTS `g_price_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_price_type` (
  `id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_price_type`
--

LOCK TABLES `g_price_type` WRITE;
/*!40000 ALTER TABLE `g_price_type` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `g_price_type` VALUES ('1c552733-a302-11e4-908b-00155d881200','Рекомендованная розница USD'),('739a53ba-8389-11df-9af5-001517456eb1','Розница'),('8a2c3b6e-8ff9-11df-b9fd-001517456eb1','Дилер 1'),('8a2c3b6f-8ff9-11df-b9fd-001517456eb1','Дилер 2'),('9dd5316a-900c-11df-b9fd-001517456eb1','del Себестоимость'),('b44d68bb-676c-11e0-a7ed-d485644c7711','Закупка'),('d549a21e-900e-11df-b9fd-001517456eb1','Дилер 3');
/*!40000 ALTER TABLE `g_price_type` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_producers`
--

DROP TABLE IF EXISTS `g_producers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_producers` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_producers`
--

LOCK TABLES `g_producers` WRITE;
/*!40000 ALTER TABLE `g_producers` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_producers` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_serials`
--

DROP TABLE IF EXISTS `g_serials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_serials` (
  `bill_no` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `code_1c` varchar(100) NOT NULL DEFAULT '',
  `serial` varchar(100) NOT NULL DEFAULT '',
  KEY `k_bill_no` (`bill_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_serials`
--

LOCK TABLES `g_serials` WRITE;
/*!40000 ALTER TABLE `g_serials` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `g_serials` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_store`
--

DROP TABLE IF EXISTS `g_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_store` (
  `id` char(36) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `deleted` smallint(6) NOT NULL DEFAULT '0' COMMENT 'is store deleted: 1 - true, 0 - false',
  `is_show` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_store`
--

LOCK TABLES `g_store` WRITE;
/*!40000 ALTER TABLE `g_store` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `g_store` VALUES ('02bfda5b-5bde-11e2-b5e2-00155d881200','СПД',0,'yes'),('03e137f6-0885-11e2-9c41-00155d881200','УС Санкт-Петербург',0,'yes'),('09281563-7f50-11e3-9482-00155d881200','Ростов-на-Дону',0,'yes'),('0a477bb8-ab66-11df-a255-001517456eb1','Производство',0,'yes'),('0a477bb9-ab66-11df-a255-001517456eb1','ЭмСиЭн',0,'yes'),('131959cd-60f4-11e0-898b-d485644c7711','Картриджи',0,'yes'),('132c7b3a-8a6c-11df-866d-001517456eb1','Диагностика',0,'yes'),('132c7b3b-8a6c-11df-866d-001517456eb1','С/Ц',0,'yes'),('132c7b3c-8a6c-11df-866d-001517456eb1','Некондиция',0,'yes'),('32a5f455-9847-11e2-af24-00155d881200','УС Екатеринбург',0,'yes'),('3bec99a4-b5a3-11df-9863-001517456eb1','Картриджи. Заправка/Ремонт',0,'yes'),('4b0f34dd-aa27-11e1-b733-00155d881200','Магазин Офис',0,'yes'),('534345fc-b9c1-11df-9863-001517456eb1','МСН',0,'yes'),('5c19e132-f851-11e3-99bf-00155d881200','УС Владивосток',0,'yes'),('5c418276-bcda-11df-9864-001517456eb1','склад не рабочее, не гарантия',0,'yes'),('5c825875-c75b-11e2-91cc-00155d881200','УС Новосибирск',0,'yes'),('6301fd66-85da-11df-866d-001517456eb1','для Внутренних нужд',0,'yes'),('6301fd67-85da-11df-866d-001517456eb1','Брак',0,'yes'),('8e5c7b22-8385-11df-9af5-001517456eb1','Основной склад',0,'yes'),('9d2b4bb6-a5b7-11e2-bb6c-00155d881200','Ангар',0,'yes'),('a06f6788-0663-11e3-a285-00155d881200','УС Казань',0,'yes'),('a260eb36-b675-11df-9863-001517456eb1','Маркомнет',0,'yes'),('a53774eb-d762-11df-8f7f-001517456eb0','Розница Раменки',0,'yes'),('ad06aff0-3a13-11e2-9369-00155d881200','Ви Клевер',0,'yes'),('c19360b5-4911-11e2-bdc0-00155d881200','УС Самара',0,'yes'),('ed471080-8b25-11df-866d-001517456eb1','После Диагностики и С/Ц',0,'yes'),('f11b4ab1-9d29-11e3-9482-00155d881200','Уцененные товары',0,'yes'),('f1f53f50-fee6-11df-9c11-d485644c7711','Комната 46',0,'yes'),('f6e066fb-e727-11e3-904c-00155d881200','УС Нижний Новгород',0,'yes'),('fbe2990d-e05f-11e1-bfe6-00155d881200','УС Краснодар',0,'yes');
/*!40000 ALTER TABLE `g_store` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `g_unit`
--

DROP TABLE IF EXISTS `g_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `g_unit` (
  `id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL,
  `okei` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `g_unit`
--

LOCK TABLES `g_unit` WRITE;
/*!40000 ALTER TABLE `g_unit` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `g_unit` VALUES ('03dd5890-6494-11de-96c3-001517456eb1','шт.','796'),('0fa0aa28-dd2f-11e0-bdf8-00155d21fe06','л','112'),('15a3236a-0207-11e0-9c11-d485644c7711','бухта','616'),('1af08384-8f4f-11df-866d-001517456eb1','м','006'),('1af08385-8f4f-11df-866d-001517456eb1','кг','166'),('54f61867-01da-11e4-887e-00155d881200','упак','778'),('62da39fd-2833-11e4-b8cf-00155d881200','пар','715'),('6bb9efd7-2c60-11e4-a2ab-00155d881200','Мбайт','257'),('7bb0772c-9f1b-11df-8ed6-001517456eb1','del_шт','796'),('961d4cb4-2c59-11e4-a2ab-00155d881200','м2','055'),('bc54b002-2c59-11e4-a2ab-00155d881200','мин','355'),('cdcb0d98-2c59-11e4-a2ab-00155d881200','мес','362'),('d83694d7-2390-11e4-926b-00155d881200','набор','704'),('d83694d9-2390-11e4-926b-00155d881200','рул','736'),('efa1d7a6-6550-11de-96c3-001517456eb1','м3','113');
/*!40000 ALTER TABLE `g_unit` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `history_changes`
--

DROP TABLE IF EXISTS `history_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model` varchar(50) NOT NULL,
  `model_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `action` enum('insert','update','delete') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `data_json` text NOT NULL,
  `prev_data_json` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `history_changes__model_model_id` (`model`,`model_id`)
) ENGINE=InnoDB AUTO_INCREMENT=934581 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history_changes`
--

LOCK TABLES `history_changes` WRITE;
/*!40000 ALTER TABLE `history_changes` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `history_changes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `history_version`
--

DROP TABLE IF EXISTS `history_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_version` (
  `model` varchar(50) NOT NULL,
  `model_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `data_json` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`model`,`model_id`,`date`),
  KEY `fk-history_version-user_id` (`user_id`),
  CONSTRAINT `fk-history_version-user_id` FOREIGN KEY (`user_id`) REFERENCES `user_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history_version`
--

LOCK TABLES `history_version` WRITE;
/*!40000 ALTER TABLE `history_version` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `history_version` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `important_events`
--

DROP TABLE IF EXISTS `important_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `important_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `client_id` int(11) DEFAULT NULL,
  `event` varchar(50) DEFAULT NULL,
  `source_id` int(11) DEFAULT NULL,
  `from_ip` varbinary(39) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `important_events`
--

LOCK TABLES `important_events` WRITE;
/*!40000 ALTER TABLE `important_events` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `important_events` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `important_events_groups`
--

DROP TABLE IF EXISTS `important_events_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `important_events_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `important_events_groups`
--

LOCK TABLES `important_events_groups` WRITE;
/*!40000 ALTER TABLE `important_events_groups` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `important_events_groups` VALUES (1,'Базовая группа'),(9,'Услуга телефония');
/*!40000 ALTER TABLE `important_events_groups` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `important_events_names`
--

DROP TABLE IF EXISTS `important_events_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `important_events_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `value` varchar(150) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_group_id` (`code`,`group_id`),
  KEY `important_events_names__group_id` (`group_id`),
  CONSTRAINT `important_events_names__group_id` FOREIGN KEY (`group_id`) REFERENCES `important_events_groups` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `important_events_names`
--

LOCK TABLES `important_events_names` WRITE;
/*!40000 ALTER TABLE `important_events_names` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `important_events_names` VALUES (1,'add_pay_notif','Уведомление: Зачисление средств',1,NULL),(2,'min_day_limit','Минимальный суточный лимит',1,NULL),(3,'min_balance','Критический остаток',1,NULL),(4,'unset_min_balance','Снятие: Критический остаток',1,NULL),(5,'unset_zero_balance','Снятие: Финансовая блокировка',1,NULL),(6,'zero_balance','Финансовая блокировка',1,NULL),(7,'new_account','Создание: Клиент',1,NULL),(8,'account_changed','Изменение: Клиент',1,NULL),(9,'extend_account_contract','Создание: Доп. контракт',1,NULL),(10,'contract_transfer','Перемещение: Контракт',1,NULL),(11,'account_contract_changed','Изменение: Контракт',1,NULL),(12,'transfer_contragent','Перемещение: Контрагент',1,NULL),(13,'created_trouble','Создание: Заявка',1,NULL),(14,'closed_trouble','Закрытие: Заявка',1,NULL),(15,'set_state_trouble','Изменение: Статус заявки',1,NULL),(16,'set_responsible_trouble','Изменение: Ответственного за заявку',1,NULL),(17,'new_comment_trouble','Создание: Комментарий к заявке',1,NULL),(18,'enabled_usage','Подключено: Услуга',1,NULL),(19,'disabled_usage','Отключено: Услуга',1,NULL),(20,'created_usage','Создание: Услуга',1,NULL),(21,'updated_usage','Изменение: Услуга',1,NULL),(22,'deleted_usage','Удаление: Услуга',1,NULL),(23,'transfer_usage','Перемещение: Услуга',1,NULL),(24,'unset_min_day_limit','Снятие: Минимальный суточны лимит',9,NULL),(25,'day_limit','Блокировка по достижению суточного лимита',9,NULL),(26,'unset_day_limit','Снятие блокировки по суточному лимиту',9,NULL);
/*!40000 ALTER TABLE `important_events_names` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `important_events_properties`
--

DROP TABLE IF EXISTS `important_events_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `important_events_properties` (
  `event_id` int(11) NOT NULL DEFAULT '0',
  `property` varchar(30) NOT NULL DEFAULT '',
  `value` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`event_id`,`property`,`value`),
  CONSTRAINT `important_events_properties__event_id` FOREIGN KEY (`event_id`) REFERENCES `important_events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `important_events_properties`
--

LOCK TABLES `important_events_properties` WRITE;
/*!40000 ALTER TABLE `important_events_properties` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `important_events_properties` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `important_events_rules`
--

DROP TABLE IF EXISTS `important_events_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `important_events_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL DEFAULT '',
  `action` varchar(30) DEFAULT NULL,
  `event` varchar(50) DEFAULT NULL,
  `message_template_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `important_events_rules__message_template_id` (`message_template_id`),
  KEY `important_events_rules__event` (`event`),
  CONSTRAINT `important_events_rules__event` FOREIGN KEY (`event`) REFERENCES `important_events_names` (`code`) ON DELETE SET NULL,
  CONSTRAINT `important_events_rules__message_template_id` FOREIGN KEY (`message_template_id`) REFERENCES `message_template` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `important_events_rules`
--

LOCK TABLES `important_events_rules` WRITE;
/*!40000 ALTER TABLE `important_events_rules` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `important_events_rules` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `important_events_rules_conditions`
--

DROP TABLE IF EXISTS `important_events_rules_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `important_events_rules_conditions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_id` int(11) DEFAULT NULL,
  `property` varchar(30) DEFAULT NULL,
  `condition` enum('==','<>','<=','>=','<','>','isset') NOT NULL DEFAULT 'isset',
  `value` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `important_events_rules_conditions__rule_id` (`rule_id`),
  CONSTRAINT `important_events_rules_conditions__rule_id` FOREIGN KEY (`rule_id`) REFERENCES `important_events_rules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `important_events_rules_conditions`
--

LOCK TABLES `important_events_rules_conditions` WRITE;
/*!40000 ALTER TABLE `important_events_rules_conditions` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `important_events_rules_conditions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `important_events_sources`
--

DROP TABLE IF EXISTS `important_events_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `important_events_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `important_events_sources`
--

LOCK TABLES `important_events_sources` WRITE;
/*!40000 ALTER TABLE `important_events_sources` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `important_events_sources` VALUES (1,'stat','MCN Stat'),(2,'billing','Билинг'),(3,'core','Ядро'),(4,'platform','Платформа');
/*!40000 ALTER TABLE `important_events_sources` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `invoice_settings`
--

DROP TABLE IF EXISTS `invoice_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_settings` (
  `doer_organization_id` int(11) DEFAULT NULL,
  `customer_country_code` int(4) DEFAULT NULL,
  `vat_apply_scheme` int(11) DEFAULT '1',
  `settlement_account_type_id` int(11) DEFAULT NULL,
  `vat_rate` int(6) DEFAULT NULL,
  UNIQUE KEY `org_id-customer_country-settlement_account-scheme` (`doer_organization_id`,`customer_country_code`,`settlement_account_type_id`,`vat_apply_scheme`),
  KEY `fk-invoice_settings-customer_country_code` (`customer_country_code`),
  CONSTRAINT `fk-invoice_settings-customer_country_code` FOREIGN KEY (`customer_country_code`) REFERENCES `country` (`code`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_settings`
--

LOCK TABLES `invoice_settings` WRITE;
/*!40000 ALTER TABLE `invoice_settings` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `invoice_settings` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `ip_block`
--

DROP TABLE IF EXISTS `ip_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ip_block` (
  `ip` varchar(32) NOT NULL,
  `block_time` datetime DEFAULT NULL,
  `unblock_time` datetime DEFAULT NULL,
  PRIMARY KEY (`ip`),
  KEY `idx_unblock_time` (`unblock_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_block`
--

LOCK TABLES `ip_block` WRITE;
/*!40000 ALTER TABLE `ip_block` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `ip_block` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `language`
--

DROP TABLE IF EXISTS `language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `language` (
  `code` varchar(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `language`
--

LOCK TABLES `language` WRITE;
/*!40000 ALTER TABLE `language` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `language` VALUES ('de-DE','Deutsch'),('en-EN','English'),('hu-HU','Magyar'),('ru-RU','Русский');
/*!40000 ALTER TABLE `language` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `lk_client_settings`
--

DROP TABLE IF EXISTS `lk_client_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lk_client_settings` (
  `client_id` int(11) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `min_balance` decimal(8,2) NOT NULL,
  `min_balance_sent` timestamp NOT NULL DEFAULT '1970-01-02 00:00:00',
  `is_min_balance_sent` int(4) NOT NULL DEFAULT '0',
  `min_day_limit` decimal(8,2) NOT NULL,
  `min_day_limit_sent` timestamp NOT NULL DEFAULT '1970-01-02 00:00:00',
  `is_min_day_limit_sent` int(4) NOT NULL DEFAULT '0',
  `zero_balance_sent` timestamp NOT NULL DEFAULT '1970-01-02 00:00:00',
  `is_zero_balance_sent` int(4) NOT NULL DEFAULT '0',
  `day_limit_sent` timestamp NOT NULL DEFAULT '1970-01-02 00:00:00',
  `is_day_limit_sent` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=koi8r ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lk_client_settings`
--

LOCK TABLES `lk_client_settings` WRITE;
/*!40000 ALTER TABLE `lk_client_settings` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `lk_client_settings` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `lk_notice`
--

DROP TABLE IF EXISTS `lk_notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lk_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('email','phone') DEFAULT 'email',
  `data` varchar(100) NOT NULL,
  `subject` mediumtext NOT NULL,
  `message` mediumtext NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `lang` varchar(5) DEFAULT 'ru-RU',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3343 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lk_notice`
--

LOCK TABLES `lk_notice` WRITE;
/*!40000 ALTER TABLE `lk_notice` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `lk_notice` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `lk_notice_log`
--

DROP TABLE IF EXISTS `lk_notice_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lk_notice_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `client_id` int(11) NOT NULL DEFAULT '0',
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `event` enum('add_pay_notif','day_limit','zero_balance','prebil_prepayers_notif','min_balance','min_day_limit') DEFAULT NULL,
  `is_set` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'is set, or reset limit',
  `balance` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT 'client balance',
  `limit` int(11) NOT NULL DEFAULT '0',
  `value` decimal(11,2) NOT NULL COMMENT 'payment sum value',
  PRIMARY KEY (`id`),
  KEY `client_id` (`date`,`client_id`) USING BTREE,
  KEY `date` (`date`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=14636 DEFAULT CHARSET=koi8r ROW_FORMAT=FIXED;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lk_notice_log`
--

LOCK TABLES `lk_notice_log` WRITE;
/*!40000 ALTER TABLE `lk_notice_log` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `lk_notice_log` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `lk_notice_settings`
--

DROP TABLE IF EXISTS `lk_notice_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lk_notice_settings` (
  `client_contact_id` int(11) NOT NULL DEFAULT '0',
  `client_id` int(11) NOT NULL DEFAULT '0',
  `min_balance` tinyint(1) NOT NULL DEFAULT '0',
  `min_day_limit` tinyint(1) NOT NULL DEFAULT '0',
  `add_pay_notif` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('working','connecting') NOT NULL DEFAULT 'connecting',
  `activate_code` varchar(10) NOT NULL,
  PRIMARY KEY (`client_contact_id`,`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=koi8r ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lk_notice_settings`
--

LOCK TABLES `lk_notice_settings` WRITE;
/*!40000 ALTER TABLE `lk_notice_settings` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `lk_notice_settings` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `lk_wizard_state`
--

DROP TABLE IF EXISTS `lk_wizard_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lk_wizard_state` (
  `contract_id` int(11) NOT NULL,
  `step` tinyint(4) NOT NULL DEFAULT '0',
  `state` enum('rejected','review','approve','process') NOT NULL DEFAULT 'process',
  `trouble_id` int(11) NOT NULL DEFAULT '0',
  `type` enum('euro','mcn') NOT NULL DEFAULT 'mcn',
  `is_bonus_added` tinyint(4) NOT NULL DEFAULT '0',
  `is_on` tinyint(4) NOT NULL DEFAULT '1',
  `is_rules_accept_legal` int(1) NOT NULL DEFAULT '0',
  `is_rules_accept_person` int(1) NOT NULL DEFAULT '0',
  `is_contract_accept` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`contract_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lk_wizard_state`
--

LOCK TABLES `lk_wizard_state` WRITE;
/*!40000 ALTER TABLE `lk_wizard_state` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `lk_wizard_state` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `message` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51473 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log_block`
--

DROP TABLE IF EXISTS `log_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `id_service` int(11) NOT NULL DEFAULT '0',
  `block` tinyint(1) NOT NULL DEFAULT '0',
  `id_user` int(11) NOT NULL DEFAULT '0',
  `ts` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `comment` varchar(255) DEFAULT NULL,
  `fields_changes` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `k_service` (`id_service`,`service`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=98666 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_block`
--

LOCK TABLES `log_block` WRITE;
/*!40000 ALTER TABLE `log_block` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log_block` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log_client`
--

DROP TABLE IF EXISTS `log_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL DEFAULT '0',
  `super_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ts` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `comment` text NOT NULL,
  `type` enum('msg','fields','company_name') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'msg',
  `apply_ts` date NOT NULL DEFAULT '1970-01-02',
  `is_overwrited` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'no',
  `is_apply_set` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  `comment2` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=92589 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_client`
--

LOCK TABLES `log_client` WRITE;
/*!40000 ALTER TABLE `log_client` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log_client` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log_contract_template_edit`
--

DROP TABLE IF EXISTS `log_contract_template_edit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_contract_template_edit` (
  `group` varchar(30) NOT NULL DEFAULT '',
  `contract` varchar(30) NOT NULL DEFAULT '',
  `action` varchar(15) NOT NULL DEFAULT '',
  `user` int(4) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `length` int(4) NOT NULL DEFAULT '0',
  KEY `idx` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_contract_template_edit`
--

LOCK TABLES `log_contract_template_edit` WRITE;
/*!40000 ALTER TABLE `log_contract_template_edit` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log_contract_template_edit` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log_newbills`
--

DROP TABLE IF EXISTS `log_newbills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_newbills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `ts` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_no` (`bill_no`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=1822274 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_newbills`
--

LOCK TABLES `log_newbills` WRITE;
/*!40000 ALTER TABLE `log_newbills` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log_newbills` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log_newbills_static`
--

DROP TABLE IF EXISTS `log_newbills_static`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_newbills_static` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(32) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL,
  `ts` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_no` (`bill_no`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18294 DEFAULT CHARSET=koi8r;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_newbills_static`
--

LOCK TABLES `log_newbills_static` WRITE;
/*!40000 ALTER TABLE `log_newbills_static` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log_newbills_static` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log_send_voip_settings`
--

DROP TABLE IF EXISTS `log_send_voip_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_send_voip_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `user` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `phones` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `client` (`client`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=3794 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_send_voip_settings`
--

LOCK TABLES `log_send_voip_settings` WRITE;
/*!40000 ALTER TABLE `log_send_voip_settings` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log_send_voip_settings` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log_tarif`
--

DROP TABLE IF EXISTS `log_tarif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_tarif` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `id_service` int(11) NOT NULL DEFAULT '0',
  `id_tarif` int(11) DEFAULT '0',
  `id_tarif_local_mob` int(11) DEFAULT NULL,
  `id_tarif_russia` int(11) DEFAULT NULL,
  `id_tarif_russia_mob` int(11) DEFAULT NULL,
  `id_tarif_intern` int(11) DEFAULT NULL,
  `id_tarif_sng` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL DEFAULT '0',
  `ts` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `comment` varchar(255) DEFAULT NULL,
  `date_activation` date DEFAULT NULL,
  `dest_group` varchar(5) DEFAULT NULL,
  `minpayment_group` smallint(6) DEFAULT NULL,
  `minpayment_local_mob` smallint(6) DEFAULT NULL,
  `minpayment_russia` smallint(6) DEFAULT NULL,
  `minpayment_intern` smallint(6) DEFAULT NULL,
  `minpayment_sng` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ts` (`ts`),
  KEY `id_service` (`id_service`,`service`),
  KEY `idx-service-date_activation` (`service`,`date_activation`)
) ENGINE=InnoDB AUTO_INCREMENT=38599 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_tarif`
--

LOCK TABLES `log_tarif` WRITE;
/*!40000 ALTER TABLE `log_tarif` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log_tarif` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log_tech_cpe`
--

DROP TABLE IF EXISTS `log_tech_cpe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_tech_cpe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tech_cpe_id` int(11) NOT NULL,
  `ts` datetime NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tech_cpe_id` (`tech_cpe_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27109 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_tech_cpe`
--

LOCK TABLES `log_tech_cpe` WRITE;
/*!40000 ALTER TABLE `log_tech_cpe` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log_tech_cpe` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log_usage_history`
--

DROP TABLE IF EXISTS `log_usage_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_usage_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `service` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_id` (`service`,`service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14773 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_usage_history`
--

LOCK TABLES `log_usage_history` WRITE;
/*!40000 ALTER TABLE `log_usage_history` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log_usage_history` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log_usage_history_fields`
--

DROP TABLE IF EXISTS `log_usage_history_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_usage_history_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_usage_history_id` int(11) NOT NULL DEFAULT '0',
  `field` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `value_from` varchar(255) DEFAULT '',
  `value_to` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `log_usage_history_id` (`log_usage_history_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25535 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_usage_history_fields`
--

LOCK TABLES `log_usage_history_fields` WRITE;
/*!40000 ALTER TABLE `log_usage_history_fields` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log_usage_history_fields` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `log_usage_ip_routes`
--

DROP TABLE IF EXISTS `log_usage_ip_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_usage_ip_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usage_ip_routes_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ts` datetime NOT NULL,
  `actual_from` date NOT NULL,
  `actual_to` date NOT NULL,
  `net` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `usage_ip_routes_id` (`usage_ip_routes_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18834 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log_usage_ip_routes`
--

LOCK TABLES `log_usage_ip_routes` WRITE;
/*!40000 ALTER TABLE `log_usage_ip_routes` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `log_usage_ip_routes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `mail_files`
--

DROP TABLE IF EXISTS `mail_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_files`
--

LOCK TABLES `mail_files` WRITE;
/*!40000 ALTER TABLE `mail_files` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `mail_files` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `mail_job`
--

DROP TABLE IF EXISTS `mail_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_job` (
  `job_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_subject` text NOT NULL,
  `template_body` text NOT NULL,
  `date_edit` datetime NOT NULL,
  `user_edit` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `job_state` enum('stop','ready','test','news','PM') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'stop',
  PRIMARY KEY (`job_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1115 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_job`
--

LOCK TABLES `mail_job` WRITE;
/*!40000 ALTER TABLE `mail_job` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `mail_job` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `mail_letter`
--

DROP TABLE IF EXISTS `mail_letter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_letter` (
  `job_id` int(11) NOT NULL,
  `client` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `send_date` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `send_message` text NOT NULL,
  `letter_state` enum('error','ready','sent') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'ready',
  PRIMARY KEY (`job_id`,`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_letter`
--

LOCK TABLES `mail_letter` WRITE;
/*!40000 ALTER TABLE `mail_letter` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `mail_letter` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `mail_object`
--

DROP TABLE IF EXISTS `mail_object`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_object` (
  `object_id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `object_type` enum('bill','PM','assignment','order','notice','invoice','akt','new_director_info','upd','lading','notice_mcm_telekom','sogl_mcm_telekom') DEFAULT NULL,
  `object_param` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `source` int(4) NOT NULL DEFAULT '2',
  `view_count` tinyint(4) NOT NULL DEFAULT '0',
  `view_ts` datetime NOT NULL COMMENT '??? ??????? ?????????',
  PRIMARY KEY (`object_id`),
  KEY `job_id` (`job_id`,`client_id`),
  KEY `object_type` (`object_type`,`object_param`)
) ENGINE=InnoDB AUTO_INCREMENT=722053 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_object`
--

LOCK TABLES `mail_object` WRITE;
/*!40000 ALTER TABLE `mail_object` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `mail_object` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `mcn_client_may`
--

DROP TABLE IF EXISTS `mcn_client_may`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mcn_client_may` (
  `id` int(4) DEFAULT NULL,
  `client` varchar(255) DEFAULT NULL,
  KEY `client` (`client`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mcn_client_may`
--

LOCK TABLES `mcn_client_may` WRITE;
/*!40000 ALTER TABLE `mcn_client_may` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `mcn_client_may` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `subject` varchar(250) NOT NULL,
  `created_at` datetime NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `date` (`created_at`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message`
--

LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `message_template`
--

DROP TABLE IF EXISTS `message_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_template`
--

LOCK TABLES `message_template` WRITE;
/*!40000 ALTER TABLE `message_template` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `message_template` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `message_template_content`
--

DROP TABLE IF EXISTS `message_template_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_template_content` (
  `template_id` int(11) DEFAULT NULL,
  `lang_code` varchar(5) DEFAULT 'ru-RU',
  `type` enum('email','sms','email_inner') DEFAULT 'email',
  `title` varchar(150) DEFAULT NULL,
  `content` mediumtext,
  `filename` varchar(255) DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  UNIQUE KEY `template_id_lang_code_type_country_id` (`template_id`,`lang_code`,`type`,`country_id`),
  KEY `message_template_content__lang_code` (`lang_code`),
  KEY `fk-message_template_content-country_id` (`country_id`),
  CONSTRAINT `fk-message_template_content-country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`),
  CONSTRAINT `message_template_content__lang_code` FOREIGN KEY (`lang_code`) REFERENCES `language` (`code`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_template_content`
--

LOCK TABLES `message_template_content` WRITE;
/*!40000 ALTER TABLE `message_template_content` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `message_template_content` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `message_templates_events`
--

DROP TABLE IF EXISTS `message_templates_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_templates_events` (
  `template_id` int(11) NOT NULL,
  `event_code` varchar(50) NOT NULL,
  PRIMARY KEY (`template_id`,`event_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_templates_events`
--

LOCK TABLES `message_templates_events` WRITE;
/*!40000 ALTER TABLE `message_templates_events` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `message_templates_events` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `message_text`
--

DROP TABLE IF EXISTS `message_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `message_text` (
  `message_id` int(11) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `message_text`
--

LOCK TABLES `message_text` WRITE;
/*!40000 ALTER TABLE `message_text` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `message_text` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `metro`
--

DROP TABLE IF EXISTS `metro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `metro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(35) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metro`
--

LOCK TABLES `metro` WRITE;
/*!40000 ALTER TABLE `metro` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `metro` VALUES (2,'Автозаводская'),(3,'Академическая'),(4,'Алексеевская'),(5,'Алтуфьево'),(6,'Аннино'),(7,'Арбатская'),(8,'Аэропорт'),(9,'Бабушкинская'),(10,'Багратионовская'),(12,'Баррикадная'),(13,'Бауманская'),(14,'Беговая'),(15,'Белорусская'),(16,'Беляево'),(17,'Бибирево'),(18,'Библиотека им. Ленина'),(19,'Битцевский Парк'),(21,'Боровицкая'),(22,'Ботанический Сад'),(24,'Братиславская'),(25,'Варшавская'),(26,'ВДНХ'),(27,'Владыкино'),(28,'Водный Стадион'),(29,'Войковская'),(30,'Волгоградский Проспект'),(31,'Волжская'),(32,'Выхино'),(33,'Динамо'),(34,'Дмитровская'),(35,'Добрынинская'),(36,'Домодедовская'),(37,'Дубровка'),(39,'Измайловская'),(40,'Партизанская'),(41,'Калужская'),(42,'Кантемировская'),(43,'Каховская'),(44,'Каширская'),(45,'Киевская'),(46,'Китай-Город'),(47,'Кожуховская'),(48,'Коломенская'),(49,'Комсомольская'),(50,'Коньково'),(51,'Красногвардейская'),(52,'Краснопресненская'),(53,'Красносельская'),(54,'Красные Ворота'),(55,'Крестьянская Застава'),(56,'Кропоткинская'),(57,'Крылатское'),(58,'Кузнецкий Мост'),(59,'Кузьминки'),(60,'Кунцевская'),(61,'Курская'),(62,'Кутузовская'),(63,'Воробьевы Горы'),(64,'Ленинский Проспект'),(65,'Лубянка'),(66,'Люблино'),(67,'Марксистская'),(68,'Марьино'),(69,'Маяковская'),(70,'Медведково'),(71,'Менделеевская'),(72,'Молодежная'),(73,'Нагатинская'),(74,'Нагорная'),(75,'Нахимовский Проспект'),(76,'Новогиреево'),(77,'Новокузнецкая'),(78,'Новослободская'),(79,'Новые Черемушки'),(80,'Октябрьская'),(81,'Октябрьское Поле'),(82,'Орехово'),(83,'Отрадное'),(84,'Охотный Ряд'),(85,'Павелецкая'),(86,'Парк Культуры'),(87,'Парк Победы'),(88,'Первомайская'),(89,'Перово'),(90,'Петровско-Разумовская'),(91,'Печатники'),(92,'Пионерская'),(93,'Планерная'),(94,'Площадь Ильича'),(95,'Площадь Революции'),(96,'Полежаевская'),(97,'Полянка'),(98,'Пражская'),(99,'Преображенская Площадь'),(100,'Пролетарская'),(101,'Проспект Вернадского'),(102,'Проспект Мира'),(103,'Профсоюзная'),(104,'Пушкинская'),(105,'Речной Вокзал'),(106,'Рижская'),(107,'Римская'),(109,'Рязанский Проспект'),(110,'Савеловская'),(111,'Свиблово'),(112,'Севастопольская'),(113,'Семеновская'),(114,'Серпуховская'),(115,'Смоленская'),(116,'Сокол'),(117,'Сокольники'),(118,'Спортивная'),(119,'Студенческая'),(120,'Сухаревская'),(121,'Сходненская'),(122,'Таганская'),(123,'Тверская'),(124,'Театральная'),(125,'Текстильщики'),(126,'Теплый Стан'),(127,'Тимирязевская'),(128,'Третьяковская'),(129,'Тульская'),(130,'Тургеневская'),(131,'Тушинская'),(132,'Ул. Академика Янгеля'),(133,'Улица 1905 года'),(134,'Улица Подбельского'),(135,'Университет'),(136,'Филевский Парк'),(137,'Фили'),(138,'Фрунзенская'),(139,'Царицыно'),(140,'Цветной Бульвар'),(141,'Черкизовская'),(142,'Чертановская'),(143,'Чеховская'),(144,'Чистые Пруды'),(145,'Чкаловская'),(146,'Шаболовская'),(147,'Шипиловская'),(148,'Шоссе Энтузиастов'),(149,'Щелковская'),(150,'Щукинская'),(151,'Электрозаводская'),(152,'Юго-Западная'),(153,'Южная'),(154,'Ясенево'),(160,'Александровский Сад'),(170,'Авиамоторная'),(171,'Бульвар Дмитрия Донского'),(172,'Международная'),(173,'Деловой центр'),(174,'Трубная'),(175,'Улица Старокачаловская'),(176,'Улица Скобелевская'),(177,'Бульвар адмирала Ушакова'),(178,'Улица Горчакова'),(179,'Бунинская аллея'),(180,'Солнцево'),(181,'Переделкино'),(182,'Внуково'),(183,'Достоевская'),(184,'Площадь суворова'),(185,'Марьина роща'),(186,'Новоясеневская'),(187,'Выставочная'),(188,'Славянский бульвар'),(189,'Митино'),(190,'Волоколамская'),(191,'Мякинино');
/*!40000 ALTER TABLE `metro` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `migration`
--

DROP TABLE IF EXISTS `migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration`
--

LOCK TABLES `migration` WRITE;
/*!40000 ALTER TABLE `migration` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `migration` VALUES ('m150211_160538_server_region',1423761939),('m150216_153752_core_sync_ids',1424192677),('m150218_132845_update_statuses',1424266478),('m150210_151217_business_process_id',1424361496),('m150218_155311_client_bps_id',1424361509),('m150219_092747_client__is_active',1424361524),('m150219_104454_is_close_status',1424361526),('m150220_160854_publish_comment',1424451792),('m260218_132846_update_statuses',1424963661),('m150227_132847_update_statuses',1425046883),('m150227_134444_tickets',1425048444),('m150224_181512_client_account_manager',1425050029),('m150212_155931_bills_payments',1425281483),('m150212_155958_transactions',1425281732),('m150225_201217_status_color',1425281734),('m150302_132848_update_contract_type',1425294660),('m150302_122633_debt_is_not_close_status',1425329027),('m150304_181347_account_is_active',1425580994),('m150305_173622_no_debt_status',1425580994),('m150305_181347_account_is_active_rollback',1425645600),('m150306_174518_debt_folder',1425664226),('m150403_131827_contract_sections',1428339124),('m150402_115159_timezone',1428405491),('m150413_170600_paypal',1429012648),('m150415_193419_contragent',1429126841),('m150416_123743_contragent_address_post',1429188603),('m150415_170322_history',1429251839),('m150417_113458_contragent_fields',1429271126),('m150417_120843_trunk',1429296399),('m150421_085511_domains_date',1429606639),('m150422_171806_del_folders',1429723256),('m150430_142735_stat206532_fix_account_manager',1430812398),('m150505_093414_stat206496_remove_conn7830',1430941002),('m150505_155540_messages',1430941004),('m150506_154130_state',1430941299),('m150508_181329_fix_state__connect_wait_docs',1431109773),('m150507_175657_create_lk_wizard',1431428214),('m150512_191804_support_ticket__flag_new_comment',1431632556),('m150518_121036_rename_client_contracts',1431952176),('m150605_112533_i18n',1434549182),('m150527_145154_transfer',1434625547),('m150528_162330_stat_product_id',1434640799),('m150624_150754_stat_report_cache',1435158730),('m150626_170757_firm_account_mcm',1435347977),('m150629_184147_cl_type_ip',1435666947),('m150702_130443_line_7800',1435852890),('m150703_105126_public74996854',1435920870),('m150619_154227_organizations',1436187458),('m150703_105924_person_update',1436187465),('m150703_142811_tax_rate',1436187677),('m150706_092226_wizard_type',1436191804),('m150706_135922_organization_tax_system',1436273571),('m150707_133944_remove_firms_from_rights',1436345547),('m150707_135527_markomnet_organization',1436345547),('m150708_000000_number_city',1436369661),('m150708_164146_add_number_region_81',1436375855),('m150709_114755_bill_price_include_vat',1436444246),('m150709_162639_bill_lines_remove_discount_field',1436466998),('m150707_152123_inc_vat_to_tariff',1436531912),('m150709_085546_update_tarifs_price',1436531912),('m150709_145753_remove_is_use_tax',1436541290),('m150720_155951_updateClientType',1437408410),('m150720_172049_extend_incomegoods_mask',1437413480),('m150722_120829_mail_object_sogl_mcm',1437647399),('m150710_000001_history_version',1437718139),('m150710_000002_exportClientsLogAndHistory',1437724086),('m150710_000003_alterWizard',1437724089),('m150710_000004_editTriggerToPGClients',1437724089),('m150710_000005_super',1437724095),('m150710_000006_updateEmptyNumberForContracts',1437724095),('m150710_000007_file_clientId_to_contractId',1437724105),('m150710_000008_documents',1437724113),('m150710_000009_organizationIdToContract',1437724188),('m150716_112304_grid_settings',1437724189),('m150717_072558_client_document',1437724196),('m150717_135416_news',1437724197),('m150717_144453_removePassFromClients',1437724197),('m150720_103940_addRights',1437724197),('m150720_142319_removePassFromClientsTrigger',1437724197),('m150720_145223_removeClientSelect',1437724197),('m150720_150018_removeWelltimeUpdates',1437724197),('m150720_162637_removeSCTrigger',1437724197),('m150721_121436_updateStatuses',1437724197),('m150721_143933_removePaymentComment',1437724210),('m150723_133516_deleteContragent_idFromClients',1437724219),('m150723_161122_dropTech_devices',1437725340),('m150723_180421_tax_regime',1437725348),('m150727_164712_notice_mcm_telekom',1438075853),('m150728_084617_contract_external_state',1438079708),('m150727_110648_recoveryLostData',1438118392),('m150727_143813_orgToOrg_id',1438118470),('m150731_163134_dropOrganization_id',1438367258),('m150804_122910_number_index_income_order',1438691685),('m150731_125238_tt_files',1438776388),('m150805_093459_wizard__is_bonus_added',1438790748),('m150806_123000_msmTaxRegime',1438868133),('m150806_152235_append_raiffeisen_bank',1438937796),('m150807_114302_client_is_external',1438954693),('m150806_193239_grids',1438969982),('m150808_082959_tech_cpe_transfer',1439205150),('m150515_181210_voip',1439290058),('m150714_083940_voip_fields_rename',1439290060),('m150714_135339_voip_destination',1439290061),('m150716_145449_voip_package',1439290062),('m150717_122836_usage_voip_package',1439290063),('m150720_114301_voip_prefixlist_type_field',1439290063),('m150811_175537_hold_log',1439315869),('m150811_102437_country_alpha3_field',1439462727),('m150812_101820_grid_business_process_status_fix_duplicate',1439462727),('m150811_125903_expansionContragent',1439569091),('m150812_165321_opf',1439569102),('m150813_122808_deleteBP',1439569103),('m150817_134245_bank_properties_transfer',1439824780),('m150811_124338_usage_voip_datacenter_address',1439893253),('m150818_140147_usage_voip_line7800_index',1439981533),('m150819_130300_add_81_21',1439992627),('m150820_145019_voip_tarif__add_test_7800',1440169275),('m150821_152902_tarif_folder__transit',1440171078),('m150824_092858_client_contragent_taxregime',1440415578),('m150824_113528_client_contragent_tax_regime_v2',1440600477),('m150808_095621_usage_virtpbx_region',1440758316),('m150825_121131_card_contract',1440758355),('m150824_150630_user_city',1440862434),('m150828_092736_user_users_usergroup_key',1440862435),('m150828_123829_user_users_department_key',1440862437),('m150831_144253_is_external_to_contract',1441626249),('m150902_140904_bp_rename',1441626434),('m150903_081652_hu_number_did_group_update',1441626435),('m150903_115053_remove_sogl_mcm_telekom',1441626471),('m100000_000002_data',1478607027),('m150911_104608_add_contract_types',1478607027),('m150911_113244_docs',1478607027),('m150928_130820_fix_bik',1478607027),('m150929_100409_formal_business_process_to_operators_business_process',1478607027),('m150930_042129_drop_allowed_direction',1478607028),('m150930_101610_onlime_devices_folder',1478607028),('m151001_143105_reward',1478607029),('m151007_135503_fix_super_transfer_error',1478607029),('m151008_084943_important_events',1478607029),('m151013_073449_reward_report',1478607030),('m151013_150843_fix_errors_reward',1478607030),('m151019_084550_voip_number_hold_to',1478607031),('m151022_113619_partner_contract_type',1478607031),('m151023_134632_fix_7800_type',1478607031),('m151028_151704_core_sync_id__type_admin_email',1478607031),('m151110_125703_lk_balance_view_mode',1478607032),('m151111_113546_actual_virtpbx_region',1478607032),('m151111_133118_document_folder_default',1478607033),('m151112_125602_append_contract_type',1478607033),('m151113_142511_paypal_currency',1478607033),('m151118_092918_messages_templates',1478607033),('m151120_092320_usages_rename',1478607033),('m151121_083917_usage_trunk_status_field',1478607034),('m151124_143645_important_events_update',1478607035),('m151127_081124_important_events_all',1478607037),('m151130_143254_fix_lang_code',1478607038),('m151202_092522_hungary_localization',1478607038),('m151207_104807_important_events_sources_extend',1478607039),('m151215_170522_important_events_sources',1478607039),('m151216_122809_anti_fraud_disabled',1478607039),('m151223_083334_important_events_variants',1478607039),('m160114_084450_virtpbx_stat_bigint',1478607040),('m160114_150954_create_vpbx_tariff',1478607053),('m160119_104547_create_vbpx_account_tariff',1478607061),('m160120_101958_virtpbx_recreate_primary',1478607062),('m160120_144318_lk_notice_localization',1478607062),('m160120_150349_lk_notice_remove_koi8r',1478607063),('m160122_105350_create_account_log_period',1478607064),('m160127_101016_remove_is_moved_field',1478607066),('m160128_092426_virtpbx_tariffs_update',1478607067),('m160128_154700_create_account_log_setup',1478607069),('m160128_185500_create_account_log_resource',1478607071),('m160129_110555_append_EN_lang',1478607071),('m160201_124835_currency_short',1478607072),('m160201_134838_usage_voip_pack__status',1478607072),('m160201_142444_account_mail_delivery',1478607074),('m160203_132400_create_voip_tariff',1478607078),('m160204_112359_convert_saleChanel_to_PartnerContractId',1478607078),('m160211_135800_create_voip_package_tariff',1478607080),('m160212_134200_convert_internet_tariff',1478607080),('m160212_160100_convert_collocation_tariff',1478607080),('m160212_171200_convert_vpn_tariff',1478607080),('m160212_192300_convert_extra_tariff',1478607080),('m160212_201900_convert_sms_tariff',1478607080),('m160215_150250_important_events_names_fix_russian_mistake',1478607080),('m160217_153522_business_process_status_for_parthers',1478607080),('m160218_150650_virtpbx_stat_missdata',1478607080),('m160224_132800_rename_currency_rate',1478607080),('m160225_130010_call_chat',1478607083),('m160226_122758_actual_call_chat',1478607084),('m160226_145950_client_account_options_delivery',1478607084),('m160229_110504_tarif_call_chat__status',1478607084),('m160229_151502_product_state__feedback',1478607084),('m160302_095653_contragent_name_remove_html_codes',1478607084),('m160302_115351_append_contract_type',1478607084),('m160303_070518_sync_currency_rate',1478607085),('m160303_083713_fix_documents_delivery',1478607085),('m160303_134128_number_short_field',1478607086),('m160304_134410_client_is_active_index',1478607086),('m160310_120454_add_views_for_core',1478607087),('m160311_102438_add_views_for_dbro',1478607087),('m160311_143600_add_country_currency',1478607089),('m160315_075324_append_index_tarifs_voip',1478607089),('m160315_101829_bp_it_outsource',1478607089),('m160315_124917_bp_itoutsource_old_status',1478607090),('m160321_161935_message_content_template',1478607090),('m160321_191500_trunk_min_margin',1478607091),('m160322_102455_important_event_names_change',1478607091),('m160323_112113_client_contacts_types',1478607092),('m160323_131100_create_tariff_voip_group',1478607094),('m160324_105744_message_template_events',1478607095),('m160327_101649_vats_to_welltime',1478607095),('m160328_123857_remove_address_datacenter_id',1478607095),('m160328_125646_sogl_mcm_telekom_rollback',1478607096),('m160329_132730_view_with_region_and_country',1478607096),('m160329_140217_view_for_linked_tables_events_templates',1478607096),('m160330_111743_append_tt_state',1478607097),('m160330_153500_add_account_tariff',1478607099),('m160331_094939_MCN_Numbers',1478607106),('m160331_155844_number7800_publish',1478607106),('m160331_173500_create_voip_number_type',1478607109),('m160401_193300_add_country_prefix',1478607110),('m160403_084544_number_type_internal',1478607112),('m160404_133022_fix_clients_partners_link',1478607112),('m160407_155712_view_for_client_contract',1478607112),('m160411_134600_add_tariff_is_default',1478607113),('m160411_140100_alter_tariff',1478607117),('m160411_145552_site_name',1478607118),('m160415_114413_voip_numbers_prepare_rand',1478607119),('m160420_101151_7800_cost',1478607119),('m160420_172903_operator_infrastructure_formal',1478607119),('m160425_133500_create_account_entry',1478607124),('m160425_154021_wizard_t2t_flags',1478607126),('m160427_115700_create_bill',1478607129),('m160428_142134_did_group_83',1478607129),('m160504_195400_alter_account_log_resource',1478607132),('m160504_202300_add_account_entry_vat',1478607134),('m160506_083710_eur_to_euro',1478607135),('m160510_095022_language_append_german',1478607136),('m160510_143903_currency_fix_huf',1478607136),('m160511_103521_countries_append_site',1478607136),('m160512_085950_tarifs_number_remove_fields',1478607138),('m160512_095548_tarifs_number_append_fk',1478607139),('m160512_120953_voip_registry',1478607143),('m160513_094306_message_template_country',1478607144),('m160514_160814_tarif_extra_itos',1478607144),('m160516_171448_important_event_day_limit',1478607146),('m160517_103053_voip_registry_comment',1478607146),('m160517_144422_create_view_for_dbro_and_api',1478607146),('m160518_105558_voip_numbers_drop_price',1478607148),('m160519_104447_itos_status_to_group',1478607148),('m160520_101110_client_account_mailer_options',1478607148),('m160524_081303_important_events_ip_field',1478607149),('m160528_130638_document_folder_tree',1478607151),('m160607_100414_document_templates_pos_and_default',1478607153),('m160611_152800_alter_account_tariff',1478607154),('m160615_112827_voip_new_status',1478607155),('m160616_132123_is_use_city',1478607156),('m160620_114229_cities_billing_method',1478607158),('m160621_083357_remove_number_type_external',1478607159),('m160622_142642_usage_call_chat_actual_chat_name',1478607159),('m160622_144138_trunk_counters',1478607160),('m160628_111705_callchat_without_name',1478607161),('m160628_164100_add_voip_numbers_calls_per_month',1478607164),('m160629_133345_did_group_number_type',1478607165),('m160704_143742_payments_sber_online',1478607166),('m160708_123921_bank_promsviazbank',1478607166),('m160718_103512_add_account_setup_log_price',1478607167),('m160719_085646_ip_block',1478607168),('m160720_163731_uu_welltime',1478607168),('m160721_162035_uu_account_log_min',1478607171),('m160721_181843_account_version',1478607172),('m160721_194455_drop_view_client_struct_ro',1478607172),('m160725_090947_status_test',1478607172),('m160726_082642_bill_biller_flag',1478607176),('m160729_090409_cleanup_log_tarif',1478607176),('m160804_091857_alter_uu_tariff',1478607176),('m160809_104155_geographic_to_internal',1478607177),('m160812_110357_history_version_who_when',1478607177),('m160815_102323_alter_uu_tariff',1478607178),('m160815_131146_add_uu_tariff_test',1478607178),('m160815_153156_true_is_testing',1478607178),('m160815_160636_status_7800_test',1478607179),('m160817_114429_alter_uu_account_tariff',1478607179),('m160818_102146_person_i18n',1478607184),('m160822_113544_organization_i18n',1478607189),('m160823_093100_organization_settlement_account',1478607195),('m160823_115504_organization_country_invoice_settings',1478607197),('m160824_142533_uu_account_entry_update_flag',1478607197),('m160825_130755_slovenia',1478607198),('m160826_141949_uu_delete_vpbx_resource',1478607198),('m160826_154528_add_virtpbx_stat_call',1478607199),('m160829_164133_add_uu_call_chat',1478607199),('m160901_154713_add_uu_vm_collocation',1478607199),('m160902_091721_add_uu_one_time',1478607200),('m160906_144552_tags',1478607202),('m160907_182850_uu_voip_status',1478607206),('m160908_202238_client_is_lk_show',1478607206),('m160909_101748_important_events_comment',1478607206),('m160913_121933_important_event_names_comment',1478607207),('m160914_164618_sync_uu_voip',1478607207),('m160915_112229_uu_postpaid',1478607208),('m160919_111518_clients_voip_limit_mn_day',1478607209),('m160919_150242_to_postgres_clients_after_upd_tr',1478607209),('m160919_164618_sync_uu_voip',1478607210),('m160922_132338_alter_account_tariff_log_utc',1478607211),('m160923_132327_add_event_queue_insert_time',1478607212),('m160927_142533_uu_account_tariff_update_flag',1478607213),('m160928_164618_sync_uu_voip',1478607213),('m160929_175118_fix_usage_vpbx_actiavation_dt',1478607213),('m161002_153643_clean_history',1478607213),('m161003_142942_add_vpbx_resource',1478607213),('m161004_090952_create_voip_number_trigger',1478607213),('m161004_094252_usage_voip_packages_transfer',1478607214),('m161005_110909_alter_event_queue_param',1478607214),('m161011_111123_invoice_settings_contragent_type',1478607217),('m161011_120727_organization_settlement_account_bank_accounts',1478607219),('m161013_131757_drop_uu_account_entry_flag',1478607220),('m161013_141331_number_source_portable',1478607221),('m161014_141802_invoice_settings_change',1478607222),('m161017_134800_uu_account_entry_is_default',1478607224),('m161017_141702_tech_port_4g',1478607224),('m161018_130054_add_bill_status_id',1478607225),('m161021_122211_personal_contact',1478607227),('m161024_161258_log_tariff_service_date_index',1478607227),('m161028_103129_clients_type_of_bill',1478607228),('m161031_134405_uu_resource_unit_eng',1478607228),('m161031_152832_alter_account_log_resource',1478607229),('m161101_095748_client_contract_reward_rework',1478607232),('m161104_105752_alter_uu_account_log',1478607233),('m161108_113008_drop_ro',1478607233),('f000001',1478607233);
/*!40000 ALTER TABLE `migration` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `mod_traf_1d`
--

DROP TABLE IF EXISTS `mod_traf_1d`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_traf_1d` (
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_int` int(10) unsigned NOT NULL DEFAULT '0',
  `transfer_rx` bigint(20) NOT NULL DEFAULT '0',
  `transfer_tx` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip_int`,`datetime`),
  KEY `ktime` (`datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mod_traf_1d`
--

LOCK TABLES `mod_traf_1d` WRITE;
/*!40000 ALTER TABLE `mod_traf_1d` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `mod_traf_1d` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `module` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `is_installed` tinyint(4) NOT NULL DEFAULT '0',
  `load_order` int(11) NOT NULL,
  PRIMARY KEY (`module`),
  KEY `is_installed` (`is_installed`,`load_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `modules` VALUES ('accounts',0,4),('db',0,15),('letters',0,18),('phone',0,19),('pay',0,21),('register',0,22),('.svn',0,23),('voip',0,24),('clientaccounts',0,27),('yandex',0,30),('clients',1,1),('services',1,2),('newaccounts',1,3),('tarifs',1,5),('tt',1,6),('stats',1,7),('routers',1,8),('monitoring',1,9),('modules',1,10),('users',1,11),('usercontrol',1,12),('send',1,16),('employeers',1,17),('mail',1,20),('voipnew',1,25),('voipreports',1,26),('ats',1,28),('data',1,29),('incomegoods',1,31),('ats2',1,32),('logs',1,33);
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `monitor_5min`
--

DROP TABLE IF EXISTS `monitor_5min`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitor_5min` (
  `ip_int` int(10) unsigned NOT NULL DEFAULT '0',
  `time300` int(11) unsigned NOT NULL DEFAULT '0',
  `value` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip_int`,`time300`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monitor_5min`
--

LOCK TABLES `monitor_5min` WRITE;
/*!40000 ALTER TABLE `monitor_5min` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `monitor_5min` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `monitor_5min_ins`
--

DROP TABLE IF EXISTS `monitor_5min_ins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitor_5min_ins` (
  `ip_int` int(10) unsigned NOT NULL DEFAULT '0',
  `time300` int(11) unsigned NOT NULL DEFAULT '0',
  `value` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip_int`,`time300`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monitor_5min_ins`
--

LOCK TABLES `monitor_5min_ins` WRITE;
/*!40000 ALTER TABLE `monitor_5min_ins` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `monitor_5min_ins` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `monitor_clients`
--

DROP TABLE IF EXISTS `monitor_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitor_clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `email` varchar(100) NOT NULL,
  `allow_bad` int(11) NOT NULL,
  `period_mail` int(11) NOT NULL,
  `period_use` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monitor_clients`
--

LOCK TABLES `monitor_clients` WRITE;
/*!40000 ALTER TABLE `monitor_clients` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `monitor_clients` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `monitor_ips`
--

DROP TABLE IF EXISTS `monitor_ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `monitor_ips` (
  `ip_int` int(10) unsigned NOT NULL,
  `monitor_id` int(11) DEFAULT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`ip_int`),
  KEY `client_id` (`monitor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monitor_ips`
--

LOCK TABLES `monitor_ips` WRITE;
/*!40000 ALTER TABLE `monitor_ips` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `monitor_ips` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `multitrunk`
--

DROP TABLE IF EXISTS `multitrunk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `multitrunk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `multitrunk`
--

LOCK TABLES `multitrunk` WRITE;
/*!40000 ALTER TABLE `multitrunk` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `multitrunk` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `mymfavorites`
--

DROP TABLE IF EXISTS `mymfavorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mymfavorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fav_name` text,
  `fav_source` text,
  `fav_path` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=koi8r COMMENT='Please do not modify this table!';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mymfavorites`
--

LOCK TABLES `mymfavorites` WRITE;
/*!40000 ALTER TABLE `mymfavorites` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `mymfavorites` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `nbn_mail`
--

DROP TABLE IF EXISTS `nbn_mail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nbn_mail` (
  `id` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nbn_mail`
--

LOCK TABLES `nbn_mail` WRITE;
/*!40000 ALTER TABLE `nbn_mail` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `nbn_mail` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newbill_change_log`
--

DROP TABLE IF EXISTS `newbill_change_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbill_change_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `stage_id` int(10) unsigned NOT NULL DEFAULT '0',
  `action` enum('add','delete','change') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'add',
  `code_1c` varchar(100) NOT NULL DEFAULT '',
  `item` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `k_bill_no` (`bill_no`)
) ENGINE=InnoDB AUTO_INCREMENT=88069 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbill_change_log`
--

LOCK TABLES `newbill_change_log` WRITE;
/*!40000 ALTER TABLE `newbill_change_log` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newbill_change_log` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newbill_change_log_fields`
--

DROP TABLE IF EXISTS `newbill_change_log_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbill_change_log_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `change_id` int(10) unsigned NOT NULL DEFAULT '0',
  `field` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `from` varchar(32) NOT NULL DEFAULT '',
  `to` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `k_change_id` (`change_id`)
) ENGINE=InnoDB AUTO_INCREMENT=77823 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbill_change_log_fields`
--

LOCK TABLES `newbill_change_log_fields` WRITE;
/*!40000 ALTER TABLE `newbill_change_log_fields` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newbill_change_log_fields` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newbill_lines`
--

DROP TABLE IF EXISTS `newbill_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbill_lines` (
  `pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '0',
  `item` varchar(200) NOT NULL DEFAULT '',
  `item_id` varchar(36) DEFAULT '',
  `code_1c` int(4) NOT NULL DEFAULT '0',
  `descr_id` varchar(36) NOT NULL DEFAULT '',
  `amount` decimal(13,6) DEFAULT '0.000000',
  `dispatch` int(4) NOT NULL DEFAULT '0',
  `price` decimal(13,4) DEFAULT '0.0000',
  `sum` decimal(11,2) DEFAULT NULL,
  `discount_set` decimal(11,4) NOT NULL DEFAULT '0.0000',
  `discount_auto` decimal(11,4) NOT NULL DEFAULT '0.0000',
  `service` varchar(20) NOT NULL DEFAULT '',
  `id_service` int(11) DEFAULT '0',
  `date_from` date NOT NULL DEFAULT '1970-01-02',
  `date_to` date NOT NULL DEFAULT '1970-01-02',
  `type` enum('service','zalog','zadatok','good','all4net') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'service',
  `gtd` varchar(255) NOT NULL DEFAULT '',
  `contry_maker` varchar(255) NOT NULL DEFAULT '',
  `country_id` int(4) NOT NULL DEFAULT '0',
  `tax_rate` int(11) DEFAULT NULL,
  `sum_without_tax` decimal(11,2) DEFAULT NULL,
  `sum_tax` decimal(11,2) DEFAULT NULL,
  `uu_account_entry_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`pk`),
  UNIQUE KEY `bill_sort` (`bill_no`,`sort`),
  KEY `service` (`service`,`id_service`),
  KEY `fk-newbill_lines-uu_account_entry_id` (`uu_account_entry_id`),
  CONSTRAINT `fk-newbill_lines-uu_account_entry_id` FOREIGN KEY (`uu_account_entry_id`) REFERENCES `uu_account_entry` (`id`) ON DELETE SET NULL,
  CONSTRAINT `newbill_lines__bill_no` FOREIGN KEY (`bill_no`) REFERENCES `newbills` (`bill_no`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1605425 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbill_lines`
--

LOCK TABLES `newbill_lines` WRITE;
/*!40000 ALTER TABLE `newbill_lines` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newbill_lines` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newbill_owner`
--

DROP TABLE IF EXISTS `newbill_owner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbill_owner` (
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `owner_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`bill_no`),
  KEY `k_owner` (`owner_id`),
  KEY `k_owner_bill` (`owner_id`,`bill_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbill_owner`
--

LOCK TABLES `newbill_owner` WRITE;
/*!40000 ALTER TABLE `newbill_owner` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newbill_owner` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newbill_send`
--

DROP TABLE IF EXISTS `newbill_send`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbill_send` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `state` enum('error','ready','viewed','sent') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'ready',
  `bill_no` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `last_send` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `message` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill` (`bill_no`)
) ENGINE=InnoDB AUTO_INCREMENT=5936 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbill_send`
--

LOCK TABLES `newbill_send` WRITE;
/*!40000 ALTER TABLE `newbill_send` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newbill_send` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newbill_sms`
--

DROP TABLE IF EXISTS `newbill_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbill_sms` (
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `sms_sender` varchar(20) NOT NULL DEFAULT '',
  `sms_send` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `sms_get_time` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  KEY `k_bill_no` (`bill_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbill_sms`
--

LOCK TABLES `newbill_sms` WRITE;
/*!40000 ALTER TABLE `newbill_sms` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newbill_sms` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newbill_wimax_orders`
--

DROP TABLE IF EXISTS `newbill_wimax_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbill_wimax_orders` (
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `order_mail_id` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`order_mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbill_wimax_orders`
--

LOCK TABLES `newbill_wimax_orders` WRITE;
/*!40000 ALTER TABLE `newbill_wimax_orders` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newbill_wimax_orders` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newbills`
--

DROP TABLE IF EXISTS `newbills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbills` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `bill_date` date NOT NULL DEFAULT '1970-01-02',
  `client_id` int(11) NOT NULL DEFAULT '0',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  `is_approved` tinyint(4) DEFAULT NULL,
  `sum` decimal(11,2) DEFAULT '0.00',
  `sum_with_unapproved` decimal(11,2) DEFAULT NULL,
  `price_include_vat` tinyint(4) NOT NULL DEFAULT '0',
  `is_payed` tinyint(1) DEFAULT '0' COMMENT '0 - ?????????, 1 - ????????? ???????, 2 - ?? ?????????, 3 - ???? ?????? ???? ??????',
  `inv2to1` tinyint(1) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `postreg` date NOT NULL DEFAULT '1970-01-02',
  `courier_id` int(4) unsigned NOT NULL DEFAULT '0',
  `nal` enum('beznal','nal','prov') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `sync_1c` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'no',
  `push_1c` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'yes',
  `state_1c` varchar(32) NOT NULL DEFAULT 'Новый',
  `is_rollback` tinyint(4) NOT NULL DEFAULT '0',
  `editor` enum('stat','admin') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'stat',
  `is_lk_show` tinyint(4) NOT NULL DEFAULT '1',
  `doc_date` date NOT NULL DEFAULT '1970-01-02',
  `is_user_prepay` tinyint(4) NOT NULL DEFAULT '0',
  `bill_no_ext` varchar(32) NOT NULL DEFAULT '',
  `bill_no_ext_date` date NOT NULL,
  `biller_version` int(1) unsigned DEFAULT '4',
  `uu_bill_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill_no` (`bill_no`) USING BTREE,
  KEY `client_id` (`client_id`),
  KEY `bill_date` (`bill_date`),
  KEY `courier_id` (`courier_id`),
  KEY `is_user_prepay` (`is_user_prepay`),
  KEY `fk-newbills-uu_bill_id` (`uu_bill_id`),
  CONSTRAINT `fk-newbills-uu_bill_id` FOREIGN KEY (`uu_bill_id`) REFERENCES `uu_bill` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=396266 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbills`
--

LOCK TABLES `newbills` WRITE;
/*!40000 ALTER TABLE `newbills` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newbills` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newbills_add_info`
--

DROP TABLE IF EXISTS `newbills_add_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbills_add_info` (
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `order_mail_id` int(11) NOT NULL DEFAULT '0',
  `fio` varchar(128) DEFAULT NULL,
  `address` varchar(512) NOT NULL DEFAULT '',
  `req_no` varchar(128) DEFAULT NULL COMMENT 'Номер заявки',
  `acc_no` varchar(128) DEFAULT NULL COMMENT 'Лицевой счет',
  `connum` varchar(128) DEFAULT NULL COMMENT 'Номер подключения',
  `comment1` varchar(255) DEFAULT NULL,
  `comment2` varchar(255) DEFAULT NULL,
  `passp_series` varchar(128) DEFAULT NULL,
  `passp_num` varchar(128) DEFAULT NULL,
  `passp_whos_given` varchar(128) DEFAULT NULL,
  `passp_when_given` varchar(128) DEFAULT NULL,
  `passp_code` varchar(128) DEFAULT NULL,
  `passp_birthday` varchar(128) DEFAULT NULL,
  `reg_city` varchar(128) DEFAULT NULL,
  `reg_street` varchar(128) DEFAULT NULL,
  `reg_house` varchar(128) DEFAULT NULL,
  `reg_housing` varchar(128) DEFAULT NULL,
  `reg_build` varchar(128) DEFAULT NULL,
  `reg_flat` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `order_given` text,
  `phone` varchar(128) DEFAULT NULL,
  `sms_send` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `sms_sender` varchar(16) NOT NULL DEFAULT '',
  `sms_get_time` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `line_owner` varchar(255) NOT NULL DEFAULT '',
  `metro_id` int(4) NOT NULL DEFAULT '0',
  `logistic` enum('none','selfdeliv','courier','auto','tk') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'none',
  `store_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '8e5c7b22-8385-11df-9af5-001517456eb1',
  PRIMARY KEY (`bill_no`),
  KEY `k_order_mail_id` (`order_mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbills_add_info`
--

LOCK TABLES `newbills_add_info` WRITE;
/*!40000 ALTER TABLE `newbills_add_info` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newbills_add_info` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newbills_documents`
--

DROP TABLE IF EXISTS `newbills_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbills_documents` (
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `ts` datetime NOT NULL,
  `a1` tinyint(2) DEFAULT '0',
  `a2` tinyint(2) DEFAULT '0',
  `a3` tinyint(2) DEFAULT '0',
  `i1` tinyint(2) DEFAULT '0',
  `i2` tinyint(2) DEFAULT '0',
  `i3` tinyint(2) DEFAULT '0',
  `i4` tinyint(2) DEFAULT '0',
  `i5` tinyint(2) DEFAULT '0',
  `i6` tinyint(2) DEFAULT '0',
  `i7` tinyint(2) DEFAULT '0',
  `ia1` tinyint(2) DEFAULT '0',
  `ia2` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`bill_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newbills_documents`
--

LOCK TABLES `newbills_documents` WRITE;
/*!40000 ALTER TABLE `newbills_documents` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newbills_documents` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newpayments`
--

DROP TABLE IF EXISTS `newpayments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newpayments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL DEFAULT '0',
  `payment_no` varchar(32) NOT NULL DEFAULT '0',
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `bill_vis_no` varchar(32) NOT NULL DEFAULT '',
  `payment_date` date NOT NULL DEFAULT '1970-01-02',
  `oper_date` date NOT NULL DEFAULT '1970-01-02',
  `payment_rate` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `type` enum('bank','prov','ecash','neprov') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'bank',
  `ecash_operator` enum('uniteller','cyberplat','paypal','yandex') DEFAULT NULL,
  `sum` decimal(11,2) NOT NULL DEFAULT '0.00',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB',
  `original_sum` decimal(11,2) DEFAULT NULL,
  `original_currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `comment` varchar(255) NOT NULL,
  `add_date` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `add_user` int(11) NOT NULL DEFAULT '0',
  `bank` enum('citi','mos','ural','sber','raiffeisen','promsviazbank') NOT NULL DEFAULT 'mos',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`,`payment_no`),
  KEY `bill_no_2` (`bill_no`,`bill_vis_no`),
  KEY `bill_no` (`bill_no`),
  KEY `bill_vis_no` (`bill_vis_no`)
) ENGINE=InnoDB AUTO_INCREMENT=292559 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newpayments`
--

LOCK TABLES `newpayments` WRITE;
/*!40000 ALTER TABLE `newpayments` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newpayments` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newpayments_orders`
--

DROP TABLE IF EXISTS `newpayments_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newpayments_orders` (
  `client_id` int(11) NOT NULL,
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `payment_id` varchar(32) NOT NULL,
  `sum` decimal(11,2) NOT NULL,
  PRIMARY KEY (`client_id`,`bill_no`,`payment_id`),
  KEY `payment_id` (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newpayments_orders`
--

LOCK TABLES `newpayments_orders` WRITE;
/*!40000 ALTER TABLE `newpayments_orders` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newpayments_orders` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `to_user_id` int(11) DEFAULT '0',
  `date` datetime NOT NULL,
  `priority` enum('unimportant','usual','important') NOT NULL DEFAULT 'usual',
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `to_user_id` (`to_user_id`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `newsaldo`
--

DROP TABLE IF EXISTS `newsaldo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsaldo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `saldo` decimal(11,2) DEFAULT NULL,
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  `ts` date DEFAULT NULL,
  `is_history` tinyint(1) NOT NULL DEFAULT '0',
  `edit_user` int(11) NOT NULL,
  `edit_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1468 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsaldo`
--

LOCK TABLES `newsaldo` WRITE;
/*!40000 ALTER TABLE `newsaldo` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `newsaldo` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `okvd`
--

DROP TABLE IF EXISTS `okvd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `okvd` (
  `code` int(4) NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL DEFAULT '-',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `okvd`
--

LOCK TABLES `okvd` WRITE;
/*!40000 ALTER TABLE `okvd` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `okvd` VALUES (55,'м<sup>2</sup>'),(257,'Мбайт'),(355,'мин'),(362,'мес');
/*!40000 ALTER TABLE `okvd` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `onlime_delivery`
--

DROP TABLE IF EXISTS `onlime_delivery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `onlime_delivery` (
  `bill_no` char(11) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `delivery_date` datetime DEFAULT '1970-01-02 00:00:00',
  PRIMARY KEY (`bill_no`),
  KEY `k_delivery` (`delivery_date`),
  KEY `k_bill_no` (`bill_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `onlime_delivery`
--

LOCK TABLES `onlime_delivery` WRITE;
/*!40000 ALTER TABLE `onlime_delivery` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `onlime_delivery` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `onlime_order`
--

DROP TABLE IF EXISTS `onlime_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `onlime_order` (
  `external_id` int(11) NOT NULL DEFAULT '0',
  `order_serialize` text,
  `status` int(11) DEFAULT NULL,
  `error` varchar(255) DEFAULT NULL,
  `bill_no` char(11) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `stage` enum('answer','add','new') DEFAULT NULL,
  `coupon` varchar(64) NOT NULL DEFAULT '',
  `seccode` varchar(64) NOT NULL DEFAULT '0',
  `vercode` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`external_id`),
  KEY `k_ext_id` (`external_id`),
  KEY `k_bill_no` (`bill_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `onlime_order`
--

LOCK TABLES `onlime_order` WRITE;
/*!40000 ALTER TABLE `onlime_order` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `onlime_order` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `organization`
--

DROP TABLE IF EXISTS `organization`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `organization_id` int(11) NOT NULL DEFAULT '0',
  `actual_from` date NOT NULL DEFAULT '2000-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `firma` varchar(128) DEFAULT NULL COMMENT 'ÐšÐ»ÑŽÑ‡ Ð´Ð»Ñ ÑÐ²ÑÐ·Ð¸ Ñ clients',
  `country_id` int(4) NOT NULL DEFAULT '643',
  `lang_code` varchar(5) NOT NULL DEFAULT 'ru-RU',
  `is_simple_tax_system` tinyint(1) NOT NULL DEFAULT '0',
  `vat_rate` smallint(6) NOT NULL DEFAULT '0',
  `registration_id` varchar(250) DEFAULT NULL,
  `tax_registration_id` varchar(32) DEFAULT NULL,
  `tax_registration_reason` varchar(12) DEFAULT NULL,
  `contact_phone` varchar(148) DEFAULT NULL,
  `contact_fax` varchar(148) DEFAULT NULL,
  `contact_email` varchar(128) DEFAULT NULL,
  `contact_site` varchar(250) DEFAULT NULL,
  `logo_file_name` varchar(50) DEFAULT NULL,
  `stamp_file_name` varchar(50) DEFAULT NULL,
  `director_id` int(11) DEFAULT NULL,
  `accountant_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `organization_id_actual_from` (`organization_id`,`actual_from`),
  KEY `fk_organization__director_id` (`director_id`),
  KEY `fk_organization__accountant_id` (`accountant_id`),
  CONSTRAINT `fk_organization__accountant_id` FOREIGN KEY (`accountant_id`) REFERENCES `person` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_organization__director_id` FOREIGN KEY (`director_id`) REFERENCES `person` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization`
--

LOCK TABLES `organization` WRITE;
/*!40000 ALTER TABLE `organization` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `organization` VALUES (1,1,'2000-01-01','2012-03-31','mcn_telekom',643,'ru-RU',0,18,NULL,'7727752084','772401001','(495) 950-56-78','(495) 638-50-17','info@mcn.ru','www.mcntelecom.ru','mcntelecom-logo.png','stamp_mcn_telekom.png',2,3),(2,1,'2012-04-01','2013-07-30','mcn_telekom',643,'ru-RU',0,18,NULL,'7727752084','772401001','(495) 950-56-78','(495) 638-50-17','info@mcn.ru','www.mcntelecom.ru','mcntelecom-logo.png','stamp_mcn_telekom.png',5,16),(3,1,'2013-07-31','2013-12-19','mcn_telekom',643,'ru-RU',0,18,NULL,'7727752084','772401001','(495) 950-56-78','(495) 638-50-17','info@mcn.ru','www.mcntelecom.ru','mcntelecom-logo.png','stamp_mcn_telekom.png',7,16),(4,1,'2013-12-20','2014-08-25','mcn_telekom',643,'ru-RU',0,18,NULL,'7727752084','773401001','(495) 950-56-78','(495) 638-50-17','info@mcn.ru','www.mcntelecom.ru','mcntelecom-logo.png','stamp_mcn_telekom.png',7,16),(5,1,'2014-08-26','2014-12-31','mcn_telekom',643,'ru-RU',0,18,NULL,'7727752084','773401001','(495) 105-99-99','(495) 105-99-96','info@mcn.ru','www.mcntelecom.ru','mcntelecom-logo.png','stamp_mcn_telekom.png',7,16),(6,1,'2015-01-01','2015-05-31','mcn_telekom',643,'ru-RU',0,18,NULL,'7727752084','773401001','(495) 105-99-99','(495) 638-50-17','info@mcn.ru','www.mcntelecom.ru','mcntelecom-logo.png','stamp_mcn_telekom.png',2,16),(7,1,'2015-06-01','2015-09-09','mcn_telekom',643,'ru-RU',0,18,'','7727752084','773401001','(495) 105-99-99','(495) 638-50-17','info@mcn.ru','www.mcntelecom.ru','mcntelecom-logo.png','stamp_mcn_telekom.png',2,18),(8,2,'2000-01-01','2010-09-30','ooomcn',643,'ru-RU',0,18,NULL,'7728638151','772801001','(495) 950-56-78 доп. 159','(495) 638-50-17','info@mcn.ru',NULL,NULL,'stamp_mcn.png',1,11),(9,2,'2010-10-01','2012-11-15','ooomcn',643,'ru-RU',0,18,NULL,'7728638151','772801001','(495) 950-56-78 доп. 159','(495) 638-50-17','info@mcn.ru',NULL,NULL,'stamp_mcn.png',1,13),(10,2,'2012-11-16','2014-08-25','ooomcn',643,'ru-RU',0,18,NULL,'7728638151','772801001','(495) 950-56-78 доп. 159','(495) 638-50-17','info@mcn.ru',NULL,NULL,'stamp_mcn.png',1,15),(11,2,'2014-08-26','4000-01-01','ooomcn',643,'ru-RU',0,18,NULL,'7728638151','772801001','(495) 950-56-78 доп. 159','(495) 105-99-96','info@mcn.ru',NULL,NULL,'stamp_mcn.png',1,15),(12,3,'2000-01-01','2008-03-30','mcn',643,'ru-RU',0,18,NULL,'7727508671','772701001','(495) 950-56-78','(495) 638-50-17','info@mcn.ru',NULL,'logo2.gif','stampmcn.gif',5,14),(13,3,'2008-03-31','2014-08-25','mcn',643,'ru-RU',0,18,NULL,'7727508671','772701001','(495) 950-56-78','(495) 638-50-17','info@mcn.ru',NULL,'logo2.gif','stampmcn.gif',5,16),(14,3,'2014-08-26','4000-01-01','mcn',643,'ru-RU',0,18,NULL,'7727508671','772701001','(495) 105-99-99','(495) 105-99-96','info@mcn.ru',NULL,'logo2.gif','stampmcn.gif',5,16),(15,4,'2000-01-01','4000-01-01','mcm',643,'ru-RU',0,18,'','7727667833','772701001','(495) 950-58-41','','arenda@mcn.ru','','','',10,10),(16,5,'2000-01-01','2011-12-31','ooocmc',643,'ru-RU',0,18,NULL,'7727701308','772701001','(495) 950-58-41',NULL,'arenda@mcn.ru',NULL,NULL,'stamp_si_em_si.png',7,13),(17,5,'2012-01-01','2012-11-15','ooocmc',643,'ru-RU',0,18,NULL,'7727701308','772701001','(495) 950-58-41',NULL,'arenda@mcn.ru',NULL,NULL,'stamp_si_em_si.png',7,13),(18,5,'2012-11-16','2013-02-28','ooocmc',643,'ru-RU',0,18,NULL,'7727701308','772701001','(495) 950-58-41',NULL,'arenda@mcn.ru',NULL,NULL,'stamp_si_em_si.png',7,15),(19,5,'2013-03-01','4000-01-01','ooocmc',643,'ru-RU',0,18,NULL,'7727701308','772701001','(495) 950-58-41',NULL,'arenda@mcn.ru',NULL,NULL,'stamp_si_em_si.png',5,15),(20,6,'2000-01-01','4000-01-01','all4geo',643,'ru-RU',0,18,NULL,'7727752091','772401001',NULL,NULL,NULL,NULL,NULL,NULL,6,6),(21,7,'2000-01-01','2008-03-30','all4net',643,'ru-RU',0,18,NULL,'7727731060','772701001','(495) 638-77-77',NULL,NULL,NULL,'logo_all4net.gif','stamp_all4net.jpg',2,14),(22,7,'2011-04-01','2013-08-12','all4net',643,'ru-RU',0,18,NULL,'7727731060','772701001','(495) 638-77-77',NULL,NULL,NULL,'logo_all4net.gif','stamp_all4net.jpg',2,17),(23,7,'2013-08-13','2014-08-25','all4net',643,'ru-RU',0,18,NULL,'7727731060','772701001','(495) 638-77-77',NULL,NULL,NULL,'logo_all4net.gif','stamp_all4net.jpg',2,17),(24,7,'2014-08-26','2014-12-31','all4net',643,'ru-RU',0,18,NULL,'7727731060','772701001','(495) 105-99-97',NULL,NULL,NULL,'logo_all4net.gif','stamp_all4net.jpg',2,17),(25,7,'2015-01-01','2015-08-25','all4net',643,'ru-RU',0,18,'1107746861166','7727731060','772701001','(495) 950-56-78','','','','logo_all4net.gif','stamp_all4net.jpg',12,17),(26,7,'2008-03-31','2011-03-31','all4net',643,'ru-RU',0,18,NULL,'7727731060','772701001','(495) 638-77-77',NULL,NULL,NULL,'logo_all4net.gif','stamp_all4net.jpg',2,16),(27,8,'2000-01-01','2014-08-25','wellstart',643,'ru-RU',0,18,NULL,'7724899307','772401001','(495) 950-56-78',NULL,NULL,NULL,NULL,NULL,11,11),(28,8,'2014-08-26','4000-01-01','wellstart',643,'ru-RU',0,18,NULL,'7724899307','772401001','(495) 105-99-99',NULL,NULL,NULL,NULL,NULL,11,11),(29,9,'2000-01-01','4000-01-01','mcn_telekom_hungary',348,'hu-HU',0,0,'1117746441647','7727752084','','+7 495 105-99-99','+7 495 105-99-96','info@mcn.ru','www.mcntelecom.ru','mcntelecom-logo.png','stamp_mcn_telekom.png',2,18),(30,10,'2000-01-01','4000-01-01','tel2tel_hungary',348,'hu-HU',0,27,'01 09 702746','12773246-2-43 / HU12773246','','+36 1 490-0999','+36 1 490-0998','info@tel2tel.com','www.tel2tel.com','tel2tel.png','',19,NULL),(31,11,'2000-01-01','2015-09-09','mcm_telekom',643,'ru-RU',1,0,'','7728226648','772801001','(495) 105-99-99','(495) 105-99-96','info@mcn.ru','www.mcntelecom.ru','mcntelecom-logo.png','stamp_mcm_telekom.png',1,17),(32,13,'2000-01-01','2012-09-30','markomnet_service',643,'ru-RU',0,18,NULL,'7728802130','772801001','(495) 638-63-84','(495) 638-52-80',NULL,NULL,NULL,NULL,8,8),(33,13,'2012-10-01','4000-01-01','markomnet_service',643,'ru-RU',0,18,NULL,'7728802130','772801001','(495) 638-63-84','(495) 638-52-80',NULL,NULL,NULL,NULL,9,9),(34,14,'2000-01-01','4000-01-01','markomnet',643,'ru-RU',0,18,NULL,'7734246040','773401001','(095) 950-5678',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(35,15,'2000-01-01','2011-11-30','markomnet_new',643,'ru-RU',0,18,NULL,'7727702076','772701001','(495) 638-63-84','(495) 638-52-80',NULL,NULL,NULL,NULL,4,4),(36,15,'2011-12-01','2012-03-31','markomnet_new',643,'ru-RU',0,18,NULL,'7727702076','772701001','(495) 638-63-84','(495) 638-52-80',NULL,NULL,NULL,NULL,4,13),(37,15,'2012-02-29','4001-01-01','markomnet_new',643,'ru-RU',0,18,NULL,'7727702076','772701001','(495) 638-63-84','(495) 638-52-80',NULL,NULL,NULL,NULL,4,4),(38,7,'2015-08-26','4000-01-01','all4net',643,'ru-RU',0,18,'1107746861166','7727731060','772701001','(495) 950-56-78','','','','logo_all4net.gif','stamp_all4net.jpg',12,21),(39,11,'2015-09-10','4000-01-01','mcm_telekom',643,'ru-RU',1,0,'','7728226648','772801001','(495) 105-99-99','(495) 105-99-96','info@mcn.ru','www.mcntelecom.ru','mcntelecom-logo.png','stamp_mcm_telekom.png',1,17),(40,1,'2015-09-10','4000-01-01','mcn_telekom',643,'ru-RU',0,18,'','7727752084','773401001','(495) 105-99-99','(495) 638-50-17','info@mcn.ru','www.mcntelecom.ru','mcntelecom-logo.png','stamp_mcn_telekom.png',2,18);
/*!40000 ALTER TABLE `organization` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `organization_i18n`
--

DROP TABLE IF EXISTS `organization_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_i18n` (
  `organization_record_id` int(11) DEFAULT NULL,
  `lang_code` varchar(5) NOT NULL DEFAULT 'ru-RU',
  `field` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  UNIQUE KEY `organization_record_id-lang_code-field` (`organization_record_id`,`lang_code`,`field`),
  KEY `fk-organization_i18n-lang_code` (`lang_code`),
  CONSTRAINT `fk-organization_i18n-lang_code` FOREIGN KEY (`lang_code`) REFERENCES `language` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-organization_i18n-organization_record_id` FOREIGN KEY (`organization_record_id`) REFERENCES `organization` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_i18n`
--

LOCK TABLES `organization_i18n` WRITE;
/*!40000 ALTER TABLE `organization_i18n` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `organization_i18n` VALUES (1,'ru-RU','name','ООО «МСН Телеком»'),(1,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН Телеком»'),(1,'ru-RU','legal_address','115487, г. Москва, 2-й Нагатинский пр-д, д.2, стр.8'),(1,'ru-RU','post_address','115162, г. Москва, а/я №21'),(2,'ru-RU','name','ООО «МСН Телеком»'),(2,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН Телеком»'),(2,'ru-RU','legal_address','115487, г. Москва, 2-й Нагатинский пр-д, д.2, стр.8'),(2,'ru-RU','post_address','115162, г. Москва, а/я №21'),(3,'ru-RU','name','ООО «МСН Телеком»'),(3,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН Телеком»'),(3,'ru-RU','legal_address','115487, г. Москва, 2-й Нагатинский пр-д, д.2, стр.8'),(3,'ru-RU','post_address','115162, г. Москва, а/я №21'),(4,'ru-RU','name','ООО «МСН Телеком»'),(4,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН Телеком»'),(4,'ru-RU','legal_address','123098, г.Москва, ул. Академика Бочвара, д.10Б'),(4,'ru-RU','post_address','115162, г. Москва, а/я №21'),(5,'ru-RU','name','ООО «МСН Телеком»'),(5,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН Телеком»'),(5,'ru-RU','legal_address','123098, г.Москва, ул. Академика Бочвара, д.10Б'),(5,'ru-RU','post_address','115162, г. Москва, а/я №21'),(6,'ru-RU','name','ООО «МСН Телеком»'),(6,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН Телеком»'),(6,'ru-RU','legal_address','123098, г.Москва, ул. Академика Бочвара, д.10Б'),(6,'ru-RU','post_address','115162, г. Москва, а/я №21'),(7,'ru-RU','name','ООО «МСН Телеком»'),(7,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН Телеком»'),(7,'ru-RU','legal_address','123098, г.Москва, ул. Академика Бочвара, д.10Б'),(7,'ru-RU','post_address','115162, г. Москва, а/я №21'),(8,'ru-RU','name','ООО «МСН»'),(8,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН»'),(8,'ru-RU','legal_address','117574 г. Москва, Одоевского пр-д., д. 3, кор. 7'),(8,'ru-RU','post_address','115162, г. Москва, а/я №21'),(9,'ru-RU','name','ООО «МСН»'),(9,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН»'),(9,'ru-RU','legal_address','117574 г. Москва, Одоевского пр-д., д. 3, кор. 7'),(9,'ru-RU','post_address','115162, г. Москва, а/я №21'),(10,'ru-RU','name','ООО «МСН»'),(10,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН»'),(10,'ru-RU','legal_address','117574 г. Москва, Одоевского пр-д., д. 3, кор. 7'),(10,'ru-RU','post_address','115162, г. Москва, а/я №21'),(11,'ru-RU','name','ООО «МСН»'),(11,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН»'),(11,'ru-RU','legal_address','117574 г. Москва, Одоевского пр-д., д. 3, кор. 7'),(11,'ru-RU','post_address','115162, г. Москва, а/я №21'),(12,'ru-RU','name','ООО «Эм Си Эн»'),(12,'ru-RU','full_name','Общество с ограниченной ответственностью «Эм Си Эн»'),(12,'ru-RU','legal_address','113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130'),(12,'ru-RU','post_address','115162, г. Москва, а/я №21'),(13,'ru-RU','name','ООО «Эм Си Эн»'),(13,'ru-RU','full_name','Общество с ограниченной ответственностью «Эм Си Эн»'),(13,'ru-RU','legal_address','113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130'),(13,'ru-RU','post_address','115162, г. Москва, а/я №21'),(14,'ru-RU','name','ООО «Эм Си Эн»'),(14,'ru-RU','full_name','Общество с ограниченной ответственностью «Эм Си Эн»'),(14,'ru-RU','legal_address','113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130'),(14,'ru-RU','post_address','115162, г. Москва, а/я №21'),(15,'ru-RU','name','ООО «МСМ»'),(15,'ru-RU','full_name','Общество с ограниченной ответственностью «МСМ»'),(15,'ru-RU','legal_address','117218, г. Москва, ул. Б. Черемушкинская, д. 25, стр. 97'),(15,'ru-RU','post_address',''),(16,'ru-RU','name','ООО «Си Эм Си»'),(16,'ru-RU','full_name','Общество с ограниченной ответственностью «Си Эм Си»'),(16,'ru-RU','legal_address','117218, г. Москва, ул. Б. Черемушкинская, д. 25, стр. 97'),(16,'ru-RU','post_address',NULL),(17,'ru-RU','name','ООО «Си Эм Си»'),(17,'ru-RU','full_name','Общество с ограниченной ответственностью «Си Эм Си»'),(17,'ru-RU','legal_address','117218, г. Москва, ул. Б. Черемушкинская, д. 25, стр. 97'),(17,'ru-RU','post_address',NULL),(18,'ru-RU','name','ООО «Си Эм Си»'),(18,'ru-RU','full_name','Общество с ограниченной ответственностью «Си Эм Си»'),(18,'ru-RU','legal_address','117218, г. Москва, ул. Б. Черемушкинская, д. 25, стр. 97'),(18,'ru-RU','post_address',NULL),(19,'ru-RU','name','ООО «Си Эм Си»'),(19,'ru-RU','full_name','Общество с ограниченной ответственностью «Си Эм Си»'),(19,'ru-RU','legal_address','117218, г. Москва, ул. Б. Черемушкинская, д. 25, стр. 97'),(19,'ru-RU','post_address',NULL),(20,'ru-RU','name','ООО «Олфогео»'),(20,'ru-RU','full_name','Общество с ограниченной ответственностью «Олфогео»'),(20,'ru-RU','legal_address','115487, г. Москва, Нагатинский 2-й проезд, дом 2, строение 8'),(20,'ru-RU','post_address',NULL),(21,'ru-RU','name','ООО «Олфонет»'),(21,'ru-RU','full_name','Общество с ограниченной ответственностью «Олфонет»'),(21,'ru-RU','legal_address','117218, Москва г, Черемушкинская Б. ул, дом 25, строение 97'),(21,'ru-RU','post_address',NULL),(22,'ru-RU','name','ООО «Олфонет»'),(22,'ru-RU','full_name','Общество с ограниченной ответственностью «Олфонет»'),(22,'ru-RU','legal_address','117218, Москва г, Черемушкинская Б. ул, дом 25, строение 97'),(22,'ru-RU','post_address',NULL),(23,'ru-RU','name','ООО «Олфонет»'),(23,'ru-RU','full_name','Общество с ограниченной ответственностью «Олфонет»'),(23,'ru-RU','legal_address','117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130'),(23,'ru-RU','post_address',NULL),(24,'ru-RU','name','ООО «Олфонет»'),(24,'ru-RU','full_name','Общество с ограниченной ответственностью «Олфонет»'),(24,'ru-RU','legal_address','117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130'),(24,'ru-RU','post_address',NULL),(25,'ru-RU','name','ООО «Олфонет»'),(25,'ru-RU','full_name','Общество с ограниченной ответственностью «Олфонет»'),(25,'ru-RU','legal_address','117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130'),(25,'ru-RU','post_address','117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130'),(26,'ru-RU','name','ООО «Олфонет»'),(26,'ru-RU','full_name','Общество с ограниченной ответственностью «Олфонет»'),(26,'ru-RU','legal_address','117218, Москва г, Черемушкинская Б. ул, дом 25, строение 97'),(26,'ru-RU','post_address',NULL),(27,'ru-RU','name','ООО «Веллстарт»'),(27,'ru-RU','full_name','Общество с ограниченной ответственностью «Веллстарт»'),(27,'ru-RU','legal_address','115487, Москва, 2-й Нагатинский проезд, д.2, стр.8'),(27,'ru-RU','post_address',NULL),(28,'ru-RU','name','ООО «Веллстарт»'),(28,'ru-RU','full_name','Общество с ограниченной ответственностью «Веллстарт»'),(28,'ru-RU','legal_address','115487, Москва, 2-й Нагатинский проезд, д.2, стр.8'),(28,'ru-RU','post_address',NULL),(29,'ru-RU','name','MCN Telecom Kft.'),(29,'ru-RU','full_name',''),(29,'ru-RU','legal_address','123098, Moscow, ulitsa Akademika Bochvara, 10B'),(29,'ru-RU','post_address','115487, Moscow, 2-y Nagatinsky proyezd, 2с8'),(30,'ru-RU','name','Tel2tel Kft.'),(30,'ru-RU','full_name',''),(30,'ru-RU','legal_address','Budapest, 1114, Kemenes utca 8. félemelet 3.  Magyarorsag'),(30,'ru-RU','post_address','Budapest, 1114, Kemenes utca 8. félemelet 3.  Magyarorsag'),(31,'ru-RU','name','ООО «МСМ Телеком»'),(31,'ru-RU','full_name','Общество с ограниченной ответственностью «МСМ Телеком»'),(31,'ru-RU','legal_address','117574, г. Москва, Одоевского проезд, д. 3, корп. 7'),(31,'ru-RU','post_address','115162, г. Москва, а/я 46'),(32,'ru-RU','name','ООО «Маркомнет сервис»'),(32,'ru-RU','full_name','Общество с ограниченной ответственностью «Маркомнет сервис»'),(32,'ru-RU','legal_address','117574, Москва, Одоевского проезд, д.3, к.7'),(32,'ru-RU','post_address',NULL),(33,'ru-RU','name','ООО «Маркомнет сервис»'),(33,'ru-RU','full_name','Общество с ограниченной ответственностью «Маркомнет сервис»'),(33,'ru-RU','legal_address','117574, Москва, Одоевского проезд, д.3, к.7'),(33,'ru-RU','post_address',NULL),(34,'ru-RU','name','ООО «МАРКОМНЕТ»'),(34,'ru-RU','full_name','Общество с ограниченной ответственностью «МАРКОМНЕТ»'),(34,'ru-RU','legal_address','123458, г. Москва, Таллинская ул., д.2, кв. 282'),(34,'ru-RU','post_address',NULL),(35,'ru-RU','name','ООО «МАРКОМНЕТ»'),(35,'ru-RU','full_name','Общество с ограниченной ответственностью «МАРКОМНЕТ»'),(35,'ru-RU','legal_address','117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97'),(35,'ru-RU','post_address',NULL),(36,'ru-RU','name','ООО «МАРКОМНЕТ»'),(36,'ru-RU','full_name','Общество с ограниченной ответственностью «МАРКОМНЕТ»'),(36,'ru-RU','legal_address','117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97'),(36,'ru-RU','post_address',NULL),(37,'ru-RU','name','ООО «МАРКОМНЕТ»'),(37,'ru-RU','full_name','Общество с ограниченной ответственностью «МАРКОМНЕТ»'),(37,'ru-RU','legal_address','117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97'),(37,'ru-RU','post_address',NULL),(38,'ru-RU','name','ООО «Олфонет»'),(38,'ru-RU','full_name','Общество с ограниченной ответственностью «Олфонет»'),(38,'ru-RU','legal_address','117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130'),(38,'ru-RU','post_address','117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130'),(39,'ru-RU','name','ООО «МСМ Телеком»'),(39,'ru-RU','full_name','Общество с ограниченной ответственностью «МСМ Телеком»'),(39,'ru-RU','legal_address','117574, г. Москва, Одоевского проезд, д. 3, корп. 7'),(39,'ru-RU','post_address','115162, г. Москва, а/я 46'),(40,'ru-RU','name','ООО «МСН Телеком»'),(40,'ru-RU','full_name','Общество с ограниченной ответственностью «МСН Телеком»'),(40,'ru-RU','legal_address','123098, г.Москва, ул. Академика Бочвара, д.10Б'),(40,'ru-RU','post_address','115162, г. Москва, а/я №21');
/*!40000 ALTER TABLE `organization_i18n` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `organization_settlement_account`
--

DROP TABLE IF EXISTS `organization_settlement_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_settlement_account` (
  `organization_record_id` int(11) DEFAULT NULL,
  `settlement_account_type_id` int(1) DEFAULT '1',
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_address` varchar(255) DEFAULT NULL,
  `bank_correspondent_account` varchar(64) DEFAULT NULL,
  `bank_bik` varchar(20) DEFAULT NULL,
  KEY `fk-organization_settlement_account-organization_record_id` (`organization_record_id`),
  KEY `bank_name-settlement_account_type_id` (`bank_name`,`settlement_account_type_id`),
  KEY `bank_correspondent_account` (`bank_correspondent_account`),
  KEY `bank_bik` (`bank_bik`),
  CONSTRAINT `fk-organization_settlement_account-organization_record_id` FOREIGN KEY (`organization_record_id`) REFERENCES `organization` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_settlement_account`
--

LOCK TABLES `organization_settlement_account` WRITE;
/*!40000 ALTER TABLE `organization_settlement_account` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `organization_settlement_account` VALUES (1,1,'Московский банк Сбербанка России ОАО, г.Москва',NULL,'30101810400000000225','044525225'),(2,1,'Московский банк Сбербанка России ОАО, г.Москва',NULL,'30101810400000000225','044525225'),(3,1,'Московский банк Сбербанка России ОАО, г.Москва',NULL,'30101810400000000225','044525225'),(4,1,'Московский банк Сбербанка России ОАО, г.Москва',NULL,'30101810400000000225','044525225'),(5,1,'Московский банк Сбербанка России ОАО, г.Москва',NULL,'30101810400000000225','044525225'),(6,1,'Московский банк Сбербанка России ОАО, г.Москва',NULL,'30101810400000000225','044525225'),(7,1,'Московский банк Сбербанка России ОАО, г.Москва',NULL,'30101810400000000225','044525225'),(8,1,'Московский банк Сбербанка России ОАО, г. Москва',NULL,'30101810400000000225','044525225'),(9,1,'Московский банк Сбербанка России ОАО, г. Москва',NULL,'30101810400000000225','044525225'),(10,1,'Московский банк Сбербанка России ОАО, г. Москва',NULL,'30101810400000000225','044525225'),(11,1,'Московский банк Сбербанка России ОАО, г. Москва',NULL,'30101810400000000225','044525225'),(12,1,'ЗАО КБ «Ситибанк»',NULL,'30101810300000000202','044525202'),(13,1,'ЗАО КБ «Ситибанк»',NULL,'30101810300000000202','044525202'),(14,1,'ЗАО КБ «Ситибанк»',NULL,'30101810300000000202','044525202'),(15,1,'ОАО \"УРАЛСИБ\"',NULL,'30101810100000000787','044525787'),(16,1,'ОАО «БАНК УРАЛСИБ»',NULL,'30101810100000000787','044525787'),(17,1,'ОАО «БАНК УРАЛСИБ»',NULL,'30101810100000000787','044525787'),(18,1,'ОАО «БАНК УРАЛСИБ»',NULL,'30101810100000000787','044525787'),(19,1,'ОАО «БАНК УРАЛСИБ»',NULL,'30101810100000000787','044525787'),(20,1,'ОАО Сбербанк России',NULL,'30101810400000000225','044525225'),(21,1,'ОАО \"УРАЛСИБ\"',NULL,'30101810100000000787','044525787'),(22,1,'ОАО \"УРАЛСИБ\"',NULL,'30101810100000000787','044525787'),(23,1,'ОАО \"УРАЛСИБ\"',NULL,'30101810100000000787','044525787'),(24,1,'ОАО \"УРАЛСИБ\"',NULL,'30101810100000000787','044525787'),(25,1,'ОАО \"УРАЛСИБ\"',NULL,'30101810100000000787','044525787'),(26,1,'ОАО \"УРАЛСИБ\"',NULL,'30101810100000000787','044525787'),(27,1,'ОАО СБЕРБАНК РОССИИ',NULL,'30101810400000000225','044525225'),(28,1,'ОАО СБЕРБАНК РОССИИ',NULL,'30101810400000000225','044525225'),(29,2,NULL,NULL,NULL,''),(30,2,NULL,NULL,NULL,''),(31,1,'ОАО \"СБЕРБАНК РОССИИ\"',NULL,'30101810400000000225','044525225'),(32,1,'ОАО «Сбербанк России», г. Москва',NULL,'30101810400000000225','044525225'),(33,1,'ОАО «Сбербанк России», г. Москва',NULL,'30101810400000000225','044525225'),(34,1,NULL,NULL,NULL,NULL),(35,1,'ОАО \"УРАЛСИБ\" г. Москва',NULL,'30101810100000000787','044525787'),(36,1,'ОАО \"УРАЛСИБ\" г. Москва',NULL,'30101810100000000787','044525787'),(37,1,'ОАО \"УРАЛСИБ\" г. Москва',NULL,'30101810100000000787','044525787'),(38,1,'ОАО \"УРАЛСИБ\"',NULL,'30101810100000000787','044525787'),(39,1,'ПАО Сбербанк, г.Москва',NULL,'30101810400000000225','044525225'),(40,1,'ПАО Сбербанк, г.Москва',NULL,'30101810400000000225','044525225');
/*!40000 ALTER TABLE `organization_settlement_account` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `organization_settlement_account_properties`
--

DROP TABLE IF EXISTS `organization_settlement_account_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `organization_settlement_account_properties` (
  `organization_record_id` int(11) DEFAULT NULL,
  `settlement_account_type_id` int(1) DEFAULT '1',
  `property` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  UNIQUE KEY `organization_record_id-settlement_account_type_id-property` (`organization_record_id`,`settlement_account_type_id`,`property`),
  CONSTRAINT `fk-organization_settlement_account_properties-organization_r_id` FOREIGN KEY (`organization_record_id`) REFERENCES `organization` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `organization_settlement_account_properties`
--

LOCK TABLES `organization_settlement_account_properties` WRITE;
/*!40000 ALTER TABLE `organization_settlement_account_properties` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `organization_settlement_account_properties` VALUES (1,1,'bank_account_RUB','40702810038110015462'),(2,1,'bank_account_RUB','40702810038110015462'),(3,1,'bank_account_RUB','40702810038110015462'),(4,1,'bank_account_RUB','40702810038110015462'),(5,1,'bank_account_RUB','40702810038110015462'),(6,1,'bank_account_RUB','40702810038110015462'),(7,1,'bank_account_RUB','40702810038110015462'),(8,1,'bank_account_RUB','40702810538110011157'),(9,1,'bank_account_RUB','40702810538110011157'),(10,1,'bank_account_RUB','40702810538110011157'),(11,1,'bank_account_RUB','40702810538110011157'),(12,1,'bank_account_RUB','40702810600301422002'),(13,1,'bank_account_RUB','40702810600301422002'),(14,1,'bank_account_RUB','40702810600301422002'),(15,1,'bank_account_RUB','40702810500540001425'),(16,1,'bank_account_RUB','40702810800540001507'),(17,1,'bank_account_RUB','40702810800540001507'),(18,1,'bank_account_RUB','40702810800540001507'),(19,1,'bank_account_RUB','40702810800540001507'),(20,1,'bank_account_RUB','40702810038110016607'),(21,1,'bank_account_RUB','40702810500540000002'),(22,1,'bank_account_RUB','40702810500540000002'),(23,1,'bank_account_RUB','40702810500540000002'),(24,1,'bank_account_RUB','40702810500540000002'),(25,1,'bank_account_RUB','40702810500540000002'),(26,1,'bank_account_RUB','40702810500540000002'),(27,1,'bank_account_RUB','40702810038110020279'),(28,1,'bank_account_RUB','40702810038110020279'),(29,2,'bank_account_HUF','40702810038110015462'),(30,2,'bank_account_HUF','12010611- 01424475 - 00100006 Ft'),(31,1,'bank_account_RUB','40702810038000034045'),(32,1,'bank_account_RUB','40702810538110016699'),(33,1,'bank_account_RUB','40702810538110016699'),(34,1,'bank_account_RUB',NULL),(35,1,'bank_account_RUB','40702810100540001508'),(36,1,'bank_account_RUB','40702810100540001508'),(37,1,'bank_account_RUB','40702810100540001508'),(38,1,'bank_account_RUB','40702810500540000002'),(39,1,'bank_account_RUB','40702810038000034045'),(40,1,'bank_account_RUB','40702810038110015462');
/*!40000 ALTER TABLE `organization_settlement_account_properties` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `params`
--

DROP TABLE IF EXISTS `params`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `params` (
  `param` varchar(255) NOT NULL DEFAULT '',
  `value` text,
  PRIMARY KEY (`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `params`
--

LOCK TABLES `params` WRITE;
/*!40000 ALTER TABLE `params` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `params` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `payment_sber_online`
--

DROP TABLE IF EXISTS `payment_sber_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_sber_online` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_sent_date` date DEFAULT NULL,
  `payment_received_date` date DEFAULT NULL,
  `code1` varchar(32) DEFAULT NULL,
  `code2` varchar(32) DEFAULT NULL,
  `code3` varchar(32) DEFAULT NULL,
  `code4` varchar(32) DEFAULT NULL,
  `code5` varchar(32) DEFAULT NULL,
  `payer` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `sum_paid` decimal(12,2) DEFAULT NULL,
  `sum_received` decimal(12,2) DEFAULT NULL,
  `sum_fee` decimal(12,2) DEFAULT NULL,
  `day` int(4) DEFAULT NULL,
  `month` int(4) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_sent_date__code123` (`payment_sent_date`,`code1`,`code2`,`code3`),
  KEY `payment_date` (`year`,`month`,`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_sber_online`
--

LOCK TABLES `payment_sber_online` WRITE;
/*!40000 ALTER TABLE `payment_sber_online` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `payment_sber_online` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `payments_orders`
--

DROP TABLE IF EXISTS `payments_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datestart` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateauthorize` timestamp NULL DEFAULT NULL,
  `datepaid` timestamp NULL DEFAULT NULL,
  `datecancel` timestamp NULL DEFAULT NULL,
  `type` enum('card') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `client_id` int(11) NOT NULL,
  `sum` decimal(12,2) NOT NULL,
  `status` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'Created',
  `details` text,
  `bill_no` varchar(20) DEFAULT NULL,
  `bill_payment_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1048 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments_orders`
--

LOCK TABLES `payments_orders` WRITE;
/*!40000 ALTER TABLE `payments_orders` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `payments_orders` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `paypal_payment`
--

DROP TABLE IF EXISTS `paypal_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paypal_payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `created_at` datetime NOT NULL,
  `client_id` int(11) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'HUF',
  `sum` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payer_id` varchar(64) NOT NULL,
  `payment_id` varchar(64) NOT NULL,
  `data1` text NOT NULL,
  `data2` text NOT NULL,
  `data3` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paypal_payment`
--

LOCK TABLES `paypal_payment` WRITE;
/*!40000 ALTER TABLE `paypal_payment` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `paypal_payment` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `person`
--

DROP TABLE IF EXISTS `person`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `person` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `signature_file_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `full_info` (`signature_file_name`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `person`
--

LOCK TABLES `person` WRITE;
/*!40000 ALTER TABLE `person` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `person` VALUES (4,''),(6,''),(8,''),(9,''),(10,''),(11,''),(14,''),(15,''),(16,'sign_ant.gif'),(1,'sign_bnv.png'),(12,'sign_kor.png'),(5,'sign_mel.gif'),(19,'sign_mel.gif'),(7,'sign_nat.png'),(17,'sign_nem.png'),(20,'sign_nem.png'),(2,'sign_pma.png'),(18,'sign_sim.png'),(13,'sign_usk.png'),(3,'sign_vav.png'),(21,'sign_vdn.png');
/*!40000 ALTER TABLE `person` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `person_i18n`
--

DROP TABLE IF EXISTS `person_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `person_i18n` (
  `person_id` int(11) DEFAULT NULL,
  `lang_code` varchar(5) NOT NULL DEFAULT 'ru-RU',
  `field` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  UNIQUE KEY `person_id-lang_code-field` (`person_id`,`lang_code`,`field`),
  KEY `fk-person_i18n-lang_code` (`lang_code`),
  CONSTRAINT `fk-person_i18n-lang_code` FOREIGN KEY (`lang_code`) REFERENCES `language` (`code`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-person_i18n-person_id` FOREIGN KEY (`person_id`) REFERENCES `person` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `person_i18n`
--

LOCK TABLES `person_i18n` WRITE;
/*!40000 ALTER TABLE `person_i18n` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `person_i18n` VALUES (1,'ru-RU','name_nominative','Бирюкова Н. В.'),(1,'ru-RU','name_genitive','Бирюковой Н.В.'),(1,'ru-RU','post_nominative','Генеральный директор'),(1,'ru-RU','post_genitive','Генерального директора'),(2,'ru-RU','name_nominative','Пыцкая М. А.'),(2,'ru-RU','name_genitive','Пыцкой М. А.'),(2,'ru-RU','post_nominative','Генеральный директор'),(2,'ru-RU','post_genitive','Генерального директора'),(3,'ru-RU','name_nominative','Вавилова Я. В.'),(3,'ru-RU','name_genitive','Вавиловой Я. В.'),(3,'ru-RU','post_nominative','Генеральный директор'),(3,'ru-RU','post_genitive','Генерального директора'),(4,'ru-RU','name_nominative','Мазур Т. В.'),(4,'ru-RU','name_genitive','Мазур Т. В.'),(4,'ru-RU','post_nominative','Генеральный директор'),(4,'ru-RU','post_genitive','Генерального директора'),(5,'ru-RU','name_nominative','Мельников А. К.'),(5,'ru-RU','name_genitive','Мельникова А. К.'),(5,'ru-RU','post_nominative','Генеральный директор'),(5,'ru-RU','post_genitive','Генерального директора'),(6,'ru-RU','name_nominative','Котельникова О. И.'),(6,'ru-RU','name_genitive','Котельникову О. И.'),(6,'ru-RU','post_nominative','Генеральный директор'),(6,'ru-RU','post_genitive','Генерального директора'),(7,'ru-RU','name_nominative','Надточеева Н. А.'),(7,'ru-RU','name_genitive','Надточеевой Н. А.'),(7,'ru-RU','post_nominative','Генеральный директор'),(7,'ru-RU','post_genitive','Генерального директора'),(8,'ru-RU','name_nominative','Юдицкая Н. С.'),(8,'ru-RU','name_genitive','Юдицкая Н. С.'),(8,'ru-RU','post_nominative','Генеральный директор'),(8,'ru-RU','post_genitive','Генерального директора'),(9,'ru-RU','name_nominative','Мельников Д. Б.'),(9,'ru-RU','name_genitive','Мельникова Д. Б.'),(9,'ru-RU','post_nominative','Генеральный директор'),(9,'ru-RU','post_genitive','Генерального директора'),(10,'ru-RU','name_nominative','Мельников Е. И.'),(10,'ru-RU','name_genitive','Мельникова Е. И.'),(10,'ru-RU','post_nominative','Директор'),(10,'ru-RU','post_genitive','Директора'),(11,'ru-RU','name_nominative','Полуторнова Т. В.'),(11,'ru-RU','name_genitive','Полуторнову Т. В.'),(11,'ru-RU','post_nominative','Генеральный директор'),(11,'ru-RU','post_genitive','Генерального директора'),(12,'ru-RU','name_nominative','Королева В. В.'),(12,'ru-RU','name_genitive','Королеву В. В.'),(12,'ru-RU','post_nominative','Генеральный директор'),(12,'ru-RU','post_genitive','Генерального директора'),(13,'ru-RU','name_nominative','Ускова М. С.'),(13,'ru-RU','name_genitive',''),(13,'ru-RU','post_nominative','Главный бухгалтер'),(13,'ru-RU','post_genitive','Главного бухгалтера'),(14,'ru-RU','name_nominative','Полехина Г. Н.'),(14,'ru-RU','name_genitive',''),(14,'ru-RU','post_nominative','Главный бухгалтер'),(14,'ru-RU','post_genitive','Главного бухгалтера'),(15,'ru-RU','name_nominative','Лаврова Г. М.'),(15,'ru-RU','name_genitive',''),(15,'ru-RU','post_nominative','Главный бухгалтер'),(15,'ru-RU','post_genitive','Главного бухгалтера'),(16,'ru-RU','name_nominative','Антонова Т. С.'),(16,'ru-RU','name_genitive',''),(16,'ru-RU','post_nominative','Главный бухгалтер'),(16,'ru-RU','post_genitive','Главного бухгалтера'),(17,'ru-RU','name_nominative','Нем И. В.'),(17,'ru-RU','name_genitive',''),(17,'ru-RU','post_nominative','Главный бухгалтер'),(17,'ru-RU','post_genitive','Главного бухгалтера'),(18,'ru-RU','name_nominative','Симоненко Т. Е.'),(18,'ru-RU','name_genitive',''),(18,'ru-RU','post_nominative','Главный бухгалтер'),(18,'ru-RU','post_genitive','Главного бухгалтера'),(19,'ru-RU','name_nominative','Alexander Melnikov'),(19,'ru-RU','name_genitive','Alexander Melnikov'),(19,'ru-RU','post_nominative','Igazgató'),(19,'ru-RU','post_genitive','Igazgató'),(20,'ru-RU','name_nominative','Нем И. В.'),(20,'ru-RU','name_genitive','Нем И.В.'),(20,'ru-RU','post_nominative','Главный бухгалтер'),(20,'ru-RU','post_genitive','Главного Бухгалтера'),(21,'ru-RU','name_nominative','Винокуров Д. М.'),(21,'ru-RU','name_genitive',''),(21,'ru-RU','post_nominative','Главный бухгалтер'),(21,'ru-RU','post_genitive','Главного бухгалтера');
/*!40000 ALTER TABLE `person_i18n` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `phisclients`
--

DROP TABLE IF EXISTS `phisclients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phisclients` (
  `pk` int(11) NOT NULL,
  `fio` varchar(255) NOT NULL,
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB',
  `phone` varchar(128) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone_connect` varchar(128) DEFAULT NULL,
  `contact_info` text,
  `phone_owner` varchar(255) DEFAULT NULL,
  `address_single_string` text,
  `addr_city` varchar(128) DEFAULT NULL,
  `addr_street` varchar(128) DEFAULT NULL,
  `addr_house` varchar(128) DEFAULT NULL,
  `addr_housing` varchar(128) DEFAULT NULL,
  `addr_build` varchar(128) DEFAULT NULL,
  `addr_flat` varchar(128) DEFAULT NULL,
  `addr_porch` varchar(128) DEFAULT NULL,
  `addr_floor` varchar(128) DEFAULT NULL,
  `addr_intercom` varchar(128) DEFAULT NULL,
  `passp_series` varchar(128) DEFAULT NULL,
  `passp_num` varchar(128) DEFAULT NULL,
  `passp_whos_given` varchar(128) DEFAULT NULL,
  `passp_when_given` varchar(128) DEFAULT NULL,
  `passp_code` varchar(128) DEFAULT NULL,
  `passp_birthday` varchar(128) DEFAULT NULL,
  `reg_city` varchar(128) DEFAULT NULL,
  `reg_street` varchar(128) DEFAULT NULL,
  `reg_house` varchar(128) DEFAULT NULL,
  `reg_housing` varchar(128) DEFAULT NULL,
  `reg_build` varchar(128) DEFAULT NULL,
  `reg_flat` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`pk`),
  CONSTRAINT `phisclients_ibfk_1` FOREIGN KEY (`pk`) REFERENCES `clients` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phisclients`
--

LOCK TABLES `phisclients` WRITE;
/*!40000 ALTER TABLE `phisclients` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `phisclients` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `phone_mail`
--

DROP TABLE IF EXISTS `phone_mail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phone_mail` (
  `client` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  `phone_listen` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`client`,`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phone_mail`
--

LOCK TABLES `phone_mail` WRITE;
/*!40000 ALTER TABLE `phone_mail` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `phone_mail` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `phone_mail_files`
--

DROP TABLE IF EXISTS `phone_mail_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phone_mail_files` (
  `client` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `size` int(11) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  PRIMARY KEY (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phone_mail_files`
--

LOCK TABLES `phone_mail_files` WRITE;
/*!40000 ALTER TABLE `phone_mail_files` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `phone_mail_files` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `phone_readdr`
--

DROP TABLE IF EXISTS `phone_readdr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phone_readdr` (
  `client` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phone_readdr`
--

LOCK TABLES `phone_readdr` WRITE;
/*!40000 ALTER TABLE `phone_readdr` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `phone_readdr` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `phone_readdr_time`
--

DROP TABLE IF EXISTS `phone_readdr_time`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phone_readdr_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `days` varchar(7) NOT NULL,
  `time_from` varchar(10) DEFAULT NULL,
  `time_to` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phone_readdr_time`
--

LOCK TABLES `phone_readdr_time` WRITE;
/*!40000 ALTER TABLE `phone_readdr_time` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `phone_readdr_time` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `phone_report`
--

DROP TABLE IF EXISTS `phone_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phone_report` (
  `client` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `period` enum('0','5m','30m','1h','6h','1d') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0',
  PRIMARY KEY (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phone_report`
--

LOCK TABLES `phone_report` WRITE;
/*!40000 ALTER TABLE `phone_report` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `phone_report` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `phone_short`
--

DROP TABLE IF EXISTS `phone_short`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phone_short` (
  `client` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `phone_short` varchar(3) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`client`,`phone_short`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phone_short`
--

LOCK TABLES `phone_short` WRITE;
/*!40000 ALTER TABLE `phone_short` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `phone_short` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `product_state`
--

DROP TABLE IF EXISTS `product_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_state` (
  `product` enum('vpbx','phone','feedback') NOT NULL DEFAULT 'phone',
  `client_id` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `client_id` (`client_id`,`product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_state`
--

LOCK TABLES `product_state` WRITE;
/*!40000 ALTER TABLE `product_state` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `product_state` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `qr_code`
--

DROP TABLE IF EXISTS `qr_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qr_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file` varchar(64) NOT NULL DEFAULT '',
  `code` varchar(16) DEFAULT NULL,
  `client_id` int(4) NOT NULL DEFAULT '0',
  `bill_no` char(11) NOT NULL,
  `doc_type` char(2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `k_client` (`client_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=52975 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qr_code`
--

LOCK TABLES `qr_code` WRITE;
/*!40000 ALTER TABLE `qr_code` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `qr_code` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `radcheck`
--

DROP TABLE IF EXISTS `radcheck`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radcheck` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `UserName` varbinary(64) NOT NULL DEFAULT '',
  `Attribute` varbinary(32) NOT NULL DEFAULT '',
  `op` binary(2) NOT NULL DEFAULT '==',
  `Value` varbinary(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `UserName` (`UserName`(32))
) ENGINE=InnoDB DEFAULT CHARSET=binary;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radcheck`
--

LOCK TABLES `radcheck` WRITE;
/*!40000 ALTER TABLE `radcheck` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `radcheck` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `radgroupcheck`
--

DROP TABLE IF EXISTS `radgroupcheck`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radgroupcheck` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `GroupName` varbinary(64) NOT NULL DEFAULT '',
  `Attribute` varbinary(32) NOT NULL DEFAULT '',
  `op` binary(2) NOT NULL DEFAULT '==',
  `Value` varbinary(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `GroupName` (`GroupName`(32))
) ENGINE=InnoDB DEFAULT CHARSET=binary;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radgroupcheck`
--

LOCK TABLES `radgroupcheck` WRITE;
/*!40000 ALTER TABLE `radgroupcheck` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `radgroupcheck` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `radgroupreply`
--

DROP TABLE IF EXISTS `radgroupreply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radgroupreply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `GroupName` varbinary(64) NOT NULL DEFAULT '',
  `Attribute` varbinary(32) NOT NULL DEFAULT '',
  `op` binary(2) NOT NULL DEFAULT '=\0',
  `Value` varbinary(253) NOT NULL DEFAULT '',
  `prio` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `GroupName` (`GroupName`(32))
) ENGINE=InnoDB DEFAULT CHARSET=binary;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radgroupreply`
--

LOCK TABLES `radgroupreply` WRITE;
/*!40000 ALTER TABLE `radgroupreply` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `radgroupreply` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `radreply`
--

DROP TABLE IF EXISTS `radreply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radreply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `UserName` varbinary(64) NOT NULL DEFAULT '',
  `Attribute` varbinary(32) NOT NULL DEFAULT '',
  `op` binary(2) NOT NULL DEFAULT '=\0',
  `Value` varbinary(253) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `UserName` (`UserName`(32))
) ENGINE=InnoDB DEFAULT CHARSET=binary;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radreply`
--

LOCK TABLES `radreply` WRITE;
/*!40000 ALTER TABLE `radreply` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `radreply` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `radusergroup`
--

DROP TABLE IF EXISTS `radusergroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `radusergroup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `UserName` varbinary(64) NOT NULL DEFAULT '',
  `GroupName` varbinary(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `UserName` (`UserName`(32))
) ENGINE=InnoDB DEFAULT CHARSET=binary;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `radusergroup`
--

LOCK TABLES `radusergroup` WRITE;
/*!40000 ALTER TABLE `radusergroup` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `radusergroup` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `short_name` varchar(10) NOT NULL,
  `code` int(11) DEFAULT NULL,
  `timezone_name` varchar(50) NOT NULL,
  `country_id` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regions`
--

LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `regions` VALUES (81,'Hungary','Внгр',36,'Europe/Budapest',348),(82,'Германия','Герм',49,'Europe/Moscow',276),(83,'Хабаровск','Хаб',74212,'Asia/Vladivostok',643),(84,'Уфа','Уфа',7347,'Asia/Yekaterinburg',643),(85,'Брянск','Брск',74832,'Europe/Moscow',643),(86,'Воронеж','Врнж',7473,'Europe/Moscow',643),(87,'Ростов-на-Дону','РнД',7863,'Europe/Moscow',643),(88,'Нижний Новгород','НН',7831,'Europe/Moscow',643),(89,'Владивосток','Влдв',7423,'Asia/Vladivostok',643),(90,'Челябинск','Члб',7351,'Asia/Yekaterinburg',643),(91,'Волгоград','Влгрд',78442,'Europe/Volgograd',643),(92,'Пермь','Прм',7342,'Asia/Yekaterinburg',643),(93,'Казань','Кзн',7843,'Europe/Moscow',643),(94,'Новосибирск','Нсб',7383,'Asia/Novosibirsk',643),(95,'Екатеринбург','Екб',7343,'Asia/Yekaterinburg',643),(96,'Самара','Смр',7846,'Europe/Samara',643),(97,'Краснодар','Крд',7861,'Europe/Moscow',643),(98,'Санкт-Петербург','СпБ',7812,'Europe/Moscow',643),(99,'Москва','МСК',7495,'Europe/Moscow',643);
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `saldo`
--

DROP TABLE IF EXISTS `saldo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saldo` (
  `client` varchar(32) NOT NULL DEFAULT '',
  `date_of_last_saldo` date DEFAULT '1970-01-02',
  `fix_saldo` decimal(7,2) DEFAULT NULL,
  `saldo` decimal(7,2) DEFAULT NULL,
  `non_count` decimal(7,2) DEFAULT NULL,
  `zalog` decimal(7,2) DEFAULT NULL,
  `comment` tinytext CHARACTER SET latin1,
  PRIMARY KEY (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saldo`
--

LOCK TABLES `saldo` WRITE;
/*!40000 ALTER TABLE `saldo` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `saldo` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `sale_channel`
--

DROP TABLE IF EXISTS `sale_channel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_channel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_channel`
--

LOCK TABLES `sale_channel` WRITE;
/*!40000 ALTER TABLE `sale_channel` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `sale_channel` VALUES (1,'Сарафанное радио'),(2,'Поиск в Интернет'),(3,'Реклама в Интернет');
/*!40000 ALTER TABLE `sale_channel` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `sale_channels_old`
--

DROP TABLE IF EXISTS `sale_channels_old`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_channels_old` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  `dealer_id` int(11) NOT NULL DEFAULT '0',
  `is_agent` tinyint(4) NOT NULL DEFAULT '0',
  `interest` decimal(5,2) NOT NULL DEFAULT '0.00',
  `courier_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sale_channels_old`
--

LOCK TABLES `sale_channels_old` WRITE;
/*!40000 ALTER TABLE `sale_channels_old` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `sale_channels_old` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `send_assigns`
--

DROP TABLE IF EXISTS `send_assigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `send_assigns` (
  `client` varchar(50) NOT NULL DEFAULT '',
  `id_letter` int(11) NOT NULL DEFAULT '0',
  `last_send` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `state` enum('error','ready','sent') NOT NULL DEFAULT 'error',
  `message` text NOT NULL,
  PRIMARY KEY (`client`,`id_letter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `send_assigns`
--

LOCK TABLES `send_assigns` WRITE;
/*!40000 ALTER TABLE `send_assigns` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `send_assigns` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `send_client`
--

DROP TABLE IF EXISTS `send_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `send_client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(50) NOT NULL DEFAULT '',
  `state` enum('error','ready','viewed','sent') NOT NULL DEFAULT 'ready',
  `bill_no` varchar(32) NOT NULL DEFAULT '',
  `last_send` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill` (`client`,`bill_no`)
) ENGINE=InnoDB AUTO_INCREMENT=1347 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `send_client`
--

LOCK TABLES `send_client` WRITE;
/*!40000 ALTER TABLE `send_client` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `send_client` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `send_files`
--

DROP TABLE IF EXISTS `send_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `send_files` (
  `filename` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `id_letter` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`filename`,`id_letter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `send_files`
--

LOCK TABLES `send_files` WRITE;
/*!40000 ALTER TABLE `send_files` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `send_files` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `send_letters`
--

DROP TABLE IF EXISTS `send_letters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `send_letters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `send_letters`
--

LOCK TABLES `send_letters` WRITE;
/*!40000 ALTER TABLE `send_letters` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `send_letters` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `server_pbx`
--

DROP TABLE IF EXISTS `server_pbx`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `server_pbx` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `datacenter_id` int(11) NOT NULL DEFAULT '0',
  `trunk_vpbx_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `server_pbx`
--

LOCK TABLES `server_pbx` WRITE;
/*!40000 ALTER TABLE `server_pbx` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `server_pbx` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `sms_stat`
--

DROP TABLE IF EXISTS `sms_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_stat` (
  `pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sender` int(10) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL,
  `date_hour` datetime NOT NULL,
  PRIMARY KEY (`pk`),
  UNIQUE KEY `sender_hour` (`sender`,`date_hour`),
  KEY `sender` (`sender`)
) ENGINE=InnoDB AUTO_INCREMENT=3103 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_stat`
--

LOCK TABLES `sms_stat` WRITE;
/*!40000 ALTER TABLE `sms_stat` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `sms_stat` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `stat_voip_free_cache`
--

DROP TABLE IF EXISTS `stat_voip_free_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stat_voip_free_cache` (
  `number` varchar(16) NOT NULL,
  `month` tinyint(4) NOT NULL,
  `calls` smallint(6) NOT NULL,
  KEY `number` (`number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stat_voip_free_cache`
--

LOCK TABLES `stat_voip_free_cache` WRITE;
/*!40000 ALTER TABLE `stat_voip_free_cache` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `stat_voip_free_cache` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `stats_send`
--

DROP TABLE IF EXISTS `stats_send`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_send` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `port_id` int(11) NOT NULL DEFAULT '0',
  `in_bytes` bigint(20) NOT NULL DEFAULT '0',
  `out_bytes` bigint(20) NOT NULL DEFAULT '0',
  `max_bytes` bigint(20) NOT NULL DEFAULT '0',
  `year` int(11) NOT NULL DEFAULT '0',
  `month` int(11) NOT NULL DEFAULT '0',
  `state` enum('error','ready','sent') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'ready',
  `last_send` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `message` blob NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UN` (`client`,`year`,`month`,`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stats_send`
--

LOCK TABLES `stats_send` WRITE;
/*!40000 ALTER TABLE `stats_send` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `stats_send` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `store`
--

DROP TABLE IF EXISTS `store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store` (
  `id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store`
--

LOCK TABLES `store` WRITE;
/*!40000 ALTER TABLE `store` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `store` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `support_ticket`
--

DROP TABLE IF EXISTS `support_ticket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_ticket` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account_id` int(10) unsigned NOT NULL,
  `user_id` char(24) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `subject` varchar(1000) NOT NULL,
  `status` enum('open','done','closed','reopened') NOT NULL,
  `is_with_new_comment` tinyint(4) NOT NULL DEFAULT '0',
  `department` enum('sales','accounting','technical') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'technical',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `client_account_id` (`client_account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=405 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket`
--

LOCK TABLES `support_ticket` WRITE;
/*!40000 ALTER TABLE `support_ticket` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `support_ticket` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `support_ticket_comment`
--

DROP TABLE IF EXISTS `support_ticket_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_ticket_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int(10) unsigned NOT NULL,
  `user_id` char(24) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `text` text NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=953 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `support_ticket_comment`
--

LOCK TABLES `support_ticket_comment` WRITE;
/*!40000 ALTER TABLE `support_ticket_comment` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `support_ticket_comment` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `sync_welltime_stages`
--

DROP TABLE IF EXISTS `sync_welltime_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_welltime_stages` (
  `last_stage_id` int(10) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '1970-01-02 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_welltime_stages`
--

LOCK TABLES `sync_welltime_stages` WRITE;
/*!40000 ALTER TABLE `sync_welltime_stages` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `sync_welltime_stages` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `used_times` int(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tags_resource`
--

DROP TABLE IF EXISTS `tags_resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags_resource` (
  `tag_id` int(11) DEFAULT NULL,
  `resource` varchar(128) DEFAULT NULL,
  `resource_id` int(11) DEFAULT NULL,
  UNIQUE KEY `tag_id-resource-resource_id` (`tag_id`,`resource`,`resource_id`),
  CONSTRAINT `fk-tags_resource-tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags_resource`
--

LOCK TABLES `tags_resource` WRITE;
/*!40000 ALTER TABLE `tags_resource` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tags_resource` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tariffication_feature`
--

DROP TABLE IF EXISTS `tariffication_feature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tariffication_feature` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `service_type_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tariffication_feature`
--

LOCK TABLES `tariffication_feature` WRITE;
/*!40000 ALTER TABLE `tariffication_feature` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tariffication_feature` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tariffication_product`
--

DROP TABLE IF EXISTS `tariffication_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tariffication_product` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tariffication_product`
--

LOCK TABLES `tariffication_product` WRITE;
/*!40000 ALTER TABLE `tariffication_product` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tariffication_product` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tariffication_rate`
--

DROP TABLE IF EXISTS `tariffication_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tariffication_rate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `feature_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tariffication_rate`
--

LOCK TABLES `tariffication_rate` WRITE;
/*!40000 ALTER TABLE `tariffication_rate` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tariffication_rate` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tariffication_service`
--

DROP TABLE IF EXISTS `tariffication_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tariffication_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `service_type_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tariffication_service`
--

LOCK TABLES `tariffication_service` WRITE;
/*!40000 ALTER TABLE `tariffication_service` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tariffication_service` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tariffication_service_feature_item`
--

DROP TABLE IF EXISTS `tariffication_service_feature_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tariffication_service_feature_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tariffication_service_feature_item`
--

LOCK TABLES `tariffication_service_feature_item` WRITE;
/*!40000 ALTER TABLE `tariffication_service_feature_item` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tariffication_service_feature_item` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tariffication_service_type`
--

DROP TABLE IF EXISTS `tariffication_service_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tariffication_service_type` (
  `id` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tariffication_service_type`
--

LOCK TABLES `tariffication_service_type` WRITE;
/*!40000 ALTER TABLE `tariffication_service_type` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tariffication_service_type` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tariffication_subscription`
--

DROP TABLE IF EXISTS `tariffication_subscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tariffication_subscription` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `feature_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tariffication_subscription`
--

LOCK TABLES `tariffication_subscription` WRITE;
/*!40000 ALTER TABLE `tariffication_subscription` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tariffication_subscription` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tarifs_call_chat`
--

DROP TABLE IF EXISTS `tarifs_call_chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifs_call_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL DEFAULT '',
  `price` decimal(13,4) NOT NULL DEFAULT '0.0000',
  `currency_id` char(3) NOT NULL DEFAULT 'USD',
  `price_include_vat` tinyint(1) DEFAULT '1',
  `status` enum('public','special','archive') NOT NULL DEFAULT 'public',
  `edit_user` int(11) NOT NULL DEFAULT '0',
  `edit_time` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifs_call_chat`
--

LOCK TABLES `tarifs_call_chat` WRITE;
/*!40000 ALTER TABLE `tarifs_call_chat` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tarifs_call_chat` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tarifs_extra`
--

DROP TABLE IF EXISTS `tarifs_extra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifs_extra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `description` varchar(100) NOT NULL DEFAULT '',
  `param_name` varchar(50) NOT NULL,
  `is_countable` tinyint(1) NOT NULL DEFAULT '0',
  `period` enum('month','year','once','3mon','6mon') DEFAULT NULL,
  `price` decimal(13,4) NOT NULL DEFAULT '0.0000',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  `status` enum('public','special','archive','itpark') NOT NULL DEFAULT 'public',
  `edit_user` int(11) NOT NULL DEFAULT '0',
  `edit_time` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `okvd_code` int(4) NOT NULL DEFAULT '0',
  `price_include_vat` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifs_extra`
--

LOCK TABLES `tarifs_extra` WRITE;
/*!40000 ALTER TABLE `tarifs_extra` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tarifs_extra` VALUES (25,'uspd','Поддержка первичного DNS р','',1,'month',53.1000,'RUB','public',3,'2010-11-17 09:17:39',0,1),(158,'','Аренда ПК','',1,'month',1000.0000,'RUB','itpark',33,'2010-07-13 12:08:24',0,1),(159,'','Аренда нежилых помещений ','Этаж,офис',1,'month',1041.6700,'RUB','itpark',33,'2012-10-10 13:21:00',55,1),(219,'welltime','Welltime  1.4.3','Доступ в репозиторий',0,'once',0.0000,'RUB','public',54,'2010-12-06 14:26:38',0,1),(224,'welltime','Обновление ПО Welltime ','',0,'once',5999.0000,'RUB','public',4,'2012-05-03 14:56:58',0,1),(226,'welltime','Welltime  1.4.2','Доступ в репозиторий',0,'once',0.0000,'RUB','public',54,'2010-12-06 14:26:49',0,1),(227,'welltime','Welltime  1.4.4','Доступ в репозиторий',0,'once',0.0000,'RUB','public',54,'2010-12-06 14:26:57',0,1),(999,'','Тестовая доп. услуга ','',1,'month',999.6700,'RUB','public',33,'2012-10-10 13:21:00',55,1);
/*!40000 ALTER TABLE `tarifs_extra` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tarifs_internet`
--

DROP TABLE IF EXISTS `tarifs_internet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifs_internet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `pay_once` decimal(9,2) NOT NULL DEFAULT '0.00',
  `pay_month` decimal(9,2) NOT NULL DEFAULT '0.00',
  `mb_month` int(11) NOT NULL DEFAULT '0',
  `pay_mb` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `comment` text NOT NULL,
  `type` enum('I','C','V') NOT NULL DEFAULT 'I',
  `type_internet` enum('standard','wimax','collective') NOT NULL DEFAULT 'standard',
  `sum_deposit` decimal(7,2) NOT NULL DEFAULT '0.00',
  `type_count` enum('sep','r2_f','all_f') NOT NULL DEFAULT 'sep',
  `status` enum('public','special','archive','test','adsl_su') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public',
  `month_r` int(11) NOT NULL DEFAULT '0',
  `month_r2` int(11) NOT NULL DEFAULT '0',
  `month_f` int(11) NOT NULL DEFAULT '0',
  `pay_r` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `pay_r2` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `pay_f` decimal(7,4) NOT NULL DEFAULT '0.0000',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  `edit_user` int(11) DEFAULT NULL,
  `edit_time` datetime DEFAULT NULL,
  `adsl_speed` varchar(11) DEFAULT '768/6144',
  `price_include_vat` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=560 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifs_internet`
--

LOCK TABLES `tarifs_internet` WRITE;
/*!40000 ALTER TABLE `tarifs_internet` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tarifs_internet` VALUES (1,'Минимальный',352.82,93.22,512,0.1180,'','I','standard',0.00,'sep','archive',0,0,0,0.0000,0.0000,0.0000,'USD',60,'2006-10-17 17:55:24','768/6144',1),(2,'Стартовый',116.82,116.82,1024,0.0944,'','I','standard',100.00,'sep','archive',0,0,0,0.0000,0.0000,0.0000,'USD',60,'2007-07-31 16:59:25','768/6144',1);
/*!40000 ALTER TABLE `tarifs_internet` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tarifs_number`
--

DROP TABLE IF EXISTS `tarifs_number`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifs_number` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `currency_id` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `city_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('public','special','archive') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `activation_fee` decimal(10,2) NOT NULL,
  `period` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `did_group_id` int(11) DEFAULT NULL,
  `old_beauty_level` int(11) DEFAULT NULL,
  `old_prefix` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-tarifs_number-city_id` (`city_id`),
  KEY `fk-tarifs_number-country_id` (`country_id`),
  CONSTRAINT `fk-tarifs_number-city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`),
  CONSTRAINT `fk-tarifs_number-country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifs_number`
--

LOCK TABLES `tarifs_number` WRITE;
/*!40000 ALTER TABLE `tarifs_number` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tarifs_number` VALUES (2,643,'RUB',7495,'Стандартные 499','public',0.00,'month',2,NULL,NULL),(5,643,'RUB',7495,'Серебряные','public',5999.00,'month',5,NULL,NULL);
/*!40000 ALTER TABLE `tarifs_number` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tarifs_sms`
--

DROP TABLE IF EXISTS `tarifs_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifs_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('public','archive') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public',
  `description` varchar(100) NOT NULL DEFAULT '',
  `period` enum('month') DEFAULT 'month',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB',
  `per_month_price` decimal(13,2) NOT NULL DEFAULT '0.00',
  `per_sms_price` decimal(13,2) NOT NULL DEFAULT '0.00',
  `edit_user` int(11) NOT NULL DEFAULT '0',
  `edit_time` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `price_include_vat` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifs_sms`
--

LOCK TABLES `tarifs_sms` WRITE;
/*!40000 ALTER TABLE `tarifs_sms` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tarifs_sms` VALUES (13,'public','Тариф Старт 2015','month','RUB',0.00,1.40,54,'2015-11-30 09:27:42',1),(14,'public','Тариф Стандарт 2015','month','RUB',500.00,1.30,54,'2015-11-30 09:28:30',1);
/*!40000 ALTER TABLE `tarifs_sms` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tarifs_virtpbx`
--

DROP TABLE IF EXISTS `tarifs_virtpbx`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifs_virtpbx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('public','special','archive','test') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public',
  `description` varchar(100) NOT NULL DEFAULT '',
  `period` enum('month') DEFAULT 'month',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB',
  `price` decimal(13,4) NOT NULL DEFAULT '0.0000',
  `num_ports` int(4) NOT NULL DEFAULT '0',
  `overrun_per_port` decimal(13,4) NOT NULL DEFAULT '0.0000',
  `space` int(4) NOT NULL DEFAULT '0',
  `overrun_per_gb` decimal(13,4) DEFAULT '0.0000',
  `ext_did_count` smallint(6) DEFAULT '0',
  `ext_did_monthly_payment` decimal(13,4) DEFAULT '0.0000',
  `is_record` tinyint(4) NOT NULL DEFAULT '0',
  `is_web_call` tinyint(4) NOT NULL DEFAULT '0',
  `is_fax` tinyint(4) NOT NULL DEFAULT '0',
  `edit_user` int(11) NOT NULL DEFAULT '0',
  `edit_time` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `price_include_vat` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifs_virtpbx`
--

LOCK TABLES `tarifs_virtpbx` WRITE;
/*!40000 ALTER TABLE `tarifs_virtpbx` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tarifs_virtpbx` VALUES (42,'public','Тестовый','month','RUB',0.0000,8,100.0000,50,100.0000,0,0.0000,1,0,1,60,'2015-08-20 11:11:59',1);
/*!40000 ALTER TABLE `tarifs_virtpbx` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tarifs_voip`
--

DROP TABLE IF EXISTS `tarifs_voip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifs_voip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL DEFAULT '643',
  `connection_point_id` int(11) DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `name_short` varchar(50) NOT NULL DEFAULT '',
  `sum_deposit` decimal(11,4) NOT NULL DEFAULT '0.0000',
  `month_line` decimal(11,2) NOT NULL DEFAULT '0.00',
  `month_number` decimal(11,2) NOT NULL DEFAULT '0.00',
  `once_line` decimal(11,2) NOT NULL DEFAULT '0.00',
  `once_number` decimal(11,2) NOT NULL DEFAULT '0.00',
  `type_count` enum('all','unlim_r','unlim_all') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `status` enum('public','special','archive','7800','7800_test','test','operator','transit') DEFAULT NULL,
  `period` enum('immediately','day','week','month','6months','year') NOT NULL DEFAULT 'month',
  `free_local_min` int(11) DEFAULT '0',
  `freemin_for_number` tinyint(1) NOT NULL DEFAULT '0',
  `month_min_payment` decimal(11,2) NOT NULL DEFAULT '0.00',
  `dest` smallint(6) NOT NULL,
  `currency_id` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  `priceid` int(11) NOT NULL,
  `edit_user` int(11) DEFAULT NULL,
  `edit_time` datetime DEFAULT NULL,
  `is_clientSelectable` tinyint(1) NOT NULL DEFAULT '0',
  `tarif_group` int(10) unsigned NOT NULL DEFAULT '5',
  `pricelist_id` smallint(6) NOT NULL,
  `paid_redirect` tinyint(1) NOT NULL DEFAULT '0',
  `tariffication_by_minutes` tinyint(4) NOT NULL DEFAULT '0',
  `tariffication_full_first_minute` tinyint(4) NOT NULL DEFAULT '0',
  `tariffication_free_first_seconds` tinyint(4) NOT NULL DEFAULT '0',
  `tmp` int(11) DEFAULT NULL,
  `is_virtual` tinyint(4) NOT NULL DEFAULT '0',
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  `price_include_vat` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `tarif_group` (`tarif_group`),
  KEY `is_testing_status_month_line_month_min_payment` (`is_default`,`status`,`month_line`,`month_min_payment`)
) ENGINE=InnoDB AUTO_INCREMENT=635 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifs_voip`
--

LOCK TABLES `tarifs_voip` WRITE;
/*!40000 ALTER TABLE `tarifs_voip` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tarifs_voip` VALUES (72,643,99,'Междугородные звонки Базовый','',0.0000,0.00,0.00,0.00,0.00,NULL,'public','month',0,0,0.00,1,'RUB',0,60,'2015-02-02 09:15:53',0,5,17,0,1,1,1,6,0,0,1),(78,643,99,'Дальнее зарубежье Базовый','',0.0000,0.00,0.00,0.00,0.00,NULL,'public','month',0,0,0.00,2,'RUB',0,60,'2015-02-02 09:16:13',0,5,17,0,1,1,1,6,0,0,1),(86,643,99,'Местные мобильные Базовый','',0.0000,0.00,0.00,0.00,0.00,NULL,'public','month',0,0,0.00,5,'RUB',0,60,'2015-02-02 09:15:43',0,5,39,0,1,1,1,6,0,0,1),(531,643,99,'Тариф_0_2015','',0.0000,99.00,349.00,0.00,0.00,NULL,'public','month',0,1,0.00,4,'RUB',0,60,'2016-03-14 14:45:31',0,5,61,0,1,1,1,NULL,0,0,1),(533,643,99,'Тариф_unlim_2015','',0.0000,949.00,949.00,0.00,0.00,NULL,'public','month',5000,0,0.00,4,'RUB',0,60,'2015-08-19 10:33:12',0,5,61,0,1,1,1,NULL,0,0,1),(624,643,99,'Тестовый_2015','',0.0000,0.00,0.00,0.00,0.00,NULL,'test','month',0,0,0.00,4,'RUB',0,60,'2015-08-21 15:08:40',0,5,61,0,1,1,1,NULL,0,0,1);
/*!40000 ALTER TABLE `tarifs_voip` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tarifs_voip_package`
--

DROP TABLE IF EXISTS `tarifs_voip_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifs_voip_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL DEFAULT '643',
  `connection_point_id` int(11) DEFAULT '0',
  `currency_id` char(3) NOT NULL DEFAULT 'USD',
  `destination_id` int(11) DEFAULT '0',
  `pricelist_id` smallint(6) DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `price_include_vat` tinyint(1) DEFAULT '1',
  `periodical_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `min_payment` int(11) NOT NULL DEFAULT '0',
  `minutes_count` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_tariff_voip_package__destination_id` (`destination_id`),
  CONSTRAINT `fk_tariff_voip_package__destination_id` FOREIGN KEY (`destination_id`) REFERENCES `voip_destination` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarifs_voip_package`
--

LOCK TABLES `tarifs_voip_package` WRITE;
/*!40000 ALTER TABLE `tarifs_voip_package` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tarifs_voip_package` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tech_cpe2voip`
--

DROP TABLE IF EXISTS `tech_cpe2voip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tech_cpe2voip` (
  `cpe_id` int(11) NOT NULL,
  `usage_id` int(11) NOT NULL,
  `line_number` int(11) NOT NULL,
  PRIMARY KEY (`cpe_id`,`usage_id`,`line_number`),
  KEY `usage_id` (`usage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tech_cpe2voip`
--

LOCK TABLES `tech_cpe2voip` WRITE;
/*!40000 ALTER TABLE `tech_cpe2voip` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tech_cpe2voip` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tech_cpe_models`
--

DROP TABLE IF EXISTS `tech_cpe_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tech_cpe_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `model` varchar(255) DEFAULT NULL,
  `default_deposit_sumUSD` decimal(7,2) NOT NULL DEFAULT '0.00',
  `default_deposit_sumRUB` decimal(7,2) NOT NULL DEFAULT '0.00',
  `type` enum('','voip','router','adsl','wireless','pon') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tech_cpe_models`
--

LOCK TABLES `tech_cpe_models` WRITE;
/*!40000 ALTER TABLE `tech_cpe_models` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tech_cpe_models` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tech_nets`
--

DROP TABLE IF EXISTS `tech_nets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tech_nets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `net` varchar(18) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tech_nets`
--

LOCK TABLES `tech_nets` WRITE;
/*!40000 ALTER TABLE `tech_nets` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tech_nets` VALUES (1,'89.235.159.0/24'),(2,'89.235.165.0/24'),(3,'89.235.166.0/24'),(4,'89.235.168.0/23'),(5,'89.235.174.0/23');
/*!40000 ALTER TABLE `tech_nets` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tech_ports`
--

DROP TABLE IF EXISTS `tech_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tech_ports` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `node` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `port_name` varchar(10) NOT NULL DEFAULT '',
  `port_type` enum('backbone','dedicated','pppoe','pptp','hub','adsl','wimax','cdma','adsl_cards','adsl_connect','adsl_karta','adsl_rabota','adsl_terminal','adsl_tranzit1','yota','GPON','megafon_4G') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'dedicated',
  `trafcounttype` enum('','flows','counter_smnp','counter_web') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'flows',
  `address` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8687 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tech_ports`
--

LOCK TABLES `tech_ports` WRITE;
/*!40000 ALTER TABLE `tech_ports` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tech_ports` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tech_routers`
--

DROP TABLE IF EXISTS `tech_routers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tech_routers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '4000-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `router` varchar(32) NOT NULL DEFAULT '',
  `phone` varchar(16) NOT NULL DEFAULT '',
  `location` varchar(128) NOT NULL DEFAULT '',
  `reboot_contact` varchar(100) NOT NULL DEFAULT '',
  `net` varchar(100) NOT NULL DEFAULT '',
  `adsl_modem_serial` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tech_routers`
--

LOCK TABLES `tech_routers` WRITE;
/*!40000 ALTER TABLE `tech_routers` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tech_routers` VALUES (1,'1970-01-02','4000-01-01','but12','3353723','Бутлерова ул., д. 12','but12 85.94.32.234/32 временно отключен','85.94.62.74/32',''),(2,'1970-01-02','4000-01-01','skak17','9455086','Скаковая ул., д. 17','945-50-60 Александр','85.94.50.4/30','3ge002866'),(3,'1970-01-02','4000-01-01','nam14','3310110','Наметкина ул., д.14','телефонисты:332-04-39 Михаил Сергеевич 8-916-779-01-33 Алексей','85.94.50.0/30','3ge001673');
/*!40000 ALTER TABLE `tech_routers` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tech_voip_numbers`
--

DROP TABLE IF EXISTS `tech_voip_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tech_voip_numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '0001-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `number` decimal(20,0) NOT NULL DEFAULT '0',
  `type` enum('public','provider','private') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public',
  `client` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `remark` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tech_voip_numbers`
--

LOCK TABLES `tech_voip_numbers` WRITE;
/*!40000 ALTER TABLE `tech_voip_numbers` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tech_voip_numbers` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `transaction`
--

DROP TABLE IF EXISTS `transaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account_id` int(11) NOT NULL,
  `source` enum('stat','bill','payment') NOT NULL,
  `billing_period` date DEFAULT NULL,
  `service_type` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `service_id` int(10) unsigned DEFAULT NULL,
  `package_id` int(10) unsigned DEFAULT NULL,
  `transaction_type` enum('connecting','periodical','resource') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `transaction_date` datetime NOT NULL,
  `period_from` datetime DEFAULT NULL,
  `period_to` datetime DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `price` decimal(13,4) DEFAULT NULL,
  `amount` decimal(13,6) DEFAULT NULL,
  `tax_rate` int(11) DEFAULT NULL,
  `sum` decimal(11,2) NOT NULL,
  `sum_tax` decimal(11,2) DEFAULT NULL,
  `sum_without_tax` decimal(11,2) DEFAULT NULL,
  `is_partial_write_off` tinyint(4) NOT NULL,
  `effective_amount` decimal(13,6) DEFAULT NULL,
  `effective_sum` decimal(11,2) NOT NULL,
  `payment_id` int(10) unsigned DEFAULT NULL,
  `bill_id` int(10) unsigned DEFAULT NULL,
  `bill_line_id` int(10) unsigned DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_source_payment_id` (`source`,`payment_id`),
  KEY `idx_client_account_id_source_billing_period` (`client_account_id`,`source`,`billing_period`),
  KEY `idx_client_account_id_source_transaction_date` (`client_account_id`,`source`,`transaction_date`),
  KEY `fk_transaction__bill_id` (`bill_id`),
  KEY `fk_transaction__payment_id` (`payment_id`),
  CONSTRAINT `fk_transaction__bill_id` FOREIGN KEY (`bill_id`) REFERENCES `newbills` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_transaction__payment_id` FOREIGN KEY (`payment_id`) REFERENCES `newpayments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1471169 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction`
--

LOCK TABLES `transaction` WRITE;
/*!40000 ALTER TABLE `transaction` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `transaction` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `transaction_service`
--

DROP TABLE IF EXISTS `transaction_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_service` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account_id` int(11) NOT NULL,
  `source` enum('stat','jerasoft') NOT NULL,
  `service_type` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `service_id` int(10) unsigned DEFAULT NULL,
  `service_subtype` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `period_from` datetime DEFAULT NULL,
  `period_to` datetime DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `price` decimal(13,4) DEFAULT NULL,
  `amount` decimal(13,6) NOT NULL,
  `sum` decimal(11,2) DEFAULT NULL,
  `effective_amount` decimal(13,6) DEFAULT NULL,
  `effective_sum` decimal(11,2) DEFAULT NULL,
  `transaction_date` datetime NOT NULL,
  `is_partial_write_off` tinyint(4) DEFAULT NULL,
  `is_in_bill` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_service`
--

LOCK TABLES `transaction_service` WRITE;
/*!40000 ALTER TABLE `transaction_service` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `transaction_service` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tt_doer_stages`
--

DROP TABLE IF EXISTS `tt_doer_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_doer_stages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `all4geo_id` varchar(255) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `status` varchar(50) NOT NULL DEFAULT '',
  `status_text` varchar(100) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `k_all4geo_id` (`all4geo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6520 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tt_doer_stages`
--

LOCK TABLES `tt_doer_stages` WRITE;
/*!40000 ALTER TABLE `tt_doer_stages` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tt_doer_stages` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tt_doers`
--

DROP TABLE IF EXISTS `tt_doers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_doers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stage_id` int(10) unsigned NOT NULL,
  `doer_id` int(10) unsigned NOT NULL COMMENT 'исполнитель - courier.id',
  PRIMARY KEY (`id`),
  KEY `stage_doer` (`stage_id`,`doer_id`),
  KEY `stage_id` (`stage_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48393 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tt_doers`
--

LOCK TABLES `tt_doers` WRITE;
/*!40000 ALTER TABLE `tt_doers` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tt_doers` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tt_files`
--

DROP TABLE IF EXISTS `tt_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trouble_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ts` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `comment` text NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trouble_id` (`trouble_id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tt_files`
--

LOCK TABLES `tt_files` WRITE;
/*!40000 ALTER TABLE `tt_files` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tt_files` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tt_folders`
--

DROP TABLE IF EXISTS `tt_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_folders` (
  `pk` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `order` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tt_folders`
--

LOCK TABLES `tt_folders` WRITE;
/*!40000 ALTER TABLE `tt_folders` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tt_folders` VALUES (1,'Все',0),(2,'Новый',10),(4,'Зарезервирован',30),(8,'К отгрузке',80),(16,'Отгружен',90),(32,'Доставка',70),(64,'Закрыт',130),(128,'Отказ',140),(256,'Открыт',1),(512,'Закрыт',2),(1024,'Выполнено',110),(2048,'Тр УСПД',5),(4096,'СПД',6),(8192,'Выезд',100),(16384,'коллТр',8),(32768,'массТр',9),(65536,'Отработано',10),(131072,'Тех Поддержка',11),(262144,'Выдача',12),(524288,'Подготовка',40),(1048576,'Доработка',120),(2097152,'Отложен',20),(4194304,'Подтвержден',60),(8388608,'Распределение',50),(16777216,'Активация',105),(33554432,'WiMax',15),(67108864,'Тестирование',50),(134217728,'Отложенные',60),(268435456,'OnLime Оборудование',16),(536870912,'OnLime',17),(1073741824,'MTS',18),(2147483648,'Новый',19),(4294967296,'Оплата',20),(8589934592,'Доставка',21),(17179869184,'Поступление',22),(68719476736,'Отказ',24),(34359738368,'Закрыт',23),(137438953472,'Входящие',25),(274877906944,'В стадии переговоров',26),(1099511627776,'Подключаемые',28),(2199023255552,'Техотказ',29),(4398046511104,'Отказ',30),(8796093022208,'Мусор',31),(17592186044416,'Включено',32),(35184372088832,'Проверка документов',27),(70368744177664,'Открыт повторно',11);
/*!40000 ALTER TABLE `tt_folders` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tt_send`
--

DROP TABLE IF EXISTS `tt_send`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_send` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trouble_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user` varchar(100) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12349 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tt_send`
--

LOCK TABLES `tt_send` WRITE;
/*!40000 ALTER TABLE `tt_send` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tt_send` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tt_send_count`
--

DROP TABLE IF EXISTS `tt_send_count`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_send_count` (
  `count` int(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tt_send_count`
--

LOCK TABLES `tt_send_count` WRITE;
/*!40000 ALTER TABLE `tt_send_count` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tt_send_count` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tt_stages`
--

DROP TABLE IF EXISTS `tt_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_stages` (
  `stage_id` int(11) NOT NULL AUTO_INCREMENT,
  `trouble_id` int(11) NOT NULL DEFAULT '0',
  `state_id` int(11) NOT NULL DEFAULT '0',
  `user_main` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `date_edit` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `user_edit` varchar(50) NOT NULL,
  `comment` text NOT NULL,
  `uspd` varchar(50) NOT NULL,
  `date_start` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `date_finish_desired` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `rating` int(4) NOT NULL DEFAULT '0',
  `user_rating` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`stage_id`),
  KEY `id_trouble` (`trouble_id`),
  KEY `date_start` (`date_start`),
  KEY `user_main` (`user_main`),
  CONSTRAINT `fk_tt_stages__trouble_id` FOREIGN KEY (`trouble_id`) REFERENCES `tt_troubles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=943421 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tt_stages`
--

LOCK TABLES `tt_stages` WRITE;
/*!40000 ALTER TABLE `tt_stages` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tt_stages` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tt_states`
--

DROP TABLE IF EXISTS `tt_states`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_states` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pk` bigint(20) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `order` double NOT NULL DEFAULT '0',
  `time_delta` int(11) NOT NULL DEFAULT '1',
  `folder` bigint(20) unsigned NOT NULL DEFAULT '0',
  `deny` bigint(20) unsigned NOT NULL DEFAULT '0',
  `state_1c` varchar(128) DEFAULT NULL,
  `oso` tinyint(4) NOT NULL DEFAULT '0',
  `omo` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tt_states`
--

LOCK TABLES `tt_states` WRITE;
/*!40000 ALTER TABLE `tt_states` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tt_states` VALUES (1,1,'Открыт',1,1,257,0,NULL,0,0),(2,2,'Закрыт',2,0,513,0,NULL,0,0),(3,8,'Трабл УСПД',4,1,2049,0,NULL,0,0),(4,32,'Выезд',16,2,8193,16136192,'Отгружен',7,7),(5,64,'коллекТрабл',7,1,16385,0,NULL,0,0),(6,128,'массТрабл',8,1,32769,0,NULL,0,0),(7,4,'Выполнен',3,0,1025,16136192,'Отгружен',0,8),(8,256,'Отработано',9,0,65537,0,NULL,0,0),(12,16,'СПД',5,1,4097,0,NULL,0,0),(13,512,'Тех поддержка',10,1,131073,0,NULL,0,0),(14,1024,'Выдача',11,1,262145,0,NULL,0,0),(15,2048,'Новый',12,1,3,17383460,'Новый',0,0),(16,4096,'Зарезервирован',14,1,5,17383460,'Резерв',2,2),(17,8192,'К отгрузке',15,1,9,17383460,'КОтгрузке',5,5),(18,16384,'Отгружен',16,1,17,16136192,'Отгружен',6,6),(20,65536,'Закрыт',18,1,65,0,'Закрыт',8,10),(21,131072,'Отказ',19,1,129,0,'Отказ',9,11),(22,262144,'Подготовка',14,1,524289,17383460,'Резерв',0,3),(23,524288,'Доработка',17,1,1048577,16136192,'Отгружен',0,9),(24,1048576,'Отложен',13,0,2097153,17383460,'Новый',1,1),(25,2097152,'Доставка',0,0,33,17383460,'Резерв',4,0),(26,4194304,'Подтвержден',0,0,4194305,17383460,'Резерв',3,0),(27,8388608,'Распределение',0,0,8388609,17383460,'Резерв',0,4),(28,16777216,'Активация',0,1,16777217,16136192,'Отгружен',7,0),(29,33554432,'WiMax',0,1,33554433,17383460,'Новый',2,0),(30,67108864,'Тестирование',0,1,67108865,0,NULL,0,0),(31,134217728,'Отложенные',0,1,134217729,0,NULL,0,0),(32,268435456,'OnLime Оборудование',0,1,268435457,17383460,'КОтгрузке',2,0),(33,536870912,'OnLime',0,1,536870913,17383460,'Новый',2,0),(34,1073741824,'MTS',0,1,1073741825,17383460,'Резерв',2,0),(35,2147483648,'Новый',0,1,2147483648,0,'Согласован',0,0),(36,4294967296,'Оплата',0,1,4294967296,0,'Согласован',0,0),(37,8589934592,'Доставка',0,1,8589934592,0,'Подтвержден',0,0),(38,17179869184,'Поступление',0,1,17179869184,0,'К поступлению',0,0),(39,34359738368,'Закрыт',0,1,34359738368,100931731456,'Закрыт',0,0),(40,68719476736,'Отказ',0,1,100931731456,0,'Не согласован',0,0),(41,137438953472,'Входящие',1,1,137438953473,7696581394432,NULL,0,0),(42,274877906944,'В стадии переговоров',2,1,274877906945,29274497089536,NULL,0,0),(44,1099511627776,'Подключаемые',4,1,1099511627777,44942537785344,NULL,0,0),(45,2199023255552,'Техотказ',6,1,2199023255553,67482526154752,NULL,0,0),(46,4398046511104,'Отказ',7,1,4398046511105,65558380806144,NULL,0,0),(47,8796093022208,'Мусор',8,1,8796093022209,61297773248512,NULL,0,0),(48,17592186044416,'Включено',5,1,17592186044417,52639119179776,NULL,0,0),(49,35184372088832,'Проверка документов',3,1,35184372088833,29274497089536,NULL,0,0),(50,70368744177664,'Открыт повторно',10,1,70368744177664,0,NULL,0,0);
/*!40000 ALTER TABLE `tt_states` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tt_states_o`
--

DROP TABLE IF EXISTS `tt_states_o`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_states_o` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `order` int(11) NOT NULL DEFAULT '0',
  `time_delta` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `order` (`order`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tt_states_o`
--

LOCK TABLES `tt_states_o` WRITE;
/*!40000 ALTER TABLE `tt_states_o` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tt_states_o` VALUES (1,'открыт',0,1),(2,'закрыт',1,0),(3,'трабл УСПД',3,1),(4,'на выезде',4,2),(5,'коллекТрабл',5,1),(6,'массТрабл',6,1),(7,'выполнен',1,0),(8,'отработано',8,0),(12,'СПД',3,1),(13,'Тех поддержка',9,1),(14,'Выдача',10,1);
/*!40000 ALTER TABLE `tt_states_o` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Temporary view structure for view `tt_states_rb`
--

DROP TABLE IF EXISTS `tt_states_rb`;
/*!50001 DROP VIEW IF EXISTS `tt_states_rb`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `tt_states_rb` AS SELECT 
 1 AS `id`,
 1 AS `pk`,
 1 AS `name`,
 1 AS `order`,
 1 AS `time_delta`,
 1 AS `folder`,
 1 AS `deny`,
 1 AS `state_1c`,
 1 AS `oso`,
 1 AS `omo`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `tt_troubles`
--

DROP TABLE IF EXISTS `tt_troubles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_troubles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trouble_type` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'trouble',
  `client` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `user_author` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `problem` text NOT NULL,
  `service` varchar(20) NOT NULL,
  `service_id` int(11) NOT NULL,
  `cur_stage_id` int(11) NOT NULL DEFAULT '0',
  `is_important` int(1) DEFAULT '0',
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `bill_id` varchar(36) NOT NULL DEFAULT '',
  `folder` bigint(20) unsigned DEFAULT '1',
  `doer_comment` text NOT NULL,
  `all4geo_id` int(4) NOT NULL DEFAULT '0',
  `trouble_subtype` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `date_close` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `support_ticket_id` int(11) DEFAULT NULL,
  `server_id` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `service_id` (`service_id`),
  KEY `type_folder` (`trouble_type`,`folder`),
  KEY `bill_no` (`bill_no`),
  KEY `date_creation` (`date_creation`),
  KEY `bill_id` (`bill_id`),
  KEY `support_ticket_id` (`support_ticket_id`) USING BTREE,
  KEY `server_id` (`server_id`)
) ENGINE=InnoDB AUTO_INCREMENT=219452 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tt_troubles`
--

LOCK TABLES `tt_troubles` WRITE;
/*!40000 ALTER TABLE `tt_troubles` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `tt_troubles` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tt_types`
--

DROP TABLE IF EXISTS `tt_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_types` (
  `pk` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `folders` bigint(20) unsigned DEFAULT NULL,
  `states` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`pk`),
  KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tt_types`
--

LOCK TABLES `tt_types` WRITE;
/*!40000 ALTER TABLE `tt_types` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tt_types` VALUES (1,'trouble','Тех. поддержка MCN',70368744701697,70368744310783),(2,'task','Задания MCN',524033,133119),(3,'support_welltime','Тех. поддержка Welltime',524033,133119),(4,'shop_orders','Заказы Магазина',1935679743,1936947232),(5,'mounting_orders','Заказы Установка и Монтаж',12068063,10450980),(6,'order_welltime','Заказы Welltime',202907850,202319904),(7,'incomegoods','Заказы поставщикам',135291469824,135291469824),(8,'connect','Подключение',70231305224193,70231305224192);
/*!40000 ALTER TABLE `tt_types` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_call_chat`
--

DROP TABLE IF EXISTS `usage_call_chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_call_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(100) NOT NULL DEFAULT '',
  `activation_dt` datetime DEFAULT NULL,
  `expire_dt` datetime DEFAULT NULL,
  `actual_from` date NOT NULL DEFAULT '4000-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `status` enum('connecting','working') NOT NULL DEFAULT 'working',
  `tarif_id` int(11) NOT NULL DEFAULT '0',
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_call_chat`
--

LOCK TABLES `usage_call_chat` WRITE;
/*!40000 ALTER TABLE `usage_call_chat` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_call_chat` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_callback_sess`
--

DROP TABLE IF EXISTS `usage_callback_sess`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_callback_sess` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `sess_id_from` int(11) NOT NULL,
  `sess_id_to` int(11) NOT NULL,
  `ts` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`,`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_callback_sess`
--

LOCK TABLES `usage_callback_sess` WRITE;
/*!40000 ALTER TABLE `usage_callback_sess` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_callback_sess` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_extra`
--

DROP TABLE IF EXISTS `usage_extra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_extra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `activation_dt` datetime DEFAULT NULL,
  `expire_dt` datetime DEFAULT NULL,
  `actual_from` date NOT NULL DEFAULT '2029-01-01',
  `actual_to` date NOT NULL DEFAULT '2029-01-01',
  `param_value` varchar(100) NOT NULL DEFAULT '',
  `amount` decimal(16,5) NOT NULL DEFAULT '1.00000',
  `status` enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `tarif_id` int(11) NOT NULL DEFAULT '0',
  `code` varchar(20) NOT NULL DEFAULT '',
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB AUTO_INCREMENT=3821 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_extra`
--

LOCK TABLES `usage_extra` WRITE;
/*!40000 ALTER TABLE `usage_extra` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_extra` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_ip_ports`
--

DROP TABLE IF EXISTS `usage_ip_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_ip_ports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `activation_dt` datetime DEFAULT NULL,
  `expire_dt` datetime DEFAULT NULL,
  `actual_from` date NOT NULL DEFAULT '4000-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `address` varchar(255) NOT NULL DEFAULT '',
  `port_id` int(11) DEFAULT NULL,
  `date_last_writeoff` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `status` enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working',
  `speed_mgts` varchar(32) NOT NULL DEFAULT '',
  `speed_update` datetime NOT NULL DEFAULT '1970-01-02 00:00:00',
  `amount` int(4) NOT NULL DEFAULT '1',
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB AUTO_INCREMENT=7933 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_ip_ports`
--

LOCK TABLES `usage_ip_ports` WRITE;
/*!40000 ALTER TABLE `usage_ip_ports` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_ip_ports` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_ip_ppp`
--

DROP TABLE IF EXISTS `usage_ip_ppp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_ip_ppp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '4000-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `port_id` int(11) NOT NULL DEFAULT '0',
  `login` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `password` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `user_editable` tinyint(1) NOT NULL DEFAULT '1',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `client` varchar(100) CHARACTER SET koi8r COLLATE koi8r_bin NOT NULL DEFAULT '',
  `activation_dt` datetime DEFAULT NULL,
  `expire_dt` datetime DEFAULT NULL,
  `ip` varchar(15) NOT NULL DEFAULT '',
  `nat_to_ip` varchar(15) NOT NULL DEFAULT '',
  `enabled_local_ports` varchar(100) NOT NULL DEFAULT '',
  `enabled_remote_ports` varchar(100) NOT NULL DEFAULT '*',
  `mtu` int(11) NOT NULL DEFAULT '0',
  `send_nispd_vsa` tinyint(1) NOT NULL DEFAULT '1',
  `limit_kbps_in` int(11) NOT NULL DEFAULT '0',
  `limit_kbps_out` int(11) NOT NULL DEFAULT '0',
  `day_quota_in` int(11) NOT NULL DEFAULT '0',
  `day_quota_in_used` int(11) NOT NULL DEFAULT '0',
  `day_quota_out` int(11) NOT NULL DEFAULT '0',
  `day_quota_out_used` int(11) NOT NULL DEFAULT '0',
  `month_quota_in` int(11) NOT NULL DEFAULT '0',
  `month_quota_in_used` int(11) NOT NULL DEFAULT '0',
  `month_quota_out` int(11) NOT NULL DEFAULT '0',
  `month_quota_out_used` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `k_login` (`login`),
  KEY `client` (`client`)
) ENGINE=InnoDB AUTO_INCREMENT=1451 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_ip_ppp`
--

LOCK TABLES `usage_ip_ppp` WRITE;
/*!40000 ALTER TABLE `usage_ip_ppp` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_ip_ppp` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_ip_routes`
--

DROP TABLE IF EXISTS `usage_ip_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_ip_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activation_dt` datetime DEFAULT NULL,
  `expire_dt` datetime DEFAULT NULL,
  `actual_from` date NOT NULL DEFAULT '4000-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `port_id` int(11) NOT NULL DEFAULT '0',
  `net` varchar(18) NOT NULL DEFAULT '',
  `nat_net` varchar(18) NOT NULL DEFAULT '',
  `dnat` varchar(18) NOT NULL DEFAULT '',
  `type` enum('unused','uplink','uplink+pool','client','client-nat','pool','aggregate','reserved','gpon') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'unused',
  `up_node` varchar(32) NOT NULL DEFAULT '',
  `flows_node` varchar(32) NOT NULL DEFAULT 'rubicon',
  `comment` varchar(255) DEFAULT NULL,
  `gpon_reserv` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `net` (`net`),
  KEY `port_id` (`port_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7896 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_ip_routes`
--

LOCK TABLES `usage_ip_routes` WRITE;
/*!40000 ALTER TABLE `usage_ip_routes` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_ip_routes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_sms`
--

DROP TABLE IF EXISTS `usage_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `activation_dt` datetime DEFAULT NULL,
  `expire_dt` datetime DEFAULT NULL,
  `actual_from` date NOT NULL DEFAULT '2029-01-01',
  `actual_to` date NOT NULL DEFAULT '2029-01-01',
  `status` enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `tarif_id` int(11) NOT NULL DEFAULT '0',
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_sms`
--

LOCK TABLES `usage_sms` WRITE;
/*!40000 ALTER TABLE `usage_sms` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_sms` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Temporary view structure for view `usage_sms_gate`
--

DROP TABLE IF EXISTS `usage_sms_gate`;
/*!50001 DROP VIEW IF EXISTS `usage_sms_gate`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `usage_sms_gate` AS SELECT 
 1 AS `usage_id`,
 1 AS `client`,
 1 AS `client_id`,
 1 AS `password`,
 1 AS `salt`,
 1 AS `actual_from`,
 1 AS `actual_to`,
 1 AS `sms_max`,
 1 AS `status`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `usage_tech_cpe`
--

DROP TABLE IF EXISTS `usage_tech_cpe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_tech_cpe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '4000-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `id_model` int(11) NOT NULL DEFAULT '0',
  `client` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `serial` varchar(32) NOT NULL DEFAULT '',
  `mac` varchar(12) NOT NULL DEFAULT '',
  `ip` varchar(100) NOT NULL DEFAULT '',
  `ip_nat` varchar(15) NOT NULL DEFAULT '',
  `ip_cidr` varchar(100) NOT NULL DEFAULT '',
  `ip_gw` varchar(100) NOT NULL DEFAULT '',
  `admin_login` varchar(100) NOT NULL DEFAULT '',
  `admin_pass` varchar(100) NOT NULL DEFAULT '',
  `numbers` varchar(100) NOT NULL DEFAULT '',
  `logins` varchar(100) NOT NULL DEFAULT '',
  `owner` enum('','mcn','client','mgts') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `tech_support` enum('','mcn','client','mgts') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `node` varchar(100) NOT NULL DEFAULT '',
  `service` varchar(30) NOT NULL DEFAULT '',
  `id_service` int(11) NOT NULL DEFAULT '0',
  `deposit_sumUSD` decimal(7,2) NOT NULL DEFAULT '0.00',
  `deposit_sumRUB` decimal(7,2) NOT NULL DEFAULT '0.00',
  `snmp` tinyint(1) NOT NULL DEFAULT '0',
  `ast_autoconf` tinyint(1) NOT NULL DEFAULT '0',
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_model` (`id_model`),
  KEY `service` (`service`,`id_service`),
  KEY `client` (`client`)
) ENGINE=InnoDB AUTO_INCREMENT=17334 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_tech_cpe`
--

LOCK TABLES `usage_tech_cpe` WRITE;
/*!40000 ALTER TABLE `usage_tech_cpe` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_tech_cpe` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_trunk`
--

DROP TABLE IF EXISTS `usage_trunk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_trunk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_account_id` int(11) NOT NULL,
  `connection_point_id` int(11) NOT NULL,
  `trunk_id` int(11) NOT NULL,
  `actual_from` date NOT NULL DEFAULT '4000-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `activation_dt` datetime NOT NULL,
  `expire_dt` datetime NOT NULL,
  `status` enum('connecting','working') NOT NULL DEFAULT 'working',
  `orig_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `term_enabled` tinyint(4) NOT NULL DEFAULT '0',
  `orig_min_payment` int(11) NOT NULL DEFAULT '0',
  `term_min_payment` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `tmp` int(11) DEFAULT '0',
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `usage_trunk__connection_point_id_trunk_name` (`connection_point_id`) USING BTREE,
  KEY `usage_trunk__client_account_id` (`client_account_id`) USING BTREE,
  CONSTRAINT `usage_trunk__client_account_id` FOREIGN KEY (`client_account_id`) REFERENCES `clients` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `usage_trunk__connection_point_id` FOREIGN KEY (`connection_point_id`) REFERENCES `regions` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_trunk`
--

LOCK TABLES `usage_trunk` WRITE;
/*!40000 ALTER TABLE `usage_trunk` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_trunk` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_trunk_settings`
--

DROP TABLE IF EXISTS `usage_trunk_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_trunk_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usage_id` int(11) NOT NULL,
  `type` smallint(6) NOT NULL,
  `order` smallint(6) NOT NULL,
  `src_number_id` int(11) DEFAULT NULL,
  `dst_number_id` int(11) DEFAULT NULL,
  `pricelist_id` int(11) DEFAULT NULL,
  `tmp` int(11) DEFAULT '0',
  `minimum_margin` decimal(10,5) NOT NULL DEFAULT '0.00000',
  `minimum_margin_type` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `usage_id_type_order` (`usage_id`,`type`,`order`),
  CONSTRAINT `usage_trunk_settings__usag_id` FOREIGN KEY (`usage_id`) REFERENCES `usage_trunk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_trunk_settings`
--

LOCK TABLES `usage_trunk_settings` WRITE;
/*!40000 ALTER TABLE `usage_trunk_settings` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_trunk_settings` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_virtpbx`
--

DROP TABLE IF EXISTS `usage_virtpbx`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_virtpbx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `region` smallint(6) NOT NULL,
  `activation_dt` datetime DEFAULT NULL,
  `expire_dt` datetime DEFAULT NULL,
  `actual_from` date NOT NULL DEFAULT '2029-01-01',
  `actual_to` date NOT NULL DEFAULT '2029-01-01',
  `amount` decimal(16,5) NOT NULL DEFAULT '1.00000',
  `status` enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `tarif_id` int(11) NOT NULL DEFAULT '0',
  `moved_from` int(11) NOT NULL,
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB AUTO_INCREMENT=3571 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_virtpbx`
--

LOCK TABLES `usage_virtpbx` WRITE;
/*!40000 ALTER TABLE `usage_virtpbx` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_virtpbx` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_voip`
--

DROP TABLE IF EXISTS `usage_voip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_voip` (
  `tarif` varchar(100) CHARACTER SET koi8r NOT NULL DEFAULT '',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `region` smallint(6) NOT NULL,
  `actual_from` date NOT NULL DEFAULT '4000-01-01',
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `client` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `type_id` enum('number','line','7800','operator') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `activation_dt` datetime NOT NULL,
  `expire_dt` datetime NOT NULL,
  `E164` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `no_of_lines` int(11) NOT NULL DEFAULT '1',
  `status` enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working',
  `address` text NOT NULL,
  `address_from_datacenter_id` int(11) DEFAULT NULL,
  `edit_user_id` int(11) DEFAULT NULL,
  `is_trunk` enum('0','1') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `one_sip` tinyint(4) NOT NULL DEFAULT '0',
  `line7800_id` int(11) NOT NULL DEFAULT '0',
  `create_params` varchar(1024) NOT NULL,
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `E164` (`E164`),
  KEY `client` (`client`),
  KEY `fk_usage_voip__address_from_datacenter_id` (`address_from_datacenter_id`),
  KEY `line7800_id` (`line7800_id`),
  KEY `idx-activation_dt` (`activation_dt`),
  KEY `idx-expire_dt` (`expire_dt`),
  CONSTRAINT `fk_usage_voip__address_from_datacenter_id` FOREIGN KEY (`address_from_datacenter_id`) REFERENCES `datacenter` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14344 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_voip`
--

LOCK TABLES `usage_voip` WRITE;
/*!40000 ALTER TABLE `usage_voip` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_voip` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_voip_package`
--

DROP TABLE IF EXISTS `usage_voip_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_voip_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(100) NOT NULL DEFAULT '0',
  `activation_dt` datetime NOT NULL,
  `expire_dt` datetime NOT NULL DEFAULT '4000-01-01 23:59:59',
  `actual_from` date NOT NULL,
  `actual_to` date NOT NULL DEFAULT '4000-01-01',
  `tariff_id` int(11) NOT NULL DEFAULT '0',
  `usage_voip_id` int(11) NOT NULL DEFAULT '0',
  `status` enum('connecting','working') NOT NULL DEFAULT 'working',
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_voip_package`
--

LOCK TABLES `usage_voip_package` WRITE;
/*!40000 ALTER TABLE `usage_voip_package` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_voip_package` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `usage_welltime`
--

DROP TABLE IF EXISTS `usage_welltime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_welltime` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `activation_dt` datetime DEFAULT NULL,
  `expire_dt` datetime DEFAULT NULL,
  `actual_from` date NOT NULL DEFAULT '2029-01-01',
  `actual_to` date NOT NULL DEFAULT '2029-01-01',
  `ip` varchar(100) NOT NULL DEFAULT '',
  `amount` decimal(16,5) NOT NULL DEFAULT '1.00000',
  `status` enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `tarif_id` int(11) NOT NULL DEFAULT '0',
  `router` varchar(255) NOT NULL DEFAULT '',
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB AUTO_INCREMENT=3166 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usage_welltime`
--

LOCK TABLES `usage_welltime` WRITE;
/*!40000 ALTER TABLE `usage_welltime` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `usage_welltime` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_departs`
--

DROP TABLE IF EXISTS `user_departs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_departs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_departs`
--

LOCK TABLES `user_departs` WRITE;
/*!40000 ALTER TABLE `user_departs` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `user_departs` VALUES (21,'mcn'),(22,'welltime'),(23,'wellsystems'),(24,'compapa'),(25,'all4net'),(26,'it.park'),(27,'mcntelecom'),(28,'Sales'),(29,'Zakupki');
/*!40000 ALTER TABLE `user_departs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_grant_groups`
--

DROP TABLE IF EXISTS `user_grant_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_grant_groups` (
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `resource` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `access` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`name`,`resource`),
  KEY `fk_user_grant_groups_resource` (`resource`),
  CONSTRAINT `fk_user_grant_groups_resource` FOREIGN KEY (`resource`) REFERENCES `user_rights` (`resource`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_grant_groups`
--

LOCK TABLES `user_grant_groups` WRITE;
/*!40000 ALTER TABLE `user_grant_groups` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `user_grant_groups` VALUES ('account_managers','clients','read,read_filter,read_all,new,edit,restatus,file,all4net,client_type_change'),('account_managers','data','access'),('account_managers','logs','read'),('account_managers','mail','r,w'),('account_managers','monitoring','view,edit'),('account_managers','newaccounts_balance','read'),('account_managers','newaccounts_bills','read,edit,delete'),('account_managers','newaccounts_mass','access'),('account_managers','newaccounts_payments','read,edit,delete'),('account_managers','newaccounts_usd','access'),('account_managers','routers_devices','r,edit,add,delete'),('account_managers','routers_models','r'),('account_managers','routers_nets','r'),('account_managers','routers_routers','r'),('account_managers','services_additional','r,edit,addnew,activate,close'),('account_managers','services_collocation','r,edit,addnew,activate,close'),('account_managers','services_domains','r,edit,addnew,close'),('account_managers','services_internet','r,edit,addnew,activate,close,edit_off,tarif'),('account_managers','services_mail','r,edit,addnew,activate,chpass'),('account_managers','services_ppp','r,edit,addnew,full,activate,chpass,close'),('account_managers','services_voip','r,edit,addnew,activate,close,send_settings,e164,del4000'),('account_managers','services_welltime','full,docs'),('account_managers','stats','r,report,vip_report,voip_recognition,onlime_read,onlime_create,onlime_full'),('account_managers','tarifs','read,edit'),('account_managers','tt','view,view_cl,use,time,admin,doers_edit,shop_orders'),('account_managers','usercontrol','edit_pass,edit_full,edit_panels,edit_flags'),('account_managers','users','r'),('account_managers','voip','access,catalog'),('accounts_department','clients','read,read_filter,read_all,new,edit,file,all4net'),('accounts_department','data','access'),('accounts_department','newaccounts_balance','read'),('accounts_department','newaccounts_bills','read,edit'),('accounts_department','newaccounts_mass','access'),('accounts_department','newaccounts_payments','read,edit'),('accounts_department','newaccounts_usd','access'),('accounts_department','routers_devices','r,edit,add,delete'),('accounts_department','routers_models','r'),('accounts_department','routers_nets','r'),('accounts_department','services_additional','r'),('accounts_department','services_collocation','r'),('accounts_department','services_internet','r'),('accounts_department','services_mail','r'),('accounts_department','services_voip','r'),('accounts_department','stats','r'),('accounts_department','tarifs','read'),('accounts_department','tt','view,view_cl,use,time'),('accounts_department','usercontrol','edit_pass'),('admin','ats','access'),('admin','clients','read,read_filter,read_all,new,edit,restatus,sale_channels,file,inn_double,all4net,client_type_change,changeback_contract_state'),('admin','data','access'),('admin','employeers','r'),('admin','incomegoods','access,admin'),('admin','logs','read'),('admin','mail','r,w'),('admin','monitoring','view,edit'),('admin','newaccounts_balance','read'),('admin','newaccounts_bills','read,edit,delete,del_docs,edit_ext'),('admin','newaccounts_mass','access'),('admin','newaccounts_payments','read,edit,delete'),('admin','newaccounts_usd','access'),('admin','organization','read,edit'),('admin','person','read,edit,delete'),('admin','routers_devices','r,edit,add,delete'),('admin','routers_models','r,w'),('admin','routers_nets','r'),('admin','routers_routers','r,edit,add,delete'),('admin','send','r'),('admin','services_additional','r,edit,addnew,full,activate,close'),('admin','services_collocation','r,edit,addnew,activate,close'),('admin','services_domains','r,edit,addnew,close'),('admin','services_internet','r,edit,addnew,activate,close,full,edit_off,tarif'),('admin','services_itpark','full'),('admin','services_mail','r,edit,addnew,full,activate,chpass,whitelist'),('admin','services_ppp','r,edit,addnew,full,activate,chpass'),('admin','services_voip','r,edit,addnew,full,activate,close,view_reg,view_regpass,send_settings,e164,del4000'),('admin','services_wellsystem','full'),('admin','services_welltime','full,docs'),('admin','stats','r,report,voip_recognition,onlime_read,onlime_create,onlime_full'),('admin','tarifs','read,edit'),('admin','tt','view,use,admin,states,report,rating,limit'),('admin','usercontrol','edit_pass,edit_full,edit_panels,edit_flags,dealer'),('admin','users','r,change,grant'),('admin','voip','access,admin,catalog'),('admin','voipreports','access,admin'),('client','mail','r'),('client','services_additional','r'),('client','services_mail','whitelist,r,addnew,activate,chpass'),('client','services_ppp','r,chpass'),('client','stats','r'),('client','usercontrol','r,edit_pass'),('developer','data','access'),('manager','clients','read,read_filter,read_all,new,edit,restatus,file,all4net'),('manager','data','access'),('manager','mail','w'),('manager','monitoring','view,edit'),('manager','newaccounts_balance','read'),('manager','newaccounts_bills','read,edit'),('manager','newaccounts_payments','read'),('manager','newaccounts_usd','access'),('manager','routers_devices','r,edit,add,delete'),('manager','routers_models','r'),('manager','routers_routers','r'),('manager','services_additional','r,edit,addnew,activate,close'),('manager','services_collocation','r,edit,addnew,activate,close'),('manager','services_domains','r'),('manager','services_internet','r,edit,addnew,activate,close,edit_off,tarif'),('manager','services_mail','r,edit,addnew,activate,chpass'),('manager','services_ppp','r,edit,addnew,full,activate,chpass,close'),('manager','services_voip','r,edit,addnew,activate,close,e164'),('manager','stats','r,report,voip_recognition,onlime_read,onlime_create,onlime_full'),('manager','tarifs','read'),('manager','tt','view,view_cl,use,time,doers_edit,shop_orders'),('manager','usercontrol','edit_pass,edit_full,edit_panels,edit_flags'),('manager','users','r'),('manager1','clients','read,read_all'),('manager1','employeers','r'),('manager1','monitoring','view'),('manager1','usercontrol','edit_panels,edit_flags'),('marketing','clients','read,read_filter,all4net'),('marketing','data','access'),('marketing','newaccounts_bills','read'),('marketing','tt','view,view_cl,use'),('operator','clients','read,read_all,new,all4net'),('operator','data','access'),('operator','newaccounts_balance','read'),('operator','newaccounts_bills','read'),('operator','tt','view,view_cl,use'),('sklad','data','access'),('sklad','newaccounts_bills','read'),('sklad','tt','view,use,report,comment'),('sklad','usercontrol','read,edit_pass'),('support','clients','read,read_filter,read_all,new,edit,restatus,file,all4net'),('support','data','access'),('support','monitoring','view,top,edit'),('support','newaccounts_balance','read'),('support','newaccounts_bills','read'),('support','routers_devices','r,edit,add,delete'),('support','routers_models','r,w'),('support','routers_nets','r'),('support','routers_routers','r,edit,add,delete'),('support','services_additional','r,edit,addnew,full,activate,close'),('support','services_collocation','r,edit,addnew,activate,close'),('support','services_domains','r,edit,addnew,close'),('support','services_internet','r,edit,addnew,activate,close,full,edit_off'),('support','services_mail','r,edit,addnew,full,activate,chpass,whitelist'),('support','services_ppp','r,edit,addnew,full,activate,chpass'),('support','services_voip','r,view_reg,send_settings'),('support','stats','r,report,onlime_read,onlime_create,onlime_full'),('support','tarifs','read'),('support','tt','view,use,states,comment,rating'),('support','usercontrol','edit_pass,edit_full,edit_flags'),('telemarket','clients','read,read_filter,read_all,new,edit,restatus,edit_tele'),('telemarketing','clients','read,read_filter,read_all,edit,restatus,edit_tele,file'),('telemarketing','data','access'),('telemarketing','monitoring','view'),('telemarketing','newaccounts_balance','read'),('telemarketing','newaccounts_bills','read'),('telemarketing','stats','r'),('telemarketing','tarifs','read'),('telemarketing','tt','view,view_cl,use,time'),('telemarketing','usercontrol','edit_pass,edit_full,edit_panels,edit_flags'),('user','data','access'),('user','tt','view,use,time,states,report'),('virtual','data','access'),('zakupshiki','data','access');
/*!40000 ALTER TABLE `user_grant_groups` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_grant_users`
--

DROP TABLE IF EXISTS `user_grant_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_grant_users` (
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `resource` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `access` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`name`,`resource`),
  KEY `fk_user_grant_users_resource` (`resource`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_grant_users`
--

LOCK TABLES `user_grant_users` WRITE;
/*!40000 ALTER TABLE `user_grant_users` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `user_grant_users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_groups`
--

DROP TABLE IF EXISTS `user_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_groups` (
  `usergroup` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `comment` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`usergroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_groups`
--

LOCK TABLES `user_groups` WRITE;
/*!40000 ALTER TABLE `user_groups` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `user_groups` VALUES ('account_managers','Аккаунт-менеджеры'),('accounts_department','Бухгалтерия'),('admin','Администраторы'),('base','Базовая'),('client','Клиенты'),('developer','Разработчик'),('manager','Менеджер'),('marketing','маркетинг'),('operator','Операторы'),('sklad','Склад'),('support','Тех. поддержка'),('telemarketing','Телемаркетинг'),('user','Менеджеры с Шухова / пользователи'),('virtual','Виртуальные пользователи'),('zakupshiki','Отдел закупки');
/*!40000 ALTER TABLE `user_groups` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_rights`
--

DROP TABLE IF EXISTS `user_rights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_rights` (
  `resource` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  `values` varchar(255) NOT NULL DEFAULT '',
  `values_desc` text NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`resource`),
  KEY `order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_rights`
--

LOCK TABLES `user_rights` WRITE;
/*!40000 ALTER TABLE `user_rights` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `user_rights` VALUES ('ats','Управление ATC','access,support','доступ,ограниченный доступ',0),('clients','Работа с клиентами','read,read_filter,read_all,new,edit,restatus,edit_tele,sale_channels,file,inn_double,all4net,history_edit,client_type_change,changeback_contract_state','просмотр вообще,просмотр с фильтрами,просмотр всех,создание,редактирование,изменение статуса,редактирование для телемаркетинга,редактирование каналов продаж,доступ к файлам,заведение совпадающих ИНН,синхронизация с all4net,редактирование истории,Изменение тип договора,Изменение статуса проверки документов на \"не проверено\"',0),('data','Данные справочников','access','доступ',0),('employeers','Сотрудники','r','чтение',0),('incomegoods','Закупки','access,admin','доступ,администрирование',0),('logs','Логи','read','просмотр',0),('mail','Письма клиентам','r,w','просмотр PM,работа с рассылкой',0),('monitoring','Просмотр данных мониторинга','view,top,edit,graphs','просмотр,панелька сверху,редактирование списка VIP-клиентов,просмотр графиков динамики',0),('newaccounts_balance','Баланс','read','просмотр',0),('newaccounts_bills','Счета','read,edit,delete,admin,del_docs,edit_ext','просмотр,изменение,удаление,изменение счета в любое время,Удаление отсканированных актов,Редактирование номера внешнего счета',0),('newaccounts_mass','Массовые операции','access','доступ',0),('newaccounts_payments','Платежи','read,edit,delete','просмотр,изменение,удаление',0),('newaccounts_usd','Курс доллара','access','доступ',0),('organization','Организации','read,edit','чтение,изменение',0),('person','Ответственные лица','read,edit,delete','чтение,изменение,удаление',0),('routers_devices','Клиентские устройства','r,edit,add,delete','чтение,редактирование,добавление,удаление',0),('routers_models','Модели клиентских устройств','r,w','чтение,редактирование',0),('routers_nets','Сети','r','доступ',0),('routers_routers','Роутеры','r,edit,add,delete','чтение,редактирование,добавление,удаление',0),('send','Массовая отправка счетов','r,send','просмотр состояния,отправка',0),('services_additional','Дополнительные услуги','r,edit,addnew,full,activate,close','просмотр,редактирование,добавление,доступ ко всем полям,активирование,отключение',0),('services_collocation','Collocation','r,edit,addnew,activate,close','просмотр,редактирование,добавление,активирование,отключение',0),('services_domains','Доменные имена','r,edit,addnew,close','просмотр,редактирование,добавление,отключение',0),('services_internet','Интернет','r,edit,addnew,activate,close,full,edit_off,tarif','просмотр,изменение,добавление,активирование,отключение,полная информация по сетям (общее с collocation),редактирование отключенных сетей (общее с collocation),изменение тарифа (общее с collocation)',0),('services_itpark','Услуги IT Park\'а','full','полный доступ',0),('services_mail','E-mail','r,edit,addnew,full,activate,chpass,whitelist','просмотр,редактирование,добавление,доступ ко всем полям,активирование,смена пароля,белый список',0),('services_ppp','PPP-логины','r,edit,addnew,full,activate,chpass,close','просмотр,редактирование,добавление,доступ ко всем полям,активирование,смена пароля,отключение',0),('services_voip','IP Телефония','r,edit,addnew,full,activate,close,view_reg,view_regpass,send_settings,e164,del4000','просмотр,редактирование,добавление,доступ ко всем полям,активирование,отключение,просмотр регистрации,отображение пароля,выслать настройки,номерные емкости,удалять невключенные номера',0),('services_wellsystem','WellSystem','full','полный доступ',0),('services_welltime','WellTime','full,docs','полный доступ,документы',0),('stats','Статистика','r,report,vip_report,voip_recognition,sale_channel_report,onlime_read,onlime_create,onlime_full','просмотр,отчет,vip report,телефония-нераспознаные,региональные представители,onlime просмотр отчета,onlime создание заявок,onlime полный доступ',0),('tarifs','Работа с тарифами','read,edit','чтение,изменение',0),('tt','Работа с заявками','view,view_cl,use,time,admin,states,report,doers_edit,shop_orders,comment,rating,limit','просмотр,показывать \"Запросы клиентов\",использование,управление временем,администраторский доступ,редактирование состояний,отчёт,редактирование исполнителей,заказы магазина,коментарии для не своих заявок,оценка заявки,просмотр остатков',0),('usercontrol','О пользователе','read,edit_pass,edit_full,edit_panels,edit_flags,dealer','чтение,смена пароля,изменение всех данных,настройка скрытых/открытых панелей (sys),настройка флагов (sys),дилерский список',0),('users','Работа с пользователями','r,change,grant','чтение,изменение,раздача прав',0),('voip','Телефония','access,admin,catalog','доступ,администрирование,справочники',0),('voipreports','Телефония Отчеты','access,admin','доступ,администрирование',0);
/*!40000 ALTER TABLE `user_rights` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_users`
--

DROP TABLE IF EXISTS `user_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `pass` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `usergroup` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'client',
  `name` varchar(100) NOT NULL DEFAULT '',
  `color` varchar(7) NOT NULL DEFAULT '',
  `trouble_redirect` varchar(50) DEFAULT NULL,
  `email` varchar(64) DEFAULT NULL,
  `icq` varchar(20) DEFAULT NULL,
  `photo` varchar(4) DEFAULT NULL,
  `data_panel` text,
  `phone_work` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `phone_mobile` varchar(100) DEFAULT NULL,
  `data_flags` text NOT NULL,
  `depart_id` int(10) unsigned DEFAULT NULL,
  `enabled` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  `courier_id` int(4) NOT NULL DEFAULT '0',
  `show_troubles_on_every_page` tinyint(4) NOT NULL DEFAULT '0',
  `restriction_client_id` int(11) DEFAULT NULL,
  `timezone_name` varchar(50) NOT NULL DEFAULT 'Europe/Moscow',
  `language` varchar(5) NOT NULL DEFAULT 'ru-RU',
  `city_id` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_users__city_id` (`city_id`),
  KEY `fk_user_users__user_group` (`usergroup`),
  KEY `fk_user_users__user_department` (`depart_id`),
  CONSTRAINT `fk_user_users__city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_user_users__user_department` FOREIGN KEY (`depart_id`) REFERENCES `user_departs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_user_users__user_group` FOREIGN KEY (`usergroup`) REFERENCES `user_groups` (`usergroup`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=219 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_users`
--

LOCK TABLES `user_users` WRITE;
/*!40000 ALTER TABLE `user_users` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `user_users` VALUES (1,'admin','698d51a19d8a121ce581499d7b701668','admin','Администратор стата','','','','',NULL,'a:24:{s:11:\"newaccounts\";i:1;s:7:\"modules\";i:0;s:10:\"monitoring\";i:1;s:7:\"routers\";i:1;s:5:\"users\";i:1;s:7:\"clients\";i:1;s:8:\"services\";i:1;s:2:\"tt\";i:1;s:10:\"employeers\";i:0;s:5:\"stats\";i:1;s:4:\"mail\";i:1;s:6:\"tarifs\";i:1;s:5:\"phone\";i:0;s:3:\"pay\";i:0;s:4:\"voip\";i:0;s:11:\"usercontrol\";i:0;s:14:\"clientaccounts\";i:0;s:3:\"ats\";i:1;s:11:\"incomegoods\";i:1;s:7:\"voipnew\";i:0;s:11:\"voipreports\";i:0;s:6:\"yandex\";i:0;s:4:\"ats2\";i:1;s:4:\"logs\";i:1;}','','','a:12:{s:8:\"tt_tasks\";i:2;s:9:\"statusbox\";i:1;s:21:\"tt_shop_orders_folder\";i:2;s:17:\"tt_trouble_folder\";i:512;s:26:\"tt_support_welltime_folder\";i:1;s:14:\"balance_simple\";s:1:\"0\";s:25:\"tt_mounting_orders_folder\";i:1;s:14:\"tt_task_folder\";i:256;s:19:\"tt_orders_kp_folder\";i:2;s:24:\"tt_order_welltime_folder\";i:2;s:21:\"tt_incomegoods_folder\";i:34359738368;s:17:\"tt_connect_folder\";i:35184372088832;}',27,'yes',0,0,NULL,'Europe/Moscow','ru-RU',NULL),(10,'ava','1882a5efe473e3d2c889aa311d8c0b03','manager','Default manager','',NULL,NULL,NULL,NULL,'',NULL,NULL,'',NULL,'yes',0,0,NULL,'Europe/Moscow','ru-RU',NULL),(60,'system','1882a5efe473e3d2c889aa311d8c0b03','virtual','Системный пользователь','',NULL,NULL,NULL,NULL,'',NULL,NULL,'',NULL,'yes',0,0,NULL,'Europe/Moscow','ru-RU',NULL),(177,'lk','1882a5efe473e3d2c889aa311d8c0b03','virtual','LK user','',NULL,NULL,NULL,NULL,'',NULL,NULL,'',NULL,'yes',0,0,NULL,'Europe/Moscow','ru-RU',NULL);
/*!40000 ALTER TABLE `user_users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_account_entry`
--

DROP TABLE IF EXISTS `uu_account_entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_account_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `account_tariff_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `price` float NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `bill_id` int(11) DEFAULT NULL,
  `price_without_vat` float DEFAULT NULL,
  `vat_rate` int(11) DEFAULT NULL,
  `vat` float DEFAULT NULL,
  `price_with_vat` float DEFAULT NULL,
  `is_default` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq-uu_account_entry-date-type_id-account_tariff_id` (`date`,`type_id`,`account_tariff_id`,`is_default`),
  KEY `fk-uu_account_entry-account_tariff_id` (`account_tariff_id`),
  KEY `idx-uu_account_entry-type_id` (`type_id`),
  KEY `fk-uu_account_entry-bill_id` (`bill_id`),
  CONSTRAINT `fk-uu_account_entry-account_tariff_id` FOREIGN KEY (`account_tariff_id`) REFERENCES `uu_account_tariff` (`id`),
  CONSTRAINT `fk-uu_account_entry-bill_id` FOREIGN KEY (`bill_id`) REFERENCES `uu_bill` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_account_entry`
--

LOCK TABLES `uu_account_entry` WRITE;
/*!40000 ALTER TABLE `uu_account_entry` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `uu_account_entry` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_account_log_min`
--

DROP TABLE IF EXISTS `uu_account_log_min`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_account_log_min` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `tariff_period_id` int(11) NOT NULL,
  `account_tariff_id` int(11) NOT NULL,
  `account_entry_id` int(11) DEFAULT NULL,
  `period_price` float NOT NULL,
  `coefficient` float NOT NULL,
  `price_with_coefficient` float NOT NULL,
  `price_resource` float NOT NULL,
  `price` float NOT NULL,
  `insert_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk-uu_account_log_min-tariff_period_id` (`tariff_period_id`),
  KEY `fk-uu_account_log_min-account_tariff_id` (`account_tariff_id`),
  KEY `fk-uu_account_log_min-account_entry_id` (`account_entry_id`),
  CONSTRAINT `fk-uu_account_log_min-account_entry_id` FOREIGN KEY (`account_entry_id`) REFERENCES `uu_account_entry` (`id`),
  CONSTRAINT `fk-uu_account_log_min-account_tariff_id` FOREIGN KEY (`account_tariff_id`) REFERENCES `uu_account_tariff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk-uu_account_log_min-tariff_period_id` FOREIGN KEY (`tariff_period_id`) REFERENCES `uu_tariff_period` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_account_log_min`
--

LOCK TABLES `uu_account_log_min` WRITE;
/*!40000 ALTER TABLE `uu_account_log_min` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `uu_account_log_min` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_account_log_period`
--

DROP TABLE IF EXISTS `uu_account_log_period`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_account_log_period` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `tariff_period_id` int(11) NOT NULL,
  `account_tariff_id` int(11) NOT NULL,
  `period_price` float NOT NULL,
  `coefficient` float NOT NULL,
  `price` float NOT NULL,
  `insert_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `account_entry_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-uu_account_log_period-tariff_period_id` (`tariff_period_id`),
  KEY `fk-uu_account_log_period-account_tariff_id` (`account_tariff_id`),
  KEY `fk-uu_account_log_period-account_entry_id` (`account_entry_id`),
  CONSTRAINT `fk-uu_account_log_period-account_entry_id` FOREIGN KEY (`account_entry_id`) REFERENCES `uu_account_entry` (`id`),
  CONSTRAINT `fk-uu_account_log_period-account_tariff_id` FOREIGN KEY (`account_tariff_id`) REFERENCES `uu_account_tariff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk-uu_account_log_period-tariff_period_id` FOREIGN KEY (`tariff_period_id`) REFERENCES `uu_tariff_period` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_account_log_period`
--

LOCK TABLES `uu_account_log_period` WRITE;
/*!40000 ALTER TABLE `uu_account_log_period` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `uu_account_log_period` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_account_log_resource`
--

DROP TABLE IF EXISTS `uu_account_log_resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_account_log_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `tariff_period_id` int(11) NOT NULL,
  `account_tariff_id` int(11) NOT NULL,
  `tariff_resource_id` int(11) NOT NULL,
  `amount_use` float NOT NULL,
  `amount_free` float NOT NULL,
  `amount_overhead` float DEFAULT NULL,
  `price_per_unit` float NOT NULL,
  `price` float NOT NULL,
  `insert_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `account_entry_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx-uu_account_log_resource-account_tariff-date-resource` (`account_tariff_id`,`date`,`tariff_resource_id`),
  KEY `fk-uu_account_log_resource-tariff_period_id` (`tariff_period_id`),
  KEY `fk-uu_account_log_resource-tariff_resource_id` (`tariff_resource_id`),
  KEY `fk-uu_account_log_resource-account_entry_id` (`account_entry_id`),
  CONSTRAINT `fk-uu_account_log_resource-account_entry_id` FOREIGN KEY (`account_entry_id`) REFERENCES `uu_account_entry` (`id`),
  CONSTRAINT `fk-uu_account_log_resource-account_tariff_id` FOREIGN KEY (`account_tariff_id`) REFERENCES `uu_account_tariff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk-uu_account_log_resource-tariff_period_id` FOREIGN KEY (`tariff_period_id`) REFERENCES `uu_tariff_period` (`id`),
  CONSTRAINT `fk-uu_account_log_resource-tariff_resource_id` FOREIGN KEY (`tariff_resource_id`) REFERENCES `uu_tariff_resource` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_account_log_resource`
--

LOCK TABLES `uu_account_log_resource` WRITE;
/*!40000 ALTER TABLE `uu_account_log_resource` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `uu_account_log_resource` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_account_log_setup`
--

DROP TABLE IF EXISTS `uu_account_log_setup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_account_log_setup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `tariff_period_id` int(11) NOT NULL,
  `account_tariff_id` int(11) NOT NULL,
  `price` float NOT NULL,
  `insert_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `account_entry_id` int(11) DEFAULT NULL,
  `price_setup` float NOT NULL DEFAULT '0',
  `price_number` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk-uu_account_log_setup-tariff_period_id` (`tariff_period_id`),
  KEY `fk-uu_account_log_setup-account_tariff_id` (`account_tariff_id`),
  KEY `fk-uu_account_log_setup-account_entry_id` (`account_entry_id`),
  CONSTRAINT `fk-uu_account_log_setup-account_entry_id` FOREIGN KEY (`account_entry_id`) REFERENCES `uu_account_entry` (`id`),
  CONSTRAINT `fk-uu_account_log_setup-account_tariff_id` FOREIGN KEY (`account_tariff_id`) REFERENCES `uu_account_tariff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk-uu_account_log_setup-tariff_period_id` FOREIGN KEY (`tariff_period_id`) REFERENCES `uu_tariff_period` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_account_log_setup`
--

LOCK TABLES `uu_account_log_setup` WRITE;
/*!40000 ALTER TABLE `uu_account_log_setup` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `uu_account_log_setup` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_account_tariff`
--

DROP TABLE IF EXISTS `uu_account_tariff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_account_tariff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` text,
  `client_account_id` int(11) NOT NULL,
  `service_type_id` int(11) NOT NULL,
  `region_id` int(11) DEFAULT NULL,
  `prev_account_tariff_id` int(11) DEFAULT NULL,
  `tariff_period_id` int(11) DEFAULT NULL,
  `insert_time` datetime DEFAULT NULL,
  `insert_user_id` int(11) DEFAULT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_user_id` int(11) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `voip_number` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-uu_account_tariff-client_account_id` (`client_account_id`),
  KEY `fk-uu_account_tariff-service_type_id` (`service_type_id`),
  KEY `fk-uu_account_tariff-region_id` (`region_id`),
  KEY `fk-uu_account_tariff-tariff_period_id` (`tariff_period_id`),
  KEY `fk-uu_account_tariff-insert_user_id` (`insert_user_id`),
  KEY `fk-uu_account_tariff-update_user_id` (`update_user_id`),
  KEY `fk-uu_account_tariff-city_id` (`city_id`),
  KEY `fk-uu_account_tariff-prev_account_tariff_id` (`prev_account_tariff_id`),
  KEY `idx-uu_account_tariff-voip_number-tariff_period_id` (`voip_number`,`tariff_period_id`),
  CONSTRAINT `fk-uu_account_tariff-city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`),
  CONSTRAINT `fk-uu_account_tariff-client_account_id` FOREIGN KEY (`client_account_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `fk-uu_account_tariff-insert_user_id` FOREIGN KEY (`insert_user_id`) REFERENCES `user_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk-uu_account_tariff-prev_account_tariff_id` FOREIGN KEY (`prev_account_tariff_id`) REFERENCES `uu_account_tariff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk-uu_account_tariff-region_id` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  CONSTRAINT `fk-uu_account_tariff-service_type_id` FOREIGN KEY (`service_type_id`) REFERENCES `uu_service_type` (`id`),
  CONSTRAINT `fk-uu_account_tariff-tariff_period_id` FOREIGN KEY (`tariff_period_id`) REFERENCES `uu_tariff_period` (`id`),
  CONSTRAINT `fk-uu_account_tariff-update_user_id` FOREIGN KEY (`update_user_id`) REFERENCES `user_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=100000 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_account_tariff`
--

LOCK TABLES `uu_account_tariff` WRITE;
/*!40000 ALTER TABLE `uu_account_tariff` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `uu_account_tariff` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_account_tariff_log`
--

DROP TABLE IF EXISTS `uu_account_tariff_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_account_tariff_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_tariff_id` int(11) NOT NULL,
  `tariff_period_id` int(11) DEFAULT NULL,
  `insert_time` datetime DEFAULT NULL,
  `insert_user_id` int(11) DEFAULT NULL,
  `actual_from_utc` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-uu_account_tariff_log-account_tariff_id` (`account_tariff_id`),
  KEY `fk-uu_account_tariff_log-tariff_period_id` (`tariff_period_id`),
  KEY `fk-uu_account_tariff_log-insert_user_id` (`insert_user_id`),
  CONSTRAINT `fk-uu_account_tariff_log-account_tariff_id` FOREIGN KEY (`account_tariff_id`) REFERENCES `uu_account_tariff` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk-uu_account_tariff_log-insert_user_id` FOREIGN KEY (`insert_user_id`) REFERENCES `user_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk-uu_account_tariff_log-tariff_period_id` FOREIGN KEY (`tariff_period_id`) REFERENCES `uu_tariff_period` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_account_tariff_log`
--

LOCK TABLES `uu_account_tariff_log` WRITE;
/*!40000 ALTER TABLE `uu_account_tariff_log` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `uu_account_tariff_log` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_bill`
--

DROP TABLE IF EXISTS `uu_bill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_bill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `client_account_id` int(11) NOT NULL,
  `price` float NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_default` int(11) NOT NULL DEFAULT '1',
  `is_converted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq-uu_bill-date-client_account_id` (`date`,`client_account_id`,`is_default`),
  KEY `fk-uu_bill-client_account_id` (`client_account_id`),
  KEY `idx-uu_bill-is_converted` (`is_converted`),
  CONSTRAINT `fk-uu_bill-client_account_id` FOREIGN KEY (`client_account_id`) REFERENCES `clients` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_bill`
--

LOCK TABLES `uu_bill` WRITE;
/*!40000 ALTER TABLE `uu_bill` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `uu_bill` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_period`
--

DROP TABLE IF EXISTS `uu_period`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_period` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `dayscount` int(11) NOT NULL DEFAULT '0',
  `monthscount` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_period`
--

LOCK TABLES `uu_period` WRITE;
/*!40000 ALTER TABLE `uu_period` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `uu_period` VALUES (1,'День',1,0),(2,'Месяц',0,1),(3,'Квартал',0,3),(4,'Полгода',0,6),(5,'Год',0,12);
/*!40000 ALTER TABLE `uu_period` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_resource`
--

DROP TABLE IF EXISTS `uu_resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `min_value` float NOT NULL DEFAULT '0',
  `max_value` float DEFAULT NULL,
  `service_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-uu_resource-service_type_id` (`service_type_id`),
  CONSTRAINT `fk-uu_resource-service_type_id` FOREIGN KEY (`service_type_id`) REFERENCES `uu_service_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_resource`
--

LOCK TABLES `uu_resource` WRITE;
/*!40000 ALTER TABLE `uu_resource` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `uu_resource` VALUES (1,'Дисковое пространство','Gb',0,NULL,1),(2,'Абоненты','Unit',0,NULL,1),(3,'Подключение номера другого оператора','Unit',0,NULL,1),(4,'Запись звонков с сайта','',0,1,1),(6,'Факс','',0,1,1),(7,'Линии','Unit',1,NULL,2),(8,'Звонки','¤',0,NULL,2),(9,'Трафик','Mb',0,NULL,4),(10,'Трафик Russia','Mb',0,NULL,5),(11,'Трафик Russia2','Mb',0,NULL,5),(12,'Трафик Foreign','Mb',0,NULL,5),(13,'Трафик','Mb',0,NULL,6),(14,'СМС','Unit',0,NULL,17),(15,'Процессор','Hz',0,NULL,20),(16,'Дисковое пространство','Mb',0,NULL,20),(17,'Оперативная память','Mb',0,NULL,20),(18,'Стоимость','¤',0,NULL,21),(19,'Маршрутизация по минимальной цене','',0,1,1),(20,'Маршрутизация по географии','',0,1,1);
/*!40000 ALTER TABLE `uu_resource` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_service_type`
--

DROP TABLE IF EXISTS `uu_service_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_service_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-uu_service_type-parent_id` (`parent_id`),
  CONSTRAINT `fk-uu_service_type-parent_id` FOREIGN KEY (`parent_id`) REFERENCES `uu_service_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_service_type`
--

LOCK TABLES `uu_service_type` WRITE;
/*!40000 ALTER TABLE `uu_service_type` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `uu_service_type` VALUES (1,'ВАТС',NULL),(2,'Телефония',NULL),(3,'Телефония. Пакеты',NULL),(4,'Интернет',NULL),(5,'Collocation',NULL),(6,'VPN',NULL),(7,'IT Park',NULL),(8,'Регистрация доменов',NULL),(9,'Виртуальный почтовый сервер',NULL),(10,'Старый ВАТС',NULL),(11,'Сайт',NULL),(12,'Провайдер',NULL),(13,'Wellsystem',NULL),(14,'Welltime как продукт',NULL),(15,'Дополнительные услуги',NULL),(16,'SMS Gate',NULL),(17,'SMS',NULL),(18,'Welltime как сервис',NULL),(19,'Звонок-чат',NULL),(20,'VM collocation',NULL),(21,'Разовая услуга',NULL);
/*!40000 ALTER TABLE `uu_service_type` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_tariff`
--

DROP TABLE IF EXISTS `uu_tariff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_tariff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `period_days` int(11) NOT NULL DEFAULT '0',
  `count_of_validity_period` int(11) NOT NULL DEFAULT '0',
  `is_autoprolongation` int(11) NOT NULL,
  `is_charge_after_period` int(11) NOT NULL,
  `is_charge_after_blocking` int(11) NOT NULL,
  `is_include_vat` int(11) NOT NULL,
  `currency_id` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB',
  `service_type_id` int(11) NOT NULL,
  `tariff_status_id` int(11) NOT NULL,
  `country_id` int(11) NOT NULL,
  `tariff_person_id` int(11) NOT NULL,
  `insert_time` datetime DEFAULT NULL,
  `insert_user_id` int(11) DEFAULT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `update_user_id` int(11) DEFAULT NULL,
  `voip_tarificate_id` int(11) DEFAULT NULL,
  `voip_group_id` int(11) DEFAULT NULL,
  `is_default` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-uu_tariff-currency_id` (`currency_id`),
  KEY `fk-uu_tariff-service_type_id` (`service_type_id`),
  KEY `fk-uu_tariff-tariff_status_id` (`tariff_status_id`),
  KEY `fk-uu_tariff-country_id` (`country_id`),
  KEY `fk-uu_tariff-tariff_person_id` (`tariff_person_id`),
  KEY `fk-uu_tariff-insert_user_id` (`insert_user_id`),
  KEY `fk-uu_tariff-update_user_id` (`update_user_id`),
  KEY `fk-uu_tariff-voip_tarificate_id` (`voip_tarificate_id`),
  KEY `fk-uu_tariff-voip_group_id` (`voip_group_id`),
  CONSTRAINT `fk-uu_tariff-country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`),
  CONSTRAINT `fk-uu_tariff-currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`id`),
  CONSTRAINT `fk-uu_tariff-insert_user_id` FOREIGN KEY (`insert_user_id`) REFERENCES `user_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk-uu_tariff-service_type_id` FOREIGN KEY (`service_type_id`) REFERENCES `uu_service_type` (`id`),
  CONSTRAINT `fk-uu_tariff-tariff_person_id` FOREIGN KEY (`tariff_person_id`) REFERENCES `uu_tariff_person` (`id`),
  CONSTRAINT `fk-uu_tariff-tariff_status_id` FOREIGN KEY (`tariff_status_id`) REFERENCES `uu_tariff_status` (`id`),
  CONSTRAINT `fk-uu_tariff-update_user_id` FOREIGN KEY (`update_user_id`) REFERENCES `user_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk-uu_tariff-voip_group_id` FOREIGN KEY (`voip_group_id`) REFERENCES `uu_tariff_voip_group` (`id`),
  CONSTRAINT `fk-uu_tariff-voip_tarificate_id` FOREIGN KEY (`voip_tarificate_id`) REFERENCES `uu_tariff_voip_tarificate` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10004 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_tariff`
--

LOCK TABLES `uu_tariff` WRITE;
/*!40000 ALTER TABLE `uu_tariff` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `uu_tariff` VALUES (10000,'Разовая услуга',0,0,0,1,1,1,'RUB',21,2,643,1,'2016-11-08 12:13:19',NULL,'2016-11-08 12:13:19',NULL,NULL,NULL,1),(10001,'Single-Service',0,0,0,1,1,1,'EUR',21,2,276,1,'2016-11-08 12:13:19',NULL,'2016-11-08 12:13:19',NULL,NULL,NULL,1),(10002,'Jedno-time ponuka',0,0,0,1,1,1,'EUR',21,2,703,1,'2016-11-08 12:13:20',NULL,'2016-11-08 12:13:20',NULL,NULL,NULL,1),(10003,'Egyszeri ajánlat',0,0,0,1,1,1,'HUF',21,2,348,1,'2016-11-08 12:13:20',NULL,'2016-11-08 12:13:20',NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `uu_tariff` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_tariff_period`
--

DROP TABLE IF EXISTS `uu_tariff_period`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_tariff_period` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price_per_period` float NOT NULL DEFAULT '0',
  `price_setup` float NOT NULL DEFAULT '0',
  `price_min` float NOT NULL DEFAULT '0',
  `tariff_id` int(11) NOT NULL,
  `period_id` int(11) NOT NULL,
  `charge_period_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-uu_tariff_period-tariff_id` (`tariff_id`),
  KEY `fk-uu_tariff_period-period_id` (`period_id`),
  KEY `fk-uu_tariff_period-charge_period_id` (`charge_period_id`),
  CONSTRAINT `fk-uu_tariff_period-charge_period_id` FOREIGN KEY (`charge_period_id`) REFERENCES `uu_period` (`id`),
  CONSTRAINT `fk-uu_tariff_period-period_id` FOREIGN KEY (`period_id`) REFERENCES `uu_period` (`id`),
  CONSTRAINT `fk-uu_tariff_period-tariff_id` FOREIGN KEY (`tariff_id`) REFERENCES `uu_tariff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_tariff_period`
--

LOCK TABLES `uu_tariff_period` WRITE;
/*!40000 ALTER TABLE `uu_tariff_period` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `uu_tariff_period` VALUES (1,0,0,0,10000,1,1),(2,0,0,0,10001,1,1),(3,0,0,0,10002,1,1),(4,0,0,0,10003,1,1);
/*!40000 ALTER TABLE `uu_tariff_period` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_tariff_person`
--

DROP TABLE IF EXISTS `uu_tariff_person`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_tariff_person` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_tariff_person`
--

LOCK TABLES `uu_tariff_person` WRITE;
/*!40000 ALTER TABLE `uu_tariff_person` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `uu_tariff_person` VALUES (1,'Для всех'),(2,'Только для физ. лиц'),(3,'Только для юр. лиц');
/*!40000 ALTER TABLE `uu_tariff_person` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_tariff_resource`
--

DROP TABLE IF EXISTS `uu_tariff_resource`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_tariff_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `amount` float NOT NULL DEFAULT '0',
  `price_per_unit` float NOT NULL DEFAULT '0',
  `price_min` float NOT NULL DEFAULT '0',
  `resource_id` int(11) NOT NULL,
  `tariff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-uu_tariff_resource-tariff_id` (`tariff_id`),
  KEY `u-uu_tariff_resource-resource_id-tariff_id` (`resource_id`,`tariff_id`),
  CONSTRAINT `fk-uu_tariff_resource-resource_id` FOREIGN KEY (`resource_id`) REFERENCES `uu_resource` (`id`),
  CONSTRAINT `fk-uu_tariff_resource-tariff_id` FOREIGN KEY (`tariff_id`) REFERENCES `uu_tariff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_tariff_resource`
--

LOCK TABLES `uu_tariff_resource` WRITE;
/*!40000 ALTER TABLE `uu_tariff_resource` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `uu_tariff_resource` VALUES (1,0,1,0,18,10000),(2,0,1,0,18,10001),(3,0,1,0,18,10002),(4,0,1,0,18,10003);
/*!40000 ALTER TABLE `uu_tariff_resource` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_tariff_status`
--

DROP TABLE IF EXISTS `uu_tariff_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_tariff_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `service_type_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-uu_tariff_status-service_type_id` (`service_type_id`),
  CONSTRAINT `fk-uu_tariff_status-service_type_id` FOREIGN KEY (`service_type_id`) REFERENCES `uu_service_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_tariff_status`
--

LOCK TABLES `uu_tariff_status` WRITE;
/*!40000 ALTER TABLE `uu_tariff_status` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `uu_tariff_status` VALUES (1,'Публичный',NULL),(2,'Специальный',NULL),(3,'Архивный',NULL),(4,'Тестовый',NULL),(5,'8-800',2),(6,'Операторский',2),(7,'Переходный',2),(8,'ADSL',4),(9,'8-800 тестовый',NULL);
/*!40000 ALTER TABLE `uu_tariff_status` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_tariff_voip_city`
--

DROP TABLE IF EXISTS `uu_tariff_voip_city`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_tariff_voip_city` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tariff_id` int(11) NOT NULL,
  `city_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk-uu_tariff_voip_city-tariff_id` (`tariff_id`),
  KEY `fk-uu_tariff_voip_city-city_id` (`city_id`),
  CONSTRAINT `fk-uu_tariff_voip_city-city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`),
  CONSTRAINT `fk-uu_tariff_voip_city-tariff_id` FOREIGN KEY (`tariff_id`) REFERENCES `uu_tariff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_tariff_voip_city`
--

LOCK TABLES `uu_tariff_voip_city` WRITE;
/*!40000 ALTER TABLE `uu_tariff_voip_city` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `uu_tariff_voip_city` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_tariff_voip_group`
--

DROP TABLE IF EXISTS `uu_tariff_voip_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_tariff_voip_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_tariff_voip_group`
--

LOCK TABLES `uu_tariff_voip_group` WRITE;
/*!40000 ALTER TABLE `uu_tariff_voip_group` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `uu_tariff_voip_group` VALUES (1,'Универсальные'),(2,'Местные'),(3,'Междугородние'),(4,'Международные');
/*!40000 ALTER TABLE `uu_tariff_voip_group` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `uu_tariff_voip_tarificate`
--

DROP TABLE IF EXISTS `uu_tariff_voip_tarificate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uu_tariff_voip_tarificate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uu_tariff_voip_tarificate`
--

LOCK TABLES `uu_tariff_voip_tarificate` WRITE;
/*!40000 ALTER TABLE `uu_tariff_voip_tarificate` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `uu_tariff_voip_tarificate` VALUES (1,'Посекундно'),(2,'Посекундно, 5 сек. бесплатно'),(3,'Поминутно'),(4,'Поминутно, 5 сек. бесплатно');
/*!40000 ALTER TABLE `uu_tariff_voip_tarificate` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `virtpbx_stat`
--

DROP TABLE IF EXISTS `virtpbx_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `virtpbx_stat` (
  `client_id` int(11) NOT NULL,
  `usage_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `use_space` bigint(20) DEFAULT '0',
  `numbers` int(11) DEFAULT NULL,
  `ext_did_count` int(11) DEFAULT NULL,
  `call_recording_enabled` int(11) DEFAULT NULL,
  `faxes_enabled` int(11) DEFAULT NULL,
  PRIMARY KEY (`client_id`,`usage_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `virtpbx_stat`
--

LOCK TABLES `virtpbx_stat` WRITE;
/*!40000 ALTER TABLE `virtpbx_stat` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `virtpbx_stat` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `voip_destination`
--

DROP TABLE IF EXISTS `voip_destination`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voip_destination` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voip_destination`
--

LOCK TABLES `voip_destination` WRITE;
/*!40000 ALTER TABLE `voip_destination` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `voip_destination` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `voip_destination_prefixes`
--

DROP TABLE IF EXISTS `voip_destination_prefixes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voip_destination_prefixes` (
  `destination_id` int(11) NOT NULL,
  `prefixlist_id` int(11) NOT NULL,
  KEY `destination_id` (`destination_id`),
  KEY `prefixlist_id` (`prefixlist_id`),
  CONSTRAINT `fk_destination_prefixes__destination_id` FOREIGN KEY (`destination_id`) REFERENCES `voip_destination` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_destination_prefixes__pricelist_id` FOREIGN KEY (`prefixlist_id`) REFERENCES `voip_prefixlist` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voip_destination_prefixes`
--

LOCK TABLES `voip_destination_prefixes` WRITE;
/*!40000 ALTER TABLE `voip_destination_prefixes` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `voip_destination_prefixes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `voip_number_type`
--

DROP TABLE IF EXISTS `voip_number_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voip_number_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voip_number_type`
--

LOCK TABLES `voip_number_type` WRITE;
/*!40000 ALTER TABLE `voip_number_type` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `voip_number_type` VALUES (1,'Национальный географический'),(2,'Национальный негеографический'),(3,'Международный'),(8,'7800');
/*!40000 ALTER TABLE `voip_number_type` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `voip_number_type_country`
--

DROP TABLE IF EXISTS `voip_number_type_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voip_number_type_country` (
  `voip_number_type_id` int(11) NOT NULL,
  `country_id` int(4) NOT NULL,
  PRIMARY KEY (`voip_number_type_id`,`country_id`),
  KEY `fk-voip_number_type_country-country_id` (`country_id`),
  CONSTRAINT `fk-voip_number_type_country-country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`),
  CONSTRAINT `fk-voip_number_type_country-voip_number_type_id` FOREIGN KEY (`voip_number_type_id`) REFERENCES `voip_number_type` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voip_number_type_country`
--

LOCK TABLES `voip_number_type_country` WRITE;
/*!40000 ALTER TABLE `voip_number_type_country` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `voip_number_type_country` VALUES (1,276),(2,276),(3,276),(1,348),(2,348),(3,348),(1,643);
/*!40000 ALTER TABLE `voip_number_type_country` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `voip_numbers`
--

DROP TABLE IF EXISTS `voip_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voip_numbers` (
  `number` varchar(15) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `status` enum('notsale','instock','active_tested','active_commercial','notactive_reserved','notactive_hold','released') NOT NULL DEFAULT 'notsale',
  `reserve_from` datetime DEFAULT NULL,
  `reserve_till` datetime DEFAULT NULL,
  `hold_from` datetime DEFAULT NULL,
  `hold_to` datetime DEFAULT NULL,
  `beauty_level` tinyint(4) NOT NULL DEFAULT '0',
  `region` smallint(6) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `usage_id` int(11) DEFAULT NULL,
  `uu_account_tariff_id` int(11) DEFAULT NULL,
  `reserved_free_date` datetime DEFAULT NULL,
  `used_until_date` datetime DEFAULT NULL,
  `edit_user_id` int(11) DEFAULT NULL,
  `site_publish` enum('N','Y') NOT NULL DEFAULT 'N',
  `city_id` int(11) NOT NULL,
  `did_group_id` int(11) DEFAULT NULL,
  `number_tech` varchar(15) DEFAULT NULL,
  `operator_account_id` int(11) DEFAULT NULL,
  `country_code` int(4) DEFAULT NULL,
  `ndc` int(10) DEFAULT NULL,
  `number_subscriber` varchar(15) DEFAULT NULL,
  `number_type` int(11) DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `number_cut` varchar(2) DEFAULT NULL,
  `calls_per_month_0` int(11) DEFAULT NULL,
  `calls_per_month_1` int(11) DEFAULT NULL,
  `calls_per_month_2` int(11) DEFAULT NULL,
  `is_ported` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`number`),
  UNIQUE KEY `voip_numbers__number_tech` (`number_tech`),
  KEY `region` (`region`),
  KEY `fk_voip_number__city_id` (`city_id`),
  KEY `fk_voip_number__did_group_id` (`did_group_id`),
  KEY `fk-voip_numbers-number_type` (`number_type`),
  KEY `voip_numbers_number_cut` (`number_cut`),
  KEY `fk-uu_account_tariff-id` (`uu_account_tariff_id`),
  CONSTRAINT `fk-uu_account_tariff-id` FOREIGN KEY (`uu_account_tariff_id`) REFERENCES `uu_account_tariff` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk-voip_numbers-number_type` FOREIGN KEY (`number_type`) REFERENCES `voip_number_type` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_voip_number__city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_voip_number__did_group_id` FOREIGN KEY (`did_group_id`) REFERENCES `did_group` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voip_numbers`
--

LOCK TABLES `voip_numbers` WRITE;
/*!40000 ALTER TABLE `voip_numbers` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `voip_numbers` VALUES ('74992130006','instock',NULL,NULL,NULL,NULL,1,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,5,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'06',NULL,NULL,NULL,0),('74992130007','instock',NULL,NULL,NULL,NULL,1,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,5,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'07',NULL,NULL,NULL,0),('74992130010','instock',NULL,NULL,NULL,NULL,1,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,2,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,'10',NULL,NULL,NULL,0),('78002003012','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410544',38319,7,800,'2003012',8,'2016-11-08 12:11:46',NULL,'12',NULL,NULL,NULL,0),('78003503001','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410586',38319,7,800,'3503001',8,'2016-11-08 12:11:46',NULL,'01',NULL,NULL,NULL,0),('78003503004','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410587',38319,7,800,'3503004',8,'2016-11-08 12:11:46',NULL,'04',NULL,NULL,NULL,0),('78003503006','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410588',38319,7,800,'3503006',8,'2016-11-08 12:11:46',NULL,'06',NULL,NULL,NULL,0),('78003503007','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410589',38319,7,800,'3503007',8,'2016-11-08 12:11:46',NULL,'07',NULL,NULL,NULL,0),('78003503008','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410590',38319,7,800,'3503008',8,'2016-11-08 12:11:46',NULL,'08',NULL,NULL,NULL,0),('78003503009','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410591',38319,7,800,'3503009',8,'2016-11-08 12:11:46',NULL,'09',NULL,NULL,NULL,0),('78003503012','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410592',38319,7,800,'3503012',8,'2016-11-08 12:11:46',NULL,'12',NULL,NULL,NULL,0),('78003503013','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410593',38319,7,800,'3503013',8,'2016-11-08 12:11:46',NULL,'13',NULL,NULL,NULL,0),('78003503014','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410594',38319,7,800,'3503014',8,'2016-11-08 12:11:46',NULL,'14',NULL,NULL,NULL,0),('78003503015','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410595',38319,7,800,'3503015',8,'2016-11-08 12:11:46',NULL,'15',NULL,NULL,NULL,0),('78003503016','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410596',38319,7,800,'3503016',8,'2016-11-08 12:11:46',NULL,'16',NULL,NULL,NULL,0),('78003503017','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410597',38319,7,800,'3503017',8,'2016-11-08 12:11:46',NULL,'17',NULL,NULL,NULL,0),('78003503021','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410598',38319,7,800,'3503021',8,'2016-11-08 12:11:46',NULL,'21',NULL,NULL,NULL,0),('78003503023','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410599',38319,7,800,'3503023',8,'2016-11-08 12:11:46',NULL,'23',NULL,NULL,NULL,0),('78003503024','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410600',38319,7,800,'3503024',8,'2016-11-08 12:11:46',NULL,'24',NULL,NULL,NULL,0),('78003503025','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410601',38319,7,800,'3503025',8,'2016-11-08 12:11:46',NULL,'25',NULL,NULL,NULL,0),('78003503026','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410602',38319,7,800,'3503026',8,'2016-11-08 12:11:46',NULL,'26',NULL,NULL,NULL,0),('78003503027','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410603',38319,7,800,'3503027',8,'2016-11-08 12:11:46',NULL,'27',NULL,NULL,NULL,0),('78003503031','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410604',38319,7,800,'3503031',8,'2016-11-08 12:11:46',NULL,'31',NULL,NULL,NULL,0),('78003503032','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410605',38319,7,800,'3503032',8,'2016-11-08 12:11:46',NULL,'32',NULL,NULL,NULL,0),('78003503034','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410606',38319,7,800,'3503034',8,'2016-11-08 12:11:46',NULL,'34',NULL,NULL,NULL,0),('78003503036','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410607',38319,7,800,'3503036',8,'2016-11-08 12:11:46',NULL,'36',NULL,NULL,NULL,0),('78003503037','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410608',38319,7,800,'3503037',8,'2016-11-08 12:11:46',NULL,'37',NULL,NULL,NULL,0),('78003503038','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410609',38319,7,800,'3503038',8,'2016-11-08 12:11:46',NULL,'38',NULL,NULL,NULL,0),('78003503041','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410610',38319,7,800,'3503041',8,'2016-11-08 12:11:46',NULL,'41',NULL,NULL,NULL,0),('78003503042','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410611',38319,7,800,'3503042',8,'2016-11-08 12:11:46',NULL,'42',NULL,NULL,NULL,0),('78003503043','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410612',38319,7,800,'3503043',8,'2016-11-08 12:11:46',NULL,'43',NULL,NULL,NULL,0),('78003503045','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410613',38319,7,800,'3503045',8,'2016-11-08 12:11:46',NULL,'45',NULL,NULL,NULL,0),('78003503046','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410614',38319,7,800,'3503046',8,'2016-11-08 12:11:46',NULL,'46',NULL,NULL,NULL,0),('78003503047','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410615',38319,7,800,'3503047',8,'2016-11-08 12:11:46',NULL,'47',NULL,NULL,NULL,0),('78003503051','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410616',38319,7,800,'3503051',8,'2016-11-08 12:11:46',NULL,'51',NULL,NULL,NULL,0),('78003503052','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410617',38319,7,800,'3503052',8,'2016-11-08 12:11:46',NULL,'52',NULL,NULL,NULL,0),('78003503054','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410618',38319,7,800,'3503054',8,'2016-11-08 12:11:46',NULL,'54',NULL,NULL,NULL,0),('78003503056','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410619',38319,7,800,'3503056',8,'2016-11-08 12:11:46',NULL,'56',NULL,NULL,NULL,0),('78003503057','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410620',38319,7,800,'3503057',8,'2016-11-08 12:11:46',NULL,'57',NULL,NULL,NULL,0),('78003503058','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410621',38319,7,800,'3503058',8,'2016-11-08 12:11:46',NULL,'58',NULL,NULL,NULL,0),('78003503061','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410622',38319,7,800,'3503061',8,'2016-11-08 12:11:46',NULL,'61',NULL,NULL,NULL,0),('78003503062','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410623',38319,7,800,'3503062',8,'2016-11-08 12:11:46',NULL,'62',NULL,NULL,NULL,0),('78003503063','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410624',38319,7,800,'3503063',8,'2016-11-08 12:11:46',NULL,'63',NULL,NULL,NULL,0),('78003503064','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410625',38319,7,800,'3503064',8,'2016-11-08 12:11:46',NULL,'64',NULL,NULL,NULL,0),('78003503065','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410626',38319,7,800,'3503065',8,'2016-11-08 12:11:46',NULL,'65',NULL,NULL,NULL,0),('78003503067','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410627',38319,7,800,'3503067',8,'2016-11-08 12:11:46',NULL,'67',NULL,NULL,NULL,0),('78003503071','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410628',38319,7,800,'3503071',8,'2016-11-08 12:11:46',NULL,'71',NULL,NULL,NULL,0),('78003503072','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410629',38319,7,800,'3503072',8,'2016-11-08 12:11:46',NULL,'72',NULL,NULL,NULL,0),('78003503073','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410630',38319,7,800,'3503073',8,'2016-11-08 12:11:46',NULL,'73',NULL,NULL,NULL,0),('78003503074','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410631',38319,7,800,'3503074',8,'2016-11-08 12:11:46',NULL,'74',NULL,NULL,NULL,0),('78003503075','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410632',38319,7,800,'3503075',8,'2016-11-08 12:11:46',NULL,'75',NULL,NULL,NULL,0),('78003503076','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410633',38319,7,800,'3503076',8,'2016-11-08 12:11:46',NULL,'76',NULL,NULL,NULL,0),('78003503081','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410634',38319,7,800,'3503081',8,'2016-11-08 12:11:46',NULL,'81',NULL,NULL,NULL,0),('78003503082','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410635',38319,7,800,'3503082',8,'2016-11-08 12:11:46',NULL,'82',NULL,NULL,NULL,0),('78003503083','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410636',38319,7,800,'3503083',8,'2016-11-08 12:11:46',NULL,'83',NULL,NULL,NULL,0),('78003503084','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410637',38319,7,800,'3503084',8,'2016-11-08 12:11:46',NULL,'84',NULL,NULL,NULL,0),('78003503085','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410638',38319,7,800,'3503085',8,'2016-11-08 12:11:46',NULL,'85',NULL,NULL,NULL,0),('78003503086','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410639',38319,7,800,'3503086',8,'2016-11-08 12:11:46',NULL,'86',NULL,NULL,NULL,0),('78003503091','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410640',38319,7,800,'3503091',8,'2016-11-08 12:11:46',NULL,'91',NULL,NULL,NULL,0),('78003503092','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410641',38319,7,800,'3503092',8,'2016-11-08 12:11:46',NULL,'92',NULL,NULL,NULL,0),('78003503093','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410642',38319,7,800,'3503093',8,'2016-11-08 12:11:46',NULL,'93',NULL,NULL,NULL,0),('78003503094','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410643',38319,7,800,'3503094',8,'2016-11-08 12:11:46',NULL,'94',NULL,NULL,NULL,0),('78003503095','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410644',38319,7,800,'3503095',8,'2016-11-08 12:11:46',NULL,'95',NULL,NULL,NULL,0),('78003503096','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410645',38319,7,800,'3503096',8,'2016-11-08 12:11:46',NULL,'96',NULL,NULL,NULL,0),('78003503101','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410546',38319,7,800,'3503101',8,'2016-11-08 12:11:46',NULL,'01',NULL,NULL,NULL,0),('78003503102','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410547',38319,7,800,'3503102',8,'2016-11-08 12:11:46',NULL,'02',NULL,NULL,NULL,0),('78003503103','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410548',38319,7,800,'3503103',8,'2016-11-08 12:11:46',NULL,'03',NULL,NULL,NULL,0),('78003503104','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410549',38319,7,800,'3503104',8,'2016-11-08 12:11:46',NULL,'04',NULL,NULL,NULL,0),('78003503105','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410550',38319,7,800,'3503105',8,'2016-11-08 12:11:46',NULL,'05',NULL,NULL,NULL,0),('78003503106','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410551',38319,7,800,'3503106',8,'2016-11-08 12:11:46',NULL,'06',NULL,NULL,NULL,0),('78003503107','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410552',38319,7,800,'3503107',8,'2016-11-08 12:11:46',NULL,'07',NULL,NULL,NULL,0),('78003503108','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410553',38319,7,800,'3503108',8,'2016-11-08 12:11:46',NULL,'08',NULL,NULL,NULL,0),('78003503109','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410554',38319,7,800,'3503109',8,'2016-11-08 12:11:46',NULL,'09',NULL,NULL,NULL,0),('78003503110','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410555',38319,7,800,'3503110',8,'2016-11-08 12:11:46',NULL,'10',NULL,NULL,NULL,0),('78003503112','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410556',38319,7,800,'3503112',8,'2016-11-08 12:11:46',NULL,'12',NULL,NULL,NULL,0),('78003503114','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410557',38319,7,800,'3503114',8,'2016-11-08 12:11:46',NULL,'14',NULL,NULL,NULL,0),('78003503115','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410558',38319,7,800,'3503115',8,'2016-11-08 12:11:46',NULL,'15',NULL,NULL,NULL,0),('78003503116','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410559',38319,7,800,'3503116',8,'2016-11-08 12:11:46',NULL,'16',NULL,NULL,NULL,0),('78003503117','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410560',38319,7,800,'3503117',8,'2016-11-08 12:11:46',NULL,'17',NULL,NULL,NULL,0),('78003503118','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410561',38319,7,800,'3503118',8,'2016-11-08 12:11:46',NULL,'18',NULL,NULL,NULL,0),('78003503119','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410562',38319,7,800,'3503119',8,'2016-11-08 12:11:46',NULL,'19',NULL,NULL,NULL,0),('78003503120','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410563',38319,7,800,'3503120',8,'2016-11-08 12:11:46',NULL,'20',NULL,NULL,NULL,0),('78003503121','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410564',38319,7,800,'3503121',8,'2016-11-08 12:11:46',NULL,'21',NULL,NULL,NULL,0),('78003503123','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410565',38319,7,800,'3503123',8,'2016-11-08 12:11:46',NULL,'23',NULL,NULL,NULL,0),('78003503124','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410566',38319,7,800,'3503124',8,'2016-11-08 12:11:46',NULL,'24',NULL,NULL,NULL,0),('78003503125','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410567',38319,7,800,'3503125',8,'2016-11-08 12:11:46',NULL,'25',NULL,NULL,NULL,0),('78003503126','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410568',38319,7,800,'3503126',8,'2016-11-08 12:11:46',NULL,'26',NULL,NULL,NULL,0),('78003503127','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410569',38319,7,800,'3503127',8,'2016-11-08 12:11:46',NULL,'27',NULL,NULL,NULL,0),('78003503128','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410570',38319,7,800,'3503128',8,'2016-11-08 12:11:46',NULL,'28',NULL,NULL,NULL,0),('78003503129','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410571',38319,7,800,'3503129',8,'2016-11-08 12:11:46',NULL,'29',NULL,NULL,NULL,0),('78003503130','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410572',38319,7,800,'3503130',8,'2016-11-08 12:11:46',NULL,'30',NULL,NULL,NULL,0),('78003503132','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410573',38319,7,800,'3503132',8,'2016-11-08 12:11:46',NULL,'32',NULL,NULL,NULL,0),('78003503134','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410574',38319,7,800,'3503134',8,'2016-11-08 12:11:46',NULL,'34',NULL,NULL,NULL,0),('78003503136','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410575',38319,7,800,'3503136',8,'2016-11-08 12:11:46',NULL,'36',NULL,NULL,NULL,0),('78003503137','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410576',38319,7,800,'3503137',8,'2016-11-08 12:11:46',NULL,'37',NULL,NULL,NULL,0),('78003503138','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410577',38319,7,800,'3503138',8,'2016-11-08 12:11:46',NULL,'38',NULL,NULL,NULL,0),('78003503139','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410578',38319,7,800,'3503139',8,'2016-11-08 12:11:46',NULL,'39',NULL,NULL,NULL,0),('78003503140','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410579',38319,7,800,'3503140',8,'2016-11-08 12:11:46',NULL,'40',NULL,NULL,NULL,0),('78003503141','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410580',38319,7,800,'3503141',8,'2016-11-08 12:11:46',NULL,'41',NULL,NULL,NULL,0),('78003503151','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410581',38319,7,800,'3503151',8,'2016-11-08 12:11:46',NULL,'51',NULL,NULL,NULL,0),('78003503152','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410582',38319,7,800,'3503152',8,'2016-11-08 12:11:46',NULL,'52',NULL,NULL,NULL,0),('78003503160','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410583',38319,7,800,'3503160',8,'2016-11-08 12:11:46',NULL,'60',NULL,NULL,NULL,0),('78003503170','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410584',38319,7,800,'3503170',8,'2016-11-08 12:11:46',NULL,'70',NULL,NULL,NULL,0),('78003503190','instock',NULL,NULL,NULL,NULL,0,99,NULL,NULL,NULL,NULL,NULL,NULL,'N',7495,NULL,'5555410585',38319,7,800,'3503190',8,'2016-11-08 12:11:46',NULL,'90',NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `voip_numbers` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `voip_permit`
--

DROP TABLE IF EXISTS `voip_permit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voip_permit` (
  `client` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `callerid` varchar(30) NOT NULL DEFAULT '',
  `permit` varchar(255) NOT NULL DEFAULT '',
  `cl` int(10) unsigned NOT NULL DEFAULT '0',
  `enable` smallint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`client`,`callerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voip_permit`
--

LOCK TABLES `voip_permit` WRITE;
/*!40000 ALTER TABLE `voip_permit` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `voip_permit` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `voip_prefixlist`
--

DROP TABLE IF EXISTS `voip_prefixlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voip_prefixlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type_id` smallint(6) NOT NULL,
  `sub_type` enum('all','fixed','mobile') NOT NULL DEFAULT 'all',
  `prefixes` text,
  `country_id` int(11) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `exclude_operators` tinyint(1) DEFAULT NULL,
  `operators` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voip_prefixlist`
--

LOCK TABLES `voip_prefixlist` WRITE;
/*!40000 ALTER TABLE `voip_prefixlist` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `voip_prefixlist` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `voip_registry`
--

DROP TABLE IF EXISTS `voip_registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voip_registry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) DEFAULT NULL,
  `city_id` int(11) DEFAULT NULL,
  `source` enum('portability','operator','regulator') DEFAULT 'portability',
  `number_type_id` int(11) DEFAULT NULL,
  `number_from` varchar(32) DEFAULT NULL,
  `number_to` varchar(32) DEFAULT NULL,
  `account_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `comment` varchar(1024) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `fk-voip_registry-country_id` (`country_id`),
  KEY `fk-voip_registry-city_id` (`city_id`),
  KEY `fk-voip_registry-number_type_id` (`number_type_id`),
  KEY `fk-voip_registry-account_id` (`account_id`),
  CONSTRAINT `fk-voip_registry-account_id` FOREIGN KEY (`account_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `fk-voip_registry-city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`),
  CONSTRAINT `fk-voip_registry-country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`),
  CONSTRAINT `fk-voip_registry-number_type_id` FOREIGN KEY (`number_type_id`) REFERENCES `voip_number_type` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voip_registry`
--

LOCK TABLES `voip_registry` WRITE;
/*!40000 ALTER TABLE `voip_registry` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `voip_registry` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `vpbx_numbers`
--

DROP TABLE IF EXISTS `vpbx_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vpbx_numbers` (
  `client_id` int(11) NOT NULL DEFAULT '0',
  `number` varchar(16) DEFAULT NULL,
  KEY `client` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vpbx_numbers`
--

LOCK TABLES `vpbx_numbers` WRITE;
/*!40000 ALTER TABLE `vpbx_numbers` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `vpbx_numbers` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Temporary view structure for view `welltime_updates`
--

DROP TABLE IF EXISTS `welltime_updates`;
/*!50001 DROP VIEW IF EXISTS `welltime_updates`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `welltime_updates` AS SELECT 
 1 AS `client`,
 1 AS `password`,
 1 AS `version`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `z_sync_1c`
--

DROP TABLE IF EXISTS `z_sync_1c`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `z_sync_1c` (
  `tname` enum('clientCard') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `tid` int(11) NOT NULL,
  `rnd` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tname`,`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `z_sync_1c`
--

LOCK TABLES `z_sync_1c` WRITE;
/*!40000 ALTER TABLE `z_sync_1c` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `z_sync_1c` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `z_sync_admin`
--

DROP TABLE IF EXISTS `z_sync_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `z_sync_admin` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `event` varchar(255) NOT NULL DEFAULT 'create',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill_no` (`bill_no`)
) ENGINE=InnoDB AUTO_INCREMENT=1619 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `z_sync_admin`
--

LOCK TABLES `z_sync_admin` WRITE;
/*!40000 ALTER TABLE `z_sync_admin` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `z_sync_admin` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `z_sync_postgres`
--

DROP TABLE IF EXISTS `z_sync_postgres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `z_sync_postgres` (
  `tbase` enum('nispd','auth','nispd_dev') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `tname` enum('clients','usage_voip','usage_voip_package','tarifs_voip','log_tarif','usage_trunk','usage_trunk_settings','organization','prefixlist','tariff_package','dest_prefixes','currency_rate','uu_account_tariff') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `tid` int(11) NOT NULL,
  `rnd` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tbase`,`tname`,`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `z_sync_postgres`
--

LOCK TABLES `z_sync_postgres` WRITE;
/*!40000 ALTER TABLE `z_sync_postgres` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `z_sync_postgres` VALUES ('nispd','tarifs_voip',72,1828710306),('nispd','tarifs_voip',78,1409427445),('nispd','tarifs_voip',86,1561006367),('nispd','tarifs_voip',531,1911374716),('nispd','tarifs_voip',533,1242721112),('nispd','tarifs_voip',624,1434076968),('nispd','organization',1,632011973),('nispd','organization',2,738032275),('nispd','organization',3,1794125516),('nispd','organization',4,756530816),('nispd','organization',5,400277596),('nispd','organization',6,1731795594),('nispd','organization',7,1458145242),('nispd','organization',8,95339488),('nispd','organization',9,102261778),('nispd','organization',10,225290487),('nispd','organization',11,819667161),('nispd','organization',12,1422464407),('nispd','organization',13,653320615),('nispd','organization',14,999209913),('nispd','organization',15,1036087784),('nispd','organization',16,182809241),('nispd','organization',17,1805782916),('nispd','organization',18,480486917),('nispd','organization',19,985085898),('nispd','organization',20,1483968801),('nispd','organization',21,464586374),('nispd','organization',22,1871025527),('nispd','organization',23,1961368574),('nispd','organization',24,193766350),('nispd','organization',25,1084726091),('nispd','organization',26,842331464),('nispd','organization',27,957479111),('nispd','organization',28,260401223),('nispd','organization',29,429568844),('nispd','organization',30,1366640614),('nispd','organization',31,1544496597),('nispd','organization',32,1622561207),('nispd','organization',33,1479316303),('nispd','organization',34,528897957),('nispd','organization',35,206540935),('nispd','organization',36,1446010867),('nispd','organization',37,610431590),('nispd','organization',38,714125410),('nispd','organization',39,1739332344),('nispd','organization',40,554285551);
/*!40000 ALTER TABLE `z_sync_postgres` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Final view structure for view `tt_states_rb`
--

/*!50001 DROP VIEW IF EXISTS `tt_states_rb`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `tt_states_rb` AS select `tt_states`.`id` AS `id`,`tt_states`.`pk` AS `pk`,if((`tt_states`.`id` = 17),'К поступлению',if((`tt_states`.`id` = 18),'Принят',`tt_states`.`name`)) AS `name`,`tt_states`.`order` AS `order`,`tt_states`.`time_delta` AS `time_delta`,`tt_states`.`folder` AS `folder`,`tt_states`.`deny` AS `deny`,`tt_states`.`state_1c` AS `state_1c`,`tt_states`.`oso` AS `oso`,`tt_states`.`omo` AS `omo` from `tt_states` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `usage_sms_gate`
--

/*!50001 DROP VIEW IF EXISTS `usage_sms_gate`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=MERGE */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `usage_sms_gate` AS select sql_no_cache `u`.`id` AS `usage_id`,`c`.`client` AS `client`,`c`.`id` AS `client_id`,sha(concat(sha(`c`.`password`),sha(concat(sha(now()),`c`.`password`)))) AS `password`,sha(concat(sha(now()),`c`.`password`)) AS `salt`,`u`.`actual_from` AS `actual_from`,`u`.`actual_to` AS `actual_to`,if((isnull(`u`.`param_value`) or (`u`.`param_value` = _utf8'')),ltrim(substr(`t`.`param_name`,(locate(_utf8'=',`t`.`param_name`) + 1))),`u`.`param_value`) AS `sms_max`,`u`.`status` AS `status` from ((`clients` `c` join `usage_extra` `u` on((`u`.`client` = `c`.`client`))) join `tarifs_extra` `t` on(((`t`.`id` = `u`.`tarif_id`) and (`t`.`code` = 'sms_gate')))) */
/*!50002 WITH LOCAL CHECK OPTION */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `welltime_updates`
--

/*!50001 DROP VIEW IF EXISTS `welltime_updates`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8 */;
/*!50001 SET character_set_results     = utf8 */;
/*!50001 SET collation_connection      = utf8_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `welltime_updates` AS select `u`.`client` AS `client`,`c`.`password` AS `password`,trim(replace(`t`.`description`,'Welltime','')) AS `version` from ((`usage_extra` `u` join `tarifs_extra` `t`) join `clients` `c`) where ((`u`.`tarif_id` in (219,226,227)) and (now() between `u`.`actual_from` and `u`.`actual_to`) and (`t`.`id` = `u`.`tarif_id`) and (`c`.`client` = `u`.`client`) and (`c`.`status` in ('work','once'))) union select `u`.`client` AS `client`,`c`.`password` AS `password`,trim(replace(`t`.`description`,'Welltime','')) AS `version` from ((`usage_welltime` `u` join `tarifs_extra` `t`) join `clients` `c`) where ((`u`.`tarif_id` = 322) and (now() between `u`.`actual_from` and `u`.`actual_to`) and (`t`.`id` = `u`.`tarif_id`) and (`c`.`client` = `u`.`client`) and (`c`.`status` in ('work','once'))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-11-08 15:19:57
