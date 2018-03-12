<?php

use app\models\ClientContract;
use app\models\ClientContragent;

/**
 * Class m180308_102906_add_partner
 */
class m180308_102906_add_partner extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $contractTableName = ClientContract::tableName();
        $this->addColumn($contractTableName, 'partner_contract_id', $this->integer());
        $this->addForeignKey('fk-' . $contractTableName . '-partner_contract_id', $contractTableName, 'partner_contract_id', $contractTableName, 'id');

        // заоджно и контрагента привести к нормальному виду
        $contragentTableName = ClientContragent::tableName();
        $this->alterColumn($contragentTableName, 'partner_contract_id', $this->integer());
        $this->alterColumn($contragentTableName, 'sale_channel_id', $this->integer());
        $this->addForeignKey('fk-' . $contragentTableName . '-partner_contract_id', $contragentTableName, 'partner_contract_id', $contractTableName, 'id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $contractTableName = ClientContract::tableName();
        $this->dropForeignKey('fk-' . $contractTableName . '-partner_contract_id', $contractTableName);
        $this->dropColumn($contractTableName, 'partner_contract_id');

        $contragentTableName = ClientContragent::tableName();
        $this->dropForeignKey('fk-' . $contragentTableName . '-partner_contract_id', $contragentTableName);
        $this->alterColumn($contragentTableName, 'partner_contract_id', $this->integer(10));
        $this->alterColumn($contragentTableName, 'sale_channel_id', $this->integer(10));
    }
}
