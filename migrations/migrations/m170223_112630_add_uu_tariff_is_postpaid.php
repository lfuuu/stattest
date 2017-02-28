<?php
use app\classes\uu\model\Tariff;

/**
 * Class m170223_112630_add_uu_tariff_is_postpaid
 */
class m170223_112630_add_uu_tariff_is_postpaid extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = Tariff::tableName();
        $fieldName = 'is_postpaid';
        $this->addColumn($tableName, $fieldName, $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = Tariff::tableName();
        $fieldName = 'is_postpaid';
        $this->dropColumn($tableName, $fieldName);
    }
}
