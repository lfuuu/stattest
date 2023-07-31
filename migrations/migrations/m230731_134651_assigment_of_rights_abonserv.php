<?php

/**
 * Class m230731_134651_assigment_of_rights_abonserv
 */
class m230731_134651_assigment_of_rights_abonserv extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->executeRaw("create table a_of_r_abonserv select c.id as client_id, cc.id as contract_id from clients c, client_contract cc where price_level=1 and is_active and cc.id = c.contract_id and organization_id != 14");
        $this->createIndex('idx-a_of_r_abonserv', 'a_of_r_abonserv', 'client_id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable('a_of_r_abonserv');
    }
}
