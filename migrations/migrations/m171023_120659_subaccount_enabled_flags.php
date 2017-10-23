<?php

use app\models\ClientSubAccount;

/**
 * Class m171023_120659_subaccount_enabled_flags
 */
class m171023_120659_subaccount_enabled_flags extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientSubAccount::tableName(), 'is_voip_limit_mn_month_enabled', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn(ClientSubAccount::tableName(), 'is_voip_limit_month_enabled', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn(ClientSubAccount::tableName(), 'is_voip_limit_day_enabled', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn(ClientSubAccount::tableName(), 'is_voip_limit_mn_day_enabled', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientSubAccount::tableName(), 'is_voip_limit_mn_month_enabled');
        $this->dropColumn(ClientSubAccount::tableName(), 'is_voip_limit_month_enabled');
        $this->dropColumn(ClientSubAccount::tableName(), 'is_voip_limit_day_enabled');
        $this->dropColumn(ClientSubAccount::tableName(), 'is_voip_limit_mn_day_enabled');
    }
}
