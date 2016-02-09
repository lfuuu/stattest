<?php

class m151216_122809_anti_fraud_disabled extends \app\classes\Migration
{
    public function up()
    {
        $this->execute(" alter table `clients` add `anti_fraud_disabled` tinyint(4) NOT NULL DEFAULT '0' ");
    }

    public function down()
    {
        echo "m151216_122809_anti_fraud_disabled cannot be reverted.\n";

        return false;
    }
}
