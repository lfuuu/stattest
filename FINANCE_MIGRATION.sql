ALTER TABLE `newpayments`
ADD COLUMN `sum`  decimal(11,2) NOT NULL DEFAULT 0.00 AFTER `ecash_operator`,
ADD COLUMN `original_sum`  decimal(11,2) NULL AFTER `currency`,
ADD COLUMN `original_currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NULL AFTER `original_sum`;

ALTER TABLE `newbills`
ADD COLUMN `is_approved`  tinyint NULL AFTER `currency`,
ADD COLUMN `sum_with_unapproved`  decimal(11,2) NULL AFTER `sum`,
ADD COLUMN `is_use_tax`  tinyint NULL AFTER `sum_with_unapproved`,
MODIFY COLUMN `inv1_date`  date NOT NULL DEFAULT '0000-00-00' AFTER `inv1_rate`,
MODIFY COLUMN `inv2_date`  date NOT NULL DEFAULT '0000-00-00' AFTER `inv2_rate`,
MODIFY COLUMN `inv3_date`  date NOT NULL DEFAULT '0000-00-00' AFTER `inv3_rate`,
MODIFY COLUMN `gen_bill_date`  date NOT NULL DEFAULT '0000-00-00' AFTER `gen_bill_rate`;

update newpayments
set `sum`=sum_rub, original_currency='RUB', original_sum = sum_rub
where payment_rate=1;

update newpayments
set `sum`=sum_rub, original_currency='USD', original_sum = round(sum_rub / payment_rate,2)
where payment_rate!=1;

update newpayments_orders
set `sum`=sum_rub
where currency='USD';



update newbills set is_approved=cleared_flag, sum_with_unapproved=if(cleared_flag>0, `sum`, cleared_sum);

update newbills b
inner join clients c on b.client_id=c.id
set b.is_use_tax = if(c.nds_zero>0,0,1);


DROP PROCEDURE IF EXISTS `switch_bill_cleared`;

DELIMITER $$
CREATE DEFINER = `latyntsev`@`localhost` PROCEDURE `switch_bill_cleared`(in p_bill_no varbinary(32))
begin
		declare p_sum_with_unapproved decimal(11,2) default 0;
		declare p_sum decimal(11,2) default 0;
		declare p_is_approved INTEGER default 0;
		declare p_client_id INTEGER(11) default 0;

		start transaction;
				select `client_id`, `is_approved`, `sum_with_unapproved`
				into p_client_id, p_is_approved, p_sum_with_unapproved
				from newbills
				where bill_no = p_bill_no lock in share mode;

				if p_is_approved > 0 then
						set p_is_approved = 0;
						set p_sum = 0;
				else
						set p_is_approved = 1;
						set p_sum = p_sum_with_unapproved;
				end if;

				update newbills
				set `sum` = p_sum, `is_approved` = p_is_approved
				where bill_no = v_bill_no;
		commit;

		call add_event('update_balance', p_client_id);

end;$$
DELIMITER ;



update newbill_lines 
set 
  sum_tax = round(18 * sum / 118, 2),
  sum_without_tax = round(100 * sum / 118, 2),
  tax_type_id=18, 
  is_price_includes_tax=0;

update newbill_lines l
left join newbills b on b.bill_no=l.bill_no
left join clients c on c.id=b.client_id
set
  l.sum_without_tax = l.sum_with_tax,
  l.sum_tax = 0,
  l.tax_type_id=0,
  l.is_price_includes_tax=0
where c.nds_zero>0;

update newbill_lines l
left join g_goods g on g.id=l.item_id
set
  l.sum_without_tax = l.sum_with_tax,
  l.sum_tax = 0,   
  l.is_price_includes_tax=0,
  l.price = round((l.sum_with_tax + l.discount_auto + l.discount_set) / l.amount,4),
  l.tax_type_id=0
where l.service = '1C' and g.nds=0 and l.amount > 0;










CREATE TABLE `transaction` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_account_id` int(11) NOT NULL,
  `source` enum('stat_bill_line','1c_bill_line','whmcs_transaction','jerasoft_transaction') NOT NULL,
  `name` varchar(200) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `bill_id` int(11) DEFAULT NULL,
  `bill_line_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `newbill_lines`
ADD COLUMN `transaction_id`  int NULL AFTER `sum_with_tax`;





ALTER TABLE `newpayments`
DROP COLUMN `sum_rub`,
DROP COLUMN `push_1c`,
DROP COLUMN `sync_1c`;



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


ALTER TABLE `newpayments_orders`
DROP COLUMN `currency`,
DROP COLUMN `sum_rub`,
DROP COLUMN `sync_1c`,
MODIFY COLUMN `client_id`  int(11) NOT NULL FIRST ;

ALTER TABLE `newbill_lines`
DROP COLUMN `all4net_price`,
DROP COLUMN `doc_sum_without_tax`,
DROP COLUMN `doc_sum_tax`,
DROP COLUMN `doc_sum_with_tax`,
DROP COLUMN `xxx`;

DROP TRIGGER `newbill_lines_bill_move`;
DROP TRIGGER `newbill_lines_delete`;

DROP TABLE `newbills_overprice_additions`;
DROP TABLE `newbills_overprice_aggregate`;
DROP TABLE `newbills_rtsaldo_changes`;
DROP TABLE `newpayments_webmoney`;
