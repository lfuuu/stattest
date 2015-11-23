<?php

class m151120_092320_usages_rename extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            RENAME TABLE tech_cpe TO usage_tech_cpe;
        ');
    }

    public function down()
    {
        echo "m151120_092320_usages_rename cannot be reverted.\n";

        return false;
    }
}