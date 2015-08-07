CREATE TABLE `client_contract_business_process` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`contract_type_id` INT(11) NOT NULL,
	`name` VARCHAR(50) NOT NULL,
	`sort` TINYINT(4) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

INSERT INTO client_contract_business_process ( SELECT id, client_contract_id, name,sort FROM grid_business_process);

CREATE TABLE `client_contract_business_process_status` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`business_process_id` INT(11) NOT NULL,
	`name` VARCHAR(50) NOT NULL DEFAULT '',
	`sort` TINYINT(4) NOT NULL DEFAULT '0',
	`oldstatus` VARCHAR(20) NOT NULL DEFAULT '',
	`color` VARCHAR(20) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB
;

DROP TABLE IF EXISTS `grid_business_process_statuses`;
DROP TABLE IF EXISTS `grid_business_process`;

INSERT INTO `nispd`.`client_contract_business_process_status` VALUES
(33,3,'Заказ магазина',0,'once','silver'),

(30,9,'Заказ магазина',0,'income','#CCFFFF'),

(34,10,'Внутренний офис',0,'',''),
(111,10,'Закрытые',1,'',''),

(16,4,'Действующий',0,'once','silver'),

(37,11,'Входящий',0,'income','#CCFFFF'),
(38,11,'Переговоры',1,'negotiations','#C4DF9B'),
(39,11,'Тестирование',2,'testing','#6DCFF6'),
(40,11,'Действующий',3,'work',''),
(107,11,'Ручной счет',4,'','#CCFFFF'),
(41,11,'Приостановлен',5,'suspended','#C4a3C0'),
(42,11,'Расторгнут',6,'closed','#FFFFCC'),
(43,11,'Фрод блокировка',7,'blocked','silver'),
(44,11,'Техотказ',8,'tech_deny','#996666'),
(121,11,'Мусор',9,'trash','#996666'),

(47,12,'Входящий',0,'income','#CCFFFF'),
(48,12,'Переговоры',1,'negotiations','#C4DF9B'),
(49,12,'Тестирование',2,'testing','#6DCFF6'),
(50,12,'Действующий',3,'work',''),
(56,12,'JiraSoft',4,'work',''),
(51,12,'Приостановлен',5,'suspended','#C4a3C0'),
(52,12,'Расторгнут',6,'closed','#FFFFCC'),
(53,12,'Фрод блокировка',7,'blocked','silver'),
(54,12,'Техотказ',8,'tech_deny','#996666'),
(122,12,'Мусор',9,'trash','#996666'),

(62,13,'Входящий',0,'income','#CCFFFF'),
(63,13,'Переговоры',1,'negotiations','#C4DF9B'),
(64,13,'Тестирование',2,'testing','#6DCFF6'),
(65,13,'Действующий',3,'work',''),
(66,13,'Приостановлен',4,'suspended','#C4a3C0'),
(67,13,'Расторгнут',5,'closed','#FFFFCC'),
(68,13,'Фрод блокировка',6,'blocked','silver'),
(69,13,'Техотказ',7,'tech_deny','#996666'),
(123,13,'Мусор',8,'trash','#996666'),

(77,13,'Входящий',0,'income','#CCFFFF'),
(78,13,'Переговоры',1,'negotiations','#C4DF9B'),
(79,13,'Тестирование',2,'testing','#6DCFF6'),
(80,13,'Действующий',3,'work',''),
(81,13,'Приостановлен',4,'suspended','#C4a3C0'),
(82,13,'Расторгнут',5,'closed','#FFFFCC'),
(83,13,'Фрод блокировка',6,'blocked','silver'),
(84,13,'Техотказ',7,'tech_deny','#996666'),
(124,13,'Мусор',8,'trash','#996666'),

(35,8,'Действующий',0,'',''),


(32,5,'Действующий',0,'distr','yellow'),
(36,5,'В стадии переговоров',1,'negotiations','#C4DF9B'),

(108,6,'GPON',0,'distr',''),
(109,6,'ВОЛС',1,'distr',''),
(110,6,'Сервисный',2,'distr',''),
(15,6,'Действующий',3,'distr','yellow'),
(92,6,'Закрытый',4,'closed',''),
(93,6,'Самозакупки',5,'distr',''),
(94,6,'Разовый',6,'distr',''),

(95,15,'Пуско-наладка',0,'connecting',''),
(96,15,'Техобслуживание',1,'work',''),
(97,15,'Без Техобслуживания',2,'work',''),
(98,15,'Приостановленные',3,'suspended',''),
(99,15,'Отказ',4,'deny',''),
(100,15,'Мусор',5,'trash',''),

(19,1,'Заказ услуг',0,'negotiations','#C4DF9B'),
(8,1,'Подключаемые',1,'connecting','#F49AC1'),
(9,1,'Включенные',2,'work',''),
(10,1,'Отключенные',3,'closed','#FFFFCC'),
(22,1,'Мусор',4,'trash','#a5e934'),
(27,1,'Техотказ',5,'tech_deny','#996666'),
(28,1,'Отказ',6,'deny','#A0A0A0'),
(29,1,'Дубликат',7,'double','#60a0e0');