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
    `contract_type_id` TINYINT(4) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `contragent_id` (`contragent_id`),
    INDEX `super_id` (`super_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

ALTER TABLE `clients` ADD COLUMN `contract_id` INT(4) NOT NULL DEFAULT '0' AFTER `contragent_id`, ADD INDEX `contract_id` (`contract_id`);

SET GLOBAL group_concat_max_len=4294967295;
REPLACE INTO client_contract
    (`id`,`super_id`, `contragent_id`, `number`, `organization`, `manager`, `account_manager`,
        `business_process_id`, `business_process_status_id`, `contract_type_id`)


    SELECT
        c.`id`,
        c.`super_id`,
        c.`contragent_id`,
        cd.`contract_no`,
        c.`firma` AS `organization`,
        c.`manager`,
        c.`account_manager`,
        c.`business_process_id`,
        c.`business_process_status_id`,
        c.`contract_type_id`
        FROM clients c
        LEFT JOIN client_document cd ON cd.`client_id` = c.`id` AND cd.`type` = 'contract' AND cd.`is_active` = 1
;

UPDATE clients SET `contract_id` = `id`;

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
                            IF(lc.`apply_ts` > '2006-01-01', CONCAT(lc.`apply_ts`, ' 00:00:00'), lc.`ts`) AS `create_at`,
                            'update' AS `action`,
                            REPLACE(lcf.`value_from`, '"', '\\"') AS `value_from`,
                            REPLACE(lcf.`value_to`, '"', '\\"') AS `value_to`,
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

REPLACE INTO history_version (
        SELECT
            'ClientContract' AS `model`,
            c.`contract_id` AS `model_id`,
            IF(ISNULL(lc.`ts`), IF(ISNULL(c.`created`), '2006-01-01', DATE_FORMAT(c.`created`, '%Y-%m-%d')),  DATE(lc.`ts`)) AS `date`,
            CONCAT(
               '{',
                    '"super_id":[-super_id-]', cc.`super_id`, '[-/super_id-],',
                    '"contragent_id":[-contragent_id-]', cc.`contragent_id`, '[-/contragent_id-],',
                    '"number":[-number-]', cc.`number`, '[-/number-],',
                    '"organization":[-organization-]', cc.`organization`, '[-/organization-],',
                    '"manager":[-manager-]', cc.`manager`, '[-/manager-],',
                    '"account_manager":[-account_manager-]', cc.`account_manager`, '[-/account_manager-],',
                    '"business_process_id":[-business_process_id-]', cc.`business_process_id`, '[-/business_process_id-],',
                    '"business_process_status_id":[-business_process_status_id-]', cc.`business_process_status_id`, '[-/business_process_status_id-],',
                    '"contract_type_id":[-contract_type_id-]', cc.`contract_type_id`, '[-/contract_type_id-]',
               '}'
            ) AS `data_json`
            FROM client_contract cc
            INNER JOIN clients c ON c.contract_id = cc.id
            LEFT JOIN log_client lc ON lc.`client_id` = c.`id`
				    LEFT JOIN log_client_fields lcf ON lcf.`ver_id` = lc.`id`
            WHERE lc.`type` = 'fields'
							  AND lcf.`field` IN ('super_id', 'contract_id', 'firma','manager','account_manager','business_process_id','business_process_status_id','contract_type_id')
)
;

INSERT INTO history_version
  SELECT hv.`model`, hv.`model_id`, hv.`date`, '' FROM history_version hv
    INNER JOIN clients c ON c.contract_id = hv.model_id
    INNER JOIN (
        SELECT * FROM (
          SELECT
          DATE(IF(lc.`apply_ts` > lc.`ts`, lc.`apply_ts`, lc.`ts`)) AS `date_c`,
          REPLACE(lcf.`value_to`, '"', '\\"') AS `value_to`,
                 IF(lcf.`field` = 'firma', 'organization', lcf.`field`) AS `field_name`,
          lc.client_id
          FROM log_client lc
          LEFT JOIN log_client_fields lcf ON lcf.`ver_id` = lc.`id`

          WHERE lc.`type` = 'fields'
            AND lcf.`field` IN ('super_id', 'contract_id', 'firma','manager','account_manager','business_process_id','business_process_status_id','contract_type_id')
               ORDER BY date_c DESC
        ) d
        GROUP BY `field_name`, `date_c`, `client_id`
        ORDER BY `date_c`
    ) l ON l.`client_id` = c.`id` AND l.`date_c` <= hv.`date`

		WHERE hv.`model` = 'ClientContract'
		ORDER BY hv.`date` DESC
ON DUPLICATE KEY UPDATE history_version.`data_json` = REPLACE(history_version.`data_json`,
  SUBSTRING(history_version.`data_json`,
    LOCATE(CONCAT('[-', l.`field_name` ,'-]'), history_version.`data_json`),
    (LOCATE(CONCAT('[-/', l.`field_name` ,'-]'), history_version.`data_json`) + LENGTH(CONCAT('[-/', l.`field_name` ,'-]')) - LOCATE(CONCAT('[-', l.`field_name` ,'-]'), history_version.`data_json`))
  ),
  CONCAT('[-', l.`field_name` ,'-]',l.`value_to`,'[-/', l.`field_name` ,'-]')
)
;

    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/super_id-]',''),'[-super_id-]','') WHERE `model` = 'ClientContract';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/contragent_id-]',''),'[-contragent_id-]','') WHERE `model` = 'ClientContract';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/number-]','"'),'[-number-]','"') WHERE `model` = 'ClientContract';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/organization-]','"'),'[-organization-]','"') WHERE `model` = 'ClientContract';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/manager-]','"'),'[-manager-]','"') WHERE `model` = 'ClientContract';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/account_manager-]','"'),'[-account_manager-]','"') WHERE `model` = 'ClientContract';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/business_process_id-]',''),'[-business_process_id-]','') WHERE `model` = 'ClientContract';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/business_process_status_id-]',''),'[-business_process_status_id-]','') WHERE `model` = 'ClientContract';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/contract_type_id-]',''),'[-contract_type_id-]','') WHERE `model` = 'ClientContract';

    UPDATE history_changes SET `data_json` = REPLACE(`data_json`,'"null"', 'null') WHERE `model` = 'ClientContract';
    UPDATE history_changes SET `data_json` = REPLACE(`prev_data_json`,'"null"', 'null') WHERE `model` = 'ClientContract';
    UPDATE history_version SET `data_json` = REPLACE(`data_json`,'"null"', 'null') WHERE `model` = 'ClientContract';

    ALTER TABLE `client_statuses`
        CHANGE COLUMN `id_client` `contract_id` INT(11) NOT NULL DEFAULT '0' AFTER `id`,
        CHANGE COLUMN `is_publish` `is_publish` TINYINT(1) NOT NULL DEFAULT '0' AFTER `ts`,
        DROP COLUMN `status`;

    RENAME TABLE `client_statuses` TO `client_contract_comment`;

    DELETE FROM client_contract_comment WHERE `comment` = '' OR `contract_id` = 0;

    ALTER TABLE `client_contract`
        ADD COLUMN `state` ENUM('unchecked','checked_copy','checked_original') NULL DEFAULT 'unchecked' AFTER `contract_type_id`;

    UPDATE `client_contract` SET `state`='checked_original';