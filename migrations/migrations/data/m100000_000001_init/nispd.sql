-- MySQL dump 10.13  Distrib 5.5.38, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: test_all
-- ------------------------------------------------------
-- Server version	5.5.38-0ubuntu0.14.04.1

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
  `direction` enum('localmob','local','full','blocked','russia') NOT NULL DEFAULT 'full',
  `is_blocked` tinyint(4) NOT NULL DEFAULT '0',
  `is_disabled` tinyint(4) NOT NULL DEFAULT '0',
  `number7800` char(13) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21880 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`usage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `bill_currency_rate`
--

DROP TABLE IF EXISTS `bill_currency_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_currency_rate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  `rate` decimal(10,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`),
  KEY `date` (`date`,`currency`)
) ENGINE=InnoDB AUTO_INCREMENT=8408 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bill_monthlyadd`
--

DROP TABLE IF EXISTS `bill_monthlyadd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_monthlyadd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '2004-01-01',
  `actual_to` date NOT NULL DEFAULT '9999-12-31',
  `client` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  `amount` varchar(100) NOT NULL DEFAULT '',
  `price` varchar(100) NOT NULL DEFAULT '',
  `period` enum('day','week','month','year','once') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'day',
  `enabled` tinyint(4) DEFAULT '1',
  `date_last_writeoff` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `credit_usd` decimal(7,2) NOT NULL DEFAULT '0.00',
  `status` enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=2054 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `actual_to` date NOT NULL DEFAULT '9999-12-31',
  `client` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `description` varchar(100) NOT NULL DEFAULT '',
  `amount` varchar(100) NOT NULL DEFAULT '',
  `price` varchar(100) NOT NULL DEFAULT '',
  `period` enum('day','week','month','year','once') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'day',
  `enabled` tinyint(4) DEFAULT '1',
  `date_last_writeoff` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
  PRIMARY KEY (`id`),
  KEY `fk_city__country_id` (`country_id`),
  CONSTRAINT `fk_city__country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `client_contacts`
--

DROP TABLE IF EXISTS `client_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `type` enum('email','phone','fax','sms') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
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
  PRIMARY KEY (`id`),
  KEY `contragent_id` (`contragent_id`),
  KEY `super_id` (`super_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35802 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  KEY `super_client_id` (`super_id`)
) ENGINE=InnoDB AUTO_INCREMENT=79289 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `passport_date_issued` date DEFAULT '1970-01-01',
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
-- Table structure for table `client_files`
--

DROP TABLE IF EXISTS `client_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contract_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`contract_id`)
) ENGINE=InnoDB AUTO_INCREMENT=86520 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1365 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `who` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `k_pay_acc` (`pay_acc`),
  KEY `k_client` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=974 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `client_super`
--

DROP TABLE IF EXISTS `client_super`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client_super` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `financial_manager_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=79276 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `hid_rtsaldo_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `_1c_uk` (`cli_1c`,`con_1c`),
  KEY `client` (`client`),
  KEY `status` (`status`),
  KEY `super_id` (`super_id`),
  KEY `contract_id` (`contract_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35807 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_clients_after_ins_tr` AFTER INSERT ON `clients`
FOR EACH ROW BEGIN
     call z_sync_postgres('clients', NEW.id);

     call add_event('add_account', NEW.id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `client_status` BEFORE UPDATE ON `clients` FOR EACH ROW begin
if NEW.status='' then
SET NEW.status=OLD.status;
end if;
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_clients_after_upd_tr` AFTER UPDATE ON `clients`
FOR EACH ROW BEGIN
                if NEW.voip_credit_limit <> OLD.voip_credit_limit
                    or
                   NEW.voip_credit_limit_day <> OLD.voip_credit_limit_day
                    or
                   NEW.voip_disabled <> OLD.voip_disabled
                    or
                   NEW.balance <> OLD.balance
                    or
                   NEW.credit <> OLD.credit
                    or
                   NEW.is_blocked <> OLD.is_blocked
                    or
                   ifnull(NEW.last_account_date, '2000-01-01') <> ifnull(OLD.last_account_date,'2000-01-01')
                    or
                   ifnull(NEW.last_payed_voip_month, '2000-01-01') <> ifnull(OLD.last_payed_voip_month,'2000-01-01')
              then
                 call z_sync_postgres('clients', NEW.id);
              end if;



            if OLD.admin_contact_id <> NEW.admin_contact_id THEN
                 call add_event('admin_changed', NEW.id);
            end if;

            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_clients_after_del_tr` AFTER DELETE ON `clients`
FOR EACH ROW BEGIN
     call z_sync_postgres('clients', OLD.id);

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
-- Table structure for table `core_sync_ids`
--

DROP TABLE IF EXISTS `core_sync_ids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_sync_ids` (
  `id` int(4) NOT NULL,
  `type` enum('account','contragent','super_client') NOT NULL DEFAULT 'account',
  `external_id` varchar(32) NOT NULL,
  KEY `type_id` (`id`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `lang` varchar(5) DEFAULT 'ru',
  PRIMARY KEY (`code`),
  KEY `in_use` (`in_use`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `currency`
--

DROP TABLE IF EXISTS `currency`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currency` (
  `id` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`id`),
  KEY `fk_did_group__city_id` (`city_id`),
  CONSTRAINT `fk_did_group__city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `domains`
--

DROP TABLE IF EXISTS `domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '0000-00-00',
  `actual_to` date NOT NULL DEFAULT '0000-00-00',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `client` varchar(32) NOT NULL DEFAULT '',
  `primary_mx` varchar(64) NOT NULL DEFAULT '',
  `registrator` enum('','RUCENTER-REG-RIPN') NOT NULL,
  `rucenter_form_no` decimal(6,0) NOT NULL,
  `dns` varchar(64) NOT NULL DEFAULT '',
  `paid_till` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=349 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `email_whitelist_chck` BEFORE INSERT ON `email_whitelist` FOR EACH ROW BEGIN
 IF (
   (NEW.local_part IS NULL OR NEW.local_part='')
 AND
   (NEW.sender_address IS NULL OR NEW.sender_address='')
 AND
   (NEW.domain IS NULL OR NEW.domain='')
 AND
   (NEW.sender_address_domain IS NULL OR NEW.sender_address_domain='')
 AND
   (NEW.sender_host_address IS NULL OR NEW.sender_host_address='')
 ) THEN
   SET NEW.sender_host_address := '10.10.10.10';
   SET NEW.domain := 'record.null';
 END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
-- Table structure for table `emails`
--

DROP TABLE IF EXISTS `emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `actual_from` date NOT NULL DEFAULT '0000-00-00',
  `actual_to` date NOT NULL DEFAULT '0000-00-00',
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
-- Table structure for table `event_queue`
--

DROP TABLE IF EXISTS `event_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_queue` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `event` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `param` varchar(255) NOT NULL,
  `status` enum('plan','ok','error','stop') NOT NULL DEFAULT 'plan',
  `iteration` smallint(6) NOT NULL DEFAULT '0',
  `next_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `log_error` text NOT NULL,
  `code` char(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `is_handled` (`status`) USING BTREE,
  KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=58247 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  PRIMARY KEY (`model`,`model_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `lk_client_settings`
--

DROP TABLE IF EXISTS `lk_client_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lk_client_settings` (
  `client_id` int(11) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `min_balance` decimal(8,2) NOT NULL,
  `min_balance_sent` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_min_balance_sent` int(4) NOT NULL DEFAULT '0',
  `day_limit` decimal(8,2) NOT NULL,
  `day_limit_sent` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_day_limit_sent` int(4) NOT NULL DEFAULT '0',
  `zero_balance_sent` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_zero_balance_sent` int(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=koi8r ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `subject` text NOT NULL,
  `message` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3343 DEFAULT CHARSET=koi8r ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `event` enum('add_pay_notif','day_limit','zero_balance','prebil_prepayers_notif','min_balance') DEFAULT NULL,
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
-- Table structure for table `lk_notice_settings`
--

DROP TABLE IF EXISTS `lk_notice_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lk_notice_settings` (
  `client_contact_id` int(11) NOT NULL DEFAULT '0',
  `client_id` int(11) NOT NULL DEFAULT '0',
  `min_balance` tinyint(1) NOT NULL DEFAULT '0',
  `day_limit` tinyint(1) NOT NULL DEFAULT '0',
  `add_pay_notif` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('working','connecting') NOT NULL DEFAULT 'connecting',
  `activate_code` varchar(10) NOT NULL,
  PRIMARY KEY (`client_contact_id`,`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=koi8r ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `type` enum('t2t','mcn') NOT NULL DEFAULT 'mcn',
  `is_bonus_added` tinyint(4) NOT NULL DEFAULT '0',
  `is_on` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`contract_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` varchar(255) DEFAULT NULL,
  `fields_changes` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `k_service` (`id_service`,`service`),
  KEY `ts` (`ts`)
) ENGINE=InnoDB AUTO_INCREMENT=98666 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  `type` enum('msg','fields','company_name') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'msg',
  `apply_ts` date NOT NULL DEFAULT '0000-00-00',
  `is_overwrited` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'no',
  `is_apply_set` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'yes',
  `comment2` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=92589 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `length` int(4) NOT NULL DEFAULT '0',
  KEY `idx` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `log_send_voip_settings`
--

DROP TABLE IF EXISTS `log_send_voip_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_send_voip_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `phones` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `client` (`client`,`date`)
) ENGINE=InnoDB AUTO_INCREMENT=3794 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
  KEY `id_service` (`id_service`,`service`)
) ENGINE=InnoDB AUTO_INCREMENT=38599 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_log_tarif_after_ins_tr` AFTER INSERT ON `log_tarif`
FOR EACH ROW BEGIN
     IF NEW.service = 'usage_voip' THEN
         call z_sync_postgres('log_tarif', NEW.id);
     END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_log_tarif_after_upd_tr` AFTER UPDATE ON `log_tarif`
FOR EACH ROW BEGIN
     IF NEW.service = 'usage_voip' THEN
         call z_sync_postgres('log_tarif', NEW.id);
     END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_log_tarif_after_del_tr` AFTER DELETE ON `log_tarif`
FOR EACH ROW BEGIN
     IF OLD.service = 'usage_voip' THEN
         call z_sync_postgres('log_tarif', OLD.id);
     END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
-- Table structure for table `mail_letter`
--

DROP TABLE IF EXISTS `mail_letter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mail_letter` (
  `job_id` int(11) NOT NULL,
  `client` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `send_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `send_message` text NOT NULL,
  `letter_state` enum('error','ready','sent') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'ready',
  PRIMARY KEY (`job_id`,`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `object_type` enum('bill','PM','assignment','order','notice','invoice','akt','new_director_info','upd','lading','notice_mcm_telekom') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'bill',
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
-- Table structure for table `newbill_change_log`
--

DROP TABLE IF EXISTS `newbill_change_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbill_change_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `stage_id` int(10) unsigned NOT NULL DEFAULT '0',
  `action` enum('add','delete','change') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'add',
  `code_1c` varchar(100) NOT NULL DEFAULT '',
  `item` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `k_bill_no` (`bill_no`)
) ENGINE=InnoDB AUTO_INCREMENT=88069 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `date_from` date NOT NULL DEFAULT '0000-00-00',
  `date_to` date NOT NULL DEFAULT '0000-00-00',
  `type` enum('service','zalog','zadatok','good','all4net') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'service',
  `gtd` varchar(255) NOT NULL DEFAULT '',
  `contry_maker` varchar(255) NOT NULL DEFAULT '',
  `country_id` int(4) NOT NULL DEFAULT '0',
  `tax_rate` int(11) DEFAULT NULL,
  `sum_without_tax` decimal(11,2) DEFAULT NULL,
  `sum_tax` decimal(11,2) DEFAULT NULL,
  PRIMARY KEY (`pk`),
  UNIQUE KEY `bill_sort` (`bill_no`,`sort`),
  KEY `service` (`service`,`id_service`),
  CONSTRAINT `newbill_lines__bill_no` FOREIGN KEY (`bill_no`) REFERENCES `newbills` (`bill_no`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1605425 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `last_send` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `message` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill` (`bill_no`)
) ENGINE=InnoDB AUTO_INCREMENT=5936 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newbill_sms`
--

DROP TABLE IF EXISTS `newbill_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbill_sms` (
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `sms_sender` varchar(20) NOT NULL DEFAULT '',
  `sms_send` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sms_get_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `k_bill_no` (`bill_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `newbills`
--

DROP TABLE IF EXISTS `newbills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newbills` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `bill_date` date NOT NULL DEFAULT '0000-00-00',
  `client_id` int(11) NOT NULL DEFAULT '0',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD',
  `is_approved` tinyint(4) DEFAULT NULL,
  `sum` decimal(11,2) DEFAULT '0.00',
  `sum_with_unapproved` decimal(11,2) DEFAULT NULL,
  `price_include_vat` tinyint(4) NOT NULL DEFAULT '0',
  `is_payed` tinyint(1) DEFAULT '0' COMMENT '0 - ?????????, 1 - ????????? ???????, 2 - ?? ?????????, 3 - ???? ?????? ???? ??????',
  `inv2to1` tinyint(1) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `postreg` date NOT NULL DEFAULT '0000-00-00',
  `courier_id` int(4) unsigned NOT NULL DEFAULT '0',
  `nal` enum('beznal','nal','prov') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `sync_1c` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'no',
  `push_1c` enum('yes','no') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT 'yes',
  `state_1c` varchar(32) NOT NULL DEFAULT 'Новый',
  `is_rollback` tinyint(4) NOT NULL DEFAULT '0',
  `editor` enum('stat','admin') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'stat',
  `is_lk_show` tinyint(4) NOT NULL DEFAULT '1',
  `doc_date` date NOT NULL DEFAULT '0000-00-00',
  `is_user_prepay` tinyint(4) NOT NULL DEFAULT '0',
  `bill_no_ext` varchar(32) NOT NULL DEFAULT '',
  `bill_no_ext_date` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill_no` (`bill_no`) USING BTREE,
  KEY `client_id` (`client_id`),
  KEY `bill_date` (`bill_date`),
  KEY `courier_id` (`courier_id`),
  KEY `is_user_prepay` (`is_user_prepay`)
) ENGINE=InnoDB AUTO_INCREMENT=396266 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `insert_newbills_after` AFTER INSERT ON `newbills`
FOR EACH ROW call add_event('newbills__insert', NEW.bill_no) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_newbills_after` AFTER UPDATE ON `newbills`
FOR EACH ROW begin 
call add_event('newbills__update', NEW.bill_no);
if OLD.doc_date <> NEW.doc_date THEN
     call add_event('doc_date_changed', concat(NEW.bill_no, "|", OLD.doc_date ,"|", NEW.doc_date));
end if;
end */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `delete_newbills` BEFORE DELETE ON `newbills`
FOR EACH ROW call add_event('newbills__delete', OLD.bill_no) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
  `sms_send` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sms_sender` varchar(16) NOT NULL DEFAULT '',
  `sms_get_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `line_owner` varchar(255) NOT NULL DEFAULT '',
  `metro_id` int(4) NOT NULL DEFAULT '0',
  `logistic` enum('none','selfdeliv','courier','auto','tk') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'none',
  `store_id` char(36) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '8e5c7b22-8385-11df-9af5-001517456eb1',
  PRIMARY KEY (`bill_no`),
  KEY `k_order_mail_id` (`order_mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `payment_date` date NOT NULL DEFAULT '0000-00-00',
  `oper_date` date NOT NULL DEFAULT '0000-00-00',
  `payment_rate` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `type` enum('bank','prov','ecash','neprov') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'bank',
  `ecash_operator` enum('uniteller','cyberplat','paypal','yandex') DEFAULT NULL,
  `sum` decimal(11,2) NOT NULL DEFAULT '0.00',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB',
  `original_sum` decimal(11,2) DEFAULT NULL,
  `original_currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `comment` varchar(255) NOT NULL,
  `add_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `add_user` int(11) NOT NULL DEFAULT '0',
  `bank` enum('citi','mos','ural','sber','raiffeisen') NOT NULL DEFAULT 'mos',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`,`payment_no`),
  KEY `bill_no_2` (`bill_no`,`bill_vis_no`),
  KEY `bill_no` (`bill_no`),
  KEY `bill_vis_no` (`bill_vis_no`)
) ENGINE=InnoDB AUTO_INCREMENT=292559 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `add_pay_notif` AFTER INSERT ON `newpayments` FOR EACH ROW BEGIN
     call add_event('add_payment', concat(NEW.id, "|", NEW.client_id));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
-- Table structure for table `onlime_delivery`
--

DROP TABLE IF EXISTS `onlime_delivery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `onlime_delivery` (
  `bill_no` char(11) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `delivery_date` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`bill_no`),
  KEY `k_delivery` (`delivery_date`),
  KEY `k_bill_no` (`bill_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `lang_code` varchar(5) NOT NULL DEFAULT 'ru',
  `is_simple_tax_system` tinyint(1) NOT NULL DEFAULT '0',
  `vat_rate` smallint(6) NOT NULL DEFAULT '0',
  `name` varchar(250) NOT NULL,
  `full_name` varchar(250) DEFAULT NULL,
  `legal_address` varchar(250) DEFAULT NULL,
  `post_address` varchar(250) DEFAULT NULL,
  `registration_id` varchar(250) DEFAULT NULL,
  `tax_registration_id` varchar(32) DEFAULT NULL,
  `tax_registration_reason` varchar(12) DEFAULT NULL,
  `bank_account` varchar(128) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_correspondent_account` varchar(64) DEFAULT NULL,
  `bank_bik` varchar(20) DEFAULT NULL,
  `bank_swift` varchar(11) DEFAULT NULL,
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
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_organization_after_ins_tr` AFTER INSERT ON `organization`
FOR EACH ROW BEGIN
     call z_sync_postgres('organization', NEW.id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_organization_after_upd_tr` AFTER UPDATE ON `organization`
FOR EACH ROW BEGIN

  if NEW.actual_from <> OLD.actual_from 
        or
       NEW.actual_to <> OLD.actual_to 
        or
       NEW.vat_rate <> OLD.vat_rate
        or
       NEW.organization_id <> OLD.organization_id
  then
     call z_sync_postgres('organization', NEW.id);
  end if;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_organization_after_del_tr` AFTER DELETE ON `organization`
FOR EACH ROW BEGIN
     call z_sync_postgres('organization', OLD.id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
-- Table structure for table `paypal_payment`
--

DROP TABLE IF EXISTS `paypal_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paypal_payment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `client_id` int(11) NOT NULL,
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
-- Table structure for table `person`
--

DROP TABLE IF EXISTS `person`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `person` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name_nominative` varchar(250) NOT NULL,
  `name_genitive` varchar(150) NOT NULL,
  `post_nominative` varchar(150) NOT NULL,
  `post_genitive` varchar(250) NOT NULL,
  `signature_file_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `full_info` (`name_nominative`,`post_nominative`,`signature_file_name`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `product_state`
--

DROP TABLE IF EXISTS `product_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_state` (
  `product` enum('vpbx','phone') NOT NULL DEFAULT 'phone',
  `client_id` int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY `client_id` (`client_id`,`product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `saldo`
--

DROP TABLE IF EXISTS `saldo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saldo` (
  `client` varchar(32) NOT NULL DEFAULT '',
  `date_of_last_saldo` date DEFAULT '0000-00-00',
  `fix_saldo` decimal(7,2) DEFAULT NULL,
  `saldo` decimal(7,2) DEFAULT NULL,
  `non_count` decimal(7,2) DEFAULT NULL,
  `zalog` decimal(7,2) DEFAULT NULL,
  `comment` tinytext CHARACTER SET latin1,
  PRIMARY KEY (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sale_channels`
--

DROP TABLE IF EXISTS `sale_channels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sale_channels` (
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
-- Table structure for table `send_assigns`
--

DROP TABLE IF EXISTS `send_assigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `send_assigns` (
  `client` varchar(50) NOT NULL DEFAULT '',
  `id_letter` int(11) NOT NULL DEFAULT '0',
  `last_send` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` enum('error','ready','sent') NOT NULL DEFAULT 'error',
  `message` text NOT NULL,
  PRIMARY KEY (`client`,`id_letter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `last_send` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `message` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill` (`client`,`bill_no`)
) ENGINE=InnoDB AUTO_INCREMENT=1347 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `last_send` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `message` blob NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UN` (`client`,`year`,`month`,`port_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `status` enum('open','done','closed') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
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
-- Table structure for table `sync_welltime_stages`
--

DROP TABLE IF EXISTS `sync_welltime_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_welltime_stages` (
  `last_stage_id` int(10) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `status` enum('public','special','archive','itpark') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public',
  `edit_user` int(11) NOT NULL DEFAULT '0',
  `edit_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `okvd_code` int(4) NOT NULL DEFAULT '0',
  `price_include_vat` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=385 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `status` enum('public','special','archive','adsl_su') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public',
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
  `connection_point_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('public','special','archive') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `activation_fee` decimal(10,2) NOT NULL,
  `periodical_fee` decimal(10,2) NOT NULL,
  `period` varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `did_group_id` int(11) DEFAULT NULL,
  `old_beauty_level` int(11) DEFAULT NULL,
  `old_prefix` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `edit_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `price_include_vat` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tarifs_virtpbx`
--

DROP TABLE IF EXISTS `tarifs_virtpbx`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tarifs_virtpbx` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` enum('public','special','archive') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public',
  `description` varchar(100) NOT NULL DEFAULT '',
  `period` enum('month') DEFAULT 'month',
  `currency` char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB',
  `price` decimal(13,4) NOT NULL DEFAULT '0.0000',
  `num_ports` int(4) NOT NULL DEFAULT '0',
  `overrun_per_port` decimal(13,4) NOT NULL DEFAULT '0.0000',
  `space` int(4) NOT NULL DEFAULT '0',
  `overrun_per_gb` decimal(13,4) DEFAULT '0.0000',
  `is_record` tinyint(4) NOT NULL DEFAULT '0',
  `is_web_call` tinyint(4) NOT NULL DEFAULT '0',
  `is_fax` tinyint(4) NOT NULL DEFAULT '0',
  `edit_user` int(11) NOT NULL DEFAULT '0',
  `edit_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `price_include_vat` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `status` enum('public','special','archive','7800','test','operator','transit') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public',
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
  `is_testing` tinyint(4) NOT NULL DEFAULT '0',
  `price_include_vat` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `tarif_group` (`tarif_group`)
) ENGINE=InnoDB AUTO_INCREMENT=635 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = koi8r */ ;
/*!50003 SET character_set_results = koi8r */ ;
/*!50003 SET collation_connection  = koi8r_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_tarifs_voip_after_ins_tr` AFTER INSERT ON `tarifs_voip`
  FOR EACH ROW
BEGIN
	call z_sync_postgres('tarifs_voip', NEW.id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = koi8r */ ;
/*!50003 SET character_set_results = koi8r */ ;
/*!50003 SET collation_connection  = koi8r_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_tarifs_voip_after_upd_tr` AFTER UPDATE ON `tarifs_voip`
  FOR EACH ROW
BEGIN
	call z_sync_postgres('tarifs_voip', NEW.id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = koi8r */ ;
/*!50003 SET character_set_results = koi8r */ ;
/*!50003 SET collation_connection  = koi8r_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_tarifs_voip_after_del_tr` AFTER DELETE ON `tarifs_voip`
  FOR EACH ROW
BEGIN
	call z_sync_postgres('tarifs_voip', OLD.id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
-- Table structure for table `tech_cpe`
--

DROP TABLE IF EXISTS `tech_cpe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tech_cpe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '9999-00-00',
  `actual_to` date NOT NULL DEFAULT '9999-00-00',
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
-- Table structure for table `tech_ports`
--

DROP TABLE IF EXISTS `tech_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tech_ports` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `node` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `port_name` varchar(10) NOT NULL DEFAULT '',
  `port_type` enum('backbone','dedicated','pppoe','pptp','hub','adsl','wimax','cdma','adsl_cards','adsl_connect','adsl_karta','adsl_rabota','adsl_terminal','adsl_tranzit1','yota','GPON') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'dedicated',
  `trafcounttype` enum('','flows','counter_smnp','counter_web') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'flows',
  `address` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8687 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tech_routers`
--

DROP TABLE IF EXISTS `tech_routers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tech_routers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '9999-00-00',
  `actual_to` date NOT NULL DEFAULT '9999-00-00',
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
-- Table structure for table `tech_voip_numbers`
--

DROP TABLE IF EXISTS `tech_voip_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tech_voip_numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '0001-01-01',
  `actual_to` date NOT NULL DEFAULT '9999-01-01',
  `number` decimal(20,0) NOT NULL DEFAULT '0',
  `type` enum('public','provider','private') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'public',
  `client` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `remark` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `tt_doer_stages`
--

DROP TABLE IF EXISTS `tt_doer_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_doer_stages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `all4geo_id` varchar(255) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` varchar(50) NOT NULL DEFAULT '',
  `status_text` varchar(100) NOT NULL DEFAULT '',
  `comment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `k_all4geo_id` (`all4geo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6520 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `tt_files`
--

DROP TABLE IF EXISTS `tt_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tt_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trouble_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment` text NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trouble_id` (`trouble_id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `date_edit` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_edit` varchar(50) NOT NULL,
  `comment` text NOT NULL,
  `uspd` varchar(50) NOT NULL,
  `date_start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_finish_desired` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Temporary table structure for view `tt_states_rb`
--

DROP TABLE IF EXISTS `tt_states_rb`;
/*!50001 DROP VIEW IF EXISTS `tt_states_rb`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `tt_states_rb` (
  `id` tinyint NOT NULL,
  `pk` tinyint NOT NULL,
  `name` tinyint NOT NULL,
  `order` tinyint NOT NULL,
  `time_delta` tinyint NOT NULL,
  `folder` tinyint NOT NULL,
  `deny` tinyint NOT NULL,
  `state_1c` tinyint NOT NULL,
  `oso` tinyint NOT NULL,
  `omo` tinyint NOT NULL
) ENGINE=MyISAM */;
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
  `date_creation` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
  `date_close` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
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
  `actual_from` date NOT NULL DEFAULT '9999-00-00',
  `actual_to` date NOT NULL DEFAULT '9999-00-00',
  `address` varchar(255) NOT NULL DEFAULT '',
  `port_id` int(11) DEFAULT NULL,
  `date_last_writeoff` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` enum('connecting','working') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'working',
  `speed_mgts` varchar(32) NOT NULL DEFAULT '',
  `speed_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `amount` int(4) NOT NULL DEFAULT '1',
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB AUTO_INCREMENT=7933 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usage_ip_ppp`
--

DROP TABLE IF EXISTS `usage_ip_ppp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_ip_ppp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actual_from` date NOT NULL DEFAULT '9999-00-00',
  `actual_to` date NOT NULL DEFAULT '9999-00-00',
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
-- Table structure for table `usage_ip_routes`
--

DROP TABLE IF EXISTS `usage_ip_routes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usage_ip_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activation_dt` datetime DEFAULT NULL,
  `expire_dt` datetime DEFAULT NULL,
  `actual_from` date NOT NULL DEFAULT '9999-00-00',
  `actual_to` date NOT NULL DEFAULT '9999-00-00',
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
-- Temporary table structure for view `usage_sms_gate`
--

DROP TABLE IF EXISTS `usage_sms_gate`;
/*!50001 DROP VIEW IF EXISTS `usage_sms_gate`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `usage_sms_gate` (
  `usage_id` tinyint NOT NULL,
  `client` tinyint NOT NULL,
  `client_id` tinyint NOT NULL,
  `password` tinyint NOT NULL,
  `salt` tinyint NOT NULL,
  `actual_from` tinyint NOT NULL,
  `actual_to` tinyint NOT NULL,
  `sms_max` tinyint NOT NULL,
  `status` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

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
  `actual_from` date NOT NULL DEFAULT '9999-00-00',
  `actual_to` date NOT NULL DEFAULT '9999-00-00',
  `activation_dt` datetime NOT NULL,
  `expire_dt` datetime NOT NULL,
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
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_usage_trunk_after_ins_tr` AFTER INSERT ON `usage_trunk` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk', NEW.id);
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_usage_trunk_after_upd_tr` AFTER UPDATE ON `usage_trunk` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk', NEW.id);
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_usage_trunk_after_del_tr` AFTER DELETE ON `usage_trunk` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk', OLD.id);
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
  PRIMARY KEY (`id`),
  KEY `usage_id_type_order` (`usage_id`,`type`,`order`),
  CONSTRAINT `usage_trunk_settings__usag_id` FOREIGN KEY (`usage_id`) REFERENCES `usage_trunk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_usage_trunk_settings_after_ins_tr` AFTER INSERT ON `usage_trunk_settings` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk_settings', NEW.id);
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_usage_trunk_settings_after_upd_tr` AFTER UPDATE ON `usage_trunk_settings` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk_settings', NEW.id);
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_usage_trunk_settings_after_del_tr` AFTER DELETE ON `usage_trunk_settings` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk_settings', OLD.id);
            END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
  `is_moved` int(1) NOT NULL DEFAULT '0',
  `moved_from` int(11) NOT NULL,
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB AUTO_INCREMENT=3571 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `vpbx_insert` AFTER INSERT ON `usage_virtpbx`
FOR EACH ROW call add_event('usage_virtpbx__insert', concat(NEW.id,'|', NEW.client)) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `vpbx_update` AFTER UPDATE ON `usage_virtpbx`
FOR EACH ROW call add_event('usage_virtpbx__update', concat(NEW.id,'|', NEW.client)) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `vpbx_delete` AFTER DELETE ON `usage_virtpbx`
FOR EACH ROW call add_event('usage_virtpbx__delete', concat(OLD.id,'|', OLD.client)) */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
  `actual_from` date NOT NULL DEFAULT '9999-00-00',
  `actual_to` date NOT NULL DEFAULT '9999-00-00',
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
  `allowed_direction` enum('full','russia','localmob','blocked','local') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'full',
  `one_sip` tinyint(4) NOT NULL DEFAULT '0',
  `line7800_id` int(11) NOT NULL DEFAULT '0',
  `is_moved` int(1) NOT NULL DEFAULT '0',
  `is_moved_with_pbx` int(1) NOT NULL DEFAULT '0',
  `create_params` varchar(1024) NOT NULL,
  `prev_usage_id` int(11) DEFAULT '0',
  `next_usage_id` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `E164` (`E164`),
  KEY `client` (`client`),
  KEY `fk_usage_voip__address_from_datacenter_id` (`address_from_datacenter_id`),
  KEY `line7800_id` (`line7800_id`),
  CONSTRAINT `fk_usage_voip__address_from_datacenter_id` FOREIGN KEY (`address_from_datacenter_id`) REFERENCES `datacenter` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14344 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_usage_voip_after_ins_tr` AFTER INSERT ON `usage_voip`
FOR EACH ROW BEGIN
    call z_sync_postgres('usage_voip', NEW.id);

             call update_voip_number(NEW.E164, NEW.edit_user_id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_usage_voip_after_upd_tr` AFTER UPDATE ON `usage_voip`
FOR EACH ROW BEGIN
                call z_sync_postgres('usage_voip', NEW.id);
                call update_voip_number(NEW.E164, NEW.edit_user_id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = latin1 */ ;
/*!50003 SET character_set_results = latin1 */ ;
/*!50003 SET collation_connection  = latin1_swedish_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `to_postgres_usage_voip_after_del_tr` AFTER DELETE ON `usage_voip`
FOR EACH ROW BEGIN
                call z_sync_postgres('usage_voip', OLD.id);
                call update_voip_number(OLD.E164, OLD.edit_user_id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
  PRIMARY KEY (`id`),
  KEY `client` (`client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  CONSTRAINT `fk_user_users__user_department` FOREIGN KEY (`depart_id`) REFERENCES `user_departs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_user_users__city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_user_users__user_group` FOREIGN KEY (`usergroup`) REFERENCES `user_groups` (`usergroup`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=219 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `use_space` int(11) DEFAULT NULL,
  `numbers` int(11) DEFAULT NULL,
  PRIMARY KEY (`client_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `voip_numbers`
--

DROP TABLE IF EXISTS `voip_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `voip_numbers` (
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
  CONSTRAINT `fk_voip_number__city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_voip_number__did_group_id` FOREIGN KEY (`did_group_id`) REFERENCES `did_group` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Temporary table structure for view `welltime_updates`
--

DROP TABLE IF EXISTS `welltime_updates`;
/*!50001 DROP VIEW IF EXISTS `welltime_updates`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `welltime_updates` (
  `client` tinyint NOT NULL,
  `password` tinyint NOT NULL,
  `version` tinyint NOT NULL
) ENGINE=MyISAM */;
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
-- Table structure for table `z_sync_postgres`
--

DROP TABLE IF EXISTS `z_sync_postgres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `z_sync_postgres` (
  `tbase` enum('nispd','auth','nispd_dev') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `tname` enum('clients','usage_voip','tarifs_voip','log_tarif','usage_trunk','usage_trunk_settings','organization') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `tid` int(11) NOT NULL,
  `rnd` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tbase`,`tname`,`tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'test_all'
--
/*!50003 DROP FUNCTION IF EXISTS `get_tarif_internet` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `get_tarif_internet`(ID int) RETURNS int(11)
    READS SQL DATA
BEGIN
	DECLARE res int;
	SELECT
		log_tarif.id_tarif into res
	from
		log_tarif
	where
		service="usage_ip_ports"
	and
		(id_service=ID)
	and
		date_activation<=NOW()
	and
		id_tarif!=0
	order by
		ts desc
	limit 1;

	return res;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `nvoip_get_phone` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `nvoip_get_phone`(vNumber varbinary(20)) RETURNS int(11)
BEGIN
    DECLARE vId INT;
    DECLARE CONTINUE HANDLER FOR 1329 BEGIN END;
    SELECT phone_id into vId from usage_nvoip_phone WHERE phone_num = vNumber;
    if (vId IS NULL) THEN
        INSERT INTO usage_nvoip_phone (phone_num) VALUES (vNumber);
        SELECT LAST_INSERT_ID() into vId;
    END IF;
    RETURN vId;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `nvoip_get_result` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `nvoip_get_result`(vCause varbinary(30),vTime SMALLINT) RETURNS smallint(6)
BEGIN
    DECLARE vId SMALLINT;
    DECLARE CONTINUE HANDLER FOR 1329 BEGIN END;
    if (vCause='ANSWERED') THEN
        RETURN vTime;
    ELSE
        SELECT id into vId from usage_nvoip_result WHERE param = IFNULL(vCause,"");
        if (vId IS NULL) THEN
            INSERT INTO usage_nvoip_result VALUES (NULL,vCause);
            SELECT LAST_INSERT_ID() into vId;
        END IF;
        RETURN (-vId);
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `nvoip_get_ts_delta` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `nvoip_get_ts_delta`(vTime DATETIME) RETURNS mediumint(9)
    NO SQL
    DETERMINISTIC
RETURN unix_timestamp(vTime)-unix_timestamp(date_format(vTime,'%Y-%m-01')) ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `nvoip_get_ts_month` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `nvoip_get_ts_month`(vTime DATETIME) RETURNS smallint(6)
    NO SQL
    DETERMINISTIC
RETURN (YEAR(vTime)-2000)*12+MONTH(vTime)-1 ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `nvoip_ts2datetime` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `nvoip_ts2datetime`(`ts_month` SMALLINT, `ts_delta` MEDIUMINT) RETURNS datetime
    NO SQL
    DETERMINISTIC
return convert_tz('2000-01-01'+INTERVAL (ts_month) MONTH + INTERVAL ts_delta SECOND,'UTC','Europe/Moscow') ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `nvoip_ts2timestamp` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `nvoip_ts2timestamp`(ts_month SMALLINT,ts_delta MEDIUMINT) RETURNS datetime
    NO SQL
    DETERMINISTIC
RETURN '2000-01-01'+INTERVAL (ts_month) MONTH + INTERVAL ts_delta SECOND ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `add_event` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `add_event`(IN `__event` varchar (32) ,IN `__param` varchar(255))
BEGIN
    declare _code char(32);
    declare _id int(4);
    set _code = md5(concat(__event, "|||", __param));
  select id into _id from event_queue where code = _code and status not in ('ok','stop') limit 1;

  if _id is null then
       insert into event_queue (event,param,code, next_start) values (__event, __param, _code,NOW());
  ELSE
     update event_queue set status='plan', iteration = 0, next_start = NOW() where id = _id;
  end if;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `log` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `log`(IN `_message` varchar(32))
BEGIN
	insert into log (message) value (_message);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `process_usage_nvoip_sess_destination` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `process_usage_nvoip_sess_destination`(in v_first int, in v_last int)
lbl: begin
declare v_step int default v_first;
declare v_step_over int default v_first+59999;
declare v_row_counter int default v_first;
declare v_leave int default 0;

declare v_sess_pk int;
declare v_phone varbinary(15);

declare v_def varbinary(15);
declare v_dgroup tinyint(4);
declare v_dsubgroup tinyint(4);
declare v_num_1 varbinary(1);
declare v_num_2 varbinary(2);
declare v_num_3 varbinary(3);
declare v_num_4 varbinary(4);
declare v_num_5 varbinary(5);
declare v_num_6 varbinary(6);
declare v_num_7 varbinary(7);
declare v_num_8 varbinary(8);
declare v_num_9 varbinary(9);
declare v_num_10 varbinary(10);
declare v_num_11 varbinary(11);
declare v_num_12 varbinary(12);
declare v_num_13 varbinary(13);
declare v_num_14 varbinary(14);
declare v_num_15 varbinary(15);

declare v_cur cursor for select s.id,p.phone_num from usage_nvoip_sess s left join usage_nvoip_phone p on p.phone_id=s.phone_id where s.id between v_step and v_step_over;
declare continue handler for sqlstate '02000' begin end;

if v_step_over > v_last then
set v_step_over = v_last;
end if;

open v_cur;
lp: loop
if v_leave > 0 then
leave lbl;
end if;

fetch v_cur into v_sess_pk,v_phone;

set v_row_counter = v_row_counter+1;
if v_row_counter >= v_step_over then
set v_step = v_step+60000;
set v_step_over = v_step_over+60000;
if v_step_over > v_last then
set v_step_over = v_last;
end if;
if v_step >= v_last then
set v_leave = 1;
leave lbl;
end if;
close v_cur;
open v_cur;
end if;

if length(v_phone)>=10 then
set v_num_1 = substring(v_phone from 1 for 1);
set v_num_2 = substring(v_phone from 1 for 2);
set v_num_3 = substring(v_phone from 1 for 3);
set v_num_4 = substring(v_phone from 1 for 4);
set v_num_5 = substring(v_phone from 1 for 5);
set v_num_6 = substring(v_phone from 1 for 6);
set v_num_7 = substring(v_phone from 1 for 7);
set v_num_8 = substring(v_phone from 1 for 8);
set v_num_9 = substring(v_phone from 1 for 9);
set v_num_10 = substring(v_phone from 1 for 10);
set v_num_11 = substring(v_phone from 1 for 11);
set v_num_12 = substring(v_phone from 1 for 12);
set v_num_13 = substring(v_phone from 1 for 13);
set v_num_14 = substring(v_phone from 1 for 14);
set v_num_15 = substring(v_phone from 1 for 15);

select
`def`,
`dgroup`,
`dsubgroup`
into
`v_def`,
`v_dgroup`,
`v_dsubgroup`
from
`price_voip_groups`
where
(`def`=`v_num_1` and `deflen`=1)
or
(`def`=`v_num_2` and `deflen`=2)
or
(`def`=`v_num_3` and `deflen`=3)
or
(`def`=`v_num_4` and `deflen`=4)
or
(`def`=`v_num_5` and `deflen`=5)
or
(`def`=`v_num_6` and `deflen`=6)
or
(`def`=`v_num_7` and `deflen`=7)
or
(`def`=`v_num_8` and `deflen`=8)
or
(`def`=`v_num_9` and `deflen`=9)
or
(`def`=`v_num_10` and `deflen`=10)
or
(`def`=`v_num_11` and `deflen`=11)
or
(`def`=`v_num_12` and `deflen`=12)
or
(`def`=`v_num_13` and `deflen`=13)
or
(`def`=`v_num_14` and `deflen`=14)
or
(`def`=`v_num_15` and `deflen`=15)
order by
`deflen` desc
limit 1;

insert into
usage_nvoip_sess_destination (`sess_pk`,`def`,`dgroup`,`dsubgroup`)
values
(v_sess_pk,v_def,v_dgroup,v_dsubgroup);
end if;
set v_def = null;
set v_dgroup = null;
set v_dsubgroup = null;
set v_phone = null;
end loop lp;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `recalc_last_account_date` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `recalc_last_account_date`()
BEGIN
	DECLARE done INT DEFAULT 0; 
	DECLARE xxx INT;
	DECLARE cur CURSOR FOR SELECT id FROM clients Order by id;
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

	OPEN cur; 

	read_loop: LOOP
			FETCH cur INTO xxx;
			IF done THEN
				LEAVE read_loop;
			END IF;


			UPDATE clients
					SET last_account_date=(
										select b.bill_date
										from newbills b left join newbill_lines bl on b.bill_no=bl.bill_no
										where b.client_id=xxx and bl.service = 'usage_voip'
										group by b.bill_date
										order by b.bill_date desc
										limit 1)
					WHERE id=xxx;



		END LOOP;

	CLOSE cur;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `update_voip_number` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `update_voip_number`(IN p_number VARCHAR(20), IN p_edit_user_id INT)
BEGIN
		UPDATE voip_numbers n
		LEFT JOIN usage_voip u ON u.E164 = n.number and (now() BETWEEN u.actual_from AND u.actual_to or (actual_from = '2029-01-01' and actual_to = '2029-01-01'))
		LEFT JOIN clients c ON c.client = u.client
		SET n.client_id = c.id, n.usage_id = u.id, n.edit_user_id=p_edit_user_id, n.used_until_date=IFNULL(u.actual_to, n.used_until_date), site_publish = 'N'
		WHERE 
			(n.number=p_number or p_number is null) and 
			(
			(n.usage_id is null and u.id is not null ) or
			(n.usage_id is not null and u.id is null ) or
			(n.usage_id is not null and u.id is not null and n.usage_id<>u.id) or
			(u.actual_to is not null and (n.used_until_date is null or u.actual_to <> n.used_until_date))
			);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `usage_nvoip_sess_insert` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `usage_nvoip_sess_insert`(
        IN vRadAcctId BIGINT(21),
        IN vAcctSessionId VARCHAR(64),
        IN vAcctUniqueId VARCHAR(32),
        IN vUserName VARCHAR(64),
        IN vdirection VARCHAR(3),
        IN vAcctStartTime DATETIME,
        IN vAcctStopTime DATETIME,
        IN vAcctSessionTime INTEGER(12),
        IN vPrefix SMALLINT(6),
        IN vPrefix_dest SMALLINT(6),
        IN vCalledStationId VARCHAR(50),
        IN vCallingStationId VARCHAR(50),
        IN vacctterminatecause VARCHAR(32),
        IN vdisconnect_cause VARCHAR(64)
    )
lbl:begin
	DECLARE vLengthResult SMALLINT;
	DECLARE vUsageId INT;
	DECLARE vPhone VARCHAR(40);
	DECLARE vPhoneId INT;
	DECLARE vTmp INT;
	DECLARE vFlag TINYINT UNSIGNED DEFAULT 0;
	declare vInsertId int;
	DECLARE CONTINUE HANDLER FOR 1329 BEGIN END;

    SET vUsageId = NULL;

	IF vdirection = 'in' THEN
        SET vFlag = 32;
        SET vPhone = vCalledStationId;

        SELECT id INTO vUsageId FROM usage_voip
        WHERE
                E164=vPhone 
            AND (actual_from <= vAcctStartTime AND actual_to >= vAcctStartTime)
        order by
            actual_from DESC
        LIMIT 1;

        SET vPhoneId = nvoip_get_phone(vCallingStationId);
	ELSEIF vdirection = 'out' THEN
        SET vFlag = 16;
        SET vPhone = vCallingStationId;

        SELECT id INTO vUsageId FROM usage_voip
        WHERE 
                E164=vPhone 
            AND ( actual_from <= vAcctStartTime AND actual_to >= vAcctStartTime)
        order by actual_from DESC LIMIT 1;

        SET vPhoneId = nvoip_get_phone(vCalledStationId);
	END IF;

	if vUsageId is null then
        insert into voip_calls_nousage values (
        vRadAcctId,
        vAcctSessionId,
        vAcctUniqueId,
        vUserName,
        vdirection,
        vAcctStartTime,
        vAcctStopTime,
        vAcctSessionTime,
        vPrefix,
        vPrefix_dest,
        vCalledStationId,
        vCallingStationId,
        vacctterminatecause,
        vdisconnect_cause
        );
        leave lbl;
	end if;

    

	
	SET vLengthResult = vAcctSessionTime;
	

	INSERT INTO
	usage_nvoip_sess (
	usage_id,
	phone_id,
	lengthResult,
	ts_month,
	ts_delta,
	ts_full,
	flag,
	tarif_id,
	tarif_sum
	)
	VALUES (
	vUsageId,
	vPhoneId,
	vLengthResult,
	nvoip_get_ts_month(vAcctStartTime),
	nvoip_get_ts_delta(vAcctStartTime),
	vAcctStartTime,
	vFlag,
	0,
	0
	);

	select last_insert_id() into vInsertId;

	update
        `usage_nvoip_sess_destination`
	set
        `operator` = vPrefix,
        `dest_radius_pref` = vPrefix_dest
	where
        `sess_pk` = vInsertId;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_calc_odefs` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_calc_odefs`(in vDate date)
lbl: begin
drop table if exists voip_tmp_vdca;
drop table if exists voip_tmp_vdcb;


create temporary table voip_tmp_vdca (
pk int unsigned not null,
def varchar(20) not null primary key
) engine = memory;


create temporary table voip_tmp_vdcb (
pk int unsigned not null,
def varchar(20) not null primary key
) engine = memory;


insert into voip_tmp_vdca
select distinct
`vd`.`pk`,
`vd`.`def`
from
`voip_defs` `vd`
inner join
`voip_defs` `vdi`
on
`vd`.`pk` = `vdi`.`pk`
and
`vdi`.`pk` = (select pk from voip_defs where operator_pk=4 and def=vd.def order by activation_date desc limit 1)
and
`vdi`.`active` = 'Y'
where
`vd`.`operator_pk` = 4;


insert into voip_tmp_vdcb
select distinct
`vd`.`pk`,
`vd`.`def`
from
`voip_defs` `vd`
inner join
`voip_defs` `vdi`
on
`vd`.`pk` = `vdi`.`pk`
and
`vdi`.`pk` = (select pk from voip_defs where operator_pk=2 and def=vd.def order by activation_date desc limit 1)
and
`vdi`.`active` = 'Y'
where
`vd`.`operator_pk` = 2;


insert into voip_calls_odefs (call_pk,adef_pk,bdef_pk)
select
vc.pk,
vdca.pk,
vdcb.pk
from
voip_calls vc
left join
usage_nvoip_phone unp
on
unp.phone_id = vc.phone_id
left join
voip_defs vdca
on
vdca.pk = (select pk from voip_tmp_vdca where instr(unp.phone_num,def)=1 order by length(def) desc limit 1)
left join
voip_defs vdcb
on
vdcb.pk = (select pk from voip_tmp_vdcb where instr(unp.phone_num,def)=1 order by length(def) desc limit 1)
where
vc.time between vDate and (vDate + interval 1 day - interval 1 second)
and
vc.direction='out';

drop table if exists voip_tmp_vdca;
drop table if exists voip_tmp_vdcb;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_calls_reaggregate` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_calls_reaggregate`(in vTimeBegin datetime, in vTimeEnd datetime)
lbl: begin
declare vCallPk int;
declare vCallDirection varchar(3);
declare vCurDone int default 0;
declare vCounter int default 0;
declare v_cur cursor for select pk,direction from voip_calls where dcause in ('1F','10') and time between vTimeBegin and vTimeEnd order by time;
declare exit handler for sqlstate '02000' begin commit; end;

call voip_insert_call_create_mem();

open v_cur;

start transaction;

repeat
fetch v_cur into vCallPk,vCallDirection;

if not vCurDone then
if vCallDirection='in' then
call voip_incall_aggregate(vCallPk);
else
call voip_call_mem_aggregate(vCallPk);
end if;

if vCounter = 10000 then
commit;
set vCounter = 0;
start transaction;
else
set vCounter = vCounter+1;
end if;
end if;
until vCurDone end repeat;

close v_cur;
commit;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_call_aggregate` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_call_aggregate`(in vCallPk int)
lbl: begin
declare v_usage_id int;
declare v_phone_id varbinary(30);
declare v_def_pk int;
declare v_def varbinary(30);
declare v_fixormob varchar(3);
declare v_dgroup tinyint;
declare v_dsubgroup tinyint;
declare v_operator_pk tinyint;
declare v_direction varchar(3);
declare v_len int;
declare v_price_mcn decimal(6,2);
declare v_price_mcn_usd decimal(8,4);
declare v_price decimal(8,4);
declare v_price_usd decimal(8,4);
declare v_time datetime;

select
`vc`.`usage_id`,
`vc`.`phone_id`,
`vp`.`pk`,
`vp`.`def`,
`vdd`.`fixormob`,
`vdd`.`dgroup`,
`vdd`.`dsubgroup`,
`vc`.`operator_pk`,
`vc`.`direction`,
`vc`.`len`,
if(
`vdd`.`dgroup`=0 and `vdd`.`fixormob`='fix',
if(
`tv`.`free_local_min`*`uv`.`no_of_lines`*60 - ifnull(`lm`.`len`,0)>0,
0,
if(`vc`.`len`<60, `vp`.`price`,round(`vp`.`price`*`vc`.`len`/60.0,2))
),
if(`vc`.`len`<60, `vp`.`price`,round(`vp`.`price`*`vc`.`len`/60.0,2))
),
if(
`vdd`.`dgroup`=0 and `vdd`.`fixormob`='fix',
if(
`tv`.`free_local_min`*`uv`.`no_of_lines`*60 - ifnull(`lm`.`len`,0)>0,
0,
if(`vc`.`len`<60, `vp`.`price_usd`,round(`vp`.`price_usd`*`vc`.`len`/60.0,if(`vp`.`tarif_group_pk`=5,2,4)))
),
if(`vc`.`len`<60, `vp`.`price_usd`,round(`vp`.`price_usd`*`vc`.`len`/60.0,if(`vp`.`tarif_group_pk`=5,2,4)))
),
round(`vd`.`price_operator`*`vc`.`len`/60.0,4),
round(`vd`.`price_operator_usd`*`vc`.`len`/60.0,4),
`vc`.`time`
into
v_usage_id,
v_phone_id,
v_def_pk,
v_def,
v_fixormob,
v_dgroup,
v_dsubgroup,
v_operator_pk,
v_direction,
v_len,
v_price_mcn,
v_price_mcn_usd,
v_price,
v_price_usd,
v_time
from
`voip_calls` `vc`
left join
`usage_nvoip_phone` `unp`
on
`unp`.`phone_id` = `vc`.`phone_id`
left join
`voip_calls_stats_mosfree` `lm`
on
`lm`.`usage_id`=`vc`.`usage_id`
and
`lm`.`year`=year(`vc`.`time`)
and
`lm`.`month`=month(`vc`.`time`)
inner join
`usage_voip` `uv`
on
`uv`.`id` = `vc`.`usage_id`
inner join
`log_tarif` `lt`
on
`lt`.`service` = 'usage_voip'
and
`lt`.`id_service` = `vc`.`usage_id`
and
`lt`.`id` = (
select
`id`
from
`log_tarif`
where
`service` = 'usage_voip'
and
`id_service` = `vc`.`usage_id`
and
date_activation <= now()
order by
date_activation desc,
ts desc,
id desc
limit 1
)
left join
`tarifs_voip` `tv`
on
`tv`.`id` = `lt`.`id_tarif`
inner join
`voip_prices` `vp`
on
`vp`.`pk` = (
select
`pk`
from
`voip_prices`
where
`tarif_group_pk` = `tv`.`tarif_group`
and
instr(`unp`.`phone_num`,`def`) = 1
order by
length(`def`) desc
limit 1
)
inner join
`voip_defs_destinations` `vdd`
on
`vdd`.`def` = `vp`.`def`
left join
`voip_defs` `vd`
on
`vd`.`pk` = (
select
`pk`
from
`voip_defs`
where
`operator_pk` = `vc`.`operator_pk`
and
`active` = 'Y'
and
instr(`unp`.`phone_num`,`def`) = 1
order by
length(`def`) desc,
`activation_date` desc
limit 1
)
where
`vc`.`pk` = vCallPk;

if v_usage_id is not null then
insert into `voip_calls_stats`
(`call_pk`,`usage_id`,`phone_id`,`def_pk`,`def`,`dgroup`,`dsubgroup`,`fixormob`,`operator_pk`,`direction`,`len`,`price_mcn`,`price_mcn_usd`,`price`,`price_usd`,`time`)
values
(vCallPk,v_usage_id,v_phone_id,v_def_pk,v_def,v_dgroup,v_dsubgroup,v_fixormob,v_operator_pk,v_direction,v_len,v_price_mcn,v_price_mcn_usd,v_price,v_price_usd,v_time);

if v_dgroup = 0 and v_fixormob = 'fix' then
insert into `voip_calls_stats_mosfree` set `usage_id`=v_usage_id,`year`=year(v_time),`month`=month(v_time),`len`=v_len on duplicate key update `len`=`len`+v_len;
end if;
end if;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_call_bill` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_call_bill`(in vCallPk int, in vOperatorPk tinyint, in vPhone varchar(50))
lbl: begin
declare v_dgroup tinyint;
declare v_dsubgroup tinyint;
declare v_def_pk int;
declare v_num_1 varbinary(1);
declare v_num_2 varbinary(2);
declare v_num_3 varbinary(3);
declare v_num_4 varbinary(4);
declare v_num_5 varbinary(5);
declare v_num_6 varbinary(6);
declare v_num_7 varbinary(7);
declare v_num_8 varbinary(8);
declare v_num_9 varbinary(9);
declare v_num_10 varbinary(10);
declare v_num_11 varbinary(11);
declare v_num_12 varbinary(12);
declare v_num_13 varbinary(13);
declare v_num_14 varbinary(14);
declare v_num_15 varbinary(15);

if length(vPhone)>=10 then
set v_num_1 = substring(vPhone from 1 for 1);
set v_num_2 = substring(vPhone from 1 for 2);
set v_num_3 = substring(vPhone from 1 for 3);
set v_num_4 = substring(vPhone from 1 for 4);
set v_num_5 = substring(vPhone from 1 for 5);
set v_num_6 = substring(vPhone from 1 for 6);
set v_num_7 = substring(vPhone from 1 for 7);
set v_num_8 = substring(vPhone from 1 for 8);
set v_num_9 = substring(vPhone from 1 for 9);
set v_num_10 = substring(vPhone from 1 for 10);
set v_num_11 = substring(vPhone from 1 for 11);
set v_num_12 = substring(vPhone from 1 for 12);
set v_num_13 = substring(vPhone from 1 for 13);
set v_num_14 = substring(vPhone from 1 for 14);
set v_num_15 = substring(vPhone from 1 for 15);

select
`def_pk`,
`dgroup`,
`dsubgroup`
into
`v_def_pk`,
`v_dgroup`,
`v_dsubgroup`
from
`voip_defs_current`
where
`operator_pk` = vOperatorPk
and
(
(`def`=`v_num_1` and `deflen`=1)
or
(`def`=`v_num_2` and `deflen`=2)
or
(`def`=`v_num_3` and `deflen`=3)
or
(`def`=`v_num_4` and `deflen`=4)
or
(`def`=`v_num_5` and `deflen`=5)
or
(`def`=`v_num_6` and `deflen`=6)
or
(`def`=`v_num_7` and `deflen`=7)
or
(`def`=`v_num_8` and `deflen`=8)
or
(`def`=`v_num_9` and `deflen`=9)
or
(`def`=`v_num_10` and `deflen`=10)
or
(`def`=`v_num_11` and `deflen`=11)
or
(`def`=`v_num_12` and `deflen`=12)
or
(`def`=`v_num_13` and `deflen`=13)
or
(`def`=`v_num_14` and `deflen`=14)
or
(`def`=`v_num_15` and `deflen`=15)
)
order by
`deflen` desc
limit 1;

if v_def_pk is not null then
insert into
`voip_calls_dests`
set
`call_pk` = vCallPk,
`def_pk` = v_def_pk;

call voip_call_stat_build(vCallPk,v_dgroup,v_dsubgroup);
end if;
end if;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_call_insert` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_call_insert`(
in vRadAcctId bigint(21),
in vAcctSessionId varchar(64),
in vAcctUniqueId varchar(32),
in vUserName varchar(64),
in vDirection varchar(3),
in vAcctStartTime datetime,
in vAcctStopTime datetime,
in vAcctSessionTime int(12),
in vPrefix smallint(6),
in vPrefix_dest smallint(6),
in vCalledStationId varchar(50),
in vCallingStationId varchar(50),
in vAcctterminatecause varchar(32),
in vDisconnectCause varchar(64)
)
lbl: begin
declare vLengthResult smallint;
declare vUsageId int default null;
declare vPhone varchar(40);
declare vPhone_ varchar(40);
declare vPhoneId int;
declare vCallPk int;

if vdirection = 'in' then
set vPhone = vCalledStationId;
set vPhone_ = vCallingStationId;
set vPhoneId = nvoip_get_phone(vCallingStationId);

select
id into vUsageId
from
usage_voip
where
E164=vPhone
and
actual_from <= vAcctStartTime
and
actual_to >= vAcctStartTime
order by
actual_from desc
limit 1;
elseif vdirection = 'out' then
set vPhone = vCallingStationId;
set vPhone_ = vCalledStationId;
set vPhoneId = nvoip_get_phone(vCalledStationId);

select
id into vUsageId
from
usage_voip
where
E164=vPhone
and
actual_from <= vAcctStartTime
and
actual_to >= vAcctStartTime
order by
actual_from desc
limit 1;
else
leave lbl;
end if;

if vUsageId is null then
leave lbl;
end if;

set vLengthResult = vAcctSessionTime;

insert into
`voip_calls`
set
`radacct_id` = vRadAcctId,
`operator_pk` = vPrefix,
`usage_id` = vUsageId,
`phone_id` = vPhoneId,
`len` = vLengthResult,
`time` = vAcctStartTime,
`direction` = vDirection,
`dcause` = vDisconnectCause;

if vDisconnectCause = 10 or vDisconnectCause = '1F' then
set vCallPk = last_insert_id();
if vDirection = 'out' then
call voip_call_bill(vCallPk,vPrefix,vPhone_);
else
call voip_incall_bill(vCallPk);
end if;
end if;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_call_mem_aggregate` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_call_mem_aggregate`(in vCallPk int)
lbl: begin
declare v_usage_id int;
declare v_phone_id varbinary(30);
declare v_def_pk int;
declare v_def varbinary(30);
declare v_fixormob varchar(3);
declare v_dgroup tinyint;
declare v_dsubgroup tinyint;
declare v_operator_pk tinyint;
declare v_direction varchar(3);
declare v_len int;
declare v_price_mcn decimal(6,2);
declare v_price_mcn_usd decimal(8,4);
declare v_price decimal(8,4);
declare v_price_usd decimal(8,4);
declare v_time datetime;

select
`vc`.`usage_id`,
`vc`.`phone_id`,
`vp`.`pk`,
`vp`.`def`,
`vdd`.`fixormob`,
`vdd`.`dgroup`,
`vdd`.`dsubgroup`,
`vc`.`operator_pk`,
`vc`.`direction`,
`vc`.`len`,
if(
`vdd`.`dgroup`=0 and `vdd`.`fixormob`='fix',
if(
`tv`.`free_local_min`*`uv`.`no_of_lines`*60 - ifnull(`lm`.`len`,0)>0,
0,
if(`vc`.`len`<60, `vp`.`price`,round(`vp`.`price`*`vc`.`len`/60.0,2))
),
if(`vc`.`len`<60, `vp`.`price`,round(`vp`.`price`*`vc`.`len`/60.0,2))
),
if(
`vdd`.`dgroup`=0 and `vdd`.`fixormob`='fix',
if(
`tv`.`free_local_min`*`uv`.`no_of_lines`*60 - ifnull(`lm`.`len`,0)>0,
0,
if(`vc`.`len`<60, `vp`.`price_usd`,round(`vp`.`price_usd`*`vc`.`len`/60.0,if(`vp`.`tarif_group_pk`=5,2,4)))
),
if(`vc`.`len`<60, `vp`.`price_usd`,round(`vp`.`price_usd`*`vc`.`len`/60.0,if(`vp`.`tarif_group_pk`=5,2,4)))
),
round(`vd`.`price_operator`*`vc`.`len`/60.0,4),
round(`vd`.`price_operator_usd`*`vc`.`len`/60.0,4),
`vc`.`time`
into
v_usage_id,
v_phone_id,
v_def_pk,
v_def,
v_fixormob,
v_dgroup,
v_dsubgroup,
v_operator_pk,
v_direction,
v_len,
v_price_mcn,
v_price_mcn_usd,
v_price,
v_price_usd,
v_time
from
`voip_calls` `vc`
left join
`usage_nvoip_phone` `unp`
on
`unp`.`phone_id` = `vc`.`phone_id`
left join
`voip_calls_stats_mosfree` `lm`
on
`lm`.`usage_id`=`vc`.`usage_id`
and
`lm`.`year`=year(`vc`.`time`)
and
`lm`.`month`=month(`vc`.`time`)
inner join
`usage_voip` `uv`
on
`uv`.`id` = `vc`.`usage_id`
inner join
`log_tarif` `lt`
on
`lt`.`service` = 'usage_voip'
and
`lt`.`id_service` = `vc`.`usage_id`
and
`lt`.`id` = (
select
`id`
from
`log_tarif`
where
`service` = 'usage_voip'
and
`id_service` = `vc`.`usage_id`
and
date_activation <= now()
order by
date_activation desc,
ts desc,
id desc
limit 1
)
left join
`tarifs_voip` `tv`
on
`tv`.`id` = `lt`.`id_tarif`
inner join
`voip_prices` `vp`
on
`vp`.`pk` = (
select
`pk`
from
`voip_prices`
where
`tarif_group_pk` = `tv`.`tarif_group`
and
instr(`unp`.`phone_num`,`def`) = 1
order by
length(`def`) desc
limit 1
)
inner join
`voip_defs_destinations` `vdd`
on
`vdd`.`def` = `vp`.`def`
left join
`voip_defs` `vd`
on
`vd`.`pk` = if(
`vc`.`operator_pk`=4,
(select `pk` from `voip_tmp_vdca` where instr(`unp`.`phone_num`,`def`) = 1 order by length(`def`) desc limit 1),
if(
`vc`.`operator_pk`=2,
(select `pk` from `voip_tmp_vdcb` where instr(`unp`.`phone_num`,`def`) = 1 order by length(`def`) desc limit 1),
(select `pk` from `voip_defs` where `operator_pk` = `vc`.`operator_pk` and `active` = 'Y' and instr(`unp`.`phone_num`,`def`) = 1 order by length(`def`) desc,`activation_date` desc limit 1)
))
where
`vc`.`pk` = vCallPk;

if v_usage_id is not null then
insert into `voip_calls_stats`
(`call_pk`,`usage_id`,`phone_id`,`def_pk`,`def`,`dgroup`,`dsubgroup`,`fixormob`,`operator_pk`,`direction`,`len`,`price_mcn`,`price_mcn_usd`,`price`,`price_usd`,`time`)
values
(vCallPk,v_usage_id,v_phone_id,v_def_pk,v_def,v_dgroup,v_dsubgroup,v_fixormob,v_operator_pk,v_direction,v_len,v_price_mcn,v_price_mcn_usd,v_price,v_price_usd,v_time);

if v_dgroup = 0 and v_fixormob = 'fix' then
insert into `voip_calls_stats_mosfree` set `usage_id`=v_usage_id,`year`=year(v_time),`month`=month(v_time),`len`=v_len on duplicate key update `len`=`len`+v_len;
end if;
end if;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_call_stat_build` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_call_stat_build`(in vCallPk int, in vDgroup tinyint, in vDsubgroup tinyint)
lbl: begin
declare v_usage_id int;
declare v_phone_id varbinary(30);
declare v_def_pk int;
declare v_def varbinary(30);
declare v_fixormob varchar(3);
declare v_operator_pk tinyint;
declare v_direction varchar(3);
declare v_len int;
declare v_price_mcn decimal(6,2);
declare v_price_mcn_usd decimal(8,4);
declare v_price decimal(8,4);
declare v_price_usd decimal(8,4);
declare v_time datetime;

select
`vc`.`usage_id`,
`vc`.`phone_id`,
`vd`.`pk`,
`vd`.`def`,
`vd`.`fixormob`,
`vd`.`operator_pk`,
`vc`.`direction`,
`vc`.`len`,
if(
`vd`.`dgroup`=0 and `vd`.`fixormob`='fix',
if(
`tv`.`free_local_min`*`uv`.`no_of_lines`*60 - ifnull(`lm`.`len`,0)>0,
0,
if(`vc`.`len`<60, `vp`.`price`,round(`vp`.`price`*`vc`.`len`/60.0,2))
),
if(`vc`.`len`<60, `vp`.`price`,round(`vp`.`price`*`vc`.`len`/60.0,2))
),
if(
`vd`.`dgroup`=0 and `vd`.`fixormob`='fix',
if(
`tv`.`free_local_min`*`uv`.`no_of_lines`*60 - ifnull(`lm`.`len`,0)>0,
0,
if(`vc`.`len`<60, `vp`.`price_usd`,round(`vp`.`price_usd`*`vc`.`len`/60.0,if(`vp`.`tarif_group_pk`=5,2,4)))
),
if(`vc`.`len`<60, `vp`.`price_usd`,round(`vp`.`price_usd`*`vc`.`len`/60.0,if(`vp`.`tarif_group_pk`=5,2,4)))
),
round(`vd`.`price_operator`*`vc`.`len`/60.0,4),
round(`vd`.`price_operator_usd`*`vc`.`len`/60.0,4),
`vc`.`time`
into
v_usage_id,
v_phone_id,
v_def_pk,
v_def,
v_fixormob,
v_operator_pk,
v_direction,
v_len,
v_price_mcn,
v_price_mcn_usd,
v_price,
v_price_usd,
v_time
from
`voip_calls` `vc`
left join
`voip_calls_dests` `vcd`
on
`vcd`.`call_pk` = `vc`.`pk`
left join
`voip_defs` `vd`
on
`vd`.`pk` = `vcd`.`def_pk`
left join
`voip_calls_stats_mosfree` `lm`
on
`lm`.`usage_id`=`vc`.`usage_id`
and
`lm`.`year`=year(`vc`.`time`)
and
`lm`.`month`=month(`vc`.`time`)
inner join
`usage_voip` `uv`
on
`uv`.`id` = `vc`.`usage_id`
inner join
`log_tarif` `lt`
on
`lt`.`service` = 'usage_voip'
and
`lt`.`id_service` = `vc`.`usage_id`
and
`lt`.`id` = (
select
`id`
from
`log_tarif`
where
`service` = 'usage_voip'
and
`id_service` = `vc`.`usage_id`
and
date_activation <= now()
order by
date_activation desc,
ts desc,
id desc
limit 1
)
left join
`tarifs_voip` `tv`
on
`tv`.`id` = `lt`.`id_tarif`
left join
`voip_tarif_groups` `vtgn`
on
`vtgn`.`pk` = `tv`.`tarif_group`
and
`vtgn`.`dgroup` is null
and
`vtgn`.`dsubgroup` is null
left join
`voip_tarif_groups` `vtg`
on
`vtg`.`pk` = `tv`.`tarif_group`
and
`vtg`.`dgroup` = vDgroup
and
`vtg`.`dsubgroup` = vDsubgroup
inner join
`voip_prices` `vp`
on
`vp`.`def` = `vd`.`def`
and
`vp`.`tarif_group_pk` = if(`vtg`.`link_tarif` is null,`vtgn`.`pk`,`vtg`.`link_tarif`)
where
`vc`.`pk` = vCallPk;

if v_usage_id is not null then
insert into `voip_calls_stats`
(`call_pk`,`usage_id`,`phone_id`,`def_pk`,`def`,`dgroup`,`dsubgroup`,`fixormob`,`operator_pk`,`direction`,`len`,`price_mcn`,`price_mcn_usd`,`price`,`price_usd`,`time`)
values
(vCallPk,v_usage_id,v_phone_id,v_def_pk,v_def,vDgroup,vDsubgroup,v_fixormob,v_operator_pk,v_direction,v_len,v_price_mcn,v_price_mcn_usd,v_price,v_price_usd,v_time);

if vDgroup = 0 and v_fixormob = 'fix' then
insert into `voip_calls_stats_mosfree` set `usage_id`=v_usage_id,`year`=year(v_time),`month`=month(v_time),`len`=v_len on duplicate key update `len`=`len`+v_len;
end if;
end if;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_checkToDiasble` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_checkToDiasble`()
begin
    DECLARE done integer default 0;
    DECLARE clientId integer default 0;
    DECLARE nRange char(1) default 'm';
    DECLARE voipLimit integer default 0;
    DECLARE cur1 Cursor FOR 
        select `range`, c.id
        from (
                select 'm' as `range` ,c.client, sum(tarif_sum) sum_price, c.voip_credit_limit price 
                from nispd.usage_nvoip_sess s , usage_voip v, clients c 
                where s.ts_month = (date_format(now(), '%Y')-2000)*12+(date_format(now(), '%m')-1) 
                    and s.usage_id = v.id and c.client = v.client and c.status ='work' 
                    and voip_credit_limit > 0 and !voip_disabled 
                group by c.client 
                having sum_price > voip_credit_limit
                union 
                select 'd' as `range`, c.client, sum(tarif_sum) sum_price, c.voip_credit_limit_day price
                from nispd.usage_nvoip_sess s , usage_voip v, clients c
                where s.ts_month = (date_format(now(), '%Y')-2000)*12+(date_format(now(), '%m')-1) 
                    and ts_delta > 86400*(date_format(now(),'%d')-1)
                    and s.usage_id = v.id and c.client = v.client and c.status ='work'
                    and voip_credit_limit_day > 0 and !voip_disabled
                    group by c.client
                    having sum_price > voip_credit_limit_day
            )a, clients c 
        where a.client=c.client and c.client!='';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
     

    open cur1;
    repeat 
        fetch cur1 into nRange, clientId;
        if not done THEN
            select if(nRange ='m', voip_credit_limit, voip_credit_limit_day) into voipLimit from clients where id = clientId;
            update clients set voip_disabled = 1 where id = clientId;
            insert into log_client set client_id = clientId, user_id = -1, ts=now(), comment = concat('ÏÔËÌÀÞÅÎÁ ÔÅÌÅÆÏÎÉÑ,× Ó×ÑÚÉ Ó ÐÒÅ×ÙÛÅÎÉÅÍ ',if(nRange = 'm', 'ÍÅÓÑÞÎÏÇÏ','ÄÎÅ×ÎÏÇÏ'), ' ÌÉÍÉÔÁ: ', voipLimit);
        end if;
    until done end repeat;
    close cur1;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_incall_aggregate` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_incall_aggregate`(in vCallPk int)
lbl: begin
insert into `voip_calls_stats`
(`call_pk`,`usage_id`,`phone_id`,`def_pk`,`def`,`dgroup`,`dsubgroup`,`fixormob`,`operator_pk`,`direction`,`len`,`price_mcn`,`price_mcn_usd`,`price`,`price_usd`,`time`)
select
`vc`.`pk`,
`vc`.`usage_id`,
`vc`.`phone_id`,
0,0,0,0,'fix',
`vc`.`operator_pk`,
`vc`.`direction`,
`vc`.`len`,
0,0,0,0,
`vc`.`time`
from
`voip_calls` `vc`
where
`vc`.`pk` = vCallPk;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_incall_bill` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_incall_bill`(in vCallPk int)
lbl: begin
insert into `voip_calls_stats`
(`call_pk`,`usage_id`,`phone_id`,`def_pk`,`def`,`dgroup`,`dsubgroup`,`fixormob`,`operator_pk`,`direction`,`len`,`price_mcn`,`price_mcn_usd`,`price`,`price_usd`,`time`)
select
`vc`.`pk`,
`vc`.`usage_id`,
`vc`.`phone_id`,
0,0,0,0,'fix',
`vc`.`operator_pk`,
`vc`.`direction`,
`vc`.`len`,
0,0,0,0,
`vc`.`time`
from
`voip_calls` `vc`
where
`vc`.`pk` = vCallPk;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_insert_call` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_insert_call`(
        in vRadAcctId bigint(21),
        in vAcctSessionId varchar(64),
        in vAcctUniqueId varchar(32),
        in vUserName varchar(64),
        in vDirection varchar(3),
        in vAcctStartTime datetime,
        in vAcctStopTime datetime,
        in vAcctSessionTime int(12),
        in vPrefix smallint(6),
        in vPrefix_dest smallint(6),
        in vCalledStationId varchar(50),
        in vCallingStationId varchar(50),
        in vAcctterminatecause varchar(32),
        in vDisconnectCause varchar(64)
    )
lbl:begin
declare vLengthResult smallint;
declare vUsageId int default null;
declare vPhone varchar(40); 
declare vPhoneId int; 
declare vCallPk int;


if vdirection = 'in' then
set vPhone = vCalledStationId;
set vPhoneId = nvoip_get_phone(vCallingStationId);

select
id into vUsageId
from
usage_voip
where
E164=vPhone
and
actual_from <= vAcctStartTime
and
actual_to >= vAcctStartTime
order by
actual_from desc
limit 1;
elseif vdirection = 'out' then
set vPhone = vCallingStationId;
set vPhoneId = nvoip_get_phone(vCalledStationId);

select
id into vUsageId
from
usage_voip
where
E164=vPhone
and
actual_from <= vAcctStartTime
and
actual_to >= vAcctStartTime
order by
actual_from desc
limit 1;
else
leave lbl;
end if;

if vUsageId is null then
leave lbl;
end if;

set vLengthResult = vAcctSessionTime;

insert into
`voip_calls`
set
`radacct_id` = vRadAcctId,
`operator_pk` = vPrefix,
`usage_id` = vUsageId,
`phone_id` = vPhoneId,
`len` = vLengthResult,
`time` = vAcctStartTime,
`direction` = vDirection,
`dcause` = vDisconnectCause;


  set vCallPk = last_insert_id();
  if vDirection = 'out' then
    call voip_call_aggregate(vCallPk);
  else
    call voip_incall_aggregate(vCallPk);
  end if;

end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `voip_insert_call_create_mem` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `voip_insert_call_create_mem`()
lbl: begin
drop table if exists voip_tmp_vdca;
drop table if exists voip_tmp_vdcb;

create temporary table voip_tmp_vdca (
pk int unsigned not null,
def varchar(20) not null primary key
) engine = memory;


create temporary table voip_tmp_vdcb (
pk int unsigned not null,
def varchar(20) not null primary key
) engine = memory;


insert into voip_tmp_vdca
select distinct
`vd`.`pk`,
`vd`.`def`
from
`voip_defs` `vd`
inner join
`voip_defs` `vdi`
on
`vd`.`pk` = `vdi`.`pk`
and
`vdi`.`pk` = (select pk from voip_defs where operator_pk=4 and def=vd.def order by activation_date desc limit 1)
and
`vdi`.`active` = 'Y'
where
`vd`.`operator_pk` = 4;


insert into voip_tmp_vdcb
select distinct
`vd`.`pk`,
`vd`.`def`
from
`voip_defs` `vd`
inner join
`voip_defs` `vdi`
on
`vd`.`pk` = `vdi`.`pk`
and
`vdi`.`pk` = (select pk from voip_defs where operator_pk=2 and def=vd.def order by activation_date desc limit 1)
and
`vdi`.`active` = 'Y'
where
`vd`.`operator_pk` = 2;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `VOIP_INSERT_CALL_MEM` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `VOIP_INSERT_CALL_MEM`(
        in vRadAcctId bigint(21),
        in vAcctSessionId varchar(64),
        in vAcctUniqueId varchar(32),
        in vUserName varchar(64),
        in vDirection varchar(3),
        in vAcctStartTime datetime,
        in vAcctStopTime datetime,
        in vAcctSessionTime int(12),
        in vPrefix smallint(6),
        in vPrefix_dest smallint(6),
        in vCalledStationId varchar(50),
        in vCallingStationId varchar(50),
        in vAcctterminatecause varchar(32),
        in vDisconnectCause varchar(64)
        )
lbl: begin
declare vLengthResult smallint;
declare vUsageId int default null;
declare vPhone varchar(40); 
declare vPhoneId int; 
declare vCallPk int;

if vdirection = 'in' then

    set vPhone = vCalledStationId;
    set vPhoneId = nvoip_get_phone(vCallingStationId);

    select id into vUsageId
    from usage_voip
    where E164=vPhone
    and actual_from <= vAcctStartTime
    and actual_to >= vAcctStartTime
    order by actual_from desc
    limit 1;

elseif vdirection = 'out' then

    set vPhone = vCallingStationId;
    set vPhoneId = nvoip_get_phone(vCalledStationId);

    select id into vUsageId
    from usage_voip
    where E164=vPhone
    and actual_from <= vAcctStartTime
    and actual_to >= vAcctStartTime
    order by actual_from desc
    limit 1;

end if;

if vUsageId is null then
insert into voip_calls_nousage values (
        vRadAcctId,
        vAcctSessionId,
        vAcctUniqueId,
        vUserName,
        vDirection,
        vAcctStartTime,
        vAcctStopTime,
        vAcctSessionTime,
        vPrefix,
        vPrefix_dest,
        vCalledStationId,
        vCallingStationId,
        vAcctterminatecause,
        vDisconnectCause
        );
    leave lbl;
end if;

set vLengthResult = vAcctSessionTime;

insert into
`voip_calls`
set
`radacct_id` = vRadAcctId,
    `operator_pk` = vPrefix,
    `usage_id` = vUsageId,
    `phone_id` = vPhoneId,
    `len` = vLengthResult,
    `time` = vAcctStartTime,
    `direction` = vDirection,
    `dcause` = vDisconnectCause;

if vDisconnectCause in (10,'1F') then
    set vCallPk = last_insert_id();
    if vDirection = 'out' then
        call voip_call_mem_aggregate(vCallPk);
    else
        call voip_incall_aggregate(vCallPk);
    end if;
end if;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `z_sync_1c` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `z_sync_1c`(IN `p_table` varchar(20) ,IN `p_id` int)
    MODIFIES SQL DATA
BEGIN
    DECLARE Continue HANDLER FOR 1062
    BEGIN
        UPDATE z_sync_1c SET rnd=RAND()*2000000000 WHERE tname=p_table and tid=p_id;

		END;

    INSERT INTO z_sync_1c(tname, tid, rnd) VALUES (p_table, p_id, RAND()*2000000000);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `z_sync_auth` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `z_sync_auth`(IN p_table VARCHAR(20),IN p_id INTEGER(11))
BEGIN

    DECLARE Continue HANDLER FOR 1062
    BEGIN
				UPDATE z_sync_postgres SET rnd=RAND()*2000000000 WHERE tbase='auth' and tname=p_table and tid=p_id;
		END;

		INSERT INTO z_sync_postgres(tbase, tname, tid, rnd) VALUES ('auth', p_table, p_id, RAND()*2000000000);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `z_sync_postgres` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `z_sync_postgres`(IN p_table VARCHAR(20),
        IN p_id INTEGER(11))
BEGIN
    DECLARE Continue HANDLER FOR 1062
    BEGIN
        UPDATE z_sync_postgres SET rnd=RAND()*2000000000 WHERE tbase='nispd' and tname=p_table and tid=p_id;

		END;

    INSERT INTO z_sync_postgres(tbase, tname, tid, rnd) VALUES ('nispd', p_table, p_id, RAND()*2000000000);

    
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `tt_states_rb`
--

/*!50001 DROP TABLE IF EXISTS `tt_states_rb`*/;
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

/*!50001 DROP TABLE IF EXISTS `usage_sms_gate`*/;
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

/*!50001 DROP TABLE IF EXISTS `welltime_updates`*/;
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

-- Dump completed on 2015-09-12 11:47:37
