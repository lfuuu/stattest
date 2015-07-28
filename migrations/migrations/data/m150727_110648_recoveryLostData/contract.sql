INSERT INTO nispd.history_changes
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
                            c.`contract_id` AS `model_id`,
                            lc.`user_id`,
                            IF(lc.`apply_ts` > '2006-01-01', CONCAT(lc.`apply_ts`, ' 00:00:00'), lc.`ts`) AS `create_at`,
                            'update' AS `action`,
                            REPLACE(lcf.`value_from`, '"', '\\"') AS `value_from`,
                            REPLACE(lcf.`value_to`, '"', '\\"') AS `value_to`,
                            IF(lcf.`field` = 'firma', 'organization', lcf.`field`) AS `field`
                            FROM
                            q.log_client lc
                            LEFT JOIN q.log_client_fields lcf ON lcf.`ver_id` = lc.`id`
                            LEFT JOIN nispd.clients c ON c.`id` = lc.`client_id`

                            WHERE lc.`type` = 'fields' AND lc.`comment` != 'client'
                              AND lcf.`field` IN ('firma','manager','account_manager','business_process_id','business_process_status_id','contract_type_id')
                              AND NOT ISNULL(c.`contract_id`)


                    ) n
                    GROUP BY `id`
        ) m
        WHERE NOT ISNULL(`data_json`)
;

INSERT INTO nispd.history_changes
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
                            c.`contract_id` AS `model_id`,
                            lc.`user_id`,
                            '2006-01-01 00:00:00' AS `create_at`,
                            'update' AS `action`,
                            REPLACE(lcf.`value_from`, '"', '\\"') AS `value_from`,
                            REPLACE(lcf.`value_to`, '"', '\\"') AS `value_to`,
                            IF(lcf.`field` = 'firma', 'organization', lcf.`field`) AS `field`
                            FROM q.log_client lc, q.log_client_fields lcf, nispd.clients c
                            WHERE lc.client_id = c.id AND lcf.ver_id = lc.id
                            AND lcf.value_from != '' AND lc.`comment` != 'client' AND lc.`type` = 'fields'
                            AND lcf.`field` IN ('firma','manager','account_manager','business_process_id','business_process_status_id','contract_type_id')
                            GROUP BY lc.client_id, lcf.field
                    ) n
                    GROUP BY `id`
        ) m
        WHERE NOT ISNULL(`data_json`)
;

REPLACE INTO history_version (
        SELECT
            'ClientContract' AS `model`,
            cc.`id` AS `model_id`,
            '2006-01-01' AS `date`,
            CONCAT(
               '{',
                    '"super_id":[-super_id-]', cc.`super_id`, '[-/super_id-],',
                    '"contragent_id":[-contragent_id-]', cc.`contragent_id`, '[-/contragent_id-],',
                    '"number":[-number-]', cc.`number`, '[-/number-],',
                    '"organization":[-organization-]', o.firma, '[-/organization-],',
                    '"manager":[-manager-]', cc.`manager`, '[-/manager-],',
                    '"account_manager":[-account_manager-]', cc.`account_manager`, '[-/account_manager-],',
                    '"business_process_id":[-business_process_id-]', cc.`business_process_id`, '[-/business_process_id-],',
                    '"business_process_status_id":[-business_process_status_id-]', cc.`business_process_status_id`, '[-/business_process_status_id-],',
                    '"contract_type_id":[-contract_type_id-]', cc.`contract_type_id`, '[-/contract_type_id-]',
               '}'
            ) AS `data_json`
            FROM client_contract cc
            INNER JOIN (SELECT DISTINCT organization_id, firma FROM organization) o ON o.organization_id =  cc.`organization_id`
)
;

INSERT INTO nispd.history_version
  SELECT hv.`model`, hv.`model_id`, hv.`date`, ''
  FROM nispd.history_version hv
    INNER JOIN nispd.clients c ON c.contract_id = hv.model_id
    INNER JOIN (
        SELECT * FROM (
          SELECT
          '2006-01-01' AS `date_c`,
          REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"') AS `value_from`,
          IF(lcf.`field` = 'firma', 'organization', lcf.`field`) AS `field_name`,
          lc.client_id
          FROM q.log_client lc, q.log_client_fields lcf, nispd.clients c
            WHERE lc.client_id = c.id AND lcf.ver_id = lc.id
            AND lcf.value_from != '' AND lc.`comment` != 'client' AND lc.`type` = 'fields'
            AND lcf.`field` IN ('firma','manager','account_manager','business_process_id','business_process_status_id','contract_type_id')
            GROUP BY lc.client_id, lcf.field
        ) d
        GROUP BY `field_name`, `client_id`
        ORDER BY `date_c`
    ) l ON l.`client_id` = c.`id`

		WHERE hv.`model` = 'ClientContract' AND hv.`date` = '2006-01-01'
ON DUPLICATE KEY UPDATE nispd.history_version.`data_json` = REPLACE(nispd.history_version.`data_json`,
  SUBSTRING(nispd.history_version.`data_json`,
    LOCATE(CONCAT('[-', l.`field_name` ,'-]'), nispd.history_version.`data_json`),
    (LOCATE(CONCAT('[-/', l.`field_name` ,'-]'), nispd.history_version.`data_json`) + LENGTH(CONCAT('[-/', l.`field_name` ,'-]')) - LOCATE(CONCAT('[-', l.`field_name` ,'-]'), nispd.history_version.`data_json`))
  ),
  CONCAT('[-', l.`field_name` ,'-]',l.`value_from`,'[-/', l.`field_name` ,'-]')
)
;



DELETE hv1 FROM nispd.history_version hv1
    LEFT JOIN
    (
      SELECT DATE(lc.ts) AS `date`, cr.id AS `model_id`, 'ClientContract' AS `model`
      FROM q.log_client lc, q.log_client_fields lcf, nispd.clients c, nispd.client_contract cr, nispd.client_contragent cg
      WHERE lc.client_id = c.id AND c.contract_id = cr.id AND cr.contragent_id = cg.id AND lcf.ver_id = lc.id
      AND lc.`comment` != 'client' AND lc.`type` = 'fields'
      AND lcf.`field` IN ('firma','manager','account_manager','business_process_id','business_process_status_id','contract_type_id')
    ) hv2 ON hv1.model = hv2.model AND hv1.model_id = hv2.model_id AND hv1.`date` = hv2.`date`
    WHERE hv1.`date` != '2006-01-01' AND ISNULL(hv2.model) AND hv1.model = 'ClientContract'
;



REPLACE INTO history_version (
        SELECT
            'ClientContract' AS `model`,
            cc.`id` AS `model_id`,
            hv.date,
            CONCAT(
               '{',
                    '"super_id":[-super_id-]', cc.`super_id`, '[-/super_id-],',
                    '"contragent_id":[-contragent_id-]', cc.`contragent_id`, '[-/contragent_id-],',
                    '"number":[-number-]', cc.`number`, '[-/number-],',
                    '"organization":[-organization-]', o.`firma`, '[-/organization-],',
                    '"manager":[-manager-]', cc.`manager`, '[-/manager-],',
                    '"account_manager":[-account_manager-]', cc.`account_manager`, '[-/account_manager-],',
                    '"business_process_id":[-business_process_id-]', cc.`business_process_id`, '[-/business_process_id-],',
                    '"business_process_status_id":[-business_process_status_id-]', cc.`business_process_status_id`, '[-/business_process_status_id-],',
                    '"contract_type_id":[-contract_type_id-]', cc.`contract_type_id`, '[-/contract_type_id-]',
               '}'
            ) AS `data_json`
            FROM client_contract cc
            INNER JOIN nispd.history_version hv ON hv.model = 'ClientContract' AND cc.id = hv.model_id
            INNER JOIN (SELECT DISTINCT organization_id, firma FROM organization) o ON o.organization_id =  cc.`organization_id`
)
;





INSERT INTO nispd.history_version
  SELECT hv.`model`, hv.`model_id`, hv.`date`, l.value_from
  FROM nispd.history_version hv
    INNER JOIN (
        SELECT * FROM (
            SELECT
            DATE(lc.ts) AS `date_c`,
            if(DATE(lc.ts) > lc.apply_ts, DATE(lc.ts), lc.apply_ts) AS `date_r`,
            REPLACE(lcf.`value_to`, '"', '\\"') AS `value_from`,
            IF(lcf.`field` = 'firma', 'organization', lcf.`field`) AS `field_name`,
            lc.client_id
            FROM q.log_client lc, q.log_client_fields lcf, nispd.clients c, nispd.client_contract cr, nispd.client_contragent cg
				WHERE lc.client_id = c.id AND c.contract_id = cr.id AND cr.contragent_id = cg.id AND lcf.ver_id = lc.id
				AND lcf.value_from != '' AND lc.`comment` != 'client' AND lc.`type` = 'fields'
				AND lcf.`field` IN ('firma','manager','account_manager','business_process_id','business_process_status_id','contract_type_id')

				UNION

				SELECT * FROM (
            SELECT * FROM (
          SELECT
          '2006-01-01' AS `date_c`,
          '2006-01-01' AS `date_r`,
          REPLACE(IF(lcf.`value_from` = '', lcf.`value_to`, lcf.`value_from`), '"', '\\"') AS `value_from`,
          IF(lcf.`field` = 'firma', 'organization', lcf.`field`) AS `field_name`,
          lc.client_id
          FROM q.log_client lc, q.log_client_fields lcf, nispd.clients c
            WHERE lc.client_id = c.id AND lcf.ver_id = lc.id
            AND lcf.value_from != '' AND lc.`comment` != 'client' AND lc.`type` = 'fields'
            AND lcf.`field` IN ('firma','manager','account_manager','business_process_id','business_process_status_id','contract_type_id')
            GROUP BY lc.client_id, lcf.field
        ) d
        GROUP BY `field_name`, `client_id`

          ) d

				) d
        GROUP BY d.client_id, d.field_name, d.date_c, d.date_r
    ) l ON l.`client_id` = hv.`model_id` AND l.date_c <= hv.date AND hv.`model` = 'ClientContract'
    ORDER BY hv.date DESC, l.date_c
  ON DUPLICATE KEY UPDATE nispd.history_version.`data_json` = REPLACE(nispd.history_version.`data_json`,
    SUBSTRING(nispd.history_version.`data_json`,
      LOCATE(CONCAT('[-', l.`field_name` ,'-]'), nispd.history_version.`data_json`),
      (LOCATE(CONCAT('[-/', l.`field_name` ,'-]'), nispd.history_version.`data_json`) + LENGTH(CONCAT('[-/', l.`field_name` ,'-]')) - LOCATE(CONCAT('[-', l.`field_name` ,'-]'), nispd.history_version.`data_json`))
    ),
    CONCAT('[-', l.`field_name` ,'-]',l.`value_from`,'[-/', l.`field_name` ,'-]')
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