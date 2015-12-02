<?php

class m151202_092522_hungary_localization extends \app\classes\Migration
{
    public function up()
    {
        $this->execute('
            UPDATE `regions` SET `name` = "Hungary" WHERE `id` = 81;
        ');
        $this->execute('
            UPDATE `city` SET `name` = "Budapest" WHERE `id` = 361;
            UPDATE `city` SET `name` = "Miskolc" WHERE `id` = 3646;
            UPDATE `city` SET `name` = "Debrecen" WHERE `id` = 3652;
            UPDATE `city` SET `name` = "Szeged" WHERE `id` = 3662;
            UPDATE `city` SET `name` = "Pécs" WHERE `id` = 3672;
            UPDATE `city` SET `name` = "Győr" WHERE `id` = 3696;
        ');
    }

    public function down()
    {
        echo "m151202_092522_hungary_localization cannot be reverted.\n";

        return false;
    }
}