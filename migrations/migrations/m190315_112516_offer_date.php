<?php

use app\models\ClientContract;

/**
 * Class m190315_112516_offer_date
 */
class m190315_112516_offer_date extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientContract::tableName(), 'offer_date', $this->date()->defaultValue(null));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientContract::tableName(), 'offer_date');
    }
}
