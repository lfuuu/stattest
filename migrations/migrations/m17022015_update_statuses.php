<?php

class m17022015_update_statuses extends \app\classes\Migration
{
    public function up()
    {
        //обновление некорректного ид типа договора
        $this->execute("
           UPDATE clients SET contract_type_id = 5  WHERE STATUS='once';
        ");
        
        //замена таблицы grid_settings
        $this->executeSqlFile('grid_settings_dump.sql');
        
        //обновление нового статуса на основании старого
        $this->execute("
            REPLACE INTO client_grid_statuses (client_id, grid_status_id)
            SELECT c.id, g.id FROM clients c 
            INNER JOIN grid_settings g ON (c.status = g.oldstatus AND g.show_as_status = 1) WHERE g.grid_business_process_id <>2;
        ");
    }
    public function down()
    {
        echo "m17022015_update_statuses cannot be reverted.\n";
        return false;
    }
}