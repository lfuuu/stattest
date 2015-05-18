<?php

class m150515_190430_exportClientsLogAndHistory extends \app\classes\Migration
{
    public function up()
    {
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
        cc.`okpo` = c.`okpo`
;

SET GLOBAL group_concat_max_len=4294967295;
SELECT STRAIGHT_JOIN `id`, `client_id`, `super_id`, `user_id`, `ts`, `apply_ts`,
    CONCAT('{', `value_from`, '}') AS `value_from`,
    CONCAT('{', `value_to`, '}') AS `value_to`
    FROM (
        SELECT
            `ver_id` AS `id`,
            GROUP_CONCAT(CONCAT('"', `field`, '":"', `value_from`, '"') separator ',') AS `value_from`,
            GROUP_CONCAT(CONCAT('"', `field`, '":"', `value_to`, '"') separator ',') AS `value_to`
            FROM log_client_fields
            GROUP BY `ver_id`
    ) lcf
    INNER JOIN log_client lc USING(`id`)
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
		                '"contragent_id":"', `contragent_id`, '",',
		                '"super_id":"', `super_id`, '",',
		                '"legal_type":"',
		                    IF(`company_full` REGEXP '(^([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?))|(([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?)$)',
		                       'ip',
		                       IF(`type` = 'priv'
		                               AND `company_full` NOT REGEXP '(^([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?))|(([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?)$)',
		                           'person', 'legal')
		                       ),
		                '",',
		                '"name":"', REPLACE(`company`, '"', '\\"'), '",',
		                '"name_full":"', REPLACE(`company_full`, '"', '\\"'), '",',
		                '"address_jur":"', IF(ISNULL(`address_jur`), '',  REPLACE(`address_jur`, '"', '\\"')), '",',
		                '"inn":"', IF(ISNULL(`inn`), '', REPLACE(`inn`, '"', '\\"')), '",',
		                '"kpp":"', IF(ISNULL(`kpp`), '', REPLACE(`kpp`, '"', '\\"')), '",',
		                '"position":"', IF(ISNULL(`signer_position`), '', REPLACE(`signer_position`, '"', '\\"')), '",',
		                '"fio":"', IF(ISNULL(`signer_name`), '', REPLACE(`signer_name`, '"', '\\"')), '",',
		                '"ogrn":"",',
		                '"okvd":"",',
		                '"opf":"",',
                        '"okpo":"', IF(ISNULL(`okpo`), '', REPLACE(`okpo`, '"', '\\"')), '"',
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

REPLACE  history_version
    SELECT * FROM (
        SELECT hv.`model`, hv.`model_id`, l.`date_c`,
            REPLACE(hv.`data_json`, CONCAT('"', l.`field_name`,'":"', l.`value_from`,'"'), CONCAT('"', l.`field_name`,'":"', l.`value_to`,'"')) AS `data_jsonn`
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
                                           WHEN lcf.`field` = "signer_name" THEN "fio"
                                            WHEN lcf.`field` = "signer_position" THEN "position"
                                            WHEN lcf.`field` = "company" THEN "name"
                                            WHEN lcf.`field` = "company_full" THEN "name_full"
                                            WHEN lcf.`field` = "address_jur" THEN "address_jur"
                                            WHEN lcf.`field` = "inn" THEN "inn"
                                            WHEN lcf.`field` = "kpp" THEN "kpp"
                                            WHEN lcf.`field` = "okpo" THEN "okpo"
                                        END AS `field_name`,
                                REPLACE(lcf.`value_from`, '"', '\\"') AS `value_from`,
                                REPLACE(lcf.`value_to`, '"', '\\"') AS `value_to`
                                FROM
                                log_client lc
                                LEFT JOIN log_client_fields lcf ON lcf.`ver_id` = lc.`id`
                                LEFT JOIN clients c ON c.`id` = lc.`client_id`

                                WHERE lc.`type` = 'fields' AND lc.`comment` != 'client'
                                    AND lcf.`field` IN ('okpo','signer_name', 'signer_position', 'kpp', 'inn', 'address_jur', 'company_full', 'company')
                                    AND NOT ISNULL(c.`contragent_id`)
                        ) n
                        ORDER BY `date_c` DESC
                    ) z
                GROUP BY `contragent_id`, `date_c`, `field_name`
            ) l ON `contragent_id` = hv.`model_id`
            WHERE hv.`model` = 'ClientContragent'
            ORDER BY hv.`date` DESC
    ) m;

SQL;

        $this->execute($sql);
    }

    public function down()
    {
        echo "m150515_190430_exportClientsLogAndHistory cannot be reverted.\n";

        return false;
    }
}