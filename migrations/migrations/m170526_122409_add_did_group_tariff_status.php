<?php
use app\models\DidGroup;
use app\modules\uu\models\TariffStatus;

/**
 * Class m170526_122409_add_did_group_tariff_status
 */
class m170526_122409_add_did_group_tariff_status extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $didGroupTableName = DidGroup::tableName();
        $tariffStatusTableName = TariffStatus::tableName();

        for ($i = 1; $i <= 9; $i++) {
            $this->_addColumn($didGroupTableName, $tariffStatusTableName, 'tariff_status_main' . $i);
            $this->_addColumn($didGroupTableName, $tariffStatusTableName, 'tariff_status_package' . $i);
        }

        $this->_addColumn($didGroupTableName, $tariffStatusTableName, 'tariff_status_beauty');
    }

    /**
     * @param string $didGroupTableName
     * @param string $tariffStatusTableName
     * @param string $columnName
     */
    private function _addColumn($didGroupTableName, $tariffStatusTableName, $columnName)
    {
        $this->addColumn($didGroupTableName, $columnName, $this->integer());
        $this->addForeignKey('fk-' . $columnName, $didGroupTableName, $columnName, $tariffStatusTableName, 'id');
    }

    /**
     * @param string $didGroupTableName
     * @param string $tariffStatusTableName
     * @param string $columnName
     */
    private function _dropColumn($didGroupTableName, $tariffStatusTableName, $columnName)
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
        $tariffStatusTableName = TariffStatus::tableName();

        for ($i = 1; $i <= 9; $i++) {
            $this->_dropColumn($didGroupTableName, $tariffStatusTableName, 'tariff_status_main' . $i);
            $this->_dropColumn($didGroupTableName, $tariffStatusTableName, 'tariff_status_package' . $i);
        }

        $this->_dropColumn($didGroupTableName, $tariffStatusTableName, 'tariff_status_beauty');
    }
}
