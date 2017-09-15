<?php
use app\models\UsageVirtpbx;
use app\modules\uu\models\AccountTariff;

/**
 * Class m170913_134759_uu_vpbx_is_dearchived
 */
class m170913_134759_uu_vpbx_is_dearchived extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(AccountTariff::tableName(), 'is_unzipped', $this->boolean()->defaultValue(false));
        $this->renameColumn(UsageVirtpbx::tableName(), 'is_dearchived', 'is_unzipped');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(AccountTariff::tableName(), 'is_unzipped');
        $this->renameColumn(UsageVirtpbx::tableName(), 'is_unzipped', 'is_dearchived');
    }
}
