<?php

class m150708_000000_number_city extends \app\classes\Migration
{
    public function up()
    {

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
            ALTER TABLE `voip_numbers`
            ADD COLUMN `city_id`  int NULL AFTER `site_publish`,
            ADD COLUMN `did_group_id`  int NULL AFTER `city_id`;
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
        ");
    }

    public function down()
    {
        echo "m150708_000000_number_city cannot be reverted.\n";

        return false;
    }
}