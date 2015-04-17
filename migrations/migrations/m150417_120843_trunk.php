<?php

class m150417_120843_trunk extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `usage_trunk` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `client_account_id` int(11) NOT NULL,
              `connection_point_id` int(11) NOT NULL,
              `trunk_name` varchar(50) NOT NULL DEFAULT '',
              `actual_from` date NOT NULL DEFAULT '9999-00-00',
              `actual_to` date NOT NULL DEFAULT '9999-00-00',
              `activation_dt` datetime DEFAULT NULL,
              `expire_dt` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `usage_trunk__connection_point_id_trunk_name` (`connection_point_id`,`trunk_name`) USING BTREE,
              KEY `usage_trunk__client_account_id` (`client_account_id`) USING BTREE,
              CONSTRAINT `usage_trunk__connection_point_id` FOREIGN KEY (`connection_point_id`) REFERENCES `regions` (`id`) ON UPDATE CASCADE,
              CONSTRAINT `usage_trunk__client_account_id` FOREIGN KEY (`client_account_id`) REFERENCES `clients` (`id`) ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TRIGGER `to_postgres_usage_trunk_after_ins_tr` AFTER INSERT ON `usage_trunk` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk', NEW.id);
            END;

            CREATE TRIGGER `to_postgres_usage_trunk_after_upd_tr` AFTER UPDATE ON `usage_trunk` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk', NEW.id);
            END;

            CREATE TRIGGER `to_postgres_usage_trunk_after_del_tr` AFTER DELETE ON `usage_trunk` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk', OLD.id);
            END;

            CREATE TABLE `usage_trunk_settings` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `usage_id` int(11) NOT NULL,
              `type` smallint(6) NOT NULL,
              `order` smallint(6) NOT NULL,
              `src_number_id` int(11) DEFAULT NULL,
              `dst_number_id` int(11) DEFAULT NULL,
              `pricelist_id` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `usage_id_type_order` (`usage_id`,`type`,`order`),
              CONSTRAINT `usage_trunk_settings__usag_id` FOREIGN KEY (`usage_id`) REFERENCES `usage_trunk` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

            CREATE TRIGGER `to_postgres_usage_trunk_settings_after_ins_tr` AFTER INSERT ON `usage_trunk_settings` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk_settings', NEW.id);
            END;

            CREATE TRIGGER `to_postgres_usage_trunk_settings_after_upd_tr` AFTER UPDATE ON `usage_trunk_settings` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk_settings', NEW.id);
            END;

            CREATE TRIGGER `to_postgres_usage_trunk_settings_after_del_tr` AFTER DELETE ON `usage_trunk_settings` FOR EACH ROW BEGIN
                call z_sync_postgres('usage_trunk_settings', OLD.id);
            END;

            ALTER TABLE `z_sync_postgres`
            MODIFY COLUMN `tname`  enum('clients','usage_voip','tarifs_voip','log_tarif','usage_trunk','usage_trunk_settings') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `tbase`;
        ");

        $this->execute("
            delete from country where is_main = 0;
        ");

        $this->execute("
            ALTER TABLE `country`
            DROP COLUMN `is_main`,
            DROP INDEX `code`,
            ADD COLUMN `in_use`  tinyint NOT NULL DEFAULT 0 AFTER `name`,
            ADD INDEX `in_use` (`in_use`),
            ADD PRIMARY KEY (`code`);
        ");


        $this->execute("
            update country set in_use = 1 where code in (643,348,276)
        ");

        $this->execute("
            ALTER TABLE `regions`
            ADD COLUMN `country_id`  int NULL AFTER `timezone_name`;
        ");

        $this->execute("
            update `regions` set country_id = 643;
            update `regions` set country_id = 348 where id = 81;
            update `regions` set country_id = 276 where id = 82;
        ");

        $this->execute("
            ALTER TABLE `regions`
            MODIFY COLUMN `country_id`  int(10) NOT NULL AFTER `timezone_name`;
        ");

        $this->execute("
            ALTER TABLE `clients`
            ADD COLUMN `country_id` int(4) NOT NULL DEFAULT '643' AFTER `contragent_id`;
        ");
    }

    public function down()
    {
        echo "m150417_120843_trunk cannot be reverted.\n";

        return false;
    }
}