ALTER TABLE `client_contragent`
	ADD COLUMN `signer_passport` VARCHAR(20) NOT NULL DEFAULT '' AFTER `fioV`,
	CHANGE COLUMN `opf` `opf` INT NOT NULL DEFAULT '0' AFTER `tax_regime`,
	ADD COLUMN `comment` TEXT NOT NULL AFTER `ogrn`;

ALTER TABLE `client_contragent_person`
ADD COLUMN `mother_maiden_name` VARCHAR(64) NULL AFTER `registration_address`,
ADD COLUMN `birthplace` VARCHAR(255) NULL AFTER `mother_maiden_name`,
ADD COLUMN `birthday` DATE NULL AFTER `birthplace`,
ADD COLUMN `other_document` TEXT NULL AFTER `birthday`;


CREATE TABLE `language` (
	`code` VARCHAR(5) NOT NULL,
	`name` VARCHAR(50) NOT NULL,
	PRIMARY KEY (`code`)
)
	ENGINE=InnoDB
;

INSERT INTO `language` (`code`, `name`) VALUES ('ru-RU', 'Russian');
INSERT INTO `language` (`code`, `name`) VALUES ('hu-HU', 'Magyar');

ALTER TABLE `user_users` ADD COLUMN `language` VARCHAR(5) NOT NULL DEFAULT 'ru-RU' AFTER `timezone_name`;



UPDATE `country` SET `lang`='hu-HU' WHERE  `code`=348;
UPDATE `country` SET `lang`='ru-RU' WHERE  `code`<>348;