<?php

class m150519_190430_exportClientsLogAndHistory extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contragent`
        	CHANGE COLUMN `tax_regime` `tax_regime` ENUM('simplified','full') NOT NULL DEFAULT 'full' AFTER `fio`;
        	ALTER TABLE `client_contragent` DROP COLUMN `address_post`;
        	ALTER TABLE `client_contragent`
	          ADD COLUMN `positionV` VARCHAR(128) NOT NULL DEFAULT '' AFTER `fio`,
	          ADD COLUMN `fioV` VARCHAR(128) NOT NULL DEFAULT '' AFTER `positionV`;


        ");

        $sql = <<<SQL
        UPDATE client_contragent cc
            LEFT JOIN
                (SELECT
                    `contragent_id`,
                    `super_id`,
                    IF(`company_full` REGEXP '(^([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?))|(([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?)$)',
                        'ip',
                        IF(`type` = 'priv'
                                AND `company_full` NOT REGEXP '(^([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?))|(([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?)$)',
                            'person', 'legal')
                        ) AS `legal_type`,
                    `company`,
                    `company_full`,
                    IF(ISNULL(`address_jur`), '', `address_jur`) AS `address_jur`,
                    IF(ISNULL(`inn`), '', `inn`) AS `inn`,
                    IF(ISNULL(`kpp`), '', `kpp`) AS `kpp`,
                    IF(ISNULL(`signer_position`), '', `signer_position`) AS `signer_position`,
                    IF(ISNULL(`signer_name`), '', `signer_name`) AS `signer_name`,
                    IF(ISNULL(`signer_position`), '', `signer_position`) AS `signer_positionV`,
                    IF(ISNULL(`signer_name`), '', `signer_name`) AS `signer_nameV`,
                    IF(ISNULL(`okpo`), '', `okpo`) AS `okpo`

                    FROM clients)  c
                    ON cc.`id` = c.`contragent_id`

            SET
                cc.`super_id` = c.`super_id`,
                cc.`legal_type` = c.`legal_type`,
                cc.`name` = c.`company`,
                cc.`name_full` = c.`company_full`,
                cc.`address_jur` = c.`address_jur`,
                cc.`inn` = c.`inn`,
                cc.`kpp` = c.`kpp`,
                cc.`position` = c.`signer_position`,
                cc.`fio` = c.`signer_name`,
                cc.`positionV` = c.`signer_positionV`,
                cc.`fioV` = c.`signer_nameV`,
                cc.`okpo` = c.`okpo`
        ;

        SET GLOBAL group_concat_max_len=4294967295;
        INSERT INTO history_changes
            (`model`, `model_id`, `user_id`, `created_at`, `action`, `data_json`, `prev_data_json`)
            SELECT
                `model`,
                `model_id`,
                `user_id`,
                `create_at`,
                `action`,
                CONCAT('{',
                    REPLACE(`data_json`, SUBSTRING(`data_json`,LOCATE('[-type-]', `data_json`),(LOCATE('[-/type-]', `data_json`) + LENGTH('[-/type-]') - LOCATE('[-type-]', `data_json`))),
                        CONCAT('[-type-]',
                            IF(`data_json` REGEXP '(^([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?))|(([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?)$)',
                              'ip',
                              IF(LOCATE('[-type-]priv[-/type-]', `data_json`) > 0
                                      AND `data_json` NOT REGEXP '(^([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?))|(([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?)$)',
                                  'person', 'legal')
                              ),
                           '[-/type-]'
                        )
                    ),
                    '}'
                ) AS `data_json`,
                CONCAT('{',
                    REPLACE(`prev_data_json`, SUBSTRING(`prev_data_json`,LOCATE('[-legal_type-]', `prev_data_json`),(LOCATE('[-/legal_type-]', `prev_data_json`) + LENGTH('[-/legal_type-]') - LOCATE('[-legal_type-]', `prev_data_json`))),
                        CONCAT('[-legal_type-]',
                            IF(`prev_data_json` REGEXP '(^([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?))|(([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?)$)',
                              'ip',
                              IF(LOCATE('[-legal_type-]priv[-/legal_type-]', `prev_data_json`) > 0
                                      AND `prev_data_json` NOT REGEXP '(^([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?))|(([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?)$)',
                                  'person', 'legal')
                              ),
                           '[-/legal_type-]'
                        )
                    ),
                    '}'
                ) AS `prev_data_json`

                FROM
                (
                    SELECT
                        `id`,
                        `model`,
                        `model_id`,
                        `user_id`,
                        `create_at`,
                        `action`,
                      GROUP_CONCAT(CONCAT('"', `field`, '":[-', `field` ,'-]', `value_to`, '[-/', `field` ,'-]') separator ',') AS `data_json`,
                      GROUP_CONCAT(CONCAT('"', `field`, '":[-', `field` ,'-]', `value_from`, '[-/', `field` ,'-]') separator ',') AS `prev_data_json`
                        FROM
                            (
                                SELECT
                                    lc.`id`,
                                    'ClientContragent' AS `model`,
                                    c.`contragent_id` AS `model_id`,
                                    lc.`user_id`,
                                    lc.`ts` AS `create_at`,
                                    'update' AS `action`,
                                    CASE
                                             WHEN lcf.`field` = "signer_name" THEN REPLACE(lcf.`value_from`, '"', '\\"')
                                             WHEN lcf.`field` = "signer_position" THEN REPLACE(lcf.`value_from`, '"', '\\"')
                                             WHEN lcf.`field` = "signer_nameV" THEN REPLACE(lcf.`value_from`, '"', '\\"')
                                             WHEN lcf.`field` = "signer_positionV" THEN REPLACE(lcf.`value_from`, '"', '\\"')
                                             WHEN lcf.`field` = "company" THEN REPLACE(lcf.`value_from`, '"', '\\"')
                                             WHEN lcf.`field` = "company_full" THEN REPLACE(lcf.`value_from`, '"', '\\"')
                                             WHEN lcf.`field` = "address_jur" THEN REPLACE(lcf.`value_from`, '"', '\\"')
                                             WHEN lcf.`field` = "inn" THEN REPLACE(lcf.`value_from`, '"', '\\"')
                                             WHEN lcf.`field` = "kpp" THEN REPLACE(lcf.`value_from`, '"', '\\"')
                                             WHEN lcf.`field` = "okpo" THEN REPLACE(lcf.`value_from`, '"', '\\"')
                                                WHEN lcf.`field` = "nds_zero" THEN IF(lcf.`value_from` = 1, 'simplified', 'full')
                                                WHEN lcf.`field` = "type" THEN lcf.`value_to`
                                          END AS `value_from`,
                                    CASE
                                             WHEN lcf.`field` = "signer_name" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                             WHEN lcf.`field` = "signer_position" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                             WHEN lcf.`field` = "signer_nameV" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                             WHEN lcf.`field` = "signer_positionV" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                             WHEN lcf.`field` = "company" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                             WHEN lcf.`field` = "company_full" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                             WHEN lcf.`field` = "address_jur" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                             WHEN lcf.`field` = "inn" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                             WHEN lcf.`field` = "kpp" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                             WHEN lcf.`field` = "okpo" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                                WHEN lcf.`field` = "nds_zero" THEN IF(lcf.`value_to` = 1, 'simplified', 'full')
                                                WHEN lcf.`field` = "type" THEN lcf.`value_to`
                                          END AS `value_to`,
                                    CASE
                                             WHEN lcf.`field` = "signer_name" THEN "fio"
                                             WHEN lcf.`field` = "signer_position" THEN "position"
                                             WHEN lcf.`field` = "signer_nameV" THEN "fioV"
                                             WHEN lcf.`field` = "signer_positionV" THEN "positionV"
                                             WHEN lcf.`field` = "company" THEN "name"
                                             WHEN lcf.`field` = "company_full" THEN "name_full"
                                             WHEN lcf.`field` = "address_jur" THEN "address_jur"
                                             WHEN lcf.`field` = "inn" THEN "inn"
                                             WHEN lcf.`field` = "kpp" THEN "kpp"
                                             WHEN lcf.`field` = "okpo" THEN "okpo"
                                                WHEN lcf.`field` = "type" THEN "legal_type"
                                                WHEN lcf.`field` = "nds_zero" THEN "tax_regime"
                                          END AS `field`
                                    FROM
                                    log_client lc
                                    LEFT JOIN log_client_fields lcf ON lcf.`ver_id` = lc.`id`
                                    LEFT JOIN clients c ON c.`id` = lc.`client_id`

                                    WHERE lc.`type` = 'fields' AND lc.`comment` != 'client'
                                      AND lcf.`field` IN ('okpo','signer_name', 'signer_position', 'signer_nameV', 'signer_positionV', 'kpp', 'inn', 'address_jur', 'company_full', 'company', 'type', 'nds_zero')
                                      AND NOT ISNULL(c.`contragent_id`)


                            ) n
                            GROUP BY `id`
                ) m
                WHERE NOT ISNULL(`data_json`)
        ;

        REPLACE INTO history_version
                    SELECT * FROM (
                        SELECT
                                'ClientContragent' AS `model`,
                                `contragent_id` AS `model_id`,

                                  IF(lc.`t2` < '2006-01-01' OR ISNULL(lc.`t2`),
                                     IF(lc.`t` < '2006-01-01' OR ISNULL(lc.`t`),
                                       IF(DATE_FORMAT(c.`created`, '%Y-%m-%d') < '2006-01-01' OR ISNULL(DATE_FORMAT(c.`created`, '%Y-%m-%d')), '2006-01-01',  DATE_FORMAT(c.`created`, '%Y-%m-%d')),
                                       lc.`t`
                                   ),
                                     lc.`t2`
                                )
                                  AS `date`,

                                CONCAT(
                                    '{',
                                        '"contragent_id":[-contragent_id-]', `contragent_id`, '[-/contragent_id-],',
                                        '"super_id":[-super_id-]', `super_id`, '[-/super_id-],',
                                        '"legal_type":[-legal_type-]',
                                            IF(`company_full` REGEXP '(^([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?))|(([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?)$)',
                                               'ip',
                                               IF(`type` = 'priv'
                                                       AND `company_full` NOT REGEXP '(^([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?))|(([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?)$)',
                                                   'person', 'legal')
                                               ),
                                        '[-/legal_type-],',
                                        '"name":[-name-]', REPLACE(`company`, '"', '\\"'), '[-/name-],',
                                        '"name_full":[-name_full-]', REPLACE(`company_full`, '"', '\\"'), '[-/name_full-],',
                                        '"address_jur":[-address_jur-]', IF(ISNULL(`address_jur`), '',  REPLACE(`address_jur`, '"', '\\"')), '[-/address_jur-],',
                                        '"inn":[-inn-]', IF(ISNULL(`inn`), '', REPLACE(`inn`, '"', '\\"')), '[-/inn-],',
                                        '"kpp":[-kpp-]', IF(ISNULL(`kpp`), '', REPLACE(`kpp`, '"', '\\"')), '[-/kpp-],',
                                        '"position":[-position-]', IF(ISNULL(`signer_position`), '', REPLACE(`signer_position`, '"', '\\"')), '[-/position-],',
                                        '"fio":[-fio-]', IF(ISNULL(`signer_name`), '', REPLACE(`signer_name`, '"', '\\"')), '[-/fio-],',
                                        '"positionV":[-positionV-]', IF(ISNULL(`signer_positionV`), '', REPLACE(`signer_positionV`, '"', '\\"')), '[-/positionV-],',
                                        '"fio":[-fioV-]', IF(ISNULL(`signer_nameV`), '', REPLACE(`signer_nameV`, '"', '\\"')), '[-/fioV-],',
                                        '"ogrn":[-ogrn-][-/ogrn-],',
                                        '"okvd":[-okvd-][-/okvd-],',
                                        '"opf":[-opf-][-/opf-],',
                                        '"okpo":[-okpo-]', IF(ISNULL(`okpo`), '', REPLACE(`okpo`, '"', '\\"')), '[-/okpo-]',
                                    '}'
                                ) AS `json_date`
                                FROM clients c

                                INNER JOIN
                                (
                                    SELECT client_id,
                                            IF(DATE(`ts`) < '2006-01-01', '2006-01-01',  DATE(`ts`)) AS 't',
                                            IF(DATE(`apply_ts`) < '2006-01-01', '2006-01-01',  DATE(`apply_ts`)) AS 't2'
                                    FROM log_client
                                ) lc ON lc.`client_id` = c.`id`

                                ORDER BY `created` DESC
                    ) z ORDER BY z.`date` DESC
        ;

         REPLACE INTO history_version
                    SELECT * FROM (
                        SELECT hv.`model`, hv.`model_id`, l.`date_c`,
                            REPLACE(hv.`data_json`,
                                        SUBSTRING(hv.`data_json`,
                                          LOCATE(CONCAT('[-', l.`field_name` ,'-]'), hv.`data_json`),
                                          (LOCATE(CONCAT('[-/', l.`field_name` ,'-]'), hv.`data_json`) + LENGTH(CONCAT('[-/', l.`field_name` ,'-]')) - LOCATE(CONCAT('[-', l.`field_name` ,'-]'), hv.`data_json`))
                                        ),
                                        CONCAT(CONCAT('[-', l.`field_name` ,'-]'),
                                            IF(
                                                l.`field_name` = 'legal_type',
                                                IF(hv.`data_json` REGEXP '(^([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?))|(([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?)$)',
                                                  'ip',
                                                  IF(LOCATE('[-legal_type-]priv[-/legal_type-]', hv.`data_json`) > 0
                                                          AND hv.`data_json` NOT REGEXP '(^([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?))|(([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?)$)',
                                                      'person', 'legal')
                                                  ),
                                               l.`value_to`
                                            ),
                                            CONCAT('[-/', l.`field_name` ,'-]')
                                        )

                                    ) AS `data_json`
                            FROM history_version hv
                            INNER JOIN
                            (
                                SELECT * FROM
                                    (SELECT * FROM
                                        (
                                            SELECT
                                                c.`contragent_id`,
                                                DATE(IF(lc.`apply_ts` > lc.`ts`, lc.`apply_ts`, lc.`ts`)) AS `date_c`,
                                                CASE
                                                             WHEN lcf.`field` = "signer_name" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                                             WHEN lcf.`field` = "signer_position" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                                             WHEN lcf.`field` = "signer_nameV" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                                             WHEN lcf.`field` = "signer_positionV" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                                             WHEN lcf.`field` = "company" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                                             WHEN lcf.`field` = "company_full" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                                             WHEN lcf.`field` = "address_jur" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                                             WHEN lcf.`field` = "inn" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                                             WHEN lcf.`field` = "kpp" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                                             WHEN lcf.`field` = "okpo" THEN REPLACE(lcf.`value_to`, '"', '\\"')
                                                                WHEN lcf.`field` = "nds_zero" THEN IF(lcf.`value_to` = 1, 'simplified', 'full')
                                                                WHEN lcf.`field` = "type" THEN lcf.`value_to`
                                                          END AS `value_to`,
                                                             CASE
                                                      WHEN lcf.`field` = "signer_name" THEN "fio"
                                                      WHEN lcf.`field` = "signer_position" THEN "position"
                                                      WHEN lcf.`field` = "signer_nameV" THEN "fioV"
                                                      WHEN lcf.`field` = "signer_positionV" THEN "positionV"
                                                      WHEN lcf.`field` = "company" THEN "name"
                                                      WHEN lcf.`field` = "company_full" THEN "name_full"
                                                      WHEN lcf.`field` = "address_jur" THEN "address_jur"
                                                      WHEN lcf.`field` = "inn" THEN "inn"
                                                      WHEN lcf.`field` = "kpp" THEN "kpp"
                                                      WHEN lcf.`field` = "okpo" THEN "okpo"
                                                      WHEN lcf.`field` = "nds_zero" THEN "tax_regime"
                                                      WHEN lcf.`field` = "type" THEN "legal_type"
                                                  END AS `field_name`
                                                FROM
                                                log_client lc
                                                LEFT JOIN log_client_fields lcf ON lcf.`ver_id` = lc.`id`
                                                LEFT JOIN clients c ON c.`id` = lc.`client_id`

                                                WHERE lc.`type` = 'fields' AND lc.`comment` != 'client'
                                                    AND lcf.`field` IN ('okpo','signer_name', 'signer_position','signer_nameV', 'signer_positionV', 'kpp', 'inn', 'address_jur', 'company_full', 'company', 'type', 'nds_zero')
                                                    AND NOT ISNULL(c.`contragent_id`)
                                        ) n
                                        ORDER BY `date_c` DESC
                                    ) z
                                GROUP BY `contragent_id`, `date_c`, `field_name`
                            ) l ON `contragent_id` = hv.`model_id`
                            WHERE hv.`model` = 'ClientContragent'
                            ORDER BY hv.`date` DESC
                    ) m;

        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/contragent_id-]','"'),'[-contragent_id-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/super_id-]','"'),'[-super_id-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/legal_type-]','"'),'[-legal_type-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/name-]','"'),'[-name-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/name_full-]','"'),'[-name_full-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/address_jur-]','"'),'[-address_jur-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/inn-]','"'),'[-inn-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/kpp-]','"'),'[-kpp-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/position-]','"'),'[-position-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/fio-]','"'),'[-fio-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/positionV-]','"'),'[-positionV-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/fioV-]','"'),'[-fioV-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/ogrn-]','"'),'[-ogrn-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/okvd-]','"'),'[-okvd-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/okpo-]','"'),'[-okpo-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/opf-]','"'),'[-opf-]','"') WHERE `model` = 'ClientContragent';

        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/contragent_id-]','"'),'[-contragent_id-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/super_id-]','"'),'[-super_id-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/legal_type-]','"'),'[-legal_type-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/name-]','"'),'[-name-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/name_full-]','"'),'[-name_full-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/address_jur-]','"'),'[-address_jur-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/inn-]','"'),'[-inn-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/kpp-]','"'),'[-kpp-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/position-]','"'),'[-position-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/fio-]','"'),'[-fio-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/positionV-]','"'),'[-positionV-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/fioV-]','"'),'[-fioV-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/ogrn-]','"'),'[-ogrn-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/okvd-]','"'),'[-okvd-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/okpo-]','"'),'[-okpo-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/opf-]','"'),'[-opf-]','"') WHERE `model` = 'ClientContragent';

        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/contragent_id-]','"'),'[-contragent_id-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/super_id-]','"'),'[-super_id-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/legal_type-]','"'),'[-legal_type-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/name-]','"'),'[-name-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/name_full-]','"'),'[-name_full-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/address_jur-]','"'),'[-address_jur-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/inn-]','"'),'[-inn-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/kpp-]','"'),'[-kpp-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/position-]','"'),'[-position-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/fio-]','"'),'[-fio-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/positionV-]','"'),'[-positionV-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/fioV-]','"'),'[-fioV-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/ogrn-]','"'),'[-ogrn-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/okvd-]','"'),'[-okvd-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/okpo-]','"'),'[-okpo-]','"') WHERE `model` = 'ClientContragent';
        UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/opf-]','"'),'[-opf-]','"') WHERE `model` = 'ClientContragent';

SQL;

                $this->execute($sql);

    }

    public function down()
    {
        echo "m150519_190430_exportClientsLogAndHistory cannot be reverted.\n";

        return false;
    }
}