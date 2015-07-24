<?php

class m150723_161122_dropTech_devices extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
          DROP TABLE IF EXISTS `tech_devices`;
        ");
    }

    public function down()
    {
        echo "m150723_161122_dropTech_devices cannot be reverted.\n";

        return false;
    }
}