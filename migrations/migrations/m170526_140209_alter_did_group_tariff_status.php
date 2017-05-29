<?php
use app\models\DidGroup;
use app\modules\uu\models\TariffStatus;

/**
 * Class m170526_140209_alter_did_group_tariff_status
 */
class m170526_140209_alter_did_group_tariff_status extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $didGroupTableName = DidGroup::tableName();
        $tariffStatusTableName = TariffStatus::tableName();

        for ($i = 1; $i <= 9; $i++) {
            $this->_alterColumn($didGroupTableName, $tariffStatusTableName, 'tariff_status_main' . $i);
            $this->_alterColumn($didGroupTableName, $tariffStatusTableName, 'tariff_status_package' . $i);
        }
    }

    /**
     * @param string $didGroupTableName
     * @param string $tariffStatusTableName
     * @param string $columnName
     */
    private function _alterColumn($didGroupTableName, $tariffStatusTableName, $columnName)
    {
        DidGroup::updateAll([$columnName => TariffStatus::ID_PUBLIC], [$columnName => null]);

        $this->dropForeignKey('fk-' . $columnName, $didGroupTableName);
        $this->alterColumn($didGroupTableName, $columnName, $this->integer()->notNull());
        $this->addForeignKey('fk-' . $columnName, $didGroupTableName, $columnName, $tariffStatusTableName, 'id');
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
