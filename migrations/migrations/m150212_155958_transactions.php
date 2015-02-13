<?php

class m150212_155958_transactions extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `newpayments`
              MODIFY COLUMN `id`  int(11) UNSIGNED NOT NULL AUTO_INCREMENT FIRST ;
        ");

        $this->execute("
            DROP TABLE IF EXISTS `transaction` ;
        ");

        $this->execute("
            CREATE TABLE `transaction` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `client_account_id` int(11) NOT NULL,
              `source` enum('stat','bill','payment','jerasoft') NOT NULL,
              `billing_period` date DEFAULT NULL,
              `service_type` varchar(30) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
              `service_id` int(10) unsigned DEFAULT NULL,
              `package_id` int(10) unsigned DEFAULT NULL,
              `transaction_type` enum('connecting','periodical','resource') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
              `transaction_date` datetime NOT NULL,
              `period_from` datetime DEFAULT NULL,
              `period_to` datetime DEFAULT NULL,
              `name` varchar(200) DEFAULT NULL,
              `price` decimal(13,4) DEFAULT NULL,
              `amount` decimal(13,6) DEFAULT NULL,
              `tax_type_id` int(11) DEFAULT NULL,
              `sum` decimal(11,2) NOT NULL,
              `sum_tax` decimal(11,2) DEFAULT NULL,
              `sum_without_tax` decimal(11,2) DEFAULT NULL,
              `is_partial_write_off` tinyint(4) NOT NULL,
              `effective_amount` decimal(13,6) DEFAULT NULL,
              `effective_sum` decimal(11,2) NOT NULL,
              `payment_id` int(10) unsigned DEFAULT NULL,
              `bill_id` int(10) unsigned DEFAULT NULL,
              `bill_line_id` int(10) unsigned DEFAULT NULL,
              `deleted` tinyint(4) DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `idx_source_payment_id` (`source`,`payment_id`),
              KEY `idx_client_account_id_source_billing_period` (`client_account_id`,`source`,`billing_period`),
              KEY `idx_client_account_id_source_transaction_date` (`client_account_id`,`source`,`transaction_date`)
            ) ENGINE=InnoDB AUTO_INCREMENT=100000 DEFAULT CHARSET=utf8;
        ");

        $this->execute("
            ALTER TABLE `transaction` ADD CONSTRAINT `fk_transaction__bill_id` FOREIGN KEY (`bill_id`) REFERENCES `newbills` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
        ");

        $this->execute("
            ALTER TABLE `transaction` ADD CONSTRAINT `fk_transaction__payment_id` FOREIGN KEY (`payment_id`) REFERENCES `newpayments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
        ");

        $this->execute("
            insert into `transaction`
                (client_account_id, source, billing_period, service_type, service_id, transaction_type, transaction_date, period_from, period_to, name, price, amount, tax_type_id, sum, sum_tax, sum_without_tax, is_partial_write_off, effective_amount, effective_sum, bill_id, bill_line_id)
                select
                        b.client_id as client_account_id,
                        case when l.service in ('usage_voip','usage_welltime','usage_sms','usage_virtpbx','usage_ip_ports','usage_extra','emails') then 'stat' else 'bill' end as source,
                        date_format(b.bill_date, '%Y-%m-01') as billing_period,
                        l.service, l.id_service,
                        case when l.service in ('usage_voip','usage_welltime','usage_sms','usage_virtpbx','usage_ip_ports','usage_extra','emails') then 'periodical' else null end as transaction_type,
                        ifnull(l.date_from, b.bill_date) as transaction_date,
                        case when l.service in ('usage_voip','usage_welltime','usage_sms','usage_virtpbx','usage_ip_ports','usage_extra','emails') then l.date_from else null end as period_from,
                        case when l.service in ('usage_voip','usage_welltime','usage_sms','usage_virtpbx','usage_ip_ports','usage_extra','emails') then l.date_to else null end as period_to,
                        l.item, l.price, l.amount, l.tax_type_id, l.sum, l.sum_tax, l.sum_without_tax, 0 as is_partial_write_off, l.amount, l.sum, b.id, l.pk
                    from newbill_lines l
                    inner join newbills b on l.bill_no=b.bill_no
                    where b.is_approved and l.type != 'zadatok'
        ");

        $this->execute("
            insert into `transaction`
                (client_account_id, source, transaction_date, sum, effective_sum, payment_id)
                    select client_id, 'payment',payment_date, sum, sum, id from newpayments
        ");
    }

    public function down()
    {
        echo "m150212_155958_transactions cannot be reverted.\n";

        return false;
    }
}