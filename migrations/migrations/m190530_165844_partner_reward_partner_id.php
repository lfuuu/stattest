<?php

use app\models\Bill;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\PartnerRewards;

/**
 * Class m190530_165844_partner_reward_partner_id
 */
class m190530_165844_partner_reward_partner_id extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->dropTable('partner_rewards_permanent');

        $this->addColumn(PartnerRewards::tableName(), 'partner_id', $this->integer());

        $this->execute('
UPDATE ' . PartnerRewards::tableName() . ' r, (
                    SELECT
                      rewards.bill_id,
                      rewards.line_pk,
                      rewards.created_at,
                      rewards.once,
                      rewards.percentage_once,
                      rewards.percentage_of_fee,
                      rewards.percentage_of_over,
                      rewards.percentage_of_margin,
                      contract.partner_contract_id partner_id
                    FROM ' . PartnerRewards::tableName() . ' rewards
                      LEFT JOIN ' . Bill::tableName() . ' bill ON rewards.bill_id = bill.id
                      LEFT JOIN ' . ClientAccount::tableName() . ' client ON client.id = bill.client_id
                      LEFT JOIN ' . ClientContract::tableName() . ' contract ON client.contract_id = contract.id
                     ) a
SET r.partner_id = a.partner_id
WHERE a.bill_id = r.bill_id AND a.line_pk = r.line_pk
');

    }

    /**
     * Down
     */
    public function safeDown()
    {

        $sql = <<<SQL
CREATE TABLE `partner_rewards_permanent` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `bill_id` INT(10) UNSIGNED NOT NULL,
  `line_pk` INT(10) UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL,
  `once` FLOAT DEFAULT NULL,
  `percentage_once` FLOAT DEFAULT NULL,
  `percentage_of_fee` FLOAT DEFAULT NULL,
  `percentage_of_over` FLOAT DEFAULT NULL,
  `percentage_of_margin` FLOAT DEFAULT NULL,
  `partner_id` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bill_id-line_pk` (`bill_id`,`line_pk`),
  KEY `fk-partner_rewards_permanent-line_pk` (`line_pk`),
  CONSTRAINT `fk-partner_rewards_permanent-bill_id` FOREIGN KEY (`bill_id`) REFERENCES `newbills` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk-partner_rewards_permanent-line_pk` FOREIGN KEY (`line_pk`) REFERENCES `newbill_lines` (`pk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $this->execute($sql);

        $sql = '
                INSERT INTO
                  partner_rewards_permanent 
                  (bill_id, line_pk, created_at, once, percentage_once, percentage_of_fee, percentage_of_over, percentage_of_margin, partner_id)
                  (
                    SELECT
                      rewards.bill_id,
                      rewards.line_pk,
                      rewards.created_at,
                      rewards.once,
                      rewards.percentage_once,
                      rewards.percentage_of_fee,
                      rewards.percentage_of_over,
                      rewards.percentage_of_margin,
                      contract.partner_contract_id partner_id
                    FROM ' . PartnerRewards::tableName() . ' rewards
                      LEFT JOIN ' . Bill::tableName() . ' bill ON rewards.bill_id = bill.id
                      LEFT JOIN ' . ClientAccount::tableName() . ' client ON client.id = bill.client_id
                      LEFT JOIN ' . ClientContract::tableName() . ' contract ON client.contract_id = contract.id
                  )
            ';

        $this->execute($sql);


    }
}
