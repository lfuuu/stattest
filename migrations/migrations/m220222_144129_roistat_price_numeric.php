<?php

/**
 * Class m220222_144129_roistat_price_numeric
 */
class m220222_144129_roistat_price_numeric extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(\app\models\TroubleRoistat::tableName(), 'roistat_price', $this->decimal(12,2));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(\app\models\TroubleRoistat::tableName(), 'roistat_price', $this->float());
    }
}
