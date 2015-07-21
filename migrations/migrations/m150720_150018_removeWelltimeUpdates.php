<?php

class m150720_150018_removeWelltimeUpdates extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
          DROP TABLE IF EXISTS `clients_test`;
        ");
    }

    public function down()
    {
        echo "m150720_150018_removeWelltimeUpdates cannot be reverted.\n";

        return false;
    }
}