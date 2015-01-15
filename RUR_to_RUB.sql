ALTER TABLE `bill_currency_rate`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `date`;

ALTER TABLE `bill_monthlyadd`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `status`;

ALTER TABLE `bill_monthlyadd_log`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `ts`;

ALTER TABLE `bill_monthlyadd_reference`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `period`;

ALTER TABLE `clients`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB' AFTER `firma`,
MODIFY COLUMN `currency_bill`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB' AFTER `currency`;

ALTER TABLE `clients_test`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `firma`,
MODIFY COLUMN `currency_bill`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `currency`;

-- error
ALTER TABLE `currency`
MODIFY COLUMN `id`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL FIRST ;

ALTER TABLE `newbills`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `client_id`;

ALTER TABLE `newbills_overprice_aggregate`
MODIFY COLUMN `rate_currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'Валюта тарифа' AFTER `rate_id`;

ALTER TABLE `newpayments`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB' AFTER `sum_rub`;

ALTER TABLE `newpayments_orders`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL AFTER `sum`;

ALTER TABLE `newpayments_webmoney`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB' AFTER `sum`;

ALTER TABLE `newsaldo`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `saldo`;

ALTER TABLE `phisclients`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB' AFTER `fio`;

ALTER TABLE `tarifs_8800`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB' AFTER `period`;

ALTER TABLE `tarifs_extra`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `price`;

ALTER TABLE `tarifs_hosting`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `status`;

ALTER TABLE `tarifs_internet`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `pay_f`;

ALTER TABLE `tarifs_sms`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB' AFTER `period`;

ALTER TABLE `tarifs_virtpbx`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'RUB' AFTER `period`;

ALTER TABLE `tarifs_voip`
MODIFY COLUMN `currency`  char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'USD' AFTER `dest`;





ALTER TABLE `clients`
CHANGE COLUMN `hid_rtsaldo_RUR` `hid_rtsaldo_RUB`  decimal(11,2) NOT NULL DEFAULT 0.00 AFTER `hid_rtsaldo_date`,
CHANGE COLUMN `credit_RUR` `credit_RUB`  int(11) NOT NULL DEFAULT 0 AFTER `credit_USD`;

ALTER TABLE `clients_test`
CHANGE COLUMN `hid_rtsaldo_RUR` `hid_rtsaldo_RUB`  decimal(11,2) NOT NULL DEFAULT 0.00 AFTER `hid_rtsaldo_date`,
CHANGE COLUMN `credit_RUR` `credit_RUB`  decimal(11,2) NOT NULL DEFAULT 0.00 AFTER `credit_USD`;

ALTER TABLE `newbills_rtsaldo_changes`
CHANGE COLUMN `delta_RUR` `delta_RUB`  decimal(11,2) NOT NULL AFTER `client_id`,
CHANGE COLUMN `sum_RUR` `sum_RUB`  decimal(11,2) NOT NULL AFTER `delta_USD`;

ALTER TABLE `tech_cpe`
CHANGE COLUMN `deposit_sumRUR` `deposit_sumRUB`  decimal(7,2) NOT NULL DEFAULT 0.00 AFTER `deposit_sumUSD`;

ALTER TABLE `tech_cpe_models`
CHANGE COLUMN `default_deposit_sumRUR` `default_deposit_sumRUB`  decimal(7,2) NOT NULL DEFAULT 0.00 AFTER `default_deposit_sumUSD`;





UPDATE `bill_currency_rate` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `bill_monthlyadd` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `bill_monthlyadd_log` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `bill_monthlyadd_reference` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `clients` SET `currency` = 'RUB' WHERE `currency` = 'RUR';
UPDATE `clients` SET `currency_bill` = 'RUB' WHERE `currency_bill` = 'RUR';

UPDATE `clients_test` SET `currency` = 'RUB' WHERE `currency` = 'RUR';
UPDATE `clients_test` SET `currency_bill` = 'RUB' WHERE `currency_bill` = 'RUR';

UPDATE `currency` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `newbills` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `newbills_overprice_aggregate` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `newpayments` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `newpayments_orders` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `newpayments_webmoney` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `newsaldo` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `phisclients` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `tarifs_8800` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `tarifs_extra` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `tarifs_hosting` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `tarifs_internet` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `tarifs_sms` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `tarifs_virtpbx` SET `currency` = 'RUB' WHERE `currency` = 'RUR';

UPDATE `tarifs_voip` SET `currency` = 'RUB' WHERE `currency` = 'RUR';
