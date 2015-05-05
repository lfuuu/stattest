<?php

class m150430_142735_stat206532_fix_account_manager extends \app\classes\Migration
{
    public function up()
    {
	$this->execute("
		UPDATE clients SET account_manager = manager WHERE LENGTH(manager) > 1 AND LENGTH(account_manager) < 1;
	");
    }

    public function down()
    {
        echo "m150430_142735_stat206532_fix_account_manager cannot be reverted.\n";

        return false;
    }
}
