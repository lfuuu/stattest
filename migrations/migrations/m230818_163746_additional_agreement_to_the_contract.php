<?php

/**
 * Class m230818_163746_additional_agreement_to_the_contract
 */
class m230818_163746_additional_agreement_to_the_contract extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $table = \app\models\ClientContractAdditionalAgreement::tableName();
        $this->createTable($table, [
            'id' => $this->primaryKey(),
            'contract_id' => $this->integer()->notNull(),
            'account_id' => $this->integer()->notNull(),
            'from_organization_id' => $this->integer()->notNull(),
            'to_organization_id' => $this->integer()->notNull(),
            'transfer_date' => $this->date()->notNull(),
        ]);

        $this->createIndex('idx-'.$table.'-account_id', $table, 'account_id');

        $this->executeRaw(<<<SQL
insert into {$table}
select null, c.contract_id, c.id, 21, 14, '2023-09-01'# organization_id, cc.business_process_status_id, c.*
from clients c
join client_contract cc on cc.id = c.contract_id
join client_contragent cg on cg.id = cc.contragent_id
where true
and c.is_active
and cg.legal_type = 'ip'
and organization_id != 14
and c.price_level = 1
and business_process_status_id = 9

SQL

);
        

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(\app\models\ClientContractAdditionalAgreement::tableName());
    }
}
