<?php

use app\models\ClientAccount;
use app\modules\uu\models\TariffStatus;

/**
 * Class m171209_154946_add_clients_uu_folder
 */
class m171209_154946_add_clients_uu_folder extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $clientAccountTableName = ClientAccount::tableName();
        $this->addColumn($clientAccountTableName, 'uu_tariff_status_id', $this->integer());
        $this->addForeignKey('fk-uu_tariff_status_id', $clientAccountTableName, 'uu_tariff_status_id', TariffStatus::tableName(), 'id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $clientAccountTableName = ClientAccount::tableName();
        $this->dropForeignKey('fk-uu_tariff_status_id', $clientAccountTableName);
        $this->dropColumn($clientAccountTableName, 'uu_tariff_status_id');
    }
}
