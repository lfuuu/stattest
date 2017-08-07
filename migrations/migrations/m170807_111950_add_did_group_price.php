<?php
use app\models\DidGroup;
use app\modules\uu\models\TariffStatus;

/**
 * Class m170807_111950_add_did_group_price
 */
class m170807_111950_add_did_group_price extends \app\classes\Migration
{
    private $_from = 10;
    private $_to = 18;

    /**
     * Up
     */
    public function safeUp()
    {
        $didGroupTableName = DidGroup::tableName();
        $tariffStatusTableName = TariffStatus::tableName();

        for ($i = $this->_from; $i <= $this->_to; $i++) {

            $this->addColumn($didGroupTableName, 'price' . $i, $this->integer() . ' AFTER price' . ($i - 1));
            DidGroup::updateAll(['price' . $i => 0], ['price' . $i => null]);

            $this->_addColumn($didGroupTableName, $tariffStatusTableName, 'tariff_status_main' . $i, 'tariff_status_package' . ($i - 1));
            $this->_addColumn($didGroupTableName, $tariffStatusTableName, 'tariff_status_package' . $i, 'tariff_status_main' . $i);
        }
    }

    /**
     * @param string $didGroupTableName
     * @param string $tariffStatusTableName
     * @param string $columnName
     * @param string $columnNameAfter
     */
    private function _addColumn($didGroupTableName, $tariffStatusTableName, $columnName, $columnNameAfter)
    {
        $this->addColumn($didGroupTableName, $columnName, $this->integer() . ' AFTER ' . $columnNameAfter);
        DidGroup::updateAll([$columnName => TariffStatus::ID_PUBLIC], [$columnName => null]);
        $this->alterColumn($didGroupTableName, $columnName, $this->integer()->notNull());

        $this->addForeignKey('fk-' . $columnName, $didGroupTableName, $columnName, $tariffStatusTableName, 'id');
    }

    /**
     * @param string $didGroupTableName
     * @param string $columnName
     */
    private function _dropColumn($didGroupTableName, $columnName)
    {
        $this->dropForeignKey('fk-' . $columnName, $didGroupTableName);
        $this->dropColumn($didGroupTableName, $columnName);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $didGroupTableName = DidGroup::tableName();

        for ($i = $this->_from; $i <= $this->_to; $i++) {
            $this->_dropColumn($didGroupTableName, 'price' . $i);
            $this->_dropColumn($didGroupTableName, 'tariff_status_main' . $i);
            $this->_dropColumn($didGroupTableName, 'tariff_status_package' . $i);
        }
    }

}
