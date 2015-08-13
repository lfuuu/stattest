<?php

class m150813_122808_deleteBP extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `client_contract_business_process`
            	ADD COLUMN `show_as_status` ENUM('0','1') NOT NULL DEFAULT '1' AFTER `name`;

            UPDATE client_contract_business_process SET show_as_status = '0' WHERE id IN (16,2,5);
            DELETE FROM client_contract_business_process_status WHERE business_process_id IN(16,2,5);
            UPDATE client_contract SET business_process_id = 4, business_process_status_id = 16 WHERE business_process_status_id IN (32,36);
        ");
    }

    public function down()
    {
        echo "m150813_122808_deleteBP cannot be reverted.\n";

        return false;
    }
}