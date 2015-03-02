<?php

class m150212_155931_bills_payments extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `newpayments`
            ADD COLUMN `sum`  decimal(11,2) NOT NULL DEFAULT 0.00 AFTER `ecash_operator`,
            ADD COLUMN `original_sum`  decimal(11,2) NULL AFTER `currency`,
            ADD COLUMN `original_currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NULL AFTER `original_sum`;
        ");

        $this->execute("
            ALTER TABLE `newbills`
            ADD COLUMN `is_approved`  tinyint NULL AFTER `currency`,
            ADD COLUMN `sum_with_unapproved`  decimal(11,2) NULL AFTER `sum`,
            ADD COLUMN `is_use_tax`  tinyint NULL AFTER `sum_with_unapproved`,
            MODIFY COLUMN `inv1_date`  date NOT NULL DEFAULT '0000-00-00' AFTER `inv1_rate`,
            MODIFY COLUMN `inv2_date`  date NOT NULL DEFAULT '0000-00-00' AFTER `inv2_rate`,
            MODIFY COLUMN `inv3_date`  date NOT NULL DEFAULT '0000-00-00' AFTER `inv3_rate`,
            MODIFY COLUMN `gen_bill_date`  date NOT NULL DEFAULT '0000-00-00' AFTER `gen_bill_rate`;
        ");

        $this->execute("
            update newpayments
            set `sum`=sum_rub, original_currency='RUB', original_sum = sum_rub
            where payment_rate=1;
        ");

        $this->execute("
            update newpayments
            set `sum`=sum_rub, original_currency='USD', original_sum = round(sum_rub / payment_rate,2)
            where payment_rate!=1;
        ");

        $this->execute("
            update newpayments_orders
            set `sum`=sum_rub
            where currency='USD';
        ");

        $this->execute("
            DROP PROCEDURE IF EXISTS `switch_bill_cleared`;
        ");

        $this->execute("
          update newbills set is_approved=cleared_flag, sum_with_unapproved=if(cleared_flag>0, `sum`, cleared_sum);
        ");

        $this->execute("
            update newbills b
            inner join clients c on b.client_id=c.id
            set b.is_use_tax = if(c.nds_zero>0,0,1);
        ");

        $this->execute("
            update newbill_lines
            set
              sum_tax = round(18 * sum / 118, 2),
              sum_without_tax = round(100 * sum / 118, 2),
              tax_type_id=18,
              is_price_includes_tax=0;
        ");

        $this->execute("
            update newbill_lines l
            left join newbills b on b.bill_no=l.bill_no
            left join clients c on c.id=b.client_id
            set
              l.sum_without_tax = l.sum,
              l.sum_tax = 0,
              l.tax_type_id=0,
              l.is_price_includes_tax=0
            where c.nds_zero>0;
        ");

        $this->execute("
            update newbill_lines l
            left join g_goods g on g.id=l.item_id
            set
              l.sum_without_tax = l.sum,
              l.sum_tax = 0,
              l.is_price_includes_tax=0,
              l.price = round((l.sum + l.discount_auto + l.discount_set) / l.amount,4),
              l.tax_type_id=0
            where l.service = '1C' and g.nds=0 and l.amount > 0;
        ");


        $this->execute("
            ALTER TABLE `newpayments`
            DROP COLUMN `sum_rub`,
            DROP COLUMN `push_1c`,
            DROP COLUMN `sync_1c`;
        ");

        $this->execute("
            ALTER TABLE `newbills`
            DROP COLUMN `inv_rub`,
            DROP COLUMN `inv1_rate`,
            DROP COLUMN `inv1_date`,
            DROP COLUMN `inv2_rate`,
            DROP COLUMN `inv2_date`,
            DROP COLUMN `inv3_rate`,
            DROP COLUMN `inv3_date`,
            DROP COLUMN `gen_bill_rub`,
            DROP COLUMN `gen_bill_rate`,
            DROP COLUMN `gen_bill_date`,
            DROP COLUMN `cleared_sum`,
            DROP COLUMN `cleared_flag`,
            DROP COLUMN `sum_total`,
            DROP COLUMN `sum_total_with_unapproved`,
            DROP COLUMN `doc_sum_total`,
            DROP COLUMN `payed_ya`;
        ");


        $this->execute("
            ALTER TABLE `newpayments_orders`
            DROP COLUMN `currency`,
            DROP COLUMN `sum_rub`,
            DROP COLUMN `sync_1c`,
            MODIFY COLUMN `client_id`  int(11) NOT NULL FIRST ;
        ");

        $this->execute("
            ALTER TABLE `newbill_lines`
            DROP COLUMN `all4net_price`,
            DROP COLUMN `sum_with_tax`,
            DROP COLUMN `doc_sum_without_tax`,
            DROP COLUMN `doc_sum_tax`,
            DROP COLUMN `doc_sum_with_tax`,
            DROP COLUMN `xxx`;
        ");

        $this->execute("
            DROP TRIGGER `newbill_lines_bill_move`;
            DROP TRIGGER `newbill_lines_delete`;
        ");

        $this->execute("
            DROP TABLE `newbills_overprice_additions`;
            DROP TABLE `newbills_overprice_aggregate`;
            DROP TABLE `newbills_rtsaldo_changes`;
            DROP TABLE `newpayments_webmoney`;
        ");
    }

    public function down()
    {
        echo "m150212_155931_bills_payments cannot be reverted.\n";

        return false;
    }
}