<?php
use app\models\Currency;
use app\models\GoodPrice;

/**
 * Class m170408_100402_currency_goods
 */
class m170408_100402_currency_goods extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(
            GoodPrice::tableName(),
            'currency',
            'char(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT "' . Currency::RUB . '"');

        $this->addForeignKey('fk-currency-id', GoodPrice::tableName(), 'currency', Currency::tableName(), 'id');
        $this->dropPrimaryKey('', GoodPrice::tableName());
        $this->addPrimaryKey('', GoodPrice::tableName(), ['price_type_id', 'good_id', 'descr_id', 'currency']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropPrimaryKey('', GoodPrice::tableName());
        $this->addPrimaryKey('', GoodPrice::tableName(), ['price_type_id', 'good_id', 'descr_id']);
        $this->dropForeignKey('fk-currency-id', GoodPrice::tableName());
        $this->dropColumn(GoodPrice::tableName(), 'currency');
    }
}
