<?php

class m150227_132847_update_statuses extends \app\classes\Migration
{
    public function up()
    {
        
        //добавление и замена строчек таблицы grid_settings
        $this->executeSqlFile('grid_settings_dump.sql'); 
        

    }

    public function down()
    {
        echo "m150227_132847_update_statuses cannot be reverted.\n";

        return false;
    }
}