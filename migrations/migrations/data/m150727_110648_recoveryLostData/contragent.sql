SET GLOBAL group_concat_max_len=4294967295;
INSERT INTO nispd.history_changes
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
                            cg.`id` AS `model_id`,
                            lc.`user_id`,
                            '2006-01-01 00:00:00' AS `create_at`,
                            'update' AS `action`,
                            '' AS `value_from`,
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
                                        WHEN lcf.`field` = "nds_zero" THEN IF(lcf.`value_from` = 1, '0', '1')
                                        WHEN lcf.`field` = "type" THEN lcf.`value_from`
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
                            FROM q.log_client lc, q.log_client_fields lcf, nispd.clients c, nispd.client_contract cr, nispd.client_contragent cg
                            WHERE lc.client_id = c.id AND c.contract_id = cr.id AND cr.contragent_id = cg.id AND lcf.ver_id = lc.id
                            AND lcf.value_from != '' AND lc.`comment` != 'client' AND lc.`type` = 'fields'
                            AND lcf.`field` IN ('okpo','signer_name', 'signer_position', 'signer_nameV', 'signer_positionV', 'kpp', 'inn', 'address_jur', 'company_full', 'company', 'type', 'nds_zero')
                            GROUP BY lc.client_id, lcf.field
                    ) n
                    GROUP BY `id`
        ) m
        WHERE NOT ISNULL(`data_json`)
;



REPLACE INTO history_version (
    SELECT
            'ClientContragent' AS `model`,
            `id` AS `model_id`,
            '2006-01-01' AS `date`,
            CONCAT(
                '{',
                    '"id":[-id-]', `id`, '[-/id-],',
                    '"super_id":[-super_id-]', `super_id`, '[-/super_id-],',
                    '"legal_type":[-legal_type-]', legal_type ,'[-/legal_type-],',
                    '"name":[-name-]', REPLACE(`name`, '"', '\\"'), '[-/name-],',
                    '"name_full":[-name_full-]', REPLACE(`name_full`, '"', '\\"'), '[-/name_full-],',
                    '"address_jur":[-address_jur-]', IF(ISNULL(`address_jur`), '',  REPLACE(`address_jur`, '"', '\\"')), '[-/address_jur-],',
                    '"inn":[-inn-]', IF(ISNULL(`inn`), '', REPLACE(`inn`, '"', '\\"')), '[-/inn-],',
                    '"kpp":[-kpp-]', IF(ISNULL(`kpp`), '', REPLACE(`kpp`, '"', '\\"')), '[-/kpp-],',
                    '"position":[-position-]', IF(ISNULL(`position`), '', REPLACE(`position`, '"', '\\"')), '[-/position-],',
                    '"fio":[-fio-]', IF(ISNULL(`fio`), '', REPLACE(`fio`, '"', '\\"')), '[-/fio-],',
                    '"positionV":[-positionV-]', IF(ISNULL(`positionV`), '', REPLACE(`positionV`, '"', '\\"')), '[-/positionV-],',
                    '"fio":[-fioV-]', IF(ISNULL(`fioV`), '', REPLACE(`fioV`, '"', '\\"')), '[-/fioV-],',
                    '"ogrn":[-ogrn-]',REPLACE(`ogrn`, '"', '\\"'),'[-/ogrn-],',
                    '"okvd":[-okvd-]',REPLACE(`okvd`, '"', '\\"'),'[-/okvd-],',
                    '"opf":[-opf-]',REPLACE(`opf`, '"', '\\"'),'[-/opf-],',
                    '"okpo":[-okpo-]', IF(ISNULL(`okpo`), '', REPLACE(`okpo`, '"', '\\"')), '[-/okpo-]',
                '}'
            ) AS `json_date`
            FROM client_contragent
)
;

INSERT INTO nispd.history_version
  SELECT hv.`model`, hv.`model_id`, hv.`date`, ''
  FROM nispd.history_version hv
    INNER JOIN (
        SELECT * FROM (
            SELECT
            '2006-01-01' AS `date_c`,
            CASE
              WHEN lcf.`field` = "signer_name" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "signer_position" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "signer_nameV" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "signer_positionV" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "company" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "company_full" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "address_jur" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "inn" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "kpp" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "okpo" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "nds_zero" THEN IF(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`) = 1, '0', '1')
              WHEN lcf.`field` = "type" THEN IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`)
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
            END AS `field_name`,
            cg.id as `contragent_id`
            FROM q.log_client lc, q.log_client_fields lcf, nispd.clients c, nispd.client_contract cr, nispd.client_contragent cg
				WHERE lc.client_id = c.id AND c.contract_id = cr.id AND cr.contragent_id = cg.id AND lcf.ver_id = lc.id
				AND lcf.value_from != '' AND lc.`comment` != 'client' AND lc.`type` = 'fields'
				AND lcf.`field` IN ('okpo','signer_name', 'signer_position', 'signer_nameV', 'signer_positionV', 'kpp', 'inn', 'address_jur', 'company_full', 'company', 'type', 'nds_zero')
				GROUP BY lc.client_id, lcf.field

          ) d
          GROUP BY `field_name`, `contragent_id`
    ) l ON l.`contragent_id` = hv.`model_id`

		WHERE hv.`model` = 'ClientContragent' AND hv.`date` = '2006-01-01'
ON DUPLICATE KEY UPDATE history_version.`data_json` = REPLACE(history_version.`data_json`,
	                    SUBSTRING(history_version.`data_json`,
	                      LOCATE(CONCAT('[-', l.`field_name` ,'-]'), history_version.`data_json`),
	                      (LOCATE(CONCAT('[-/', l.`field_name` ,'-]'), history_version.`data_json`) + LENGTH(CONCAT('[-/', l.`field_name` ,'-]')) - LOCATE(CONCAT('[-', l.`field_name` ,'-]'), history_version.`data_json`))
	                    ),
	                    CONCAT(CONCAT('[-', l.`field_name` ,'-]'),
	                        IF(
	                            l.`field_name` = 'legal_type',
	                            IF(history_version.`data_json` REGEXP '(^([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?))|(([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?)$)',
	                              'ip',
	                              IF(LOCATE('[-legal_type-]priv[-/legal_type-]', history_version.`data_json`) > 0
	                                      AND history_version.`data_json` NOT REGEXP '(^([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?))|(([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?)$)',
	                                  'person', 'legal')
	                              ),
	                           l.`value_to`
	                        ),
	                        CONCAT('[-/', l.`field_name` ,'-]')
	                    )
	        )
;

DELETE hv1 FROM nispd.history_version hv1
    LEFT JOIN
    (
      SELECT DATE(lc.ts) AS `date`, cg.id AS `model_id`, 'ClientContragent' AS `model`
      FROM q.log_client lc, q.log_client_fields lcf, nispd.clients c, nispd.client_contract cr, nispd.client_contragent cg
      WHERE lc.client_id = c.id AND c.contract_id = cr.id AND cr.contragent_id = cg.id AND lcf.ver_id = lc.id
      AND lc.`comment` != 'client' AND lc.`type` = 'fields'
      AND lcf.`field` IN ('okpo','signer_name', 'signer_position', 'signer_nameV', 'signer_positionV', 'kpp', 'inn', 'address_jur', 'company_full', 'company', 'type', 'nds_zero')
    ) hv2 ON hv1.model = hv2.model AND hv1.model_id = hv2.model_id AND hv1.`date` = hv2.`date`
    WHERE hv1.`date` != '2006-01-01' AND ISNULL(hv2.model) AND hv1.model = 'ClientContragent'
;



REPLACE INTO history_version (
    SELECT
            'ClientContragent' AS `model`,
            `id` AS `model_id`,
            hv.date,
            CONCAT(
                '{',
                    '"id":[-id-]', `id`, '[-/id-],',
                    '"super_id":[-super_id-]', `super_id`, '[-/super_id-],',
                    '"legal_type":[-legal_type-]', legal_type ,'[-/legal_type-],',
                    '"name":[-name-]', REPLACE(`name`, '"', '\\"'), '[-/name-],',
                    '"name_full":[-name_full-]', REPLACE(`name_full`, '"', '\\"'), '[-/name_full-],',
                    '"address_jur":[-address_jur-]', IF(ISNULL(`address_jur`), '',  REPLACE(`address_jur`, '"', '\\"')), '[-/address_jur-],',
                    '"inn":[-inn-]', IF(ISNULL(`inn`), '', REPLACE(`inn`, '"', '\\"')), '[-/inn-],',
                    '"kpp":[-kpp-]', IF(ISNULL(`kpp`), '', REPLACE(`kpp`, '"', '\\"')), '[-/kpp-],',
                    '"position":[-position-]', IF(ISNULL(`position`), '', REPLACE(`position`, '"', '\\"')), '[-/position-],',
                    '"fio":[-fio-]', IF(ISNULL(`fio`), '', REPLACE(`fio`, '"', '\\"')), '[-/fio-],',
                    '"positionV":[-positionV-]', IF(ISNULL(`positionV`), '', REPLACE(`positionV`, '"', '\\"')), '[-/positionV-],',
                    '"fioV":[-fioV-]', IF(ISNULL(`fioV`), '', REPLACE(`fioV`, '"', '\\"')), '[-/fioV-],',
                    '"ogrn":[-ogrn-]',REPLACE(`ogrn`, '"', '\\"'),'[-/ogrn-],',
                    '"okvd":[-okvd-]',REPLACE(`okvd`, '"', '\\"'),'[-/okvd-],',
                    '"opf":[-opf-]',REPLACE(`opf`, '"', '\\"'),'[-/opf-],',
                    '"okpo":[-okpo-]', IF(ISNULL(`okpo`), '', REPLACE(`okpo`, '"', '\\"')), '[-/okpo-]',
                '}'
            ) AS `json_date`
            FROM client_contragent cg
            INNER JOIN nispd.history_version hv ON hv.model = 'ClientContragent' AND cg.id = hv.model_id
)
;


INSERT INTO nispd.history_version
  SELECT hv.`model`, hv.`model_id`, hv.`date`, l.value_to
  FROM nispd.history_version hv
    INNER JOIN (
        SELECT * FROM (
            SELECT
            DATE(lc.ts) AS `date_c`,
            if(DATE(lc.ts) > lc.apply_ts, DATE(lc.ts), lc.apply_ts) AS `date_r`,
            CASE
              WHEN lcf.`field` = "signer_name" THEN REPLACE(lcf.value_to, '"', '\\"')
              WHEN lcf.`field` = "signer_position" THEN REPLACE(lcf.value_to, '"', '\\"')
              WHEN lcf.`field` = "signer_nameV" THEN REPLACE(lcf.value_to, '"', '\\"')
              WHEN lcf.`field` = "signer_positionV" THEN REPLACE(lcf.value_to, '"', '\\"')
              WHEN lcf.`field` = "company" THEN REPLACE(lcf.value_to, '"', '\\"')
              WHEN lcf.`field` = "company_full" THEN REPLACE(lcf.value_to, '"', '\\"')
              WHEN lcf.`field` = "address_jur" THEN REPLACE(lcf.value_to, '"', '\\"')
              WHEN lcf.`field` = "inn" THEN REPLACE(lcf.value_to, '"', '\\"')
              WHEN lcf.`field` = "kpp" THEN REPLACE(lcf.value_to, '"', '\\"')
              WHEN lcf.`field` = "okpo" THEN REPLACE(lcf.value_to, '"', '\\"')
              WHEN lcf.`field` = "nds_zero" THEN IF(lcf.value_to = 1, '0', '1')
              WHEN lcf.`field` = "type" THEN lcf.value_to
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
            END AS `field_name`,
            cg.id as `contragent_id`
            FROM q.log_client lc, q.log_client_fields lcf, nispd.clients c, nispd.client_contract cr, nispd.client_contragent cg
				WHERE lc.client_id = c.id AND c.contract_id = cr.id AND cr.contragent_id = cg.id AND lcf.ver_id = lc.id
				AND lcf.value_from != '' AND lc.`comment` != 'client' AND lc.`type` = 'fields'
				AND lcf.`field` IN ('okpo','signer_name', 'signer_position', 'signer_nameV', 'signer_positionV', 'kpp', 'inn', 'address_jur', 'company_full', 'company', 'type', 'nds_zero')

				UNION

				SELECT * FROM (
            SELECT
            '2006-01-01' AS `date_c`,
            '2006-01-01' AS `date_r`,
            CASE
              WHEN lcf.`field` = "signer_name" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "signer_position" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "signer_nameV" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "signer_positionV" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "company" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "company_full" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "address_jur" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "inn" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "kpp" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "okpo" THEN REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"')
              WHEN lcf.`field` = "nds_zero" THEN IF(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`) = 1, '0', '1')
              WHEN lcf.`field` = "type" THEN IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`)
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
            END AS `field_name`,
            cg.id as `contragent_id`
            FROM q.log_client lc, q.log_client_fields lcf, nispd.clients c, nispd.client_contract cr, nispd.client_contragent cg
				WHERE lc.client_id = c.id AND c.contract_id = cr.id AND cr.contragent_id = cg.id AND lcf.ver_id = lc.id
				AND lcf.value_from != '' AND lc.`comment` != 'client' AND lc.`type` = 'fields'
				AND lcf.`field` IN ('okpo','signer_name', 'signer_position', 'signer_nameV', 'signer_positionV', 'kpp', 'inn', 'address_jur', 'company_full', 'company', 'type', 'nds_zero')
				GROUP BY lc.client_id, lcf.field

          ) d

				) d
        GROUP BY d.contragent_id, d.field_name, d.date_c, d.date_r
    ) l ON l.`contragent_id` = hv.`model_id` AND l.date_c <= hv.date AND hv.`model` = 'ClientContragent'
    ORDER BY hv.date DESC, l.date_c
ON DUPLICATE KEY UPDATE history_version.`data_json` = REPLACE(history_version.`data_json`,
	                    SUBSTRING(history_version.`data_json`,
	                      LOCATE(CONCAT('[-', l.`field_name` ,'-]'), history_version.`data_json`),
	                      (LOCATE(CONCAT('[-/', l.`field_name` ,'-]'), history_version.`data_json`) + LENGTH(CONCAT('[-/', l.`field_name` ,'-]')) - LOCATE(CONCAT('[-', l.`field_name` ,'-]'), history_version.`data_json`))
	                    ),
	                    CONCAT(CONCAT('[-', l.`field_name` ,'-]'),
	                        IF(
	                            l.`field_name` = 'legal_type',
	                            IF(history_version.`data_json` REGEXP '(^([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?))|(([( "\']?)(ИП|Индивидуальный предприниматель)([) "\']?)$)',
	                              'ip',
	                              IF(LOCATE('[-legal_type-]priv[-/legal_type-]', history_version.`data_json`) > 0
	                                      AND history_version.`data_json` NOT REGEXP '(^([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?))|(([( "\']?)(ООО|ЗАО|ОАО|OOO|OAO)([) "\']?)$)',
	                                  'person', 'legal')
	                              ),
	                           l.`value_to`
	                        ),
	                        CONCAT('[-/', l.`field_name` ,'-]')
	                    )
	        )
;






UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/id-]',''),'[-id-]','') WHERE `model` = 'ClientContragent';
UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/super_id-]',''),'[-super_id-]','') WHERE `model` = 'ClientContragent';
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

UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/id-]',''),'[-id-]','') WHERE `model` = 'ClientContragent';
UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/super_id-]',''),'[-super_id-]','') WHERE `model` = 'ClientContragent';
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

UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/id-]',''),'[-id-]','') WHERE `model` = 'ClientContragent';
UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/super_id-]',''),'[-super_id-]','') WHERE `model` = 'ClientContragent';
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

UPDATE history_changes SET `data_json` = REPLACE(`data_json`,'"null"', 'null') WHERE `model` = 'ClientContragent';
UPDATE history_changes SET `data_json` = REPLACE(`prev_data_json`,'"null"', 'null') WHERE `model` = 'ClientContragent';
UPDATE history_version SET `data_json` = REPLACE(`data_json`,'"null"', 'null') WHERE `model` = 'ClientContragent';