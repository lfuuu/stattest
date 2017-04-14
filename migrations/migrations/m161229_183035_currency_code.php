<?php
use app\models\Currency;

/**
 * Class m161229_183035_currency_code
 */
class m161229_183035_currency_code extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Currency::tableName(), 'code', $this->integer()->notNull()->defaultValue(0)->comment('ISO 4217'));

        $this->update(Currency::tableName(), ['code' => 643], ['id' => Currency::RUB]);
        $this->update(Currency::tableName(), ['code' => 978], ['id' => Currency::EUR]);
        $this->update(Currency::tableName(), ['code' => 840], ['id' => Currency::USD]);
        $this->update(Currency::tableName(), ['code' => 348], ['id' => Currency::HUF]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Currency::tableName(), 'code');
    }
}
