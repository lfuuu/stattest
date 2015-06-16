ALTER TABLE `clients`
    DROP COLUMN `company`,
    DROP COLUMN `address_jur`,
    DROP COLUMN `company_full`,
    DROP COLUMN `type`,
    DROP COLUMN `manager`,
    DROP COLUMN `inn`,
    DROP COLUMN `kpp`,
    DROP COLUMN `signer_name`,
    DROP COLUMN `signer_position`,
    DROP COLUMN `signer_nameV`,
    DROP COLUMN `firma`,
    DROP COLUMN `signer_positionV`,
    DROP COLUMN `nds_zero`,
    DROP COLUMN `okpo`,
    DROP COLUMN `account_manager`,
    DROP COLUMN `contract_type_id`,
    DROP COLUMN `business_process_id`,
    DROP COLUMN `business_process_status_id`
;

DELETE FROM log_client_fields
    WHERE `field` IN ('company', 'address_jur', 'company_full', 'type',
        'manager', 'inn', 'kpp', 'signer_name', 'signer_position', 'signer_nameV',
        'firma', 'signer_positionV', 'nds_zero', 'okpo', 'account_manager', 'contract_type_id',
        'business_process_id', 'business_process_id', 'business_process_status_id')
;

DELETE FROM log_client
    WHERE `comment` != 'clients' AND `type` = 'fields'
        AND id NOT IN(SELECT DISTINCT ver_id FROM log_client_fields)
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
                                    'ClientAccount' AS `model`,
                                    lc.`client_id` AS `model_id`,
                                    lc.`user_id`,
                                    lc.`ts` AS `create_at`,
                                    'update' AS `action`,
                                    IF(ISNULL(lcf.`value_from`), 'null', REPLACE(lcf.`value_from`, '"', '\\"')) AS `value_from`,
                                    IF(ISNULL(lcf.`value_to`), 'null', REPLACE(lcf.`value_to`, '"', '\\"')) AS `value_to`,
                                    lcf.`field`
                                    FROM log_client lc
                                    LEFT JOIN log_client_fields lcf ON lcf.`ver_id` = lc.`id`
                                    WHERE lc.`type` = 'fields' AND lc.`comment` != 'client' AND NOT ISNULL(lc.`client_id`)


                            ) n
                            GROUP BY `id`
                ) m
                WHERE NOT ISNULL(`data_json`)
        ;

        REPLACE INTO history_version
            SELECT
                'ClientAccount' AS `model`,
                c.`id` AS `model_id`,
                IF(DATE(c.`created`) < '2006-01-01' OR ISNULL(c.`created`), '2006-01-01',  DATE(c.`created`))
                    AS `date`,
                CONCAT(
                   '{',
                            '"id":[-id-]', REPLACE(c.`id`, '"', '\\"'), '[-/id-],',
                            '"client":[-client-]', REPLACE(c.`client`, '"', '\\"'), '[-/client-],',
                            '"super_id":[-super_id-]', REPLACE(c.`super_id`, '"', '\\"'), '[-/super_id-],',
                            '"contragent_id":[-contragent_id-]', REPLACE(c.`contragent_id`, '"', '\\"'), '[-/contragent_id-],',
                            '"contract_id":[-contract_id-]', REPLACE(c.`contract_id`, '"', '\\"'), '[-/contract_id-],',
                            '"country_id":[-country_id-]', REPLACE(c.`country_id`, '"', '\\"'), '[-/country_id-],',
                            '"password":[-password-]', REPLACE(c.`password`, '"', '\\"'), '[-/password-],',
                            '"password_type":[-password_type-]', REPLACE(c.`password_type`, '"', '\\"'), '[-/password_type-],',
                            '"comment":[-comment-]', REPLACE(c.`comment`, '"', '\\"'), '[-/comment-],',
                            '"status":[-status-]', REPLACE(c.`status`, '"', '\\"'), '[-/status-],',
                            '"usd_rate_percent":[-usd_rate_percent-]', REPLACE(c.`usd_rate_percent`, '"', '\\"'), '[-/usd_rate_percent-],',
                            '"address_post":[-address_post-]', REPLACE(c.`address_post`, '"', '\\"'), '[-/address_post-],',
                            '"address_post_real":[-address_post_real-]', REPLACE(c.`address_post_real`, '"', '\\"'), '[-/address_post_real-],',
                            '"support":[-support-]', REPLACE(c.`support`, '"', '\\"'), '[-/support-],',
                            '"login":[-login-]', REPLACE(c.`login`, '"', '\\"'), '[-/login-],',
                            '"bik":[-bik-]', REPLACE(c.`bik`, '"', '\\"'), '[-/bik-],',
                            '"bank_properties":[-bank_properties-]', REPLACE(c.`bank_properties`, '"', '\\"'), '[-/bank_properties-],',
                            '"currency":[-currency-]', REPLACE(c.`currency`, '"', '\\"'), '[-/currency-],',
                            '"currency_bill":[-currency_bill-]', REPLACE(c.`currency_bill`, '"', '\\"'), '[-/currency_bill-],',
                            '"stamp":[-stamp-]', REPLACE(c.`stamp`, '"', '\\"'), '[-/stamp-],',
                            '"nal":[-nal-]', REPLACE(c.`nal`, '"', '\\"'), '[-/nal-],',
                            '"telemarketing":[-telemarketing-]', REPLACE(c.`telemarketing`, '"', '\\"'), '[-/telemarketing-],',
                            '"sale_channel":[-sale_channel-]', REPLACE(c.`sale_channel`, '"', '\\"'), '[-/sale_channel-],',
                            '"uid":[-uid-]', IF(ISNULL(c.`uid`), 'null', REPLACE(c.`uid`, '"', '\\"')), '[-/uid-],',
                            '"site_req_no":[-site_req_no-]', REPLACE(c.`site_req_no`, '"', '\\"'), '[-/site_req_no-],',
                            '"hid_rtsaldo_date":[-hid_rtsaldo_date-]', REPLACE(c.`hid_rtsaldo_date`, '"', '\\"'), '[-/hid_rtsaldo_date-],',
                            '"hid_rtsaldo_RUB":[-hid_rtsaldo_RUB-]', REPLACE(c.`hid_rtsaldo_RUB`, '"', '\\"'), '[-/hid_rtsaldo_RUB-],',
                            '"hid_rtsaldo_USD":[-hid_rtsaldo_USD-]', REPLACE(c.`hid_rtsaldo_USD`, '"', '\\"'), '[-/hid_rtsaldo_USD-],',
                            '"credit_USD":[-credit_USD-]', REPLACE(c.`credit_USD`, '"', '\\"'), '[-/credit_USD-],',
                            '"credit_RUB":[-credit_RUB-]', REPLACE(c.`credit_RUB`, '"', '\\"'), '[-/credit_RUB-],',
                            '"credit":[-credit-]', REPLACE(c.`credit`, '"', '\\"'), '[-/credit-],',
                            '"user_impersonate":[-user_impersonate-]', REPLACE(c.`user_impersonate`, '"', '\\"'), '[-/user_impersonate-],',
                            '"address_connect":[-address_connect-]', REPLACE(c.`address_connect`, '"', '\\"'), '[-/address_connect-],',
                            '"phone_connect":[-phone_connect-]', REPLACE(c.`phone_connect`, '"', '\\"'), '[-/phone_connect-],',
                            '"id_all4net":[-id_all4net-]', REPLACE(c.`id_all4net`, '"', '\\"'), '[-/id_all4net-],',
                            '"dealer_comment":[-dealer_comment-]', REPLACE(c.`dealer_comment`, '"', '\\"'), '[-/dealer_comment-],',
                            '"form_type":[-form_type-]', REPLACE(c.`form_type`, '"', '\\"'), '[-/form_type-],',
                            '"metro_id":[-metro_id-]', REPLACE(c.`metro_id`, '"', '\\"'), '[-/metro_id-],',
                            '"payment_comment":[-payment_comment-]', REPLACE(c.`payment_comment`, '"', '\\"'), '[-/payment_comment-],',
                            '"previous_reincarnation":[-previous_reincarnation-]', IF(ISNULL(c.`previous_reincarnation`), 'null', REPLACE(c.`previous_reincarnation`, '"', '\\"')), '[-/previous_reincarnation-],',
                            '"cli_1c":[-cli_1c-]', IF(ISNULL(c.`cli_1c`), 'null', REPLACE(c.`cli_1c`, '"', '\\"')), '[-/cli_1c-],',
                            '"con_1c":[-con_1c-]', IF(ISNULL(c.`con_1c`), 'null', REPLACE(c.`con_1c`, '"', '\\"')), '[-/con_1c-],',
                            '"corr_acc":[-corr_acc-]', IF(ISNULL(c.`corr_acc`), 'null', REPLACE(c.`corr_acc`, '"', '\\"')), '[-/corr_acc-],',
                            '"pay_acc":[-pay_acc-]', IF(ISNULL(c.`pay_acc`), 'null', REPLACE(c.`pay_acc`, '"', '\\"')), '[-/pay_acc-],',
                            '"bank_name":[-bank_name-]', IF(ISNULL(c.`bank_name`), 'null', REPLACE(c.`bank_name`, '"', '\\"')), '[-/bank_name-],',
                            '"bank_city":[-bank_city-]', IF(ISNULL(c.`bank_city`), 'null', REPLACE(c.`bank_city`, '"', '\\"')), '[-/bank_city-],',
                            '"sync_1c":[-sync_1c-]', REPLACE(c.`sync_1c`, '"', '\\"'), '[-/sync_1c-],',
                            '"price_type":[-price_type-]', IF(ISNULL(c.`price_type`), 'null', REPLACE(c.`price_type`, '"', '\\"')), '[-/price_type-],',
                            '"voip_credit_limit":[-voip_credit_limit-]', REPLACE(c.`voip_credit_limit`, '"', '\\"'), '[-/voip_credit_limit-],',
                            '"voip_disabled":[-voip_disabled-]', REPLACE(c.`voip_disabled`, '"', '\\"'), '[-/voip_disabled-],',
                            '"voip_credit_limit_day":[-voip_credit_limit_day-]', REPLACE(c.`voip_credit_limit_day`, '"', '\\"'), '[-/voip_credit_limit_day-],',
                            '"balance":[-balance-]', REPLACE(c.`balance`, '"', '\\"'), '[-/balance-],',
                            '"balance_usd":[-balance_usd-]', REPLACE(c.`balance_usd`, '"', '\\"'), '[-/balance_usd-],',
                            '"voip_is_day_calc":[-voip_is_day_calc-]', REPLACE(c.`voip_is_day_calc`, '"', '\\"'), '[-/voip_is_day_calc-],',
                            '"region":[-region-]', IF(ISNULL(c.`region`), 'null', REPLACE(c.`region`, '"', '\\"')), '[-/region-],',
                            '"last_account_date":[-last_account_date-]', IF(ISNULL(c.`last_account_date`), 'null', REPLACE(c.`last_account_date`, '"', '\\"')), '[-/last_account_date-],',
                            '"last_payed_voip_month":[-last_payed_voip_month-]', IF(ISNULL(c.`last_payed_voip_month`), 'null', REPLACE(c.`last_payed_voip_month`, '"', '\\"')), '[-/last_payed_voip_month-],',
                            '"mail_print":[-mail_print-]', IF(ISNULL(c.`mail_print`), 'null', REPLACE(c.`mail_print`, '"', '\\"')), '[-/mail_print-],',
                            '"mail_who":[-mail_who-]', REPLACE(c.`mail_who`, '"', '\\"'), '[-/mail_who-],',
                            '"head_company":[-head_company-]', REPLACE(c.`head_company`, '"', '\\"'), '[-/head_company-],',
                            '"head_company_address_jur":[-head_company_address_jur-]', REPLACE(c.`head_company_address_jur`, '"', '\\"'), '[-/head_company_address_jur-],',
                            '"created":[-created-]', IF(ISNULL(c.`created`), 'null', REPLACE(c.`created`, '"', '\\"')), '[-/created-],',
                            '"bill_rename1":[-bill_rename1-]', REPLACE(c.`bill_rename1`, '"', '\\"'), '[-/bill_rename1-],',
                            '"nds_calc_method":[-nds_calc_method-]', REPLACE(c.`nds_calc_method`, '"', '\\"'), '[-/nds_calc_method-],',
                            '"admin_contact_id":[-admin_contact_id-]', REPLACE(c.`admin_contact_id`, '"', '\\"'), '[-/admin_contact_id-],',
                            '"admin_is_active":[-admin_is_active-]', REPLACE(c.`admin_is_active`, '"', '\\"'), '[-/admin_is_active-],',
                            '"is_agent":[-is_agent-]', REPLACE(c.`is_agent`, '"', '\\"'), '[-/is_agent-],',
                            '"is_bill_only_contract":[-is_bill_only_contract-]', REPLACE(c.`is_bill_only_contract`, '"', '\\"'), '[-/is_bill_only_contract-],',
                            '"is_bill_with_refund":[-is_bill_with_refund-]', REPLACE(c.`is_bill_with_refund`, '"', '\\"'), '[-/is_bill_with_refund-],',
                            '"is_with_consignee":[-is_with_consignee-]', REPLACE(c.`is_with_consignee`, '"', '\\"'), '[-/is_with_consignee-],',
                            '"consignee":[-consignee-]', REPLACE(c.`consignee`, '"', '\\"'), '[-/consignee-],',
                            '"is_upd_without_sign":[-is_upd_without_sign-]', REPLACE(c.`is_upd_without_sign`, '"', '\\"'), '[-/is_upd_without_sign-],',
                            '"is_active":[-is_active-]', REPLACE(c.`is_active`, '"', '\\"'), '[-/is_active-],',
                            '"is_blocked":[-is_blocked-]', REPLACE(c.`is_blocked`, '"', '\\"'), '[-/is_blocked-],',
                            '"is_closed":[-is_closed-]', REPLACE(c.`is_closed`, '"', '\\"'), '[-/is_closed-],',
                            '"timezone_name":[-timezone_name-]', REPLACE(c.`timezone_name`, '"', '\\"'), '[-/timezone_name-]',
                   '}'
                ) AS `data_json`
                FROM clients c
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
                               REPLACE(lcf.`value_to`, '"', '\\"') AS `value_to`,
                               lcf.`field` AS `field_name`
                               FROM log_client lc
                               LEFT JOIN log_client_fields lcf ON lcf.`ver_id` = lc.`id`
                               LEFT JOIN clients c ON c.`id` = lc.`client_id`

                               WHERE lc.`type` = 'fields' AND lc.`comment` != 'client'
                       ) n
                       ORDER BY `date_c` DESC
                   ) z
               GROUP BY `contract_id`, `date_c`, `field_name`
           ) l ON `contract_id` = hv.`model_id`
           WHERE hv.`model` = 'ClientAccount' AND NOT ISNULL(l.`value_to`) AND NOT ISNULL(hv.`data_json`)
           ORDER BY hv.`date` DESC
    ) m;

    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/id-]',''),'[-id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/client-]','"'),'[-client-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/super_id-]',''),'[-super_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/contragent_id-]',''),'[-contragent_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/contract_id-]',''),'[-contract_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/country_id-]',''),'[-country_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/password-]','"'),'[-password-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/password_type-]','"'),'[-password_type-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/comment-]','"'),'[-comment-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/status-]','"'),'[-status-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/usd_rate_percent-]',''),'[-usd_rate_percent-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/address_post-]','"'),'[-address_post-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/address_post_real-]','"'),'[-address_post_real-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/support-]','"'),'[-support-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/login-]','"'),'[-login-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/bik-]','"'),'[-bik-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/bank_properties-]','"'),'[-bank_properties-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/currency-]','"'),'[-currency-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/currency_bill-]','"'),'[-currency_bill-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/stamp-]','"'),'[-stamp-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/nal-]','"'),'[-nal-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/telemarketing-]','"'),'[-telemarketing-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/sale_channel-]',''),'[-sale_channel-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/uid-]','"'),'[-uid-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/site_req_no-]','"'),'[-site_req_no-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/hid_rtsaldo_date-]','"'),'[-hid_rtsaldo_date-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/hid_rtsaldo_RUB-]',''),'[-hid_rtsaldo_RUB-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/hid_rtsaldo_USD-]',''),'[-hid_rtsaldo_USD-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/credit_USD-]',''),'[-credit_USD-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/credit_RUB-]',''),'[-credit_RUB-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/credit-]',''),'[-credit-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/user_impersonate-]','"'),'[-user_impersonate-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/address_connect-]','"'),'[-address_connect-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/phone_connect-]','"'),'[-phone_connect-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/id_all4net-]',''),'[-id_all4net-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/dealer_comment-]','"'),'[-dealer_comment-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/form_type-]','"'),'[-form_type-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/metro_id-]',''),'[-metro_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/payment_comment-]','"'),'[-payment_comment-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/previous_reincarnation-]','"'),'[-previous_reincarnation-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/cli_1c-]','"'),'[-cli_1c-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/con_1c-]','"'),'[-con_1c-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/corr_acc-]','"'),'[-corr_acc-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/pay_acc-]','"'),'[-pay_acc-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/bank_name-]','"'),'[-bank_name-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/bank_city-]','"'),'[-bank_city-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/sync_1c-]','"'),'[-sync_1c-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/price_type-]','"'),'[-price_type-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/voip_credit_limit-]',''),'[-voip_credit_limit-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/voip_disabled-]',''),'[-voip_disabled-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/voip_credit_limit_day-]',''),'[-voip_credit_limit_day-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/balance-]',''),'[-balance-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/balance_usd-]',''),'[-balance_usd-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/voip_is_day_calc-]','"'),'[-voip_is_day_calc-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/region-]',''),'[-region-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/last_account_date-]','"'),'[-last_account_date-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/last_payed_voip_month-]','"'),'[-last_payed_voip_month-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/mail_print-]','"'),'[-mail_print-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/mail_who-]','"'),'[-mail_who-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/head_company-]','"'),'[-head_company-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/head_company_address_jur-]','"'),'[-head_company_address_jur-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/created-]','"'),'[-created-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/bill_rename1-]','"'),'[-bill_rename1-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/nds_calc_method-]','"'),'[-nds_calc_method-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/admin_contact_id-]',''),'[-admin_contact_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/admin_is_active-]',''),'[-admin_is_active-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_agent-]','"'),'[-is_agent-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_bill_only_contract-]',''),'[-is_bill_only_contract-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_bill_with_refund-]',''),'[-is_bill_with_refund-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_with_consignee-]',''),'[-is_with_consignee-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/consignee-]','"'),'[-consignee-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_upd_without_sign-]',''),'[-is_upd_without_sign-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_active-]',''),'[-is_active-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_blocked-]',''),'[-is_blocked-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_closed-]',''),'[-is_closed-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/timezone_name-]','"'),'[-timezone_name-]','"') WHERE `model` = 'ClientAccount';

    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/id-]',''),'[-id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/client-]','"'),'[-client-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/super_id-]',''),'[-super_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/contragent_id-]',''),'[-contragent_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/contract_id-]',''),'[-contract_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/country_id-]',''),'[-country_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/password-]','"'),'[-password-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/password_type-]','"'),'[-password_type-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/comment-]','"'),'[-comment-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/status-]','"'),'[-status-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/usd_rate_percent-]',''),'[-usd_rate_percent-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/address_post-]','"'),'[-address_post-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/address_post_real-]','"'),'[-address_post_real-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/support-]','"'),'[-support-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/login-]','"'),'[-login-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/bik-]','"'),'[-bik-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/bank_properties-]','"'),'[-bank_properties-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/currency-]','"'),'[-currency-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/currency_bill-]','"'),'[-currency_bill-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/stamp-]','"'),'[-stamp-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/nal-]','"'),'[-nal-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/telemarketing-]','"'),'[-telemarketing-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/sale_channel-]',''),'[-sale_channel-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/uid-]','"'),'[-uid-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/site_req_no-]','"'),'[-site_req_no-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/hid_rtsaldo_date-]','"'),'[-hid_rtsaldo_date-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/hid_rtsaldo_RUB-]',''),'[-hid_rtsaldo_RUB-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/hid_rtsaldo_USD-]',''),'[-hid_rtsaldo_USD-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/credit_USD-]',''),'[-credit_USD-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/credit_RUB-]',''),'[-credit_RUB-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/credit-]',''),'[-credit-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/user_impersonate-]','"'),'[-user_impersonate-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/address_connect-]','"'),'[-address_connect-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/phone_connect-]','"'),'[-phone_connect-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/id_all4net-]',''),'[-id_all4net-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/dealer_comment-]','"'),'[-dealer_comment-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/form_type-]','"'),'[-form_type-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/metro_id-]',''),'[-metro_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/payment_comment-]','"'),'[-payment_comment-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/previous_reincarnation-]','"'),'[-previous_reincarnation-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/cli_1c-]','"'),'[-cli_1c-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/con_1c-]','"'),'[-con_1c-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/corr_acc-]','"'),'[-corr_acc-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/pay_acc-]','"'),'[-pay_acc-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/bank_name-]','"'),'[-bank_name-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/bank_city-]','"'),'[-bank_city-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/sync_1c-]','"'),'[-sync_1c-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/price_type-]','"'),'[-price_type-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/voip_credit_limit-]',''),'[-voip_credit_limit-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/voip_disabled-]',''),'[-voip_disabled-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/voip_credit_limit_day-]',''),'[-voip_credit_limit_day-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/balance-]',''),'[-balance-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/balance_usd-]',''),'[-balance_usd-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/voip_is_day_calc-]',''),'[-voip_is_day_calc-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/region-]',''),'[-region-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/last_account_date-]','"'),'[-last_account_date-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/last_payed_voip_month-]','"'),'[-last_payed_voip_month-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/mail_print-]','"'),'[-mail_print-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/mail_who-]','"'),'[-mail_who-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/head_company-]','"'),'[-head_company-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/head_company_address_jur-]','"'),'[-head_company_address_jur-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/created-]','"'),'[-created-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/bill_rename1-]','"'),'[-bill_rename1-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/nds_calc_method-]','"'),'[-nds_calc_method-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/admin_contact_id-]',''),'[-admin_contact_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/admin_is_active-]',''),'[-admin_is_active-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_agent-]','"'),'[-is_agent-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_bill_only_contract-]',''),'[-is_bill_only_contract-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_bill_with_refund-]',''),'[-is_bill_with_refund-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_with_consignee-]',''),'[-is_with_consignee-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/consignee-]','"'),'[-consignee-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_upd_without_sign-]',''),'[-is_upd_without_sign-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_active-]',''),'[-is_active-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_blocked-]',''),'[-is_blocked-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/is_closed-]',''),'[-is_closed-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `data_json` = REPLACE(REPLACE(`data_json`, '[-/timezone_name-]','"'),'[-timezone_name-]','"') WHERE `model` = 'ClientAccount';

    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/id-]',''),'[-id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/client-]','"'),'[-client-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/super_id-]',''),'[-super_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/contragent_id-]',''),'[-contragent_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/contract_id-]',''),'[-contract_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/country_id-]',''),'[-country_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/password-]','"'),'[-password-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/password_type-]','"'),'[-password_type-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/comment-]','"'),'[-comment-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/status-]','"'),'[-status-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/usd_rate_percent-]',''),'[-usd_rate_percent-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/address_post-]','"'),'[-address_post-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/address_post_real-]','"'),'[-address_post_real-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/support-]','"'),'[-support-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/login-]','"'),'[-login-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/bik-]','"'),'[-bik-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/bank_properties-]','"'),'[-bank_properties-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/currency-]','"'),'[-currency-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/currency_bill-]','"'),'[-currency_bill-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/stamp-]','"'),'[-stamp-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/nal-]','"'),'[-nal-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/telemarketing-]','"'),'[-telemarketing-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/sale_channel-]',''),'[-sale_channel-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/uid-]','"'),'[-uid-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/site_req_no-]','"'),'[-site_req_no-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/hid_rtsaldo_date-]','"'),'[-hid_rtsaldo_date-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/hid_rtsaldo_RUB-]',''),'[-hid_rtsaldo_RUB-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/hid_rtsaldo_USD-]',''),'[-hid_rtsaldo_USD-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/credit_USD-]',''),'[-credit_USD-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/credit_RUB-]',''),'[-credit_RUB-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/credit-]',''),'[-credit-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/user_impersonate-]','"'),'[-user_impersonate-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/address_connect-]','"'),'[-address_connect-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/phone_connect-]','"'),'[-phone_connect-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/id_all4net-]',''),'[-id_all4net-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/dealer_comment-]','"'),'[-dealer_comment-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/form_type-]','"'),'[-form_type-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/metro_id-]',''),'[-metro_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/payment_comment-]','"'),'[-payment_comment-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/previous_reincarnation-]','"'),'[-previous_reincarnation-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/cli_1c-]','"'),'[-cli_1c-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/con_1c-]','"'),'[-con_1c-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/corr_acc-]','"'),'[-corr_acc-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/pay_acc-]','"'),'[-pay_acc-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/bank_name-]','"'),'[-bank_name-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/bank_city-]','"'),'[-bank_city-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/sync_1c-]','"'),'[-sync_1c-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/price_type-]','"'),'[-price_type-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/voip_credit_limit-]',''),'[-voip_credit_limit-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/voip_disabled-]',''),'[-voip_disabled-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/voip_credit_limit_day-]',''),'[-voip_credit_limit_day-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/balance-]',''),'[-balance-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/balance_usd-]',''),'[-balance_usd-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/voip_is_day_calc-]',''),'[-voip_is_day_calc-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/region-]',''),'[-region-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/last_account_date-]','"'),'[-last_account_date-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/last_payed_voip_month-]','"'),'[-last_payed_voip_month-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/mail_print-]','"'),'[-mail_print-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/mail_who-]','"'),'[-mail_who-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/head_company-]','"'),'[-head_company-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/head_company_address_jur-]','"'),'[-head_company_address_jur-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/created-]','"'),'[-created-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/bill_rename1-]','"'),'[-bill_rename1-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/nds_calc_method-]','"'),'[-nds_calc_method-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/admin_contact_id-]',''),'[-admin_contact_id-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/admin_is_active-]',''),'[-admin_is_active-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/is_agent-]','"'),'[-is_agent-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/is_bill_only_contract-]',''),'[-is_bill_only_contract-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/is_bill_with_refund-]',''),'[-is_bill_with_refund-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/is_with_consignee-]',''),'[-is_with_consignee-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/consignee-]','"'),'[-consignee-]','"') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/is_upd_without_sign-]',''),'[-is_upd_without_sign-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/is_active-]',''),'[-is_active-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/is_blocked-]',''),'[-is_blocked-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/is_closed-]',''),'[-is_closed-]','') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(REPLACE(`prev_data_json`, '[-/timezone_name-]','"'),'[-timezone_name-]','"') WHERE `model` = 'ClientAccount';

    UPDATE history_changes SET `data_json` = REPLACE(`data_json`,'"null"', 'null') WHERE `model` = 'ClientAccount';
    UPDATE history_changes SET `prev_data_json` = REPLACE(`prev_data_json`,'"null"', 'null') WHERE `model` = 'ClientAccount';
    UPDATE history_version SET `data_json` = REPLACE(`data_json`,'"null"', 'null') WHERE `model` = 'ClientAccount';

    UPDATE history_changes SET `data_json` = REPLACE(`data_json`, '":,','":"",');
    UPDATE history_changes SET `prev_data_json` = REPLACE(`prev_data_json`, '":,','":"",');
    UPDATE history_version SET `data_json` = REPLACE(`data_json`, '":,','":"",');