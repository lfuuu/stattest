<?php

use app\classes\Migration;
use app\models\ClientContract;

/**
 * Class m230516_101503_client_contract_created_at
 */
class m230516_101503_client_contract_created_at extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientContract::tableName(), 'created_at', $this->dateTime());
        $this->addColumn(ClientContract::tableName(), 'updated_at', $this->dateTime());

        $sql = <<<SQL
update client_contract c, (
    select model_id, min(date) + interval 3 hour as min_date
    from history_version
    where model = 'app\\\\models\\\\ClientContract'
    group by model_id) a
set c.created_at = a.min_date,
    c.updated_at = a.min_date
where c.created_at is null
    and c.id = a.model_id
SQL;
        $this->execute($sql);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientContract::tableName(), 'created_at');
        $this->dropColumn(ClientContract::tableName(), 'updated_at');
    }
}
