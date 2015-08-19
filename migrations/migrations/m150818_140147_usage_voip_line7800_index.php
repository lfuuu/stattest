<?php

class m150818_140147_usage_voip_line7800_index extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
          ALTER TABLE `usage_voip` ADD INDEX `line7800_id` (`line7800_id`);
        ");
    }

    public function down()
    {
        echo "m150818_140147_usage_voip_line7800_index cannot be reverted.\n";

        return false;
    }
}