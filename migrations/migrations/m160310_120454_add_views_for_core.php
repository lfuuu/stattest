<?php

class m160310_120454_add_views_for_core extends \app\classes\Migration
{
    public function up()
    {
        echo "VIEW For Accounts\n";
        $this->execute("CREATE VIEW view_client_account_ro AS select `clients`.`id` AS `id`,`clients`.`client` AS `client`,`clients`.`super_id` AS `super_id`,`clients`.`contract_id` AS `contract_id`,`clients`.`country_id` AS `country_id`,`clients`.`password` AS `password`,`clients`.`password_type` AS `password_type`,`clients`.`comment` AS `comment`,`clients`.`status` AS `status`,`clients`.`usd_rate_percent` AS `usd_rate_percent`,`clients`.`address_post` AS `address_post`,`clients`.`address_post_real` AS `address_post_real`,`clients`.`support` AS `support`,`clients`.`login` AS `login`,`clients`.`bik` AS `bik`,`clients`.`bank_properties` AS `bank_properties`,`clients`.`currency` AS `currency`,`clients`.`currency_bill` AS `currency_bill`,`clients`.`stamp` AS `stamp`,`clients`.`nal` AS `nal`,`clients`.`telemarketing` AS `telemarketing`,`clients`.`sale_channel` AS `sale_channel`,`clients`.`uid` AS `uid`,`clients`.`site_req_no` AS `site_req_no`,`clients`.`hid_rtsaldo_date` AS `hid_rtsaldo_date`,`clients`.`hid_rtsaldo_RUB` AS `hid_rtsaldo_RUB`,`clients`.`hid_rtsaldo_USD` AS `hid_rtsaldo_USD`,`clients`.`credit_USD` AS `credit_USD`,`clients`.`credit_RUB` AS `credit_RUB`,`clients`.`credit` AS `credit`,`clients`.`user_impersonate` AS `user_impersonate`,`clients`.`address_connect` AS `address_connect`,`clients`.`phone_connect` AS `phone_connect`,`clients`.`id_all4net` AS `id_all4net`,`clients`.`dealer_comment` AS `dealer_comment`,`clients`.`form_type` AS `form_type`,`clients`.`metro_id` AS `metro_id`,`clients`.`previous_reincarnation` AS `previous_reincarnation`,`clients`.`cli_1c` AS `cli_1c`,`clients`.`con_1c` AS `con_1c`,`clients`.`corr_acc` AS `corr_acc`,`clients`.`pay_acc` AS `pay_acc`,`clients`.`bank_name` AS `bank_name`,`clients`.`bank_city` AS `bank_city`,`clients`.`sync_1c` AS `sync_1c`,`clients`.`price_type` AS `price_type`,`clients`.`voip_credit_limit` AS `voip_credit_limit`,`clients`.`voip_disabled` AS `voip_disabled`,`clients`.`voip_credit_limit_day` AS `voip_credit_limit_day`,`clients`.`balance` AS `balance`,`clients`.`balance_usd` AS `balance_usd`,`clients`.`voip_is_day_calc` AS `voip_is_day_calc`,`clients`.`region` AS `region`,`clients`.`last_account_date` AS `last_account_date`,`clients`.`last_payed_voip_month` AS `last_payed_voip_month`,`clients`.`mail_print` AS `mail_print`,`clients`.`mail_who` AS `mail_who`,`clients`.`head_company` AS `head_company`,`clients`.`head_company_address_jur` AS `head_company_address_jur`,`clients`.`created` AS `created`,`clients`.`bill_rename1` AS `bill_rename1`,`clients`.`nds_calc_method` AS `nds_calc_method`,`clients`.`admin_contact_id` AS `admin_contact_id`,`clients`.`admin_is_active` AS `admin_is_active`,`clients`.`is_agent` AS `is_agent`,`clients`.`is_bill_only_contract` AS `is_bill_only_contract`,`clients`.`is_bill_with_refund` AS `is_bill_with_refund`,`clients`.`is_with_consignee` AS `is_with_consignee`,`clients`.`consignee` AS `consignee`,`clients`.`is_upd_without_sign` AS `is_upd_without_sign`,`clients`.`price_include_vat` AS `price_include_vat`,`clients`.`is_active` AS `is_active`,`clients`.`is_blocked` AS `is_blocked`,`clients`.`is_closed` AS `is_closed`,`clients`.`timezone_name` AS `timezone_name`, `clients`.`lk_balance_view_mode` AS `lk_balance_view_mode`,`clients`.`anti_fraud_disabled` AS `anti_fraud_disabled` from `clients`");
        echo "VIEW For Clients\n";
        $this->execute("CREATE VIEW view_client_super_ro AS SELECT id,name,financial_manager_id FROM client_super;");
	echo "VIEW For voip\n";
	$this->execute("CREATE VIEW view_client_usage_voip_ro AS SELECT id, tarif, region, actual_from, actual_to, client, type_id, activation_dt, expire_dt, E164, no_of_lines, status, address, address_from_datacenter_id, edit_user_id, is_trunk, created, one_sip, line7800_id FROM usage_voip;");
	echo "VIEW For call-chat\n";
	$this->execute("CREATE VIEW view_client_usage_call_chat_ro AS SELECT id,client,activation_dt,expire_dt,actual_from,actual_to,status,comment,tarif_id FROM usage_call_chat;");
	echo "VIEW For emails\n";
	$this->execute("CREATE VIEW view_client_usage_emails_ro AS SELECT id,last_modified,actual_from,actual_to,local_part,domain,password,client,box_size,box_quota,enabled,spam_act,smtp_auth,status FROM emails");
	echo "VIEW For extra\n";
	$this->execute("CREATE VIEW view_client_usage_extra_ro AS SELECT id,client,activation_dt,expire_dt,actual_from,actual_to,param_value,amount,status,comment,tarif_id,code FROM usage_extra;");
	echo "VIEW For ip-port\n";
	$this->execute("CREATE VIEW view_client_usage_ip_port_ro AS SELECT id,client,activation_dt,expire_dt,actual_from,actual_to,address,port_id,date_last_writeoff,status,speed_mgts,speed_update,amount FROM usage_ip_ports;");
	echo "VIEW For ip-routes\n";
	$this->execute("CREATE VIEW view_client_usage_ip_routes_ro AS SELECT id,activation_dt,expire_dt,actual_from,actual_to,port_id,net,nat_net,dnat,type,up_node,flows_node,comment,gpon_reserv FROM usage_ip_routes;");
	echo "VIEW For sms\n";
	$this->execute("CREATE VIEW view_client_usage_sms_ro AS SELECT id,client,activation_dt,expire_dt,actual_from,actual_to,status,comment,tarif_id FROM usage_sms;");
	echo "VIEW For tech-cpe\n";
	$this->execute("CREATE VIEW view_client_usage_tech_cpe_ro AS SELECT id,actual_from,actual_to,id_model,client,serial,mac,ip,ip_nat,ip_cidr,ip_gw,admin_login,admin_pass,numbers,logins,owner,tech_support,node,service,id_service,deposit_sumUSD,deposit_sumRUB,snmp,ast_autoconf FROM usage_tech_cpe;");
	echo "VIEW For usage_trunk\n";
	$this->execute("CREATE VIEW view_client_usage_trunk_ro AS SELECT id,client_account_id,connection_point_id,trunk_id,actual_from,actual_to,activation_dt,expire_dt,status,orig_enabled,term_enabled,orig_min_payment,term_min_payment,description,operator_id FROM usage_trunk;");
	echo "VIEW For usage_virtpbx\n";
	$this->execute("CREATE VIEW view_client_usage_virtpbx_ro AS SELECT id,client,region,activation_dt,expire_dt,actual_from,actual_to,amount,status,comment,tarif_id,moved_from FROM usage_virtpbx;");
	echo "VIEW For usage_voip_package\n";
	$this->execute("CREATE VIEW view_client_usage_voip_package_ro AS SELECT id,client,activation_dt,expire_dt,actual_from,actual_to,tariff_id,usage_voip_id,usage_trunk_id,status FROM usage_voip_package;");
	echo "VIEW For usage_welltime\n";
	$this->execute("CREATE VIEW view_client_usage_welltime_ro AS SELECT id,client,activation_dt,expire_dt,actual_from,actual_to,ip,amount,status,comment,tarif_id,router FROM usage_welltime;");
    }

    public function down()
    {
	$this->execute('DROP VIEW view_client_account_ro ;
DROP VIEW view_client_super_ro ;
DROP VIEW view_client_usage_voip_ro ;
DROP VIEW view_client_usage_call_chat_ro ;
DROP VIEW view_client_usage_emails_ro ;
DROP VIEW view_client_usage_extra_ro ;
DROP VIEW view_client_usage_ip_port_ro ;
DROP VIEW view_client_usage_ip_routes_ro ;
DROP VIEW view_client_usage_sms_ro ;
DROP VIEW view_client_usage_tech_cpe_ro ;
DROP VIEW view_client_usage_trunk_ro ;
DROP VIEW view_client_usage_virtpbx_ro ;
DROP VIEW view_client_usage_voip_package_ro ;
DROP VIEW view_client_usage_welltime_ro ;');

    }
}