<?php

class m150526_131517_alterWizard extends \app\classes\Migration
{
    public function up()
    {
        $sql = <<<SQL
ALTER TABLE `lk_wizard_state`
	ADD COLUMN `contract_id` INT NOT NULL AFTER `account_id`;

UPDATE lk_wizard_state s
	INNER JOIN clients c ON c.`id` = s.`account_id`
	SET s.`contract_id` = c.`contract_id`;

ALTER TABLE `lk_wizard_state`
	DROP COLUMN `account_id`,
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (`contract_id`);
SQL;

        $this->execute($sql);
    }

    public function down()
    {
        echo "m150526_131517_alterWizard cannot be reverted.\n";

        return false;
    }
}