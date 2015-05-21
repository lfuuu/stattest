<?php

class m150520_180304_exportContracts extends \app\classes\Migration
{
    public function up()
    {
        $sql = <<<SQL
        CREATE TABLE `client_contract` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `super_id` INT(11) NULL DEFAULT NULL,
            `contragent_id` INT(11) NULL DEFAULT NULL,
            `number` VARCHAR(100) NOT NULL,
            `organization` VARCHAR(128) NOT NULL DEFAULT 'mcn' COLLATE 'utf8_bin',
            `manager` VARCHAR(100) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
            `account_manager` VARCHAR(100) NOT NULL DEFAULT '' COLLATE 'utf8_bin',
            `business_process_id` INT(11) NOT NULL DEFAULT '0',
            `business_process_status_id` INT(11) NOT NULL DEFAULT '0',
            `comment` TEXT NOT NULL,
            `contract_type_id` TINYINT(4) NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `contragent_id` (`contragent_id`),
            INDEX `super_id` (`super_id`)
        )
        COLLATE='utf8_general_ci'
        ENGINE=InnoDB;

        ALTER TABLE `clients` ADD COLUMN `contract_id` INT(4) NOT NULL DEFAULT '0' AFTER `contragent_id`, ADD INDEX `contract_id` (`contract_id`);


        SET GLOBAL group_concat_max_len=4294967295;

        INSERT INTO client_contract
            (`super_id`, `contragent_id`, `number`, `organization`, `manager`, `account_manager`,
                `business_process_id`, `business_process_status_id`, `comment`, `contract_type_id`)
            SELECT
                c.`super_id`,
                c.`contragent_id`,
                CONCAT(c.`contragent_id`, '-', DATE_FORMAT(
                    IF(ISNULL(cs.`ts`),
                        IF(ISNULL(c.`created`), '2006-01-01', c.`created`),
                        cs.`ts`),
                '%y')) AS `number`,
                c.`firma` AS `organization`,
                c.`manager`,
                c.`account_manager`,
                c.`business_process_id`,
                c.`business_process_status_id`,
                REPLACE(cs.`comment`, '"', '\\"') AS `comment`,
                c.`contract_type_id`
                FROM clients c
                INNER JOIN (
                    SELECT * FROM client_statuses ORDER BY `ts` DESC
                ) cs ON cs.`id_client` = c.`id`
                GROUP BY c.`id`
        ;


        UPDATE clients c
            INNER JOIN client_contract cc USING(`contragent_id`)
            SET c.`contract_id` = cc.`id`
        ;

        INSERT INTO history_changes
            (`model`, `model_id`, `user_id`, `created_at`, `action`, `data_json`, `prev_data_json`)
            SELECT
                `model`,
                `model_id`,
                `user_id`,
                `create_at`,
                `action`,
                CONCAT('{', `data_json`, '}') AS `data_json`,
                CONCAT('{', `prev_data_json`, '}') AS `prev_data_json`
                FROM
                (
                    SELECT
                        `id`,
                        `model`,
                        `model_id`,
                        `user_id`,
                        `create_at`,
                        `action`,
                      GROUP_CONCAT(CONCAT('"', `field`, '":"', `value_to`, '"') separator ',') AS `data_json`,
                      GROUP_CONCAT(CONCAT('"', `field`, '":"', `value_from`, '"') separator ',') AS `prev_data_json`
                        FROM
                            (
                                SELECT
                                    lc.`id`,
                                    'ClientContract' AS `model`,
                                    c.`contragent_id` AS `model_id`,
                                    lc.`user_id`,
                                    lc.`ts` AS `create_at`,
                                    'update' AS `action`,
                                    lcf.`value_from`,
                                    lcf.`value_to`,
                                    IF(lcf.`field` = 'firma', 'organization', lcf.`field`) AS `field`
                                    FROM
                                    log_client lc
                                    LEFT JOIN log_client_fields lcf ON lcf.`ver_id` = lc.`id`
                                    LEFT JOIN clients c ON c.`id` = lc.`client_id`

                                    WHERE lc.`type` = 'fields' AND lc.`comment` != 'client'
                                      AND lcf.`field` IN ('firma','manager','account_manager','business_process_id','business_process_status_id','contract_type_id')
                                      AND NOT ISNULL(c.`contract_id`)


                            ) n
                            GROUP BY `id`
                ) m
                WHERE NOT ISNULL(`data_json`)
        ;

        REPLACE INTO history_version
            SELECT
                'ClientContract' AS `model`,
                c.`contract_id` AS `model_id`,
                IF(DATE_FORMAT(c.`ts`, '%Y-%m-%d') < '2006-01-01' OR ISNULL(DATE_FORMAT(c.`ts`, '%Y-%m-%d')), '2006-01-01',  DATE_FORMAT(c.`ts`, '%Y-%m-%d'))
                    AS `date`,
                CONCAT(
                   '{',
                        '"super_id":[-super_id-]', c.`super_id`, '[-/super_id-],',
                        '"contragent_id":[-contragent_id-]', c.`contragent_id`, '[-/contragent_id-],',
                        '"number":[-number-]', c.`number`, '[-/number-],',
                        '"organization":[-organization-]', c.`organization`, '[-/organization-],',
                        '"manager":[-manager-]', c.`manager`, '[-/manager-],',
                        '"account_manager":[-account_manager-]', c.`account_manager`, '[-/account_manager-],',
                        '"business_process_id":[-business_process_id-]', c.`business_process_id`, '[-/business_process_id-],',
                        '"business_process_status_id":[-business_process_status_id-]', c.`business_process_status_id`, '[-/business_process_status_id-],',
                        '"comment":[-comment-]', REPLACE(c.`comment`, '"', '\\"'), '[-/comment-],',
                        '"contract_type_id":[-contract_type_id-]', c.`contract_type_id`, '[-/contract_type_id-]',
                   '}'
                ) AS `json_date`
                FROM
                (
                    SELECT
                        c.`id`,
                        c.`contract_id`,
                         c.`super_id`,
                         c.`contragent_id`,
                         IF(ISNULL(cs.`ts`),
                         IF(ISNULL(c.`created`), '2006-01-01', c.`created`),
                         cs.`ts`) AS `ts`,
                         CONCAT(c.`contragent_id`, '-', DATE_FORMAT(
                             IF(ISNULL(cs.`ts`),
                                 IF(ISNULL(c.`created`), '2006-01-01', c.`created`),
                                 cs.`ts`),
                         '%y')) AS `number`,
                         c.`firma` AS `organization`,
                         c.`manager`,
                         c.`account_manager`,
                         c.`business_process_id`,
                         c.`business_process_status_id`,
                         REPLACE(cs.`comment`, '"', '\\"') AS `comment`,
                         c.`contract_type_id`
                         FROM clients c
                         INNER JOIN (
                             SELECT * FROM client_statuses ORDER BY `ts` DESC
                         ) cs ON cs.`id_client` = c.`id`
                         GROUP BY c.`id`
                ) c
        ;

        REPLACE INTO history_version
            SELECT * FROM (
               SELECT hv.`model`, hv.`model_id`, l.`date_c`,
                   REPLACE(hv.`data_json`,
                               SUBSTRING(hv.`data_json`,
                                 LOCATE(CONCAT('[-', l.`field_name` ,'-]'), hv.`data_json`),
                                 (LOCATE(CONCAT('[-/', l.`field_name` ,'-]'), hv.`data_json`) + LENGTH(CONCAT('[-/', l.`field_name` ,'-]')) - LOCATE(CONCAT('[-', l.`field_name` ,'-]'), hv.`data_json`))
                               ),
                               CONCAT('[-', l.`field_name` ,'-]',l.`value_to`,'[-/', l.`field_name` ,'-]')
                                ) AS `data_json`
                   FROM history_version hv
                   INNER JOIN
                   (
                       SELECT * FROM
                           (SELECT * FROM
                               (
                                   SELECT
                                       c.`contract_id`,
                                       DATE(IF(lc.`apply_ts` > lc.`ts`, lc.`apply_ts`, lc.`ts`)) AS `date_c`,
                                       lcf.`value_to`,
                                       IF(lcf.`field` = 'firma', 'organization', lcf.`field`) AS `field_name`
                                       FROM
                                       log_client lc
                                       LEFT JOIN log_client_fields lcf ON lcf.`ver_id` = lc.`id`
                                       LEFT JOIN clients c ON c.`id` = lc.`client_id`

                                       WHERE lc.`type` = 'fields' AND lc.`comment` != 'client'
                                           AND lcf.`field` IN ('super_id', 'contract_id', 'firma','manager','account_manager','business_process_id','business_process_status_id','contract_type_id')
                                           AND NOT ISNULL(c.`contract_id`)
                               ) n
                               ORDER BY `date_c` DESC
                           ) z
                       GROUP BY `contract_id`, `date_c`, `field_name`
                   ) l ON `contract_id` = hv.`model_id`
                   WHERE hv.`model` = 'ClientContract'
                   ORDER BY hv.`date` DESC
            ) m;

        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/super_id-]','"'),'[-super_id-]','"') WHERE `model` = 'ClientContract';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/contragent_id-]','"'),'[-contragent_id-]','"') WHERE `model` = 'ClientContract';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/number-]','"'),'[-number-]','"') WHERE `model` = 'ClientContract';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/organization-]','"'),'[-organization-]','"') WHERE `model` = 'ClientContract';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/manager-]','"'),'[-manager-]','"') WHERE `model` = 'ClientContract';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/account_manager-]','"'),'[-account_manager-]','"') WHERE `model` = 'ClientContract';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/business_process_id-]','"'),'[-business_process_id-]','"') WHERE `model` = 'ClientContract';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/business_process_status_id-]','"'),'[-business_process_status_id-]','"') WHERE `model` = 'ClientContract';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/contract_type_id-]','"'),'[-contract_type_id-]','"') WHERE `model` = 'ClientContract';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/comment-]','"'),'[-comment-]','"') WHERE `model` = 'ClientContract';
SQL;

        $this->execute($sql);
    }

    public function down()
    {
        echo "m150520_180304_exportContracts cannot be reverted.\n";

        return false;
    }
}