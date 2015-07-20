<?php

class m150717_144453_removePassFromClients extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
ALTER TABLE `clients`
	DROP COLUMN `password`,
	DROP COLUMN `password_type`;
");
    }

    public function down()
    {
        echo "m150717_144453_removePassFromClients cannot be reverted.\n";

        return false;
    }
}