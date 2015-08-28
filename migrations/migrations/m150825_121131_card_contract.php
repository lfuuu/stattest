<?php

class m150825_121131_card_contract extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            RENAME TABLE `client_contract_type` TO `client_contract_business`;

            ALTER TABLE `client_contract`
                CHANGE COLUMN `contract_type_id` `business_id` TINYINT(4) NOT NULL AFTER `business_process_status_id`;

            ALTER TABLE `client_contract_business_process`
                CHANGE COLUMN `contract_type_id` `business_id` INT(11) NULL DEFAULT NULL AFTER `id`;

            CREATE TABLE `client_contract_type` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL,
                `business_process_id` INT(11)NOT NULL,
                PRIMARY KEY (`id`)
            )
            COLLATE='utf8_general_ci'
            ENGINE=InnoDB
            ;

            ALTER TABLE `client_contract`
                CHANGE COLUMN `business_id` `business_id` TINYINT(4) NOT NULL DEFAULT '0' AFTER `account_manager`,
                ADD COLUMN `contract_type_id` TINYINT(4) NOT NULL DEFAULT '0' AFTER `business_process_status_id`,
                CHANGE COLUMN `state` `state` ENUM('unchecked','checked_copy','checked_original', 'offer') NOT NULL DEFAULT 'unchecked' AFTER `contract_type_id`,
                ADD COLUMN `financial_type` ENUM('', 'profitable','consumables','yield-consumable') NOT NULL DEFAULT '' AFTER `state`,
                ADD COLUMN `federal_district` SET('cfd','sfd','nwfd','dfo','sfo','ufo','pfo') NOT NULL DEFAULT '' AFTER `financial_type`;


            INSERT INTO `client_contract_type` (`name`, `business_process_id`) VALUES
                ('Местное присоединение', 11),
                ('Агентский на МГ МН', 11),
                ('Присоединение Зоновых сетей', 11),
                ('Присоединение МГ-сетей', 11),
                ('Присоединение МН-сетей', 11),
                ('Присоединение Зоны МСН к МГ-сети Оператора', 11),
                ('Присоединение МГ-сети МСН к Зоне Оператора', 11),
                ('Межоператорский VoIP', 11),
                ('Абонентский на услуги связи', 11),
                ('Другой', 11),

                ('Размещение', 13),
                ('Каналы связи', 13),
                ('Кроссировки', 13),
                ('Интернет / СПД', 13),
                ('Бронирование ресурсов', 13),
                ('Выдача ТУ', 13),
                ('Аренда ресурсов', 13),

                ('Абонентский на услуги связи', 12),
                ('Присоединение сетей', 12),
                ('Местное присоединение', 12),
                ('Агентский на МГ МН', 12),
                ('Другой', 12)
            ;

            ALTER TABLE `client_contract`
                DROP COLUMN `is_external`;

            ALTER TABLE `client_document`
            	ADD COLUMN `is_external` TINYINT(1) NOT NULL DEFAULT 0 AFTER `type`;

        ");
    }

    public function down()
    {
        echo "m150818_121131_card_contract cannot be reverted.\n";

        return false;
    }
}