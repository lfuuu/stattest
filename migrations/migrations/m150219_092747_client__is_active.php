<?php

class m150219_092747_client__is_active extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE `clients` ADD COLUMN `is_active`  tinyint(4) NOT NULL DEFAULT 1, 
            ADD COLUMN `is_blocked`  tinyint(4) NOT NULL DEFAULT 0");

        $this->execute("UPDATE `clients` SET is_active = 0 where status in ('closed','tech_deny','deny','debt','double','trash','move','denial','suspended')");
        $this->execute("UPDATE `clients` SET is_blocked =1 where status = 'debt'");

        $this->execute("insert into grid_settings values 
            (30,  'Входящие',          9,   1,   null,    0,   null,    1,   'income'),
            (31,  'Входящие',          2,   1,   null,    0,   null,    1,   'income'),
            (32,  'Действующий',       5,   1,   null,    0,   null,    1,   'distr'),
            (33,  'Заказ магазина',    3,   1,   null,    0,   null,    1,   'once'),
            (34,  'Внутренний офис',  10,   1,   null,    0,   null,    1,   null),
            (35,  'Действующий',       8,   1,   null,    0,   null,    1,   null)
            ");

        $this->execute("insert into grid_business_process values 
            (9,   1,   'Входящие', 1, null),   
            (10,  6,   'Внутренний офис',  1, null)
         ");   
    }

    public function down()
    {
        echo "m150219_092747_client__is_active cannot be reverted.\n";

        return false;
    }
}
