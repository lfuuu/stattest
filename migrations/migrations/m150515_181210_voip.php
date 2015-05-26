<?php

class m150515_181210_voip extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `transaction`
            MODIFY COLUMN `source`  enum('stat','bill','payment') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `client_account_id`;
        ");

        $this->execute("
            CREATE TABLE `city` (
              `id` int(10) NOT NULL,
              `name` varchar(50) NOT NULL,
              `country_id` int(11) NOT NULL,
              `connection_point_id` int(11) NULL,
              `voip_number_format` varchar(50) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `fk_city__country_id` (`country_id`),
              CONSTRAINT `fk_city__country_id` FOREIGN KEY (`country_id`) REFERENCES `country` (`code`) ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->execute("
            CREATE TABLE `did_group` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `city_id` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `fk_did_group__city_id` (`city_id`),
              CONSTRAINT `fk_did_group__city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8;
        ");

        $this->execute("
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->execute("
            ALTER TABLE `voip_numbers`
            ADD COLUMN `status` enum('notsell','instock','reserved','active','hold') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'instock' AFTER `number`,
            ADD COLUMN `reserve_from`  datetime NULL AFTER `status`,
            ADD COLUMN `reserve_till`  datetime NULL AFTER `reserve_from`,
            ADD COLUMN `hold_from`  datetime NULL AFTER `reserve_till`,
            ADD COLUMN `city_id`  int NULL AFTER `site_publish`,
            ADD COLUMN `did_group_id`  int NULL AFTER `city_id`,
            DROP COLUMN `is_special`,
            DROP COLUMN `reserved`,
            DROP COLUMN `our`,
            DROP COLUMN `nullcalls_last_2_days`;
        ");

        $this->execute("
            ALTER TABLE `tarifs_voip`
            MODIFY COLUMN `region`  int(11) NOT NULL AFTER `id`,
            ADD COLUMN `country_id`  int NOT NULL DEFAULT 643 AFTER `id`;
        ");

        $this->execute("
            update tarifs_voip set country_id=348, currency='HUF' where region = 81;
        ");

        $this->execute("
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (49, 'Германия', 276, '49 0000 000-000-000', 82);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (361, 'Будапешт', 348, '36 1 000-0000', 81);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (3646, 'Мишкольц', 348, '36 46 000-000', 81);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (3652, 'Дебрецен', 348, '36 52 000-000', 81);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (3662, 'Сегед', 348, '36 62 000-000', 81);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (3672, 'Печ', 348, '36 72 000-000', 81);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (3696, 'Дьёр', 348, '36 96 000-000', 81);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7342, 'Пермь', 643, '7 342 000-00-00', 92);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7343, 'Екатеринбург', 643, '7 343 000-00-00', 95);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7347, 'Уфа', 643, '7 347 000-00-00', 84);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7351, 'Челябинск', 643, '7 351 000-00-00', 90);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7383, 'Новосибирск', 643, '7 383 000-00-00', 94);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7473, 'Воронеж', 643, '7 473 000-00-00', 86);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7495, 'Москва', 643, '7 495 000-00-00', 99);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7812, 'Санкт-Петербург', 643, '7 812 000-00-00', 98);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7831, 'Нижний Новгород', 643, '7 831 000-00-00', 88);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7843, 'Казань', 643, '7 843 000-00-00', 93);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7846, 'Самара', 643, '7 846 000-00-00', 96);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7861, 'Краснодар', 643, '7 861 000-00-00', 97);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (7863, 'Ростов-на-Дону', 643, '7 863 000-00-00', 87);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (74212, 'Хабаровск', 643, '7 4212 00-00-00', 83);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (74232, 'Владивосток', 643, '7 4232 00-00-00', 89);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (74832, 'Брянск', 643, '7 4832 00-00-00', 85);
            INSERT INTO `city` (`id`, `name`, `country_id`, `voip_number_format`, `connection_point_id`) VALUES (78442, 'Волгоград', 643, '7 8442 00-00-00', 91);
        ");

        $this->execute("
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (1, 'Стандартные 495', 7495);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (2, 'Стандартные 499', 7495);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (3, 'Платиновые', 7495);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (4, 'Золотые', 7495);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (5, 'Серебряные', 7495);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (6, 'Бронзовые', 7495);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (7, 'Стандартные', 7812);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (8, 'Платиновые', 7812);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (9, 'Золотые', 7812);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (10, 'Серебряные', 7812);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (11, 'Бронзовые', 7812);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (12, 'Стандартные', 7861);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (13, 'Платиновые', 7861);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (14, 'Золотые', 7861);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (15, 'Серебряные', 7861);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (16, 'Бронзовые', 7861);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (17, 'Стандартные', 7846);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (18, 'Платиновые', 7846);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (19, 'Золотые', 7846);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (20, 'Серебряные', 7846);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (21, 'Бронзовые', 7846);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (22, 'Стандартные', 7343);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (23, 'Платиновые', 7343);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (24, 'Золотые', 7343);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (25, 'Серебряные', 7343);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (26, 'Бронзовые', 7343);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (27, 'Стандартные', 7383);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (28, 'Платиновые', 7383);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (29, 'Золотые', 7383);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (30, 'Серебряные', 7383);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (31, 'Бронзовые', 7383);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (32, 'Стандартные', 7843);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (33, 'Платиновые', 7843);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (34, 'Золотые', 7843);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (35, 'Серебряные', 7843);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (36, 'Бронзовые', 7843);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (37, 'Стандартные', 74232);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (38, 'Платиновые', 74232);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (39, 'Золотые', 74232);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (40, 'Серебряные', 74232);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (41, 'Бронзовые', 74232);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (42, 'Стандартные', 7831);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (43, 'Платиновые', 7831);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (44, 'Золотые', 7831);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (45, 'Серебряные', 7831);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (46, 'Бронзовые', 7831);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (47, 'Стандартные', 7863);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (48, 'Платиновые', 7863);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (49, 'Золотые', 7863);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (50, 'Серебряные', 7863);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (51, 'Бронзовые', 7863);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (52, 'Стандартные', 361);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (53, 'Стандартные', 3646);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (54, 'Стандартные', 3652);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (55, 'Стандартные', 3662);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (56, 'Стандартные', 3672);
            INSERT INTO `did_group` (`id`, `name`, `city_id`) VALUES (57, 'Стандартные', 3696);
        ");

        $this->execute("
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (1, 643, 'RUB', 7495, 99, 'Стандартные 495', 'public', 999.00, 0.00, 'month', 1, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (2, 643, 'RUB', 7495, 99, 'Стандартные 499', 'public', 0.00, 0.00, 'month', 2, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (3, 643, 'RUB', 7495, 99, 'Платиновые', 'public', 999999.00, 0.00, 'month', 3, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (4, 643, 'RUB', 7495, 99, 'Золотые', 'public', 9999.00, 0.00, 'month', 4, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (5, 643, 'RUB', 7495, 99, 'Серебряные', 'public', 5999.00, 0.00, 'month', 5, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (6, 643, 'RUB', 7495, 99, 'Бронзовые', 'public', 1999.00, 0.00, 'month', 6, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (7, 643, 'RUB', 7812, 98, 'Стандартные', 'public', 999.00, 0.00, 'month', 7, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (8, 643, 'RUB', 7812, 98, 'Платиновые', 'public', 999999.00, 0.00, 'month', 8, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (9, 643, 'RUB', 7812, 98, 'Золотые', 'public', 9999.00, 0.00, 'month', 9, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (10, 643, 'RUB', 7812, 98, 'Серебряные', 'public', 5999.00, 0.00, 'month', 10, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (11, 643, 'RUB', 7812, 98, 'Бронзовые', 'public', 1999.00, 0.00, 'month', 11, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (12, 643, 'RUB', 7861, 97, 'Стандартные', 'public', 0.00, 0.00, 'month', 12, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (13, 643, 'RUB', 7861, 97, 'Платиновые', 'public', 999999.00, 0.00, 'month', 13, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (14, 643, 'RUB', 7861, 97, 'Золотые', 'public', 9999.00, 0.00, 'month', 14, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (15, 643, 'RUB', 7861, 97, 'Серебряные', 'public', 5999.00, 0.00, 'month', 15, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (16, 643, 'RUB', 7861, 97, 'Бронзовые', 'public', 1999.00, 0.00, 'month', 16, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (17, 643, 'RUB', 7846, 96, 'Стандартные', 'public', 0.00, 0.00, 'month', 17, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (18, 643, 'RUB', 7846, 96, 'Платиновые', 'public', 999999.00, 0.00, 'month', 18, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (19, 643, 'RUB', 7846, 96, 'Золотые', 'public', 9999.00, 0.00, 'month', 19, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (20, 643, 'RUB', 7846, 96, 'Серебряные', 'public', 5999.00, 0.00, 'month', 20, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (21, 643, 'RUB', 7846, 96, 'Бронзовые', 'public', 1999.00, 0.00, 'month', 21, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (22, 643, 'RUB', 7343, 95, 'Стандартные', 'public', 0.00, 0.00, 'month', 22, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (23, 643, 'RUB', 7343, 95, 'Платиновые', 'public', 999999.00, 0.00, 'month', 23, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (24, 643, 'RUB', 7343, 95, 'Золотые', 'public', 9999.00, 0.00, 'month', 24, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (25, 643, 'RUB', 7343, 95, 'Серебряные', 'public', 5999.00, 0.00, 'month', 25, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (26, 643, 'RUB', 7343, 95, 'Бронзовые', 'public', 1999.00, 0.00, 'month', 26, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (27, 643, 'RUB', 7383, 94, 'Стандартные', 'public', 0.00, 0.00, 'month', 27, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (28, 643, 'RUB', 7383, 94, 'Платиновые', 'public', 999999.00, 0.00, 'month', 28, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (29, 643, 'RUB', 7383, 94, 'Золотые', 'public', 9999.00, 0.00, 'month', 29, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (30, 643, 'RUB', 7383, 94, 'Серебряные', 'public', 5999.00, 0.00, 'month', 30, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (31, 643, 'RUB', 7383, 94, 'Бронзовые', 'public', 1999.00, 0.00, 'month', 31, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (32, 643, 'RUB', 7843, 93, 'Стандартные', 'public', 0.00, 0.00, 'month', 32, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (33, 643, 'RUB', 7843, 93, 'Платиновые', 'public', 999999.00, 0.00, 'month', 33, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (34, 643, 'RUB', 7843, 93, 'Золотые', 'public', 9999.00, 0.00, 'month', 34, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (35, 643, 'RUB', 7843, 93, 'Серебряные', 'public', 5999.00, 0.00, 'month', 35, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (36, 643, 'RUB', 7843, 93, 'Бронзовые', 'public', 1999.00, 0.00, 'month', 36, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (37, 643, 'RUB', 74232, 89, 'Стандартные', 'public', 0.00, 0.00, 'month', 37, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (38, 643, 'RUB', 74232, 89, 'Платиновые', 'public', 999999.00, 0.00, 'month', 38, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (39, 643, 'RUB', 74232, 89, 'Золотые', 'public', 9999.00, 0.00, 'month', 39, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (40, 643, 'RUB', 74232, 89, 'Серебряные', 'public', 5999.00, 0.00, 'month', 39, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (41, 643, 'RUB', 74232, 89, 'Бронзовые', 'public', 1999.00, 0.00, 'month', 41, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (42, 643, 'RUB', 7831, 88, 'Стандартные', 'public', 0.00, 0.00, 'month', 42, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (43, 643, 'RUB', 7831, 88, 'Платиновые', 'public', 999999.00, 0.00, 'month', 43, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (44, 643, 'RUB', 7831, 88, 'Золотые', 'public', 9999.00, 0.00, 'month', 44, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (45, 643, 'RUB', 7831, 88, 'Серебряные', 'public', 5999.00, 0.00, 'month', 45, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (46, 643, 'RUB', 7831, 88, 'Бронзовые', 'public', 1999.00, 0.00, 'month', 46, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (47, 643, 'RUB', 7863, 87, 'Стандартные', 'public', 0.00, 0.00, 'month', 47, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (48, 643, 'RUB', 7863, 87, 'Платиновые', 'public', 999999.00, 0.00, 'month', 48, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (49, 643, 'RUB', 7863, 87, 'Золотые', 'public', 9999.00, 0.00, 'month', 49, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (50, 643, 'RUB', 7863, 87, 'Серебряные', 'public', 5999.00, 0.00, 'month', 50, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (51, 643, 'RUB', 7863, 87, 'Бронзовые', 'public', 1999.00, 0.00, 'month', 51, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (52, 348, 'HUF', 361, 81, 'Standard', 'public', 0.00, 0.00, 'month', 52, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (53, 348, 'HUF', 3646, 81, 'Standard', 'public', 0.00, 0.00, 'month', 53, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (54, 348, 'HUF', 3652, 81, 'Standard', 'public', 0.00, 0.00, 'month', 54, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (55, 348, 'HUF', 3662, 81, 'Standard', 'public', 0.00, 0.00, 'month', 55, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (56, 348, 'HUF', 3672, 81, 'Standard', 'public', 0.00, 0.00, 'month', 56, NULL, NULL);
            INSERT INTO `tarifs_number` (`id`, `country_id`, `currency_id`, `city_id`, `connection_point_id`, `name`, `status`, `activation_fee`, `periodical_fee`, `period`, `did_group_id`, `old_beauty_level`, `old_prefix`) VALUES (57, 348, 'HUF', 3696, 81, 'Standard', 'public', 0.00, 0.00, 'month', 57, NULL, NULL);
        ");

        $this->execute("
            update voip_numbers set city_id = 7495  where region = 99;
            update voip_numbers set city_id = 7812  where region = 98;
            update voip_numbers set city_id = 7861  where region = 97;
            update voip_numbers set city_id = 7846  where region = 96;
            update voip_numbers set city_id = 7343  where region = 95;
            update voip_numbers set city_id = 7383  where region = 94;
            update voip_numbers set city_id = 7843  where region = 93;
            update voip_numbers set city_id = 7342  where region = 92;
            update voip_numbers set city_id = 78442 where region = 91;
            update voip_numbers set city_id = 7351  where region = 90;
            update voip_numbers set city_id = 74232 where region = 89;
            update voip_numbers set city_id = 7831  where region = 88;
            update voip_numbers set city_id = 7863  where region = 87;
            update voip_numbers set city_id = 7473  where region = 86;
            update voip_numbers set city_id = 74832 where region = 85;
            update voip_numbers set city_id = 7347  where region = 84;
            update voip_numbers set city_id = 74212 where region = 83;
            update voip_numbers set city_id = 361   where region = 81 and number like '361%';


            update voip_numbers set did_group_id = 1  where region=99 and number like '7495%' and beauty_level=0;
            update voip_numbers set did_group_id = 2  where region=99 and number like '7499%' and beauty_level=0;
            update voip_numbers set did_group_id = 3  where region=99 and beauty_level=4;
            update voip_numbers set did_group_id = 4  where region=99 and beauty_level=3;
            update voip_numbers set did_group_id = 5  where region=99 and beauty_level=2;
            update voip_numbers set did_group_id = 6  where region=99 and beauty_level=1;
            update voip_numbers set did_group_id = 7  where region=98 and beauty_level=0;
            update voip_numbers set did_group_id = 8  where region=98 and beauty_level=4;
            update voip_numbers set did_group_id = 9  where region=98 and beauty_level=3;
            update voip_numbers set did_group_id = 10 where region=98 and beauty_level=2;
            update voip_numbers set did_group_id = 11 where region=98 and beauty_level=1;
            update voip_numbers set did_group_id = 12 where region=97 and beauty_level=0;
            update voip_numbers set did_group_id = 13 where region=97 and beauty_level=4;
            update voip_numbers set did_group_id = 14 where region=97 and beauty_level=3;
            update voip_numbers set did_group_id = 15 where region=97 and beauty_level=2;
            update voip_numbers set did_group_id = 16 where region=97 and beauty_level=1;
            update voip_numbers set did_group_id = 17 where region=96 and beauty_level=0;
            update voip_numbers set did_group_id = 18 where region=96 and beauty_level=4;
            update voip_numbers set did_group_id = 19 where region=96 and beauty_level=3;
            update voip_numbers set did_group_id = 20 where region=96 and beauty_level=2;
            update voip_numbers set did_group_id = 21 where region=96 and beauty_level=1;
            update voip_numbers set did_group_id = 22 where region=95 and beauty_level=0;
            update voip_numbers set did_group_id = 23 where region=95 and beauty_level=4;
            update voip_numbers set did_group_id = 24 where region=95 and beauty_level=3;
            update voip_numbers set did_group_id = 25 where region=95 and beauty_level=2;
            update voip_numbers set did_group_id = 26 where region=95 and beauty_level=1;
            update voip_numbers set did_group_id = 27 where region=94 and beauty_level=0;
            update voip_numbers set did_group_id = 28 where region=94 and beauty_level=4;
            update voip_numbers set did_group_id = 29 where region=94 and beauty_level=3;
            update voip_numbers set did_group_id = 30 where region=94 and beauty_level=2;
            update voip_numbers set did_group_id = 31 where region=94 and beauty_level=1;
            update voip_numbers set did_group_id = 32 where region=93 and beauty_level=0;
            update voip_numbers set did_group_id = 33 where region=93 and beauty_level=4;
            update voip_numbers set did_group_id = 34 where region=93 and beauty_level=3;
            update voip_numbers set did_group_id = 35 where region=93 and beauty_level=2;
            update voip_numbers set did_group_id = 36 where region=93 and beauty_level=1;
            update voip_numbers set did_group_id = 37 where region=89 and beauty_level=0;
            update voip_numbers set did_group_id = 38 where region=89 and beauty_level=4;
            update voip_numbers set did_group_id = 39 where region=89 and beauty_level=3;
            update voip_numbers set did_group_id = 40 where region=89 and beauty_level=2;
            update voip_numbers set did_group_id = 41 where region=89 and beauty_level=1;
            update voip_numbers set did_group_id = 42 where region=88 and beauty_level=0;
            update voip_numbers set did_group_id = 43 where region=88 and beauty_level=4;
            update voip_numbers set did_group_id = 44 where region=88 and beauty_level=3;
            update voip_numbers set did_group_id = 45 where region=88 and beauty_level=2;
            update voip_numbers set did_group_id = 46 where region=88 and beauty_level=1;
            update voip_numbers set did_group_id = 47 where region=87 and beauty_level=0;
            update voip_numbers set did_group_id = 48 where region=87 and beauty_level=4;
            update voip_numbers set did_group_id = 49 where region=87 and beauty_level=3;
            update voip_numbers set did_group_id = 50 where region=87 and beauty_level=2;
            update voip_numbers set did_group_id = 51 where region=87 and beauty_level=1;
            update voip_numbers set did_group_id = 52 where region=81 and number like '361%';

            update voip_numbers n
			left join (
                    select u.E164 as number, u.id as usage_id from usage_voip u where u.actual_to>=now()
            ) as u on n.number=u.number
            set n.usage_id = u.usage_id;

            update voip_numbers set status='instock', hold_from = null;

            update voip_numbers n
            inner join usage_voip u on u.actual_from<=DATE(now()) and  u.actual_to >= DATE(now()) and u.E164=n.number
            set n.status = 'active';

            update voip_numbers n
            inner join usage_voip u on u.actual_from>DATE(now()) and u.E164=n.number
            set n.status = 'reserved';

            update voip_numbers n
            inner join usage_voip u on n.usage_id=u.id
            inner join clients c on u.client=c.client
            set n.client_id=c.id
            where n.status!='instock' and n.client_id is null;

            update voip_numbers set status = 'notsell' where `status`='instock' and client_id =764;

            update voip_numbers n
            inner join (
                select E164, max(actual_to) actual_to from usage_voip u
                where actual_to < '4000-01-01' and actual_to > DATE_SUB(now(), INTERVAL 6 month)
                group by E164
            ) u on u.E164=n.number
            set status = 'hold', n.hold_from = u.actual_to
            where n.status = 'instock';
        ");

        $this->execute("
            ALTER TABLE `voip_numbers`
            MODIFY COLUMN `city_id` int(11) NOT NULL AFTER `site_publish`,
            ADD CONSTRAINT `fk_voip_number__city_id` FOREIGN KEY (`city_id`) REFERENCES `city` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            ADD CONSTRAINT `fk_voip_number__did_group_id` FOREIGN KEY (`did_group_id`) REFERENCES `did_group` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
        ");

        $this->execute("
            DROP PROCEDURE IF EXISTS e164_stat_append_nullcall;
            DROP TRIGGER `number_update`;
        ");

        $this->execute("
            ALTER TABLE `usage_voip`
            ADD COLUMN `type_id`  enum('number','line','7800','operator') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `client`,
            DROP COLUMN `date_last_writeoff`,
            DROP COLUMN `no_of_callfwd`,
            DROP COLUMN `tmp`;

            update usage_voip set type_id = 'number';
            update usage_voip set type_id = 'line' where LENGTH(E164) >=4 and  LENGTH(E164) <=5 ;
            update usage_voip set type_id = 'operator' where LENGTH(E164) = 3;
            update usage_voip set type_id = '7800' where E164 like '7800%';
        ");


        $this->executeSqlFile('usage_voip.sql');

        $this->execute("
            ALTER TABLE `tarifs_voip`
            ADD COLUMN `is_testing`  tinyint NOT NULL DEFAULT 0 AFTER `is_virtual`;
        ");
    }

    public function down()
    {
        echo "m150515_181210_voip cannot be reverted.\n";

        return false;
    }
}