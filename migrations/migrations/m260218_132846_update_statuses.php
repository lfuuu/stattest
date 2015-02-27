<?php

class m260218_132846_update_statuses extends \app\classes\Migration
{
    public function up()
    {
        
        //добавление и замена строчек таблицы grid_settings
        $this->executeSqlFile('grid_settings_dump.sql');
        

    }

    public function down()
    {
        echo "m260218_132846_update_statuses cannot be reverted.\n";

        return false;
    }
}