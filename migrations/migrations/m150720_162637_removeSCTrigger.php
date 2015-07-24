<?php

class m150720_162637_removeSCTrigger extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            DROP PROCEDURE IF EXISTS `create_super_client`;
            DROP TRIGGER IF EXISTS `create_super_client`;
        ");
    }

    public function down()
    {
        echo "m150720_162637_removeSCTrigger cannot be reverted.\n";

        return false;
    }
}