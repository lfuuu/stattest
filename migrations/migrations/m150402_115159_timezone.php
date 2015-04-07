<?php

class m150402_115159_timezone extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `clients`
            ADD COLUMN `timezone_name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Europe/Moscow' AFTER `is_closed`;

            ALTER TABLE `user_users`
            ADD COLUMN `timezone_name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Europe/Moscow' AFTER `restriction_client_id`;

            ALTER TABLE `log_tarif`
            DROP COLUMN `actual_from`,
            DROP COLUMN `actual_to`;

            ALTER TABLE `usage_extra`
            ADD COLUMN `activation_dt`  datetime NULL AFTER `client`,
            ADD COLUMN `expire_dt`  datetime NULL AFTER `activation_dt`;

            ALTER TABLE `usage_ip_ports`
            ADD COLUMN `activation_dt`  datetime NULL AFTER `client`,
            ADD COLUMN `expire_dt`  datetime NULL AFTER `activation_dt`;

            ALTER TABLE `usage_ip_ppp`
            ADD COLUMN `activation_dt`  datetime NULL AFTER `client`,
            ADD COLUMN `expire_dt`  datetime NULL AFTER `activation_dt`;

            ALTER TABLE `usage_ip_routes`
            ADD COLUMN `activation_dt`  datetime NULL AFTER `id`,
            ADD COLUMN `expire_dt`  datetime NULL AFTER `activation_dt`;

            ALTER TABLE `usage_sms`
            ADD COLUMN `activation_dt`  datetime NULL AFTER `client`,
            ADD COLUMN `expire_dt`  datetime NULL AFTER `activation_dt`;

            ALTER TABLE `usage_virtpbx`
            ADD COLUMN `activation_dt`  datetime NULL AFTER `client`,
            ADD COLUMN `expire_dt`  datetime NULL AFTER `activation_dt`;

            ALTER TABLE `usage_voip`
            ADD COLUMN `activation_dt`  datetime NULL AFTER `client`,
            ADD COLUMN `expire_dt`  datetime NULL AFTER `activation_dt`;

            ALTER TABLE `usage_welltime`
            ADD COLUMN `activation_dt`  datetime NULL AFTER `client`,
            ADD COLUMN `expire_dt`  datetime NULL AFTER `activation_dt`;
        ");

        $this->execute("
            update clients set region = 99 where region=0;

            update clients c
            inner join regions r on r.id=c.region
            set c.timezone_name=r.timezone_name;

            update usage_extra set actual_from = '4000-01-01' where actual_from > '2020-01-01';
            update usage_ip_ports set actual_from = '4000-01-01' where actual_from > '2020-01-01';
            update usage_ip_ppp set actual_from = '4000-01-01' where actual_from > '2020-01-01';
            update usage_ip_routes set actual_from = '4000-01-01' where actual_from > '2020-01-01';
            update usage_sms set actual_from = '4000-01-01' where actual_from > '2020-01-01';
            update usage_virtpbx set actual_from = '4000-01-01' where actual_from > '2020-01-01';
            update usage_voip set actual_from = '4000-01-01' where actual_from > '2020-01-01';
            update usage_welltime set actual_from = '4000-01-01' where actual_from > '2020-01-01';

            update usage_extra set actual_to = '4000-01-01' where actual_to > '2020-01-01';
            update usage_ip_ports set actual_to = '4000-01-01' where actual_to > '2020-01-01';
            update usage_ip_ppp set actual_to = '4000-01-01' where actual_to > '2020-01-01';
            update usage_ip_routes set actual_to = '4000-01-01' where actual_to > '2020-01-01';
            update usage_sms set actual_to = '4000-01-01' where actual_to > '2020-01-01';
            update usage_virtpbx set actual_to = '4000-01-01' where actual_to > '2020-01-01';
            update usage_voip set actual_to = '4000-01-01' where actual_to > '2020-01-01';
            update usage_welltime set actual_to = '4000-01-01' where actual_to > '2020-01-01';

            update usage_extra set activation_dt = actual_from, expire_dt = DATE_ADD(actual_to, interval 86399 second);
            update usage_ip_ports set activation_dt = actual_from, expire_dt = DATE_ADD(actual_to, interval 86399 second);
            update usage_ip_ppp set activation_dt = actual_from, expire_dt = DATE_ADD(actual_to, interval 86399 second);
            update usage_ip_routes set activation_dt = actual_from, expire_dt = DATE_ADD(actual_to, interval 86399 second);
            update usage_sms set activation_dt = actual_from, expire_dt = DATE_ADD(actual_to, interval 86399 second);
            update usage_virtpbx set activation_dt = actual_from, expire_dt = DATE_ADD(actual_to, interval 86399 second);
            update usage_voip set activation_dt = actual_from, expire_dt = DATE_ADD(actual_to, interval 86399 second);
            update usage_welltime set activation_dt = actual_from, expire_dt = DATE_ADD(actual_to, interval 86399 second);

            update usage_extra u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 6 hour), expire_dt = date_sub(expire_dt , interval 6 hour) where c.timezone_name = 'Asia/Novosibirsk';
            update usage_extra u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 10 hour), expire_dt = date_sub(expire_dt , interval 10 hour) where c.timezone_name = 'Asia/Vladivostok';
            update usage_extra u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 5 hour), expire_dt = date_sub(expire_dt , interval 5 hour) where c.timezone_name = 'Asia/Yekaterinburg';
            update usage_extra u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 2 hour), expire_dt = date_sub(expire_dt , interval 2 hour) where c.timezone_name = 'Europe/Budapest';
            update usage_extra u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 3 hour), expire_dt = date_sub(expire_dt , interval 3 hour) where c.timezone_name in ('Europe/Moscow', 'Europe/Samara', 'Europe/Volgograd');

            update usage_ip_ports u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 6 hour), expire_dt = date_sub(expire_dt , interval 6 hour) where c.timezone_name = 'Asia/Novosibirsk';
            update usage_ip_ports u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 10 hour), expire_dt = date_sub(expire_dt , interval 10 hour) where c.timezone_name = 'Asia/Vladivostok';
            update usage_ip_ports u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 5 hour), expire_dt = date_sub(expire_dt , interval 5 hour) where c.timezone_name = 'Asia/Yekaterinburg';
            update usage_ip_ports u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 2 hour), expire_dt = date_sub(expire_dt , interval 2 hour) where c.timezone_name = 'Europe/Budapest';
            update usage_ip_ports u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 3 hour), expire_dt = date_sub(expire_dt , interval 3 hour) where c.timezone_name in ('Europe/Moscow', 'Europe/Samara', 'Europe/Volgograd');

            update usage_ip_ppp u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 6 hour), expire_dt = date_sub(expire_dt , interval 6 hour) where c.timezone_name = 'Asia/Novosibirsk';
            update usage_ip_ppp u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 10 hour), expire_dt = date_sub(expire_dt , interval 10 hour) where c.timezone_name = 'Asia/Vladivostok';
            update usage_ip_ppp u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 5 hour), expire_dt = date_sub(expire_dt , interval 5 hour) where c.timezone_name = 'Asia/Yekaterinburg';
            update usage_ip_ppp u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 2 hour), expire_dt = date_sub(expire_dt , interval 2 hour) where c.timezone_name = 'Europe/Budapest';
            update usage_ip_ppp u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 3 hour), expire_dt = date_sub(expire_dt , interval 3 hour) where c.timezone_name in ('Europe/Moscow', 'Europe/Samara', 'Europe/Volgograd');

            update usage_ip_routes u inner join usage_ip_ports p on p.id=u.port_id inner join clients c on c.client=p.client set u.activation_dt = date_sub(u.activation_dt, interval 6 hour), u.expire_dt = date_sub(u.expire_dt , interval 6 hour) where c.timezone_name = 'Asia/Novosibirsk';
            update usage_ip_routes u inner join usage_ip_ports p on p.id=u.port_id inner join clients c on c.client=p.client set u.activation_dt = date_sub(u.activation_dt, interval 10 hour), u.expire_dt = date_sub(u.expire_dt , interval 10 hour) where c.timezone_name = 'Asia/Vladivostok';
            update usage_ip_routes u inner join usage_ip_ports p on p.id=u.port_id inner join clients c on c.client=p.client set u.activation_dt = date_sub(u.activation_dt, interval 5 hour), u.expire_dt = date_sub(u.expire_dt , interval 5 hour) where c.timezone_name = 'Asia/Yekaterinburg';
            update usage_ip_routes u inner join usage_ip_ports p on p.id=u.port_id inner join clients c on c.client=p.client set u.activation_dt = date_sub(u.activation_dt, interval 2 hour), u.expire_dt = date_sub(u.expire_dt , interval 2 hour) where c.timezone_name = 'Europe/Budapest';
            update usage_ip_routes u inner join usage_ip_ports p on p.id=u.port_id inner join clients c on c.client=p.client set u.activation_dt = date_sub(u.activation_dt, interval 3 hour), u.expire_dt = date_sub(u.expire_dt , interval 2 hour) where c.timezone_name in ('Europe/Moscow', 'Europe/Samara', 'Europe/Volgograd');

            update usage_sms u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 6 hour), expire_dt = date_sub(expire_dt , interval 6 hour) where c.timezone_name = 'Asia/Novosibirsk';
            update usage_sms u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 10 hour), expire_dt = date_sub(expire_dt , interval 10 hour) where c.timezone_name = 'Asia/Vladivostok';
            update usage_sms u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 5 hour), expire_dt = date_sub(expire_dt , interval 5 hour) where c.timezone_name = 'Asia/Yekaterinburg';
            update usage_sms u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 2 hour), expire_dt = date_sub(expire_dt , interval 2 hour) where c.timezone_name = 'Europe/Budapest';
            update usage_sms u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 3 hour), expire_dt = date_sub(expire_dt , interval 3 hour) where c.timezone_name in ('Europe/Moscow', 'Europe/Samara', 'Europe/Volgograd');

            update usage_virtpbx u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 6 hour), expire_dt = date_sub(expire_dt , interval 6 hour) where c.timezone_name = 'Asia/Novosibirsk';
            update usage_virtpbx u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 10 hour), expire_dt = date_sub(expire_dt , interval 10 hour) where c.timezone_name = 'Asia/Vladivostok';
            update usage_virtpbx u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 5 hour), expire_dt = date_sub(expire_dt , interval 5 hour) where c.timezone_name = 'Asia/Yekaterinburg';
            update usage_virtpbx u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 2 hour), expire_dt = date_sub(expire_dt , interval 2 hour) where c.timezone_name = 'Europe/Budapest';
            update usage_virtpbx u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 3 hour), expire_dt = date_sub(expire_dt , interval 3 hour) where c.timezone_name in ('Europe/Moscow', 'Europe/Samara', 'Europe/Volgograd');

            update usage_voip u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 6 hour), expire_dt = date_sub(expire_dt , interval 6 hour) where c.timezone_name = 'Asia/Novosibirsk';
            update usage_voip u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 10 hour), expire_dt = date_sub(expire_dt , interval 10 hour) where c.timezone_name = 'Asia/Vladivostok';
            update usage_voip u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 5 hour), expire_dt = date_sub(expire_dt , interval 5 hour) where c.timezone_name = 'Asia/Yekaterinburg';
            update usage_voip u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 2 hour), expire_dt = date_sub(expire_dt , interval 2 hour) where c.timezone_name = 'Europe/Budapest';
            update usage_voip u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 3 hour), expire_dt = date_sub(expire_dt , interval 3 hour) where c.timezone_name in ('Europe/Moscow', 'Europe/Samara', 'Europe/Volgograd');

            update usage_welltime u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 6 hour), expire_dt = date_sub(expire_dt , interval 6 hour) where c.timezone_name = 'Asia/Novosibirsk';
            update usage_welltime u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 10 hour), expire_dt = date_sub(expire_dt , interval 10 hour) where c.timezone_name = 'Asia/Vladivostok';
            update usage_welltime u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 5 hour), expire_dt = date_sub(expire_dt , interval 5 hour) where c.timezone_name = 'Asia/Yekaterinburg';
            update usage_welltime u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 2 hour), expire_dt = date_sub(expire_dt , interval 2 hour) where c.timezone_name = 'Europe/Budapest';
            update usage_welltime u inner join clients c on c.client=u.client set activation_dt = date_sub(activation_dt, interval 3 hour), expire_dt = date_sub(expire_dt , interval 3 hour) where c.timezone_name in ('Europe/Moscow', 'Europe/Samara', 'Europe/Volgograd');
        ");

        $this->execute("
            update client_files set ts = date_sub(ts, interval 3 hour);

            update client_inn set ts = date_sub(ts, interval 3 hour);

            update client_pay_acc set date = date_sub(date, interval 3 hour);

            update client_statuses set ts = date_sub(ts, interval 3 hour);

            update client_contacts set ts = date_sub(ts, interval 3 hour);

            update client_contracts set ts = date_sub(ts, interval 3 hour);

            update log_block set ts = date_sub(ts, interval 3 hour);

            update log_client set ts = date_sub(ts, interval 3 hour);

            update log_contract_template_edit set date = date_sub(date, interval 3 hour);

            update log_newbills set ts = date_sub(ts, interval 3 hour);

            update log_newbills_static set ts = date_sub(ts, interval 3 hour);

            update log_send_voip_settings set date = date_sub(date, interval 3 hour);

            update log_tarif set ts = date_sub(ts, interval 3 hour);

            update log_tech_cpe set ts = date_sub(ts, interval 3 hour);

            update log_usage_history set ts = date_sub(ts, interval 3 hour);

            update log_usage_ip_routes set ts = date_sub(ts, interval 3 hour);

            update mail_job set date_edit = date_sub(date_edit, interval 3 hour);

            update mail_letter set send_date = date_sub(send_date, interval 3 hour);

            update mail_object set view_ts = date_sub(view_ts, interval 3 hour);

            update newbill_change_log set date = date_sub(date, interval 3 hour);

            update newbill_send set last_send = date_sub(last_send, interval 3 hour);

            update newbill_sms set sms_send = date_sub(sms_send, interval 3 hour);

            update newbill_sms set sms_get_time = date_sub(sms_get_time, interval 3 hour);

            update newbills_add_info set sms_send = date_sub(sms_send, interval 3 hour);

            update newbills_add_info set sms_get_time = date_sub(sms_get_time, interval 3 hour);

            update newbills_documents set ts = date_sub(ts, interval 3 hour);

            update newpayments set add_date = date_sub(add_date, interval 3 hour);

            update newsaldo set edit_time = date_sub(edit_time, interval 3 hour);

            update send_assigns set last_send = date_sub(last_send, interval 3 hour);

            update send_client set last_send = date_sub(last_send, interval 3 hour);

            update stats_send set last_send = date_sub(last_send, interval 3 hour);

            update tarifs_extra set edit_time = date_sub(edit_time, interval 3 hour);

            update tarifs_hosting set edit_time = date_sub(edit_time, interval 3 hour);

            update tarifs_internet set edit_time = date_sub(edit_time, interval 3 hour);

            update tarifs_sms set edit_time = date_sub(edit_time, interval 3 hour);

            update tarifs_virtpbx set edit_time = date_sub(edit_time, interval 3 hour);

            update tarifs_voip set edit_time = date_sub(edit_time, interval 3 hour);

            update tt_doer_stages set date = date_sub(date, interval 3 hour);

            update tt_stages set date_edit = date_sub(date_edit, interval 3 hour);
            update tt_stages set date_start = date_sub(date_start, interval 3 hour);
            update tt_stages set date_finish_desired = date_sub(date_finish_desired, interval 3 hour);

            update tt_troubles set date_creation = date_sub(date_creation, interval 3 hour);
            update tt_troubles set date_close = date_sub(date_close, interval 3 hour);

            update tt_troubles set date_close = date_sub(date_close, interval 3 hour);

        ");
    }

    public function down()
    {
        echo "m150402_115159_timezone cannot be reverted.\n";

        return false;
    }
}