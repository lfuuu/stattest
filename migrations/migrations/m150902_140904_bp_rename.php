<?php

class m150902_140904_bp_rename extends \app\classes\Migration
{
    public function up()
    {
        $this->execute("
            UPDATE client_contract_business_process_status s
                INNER JOIN client_contract_business_process p ON p.id = s.business_process_id AND p.business_id = 3
                SET s.name = 'Отказ'
                WHERE s.name = 'Техотказ';
        ");
    }

    public function down()
    {
        echo "m150902_140904_bp_rename cannot be reverted.\n";

        return false;
    }
}
