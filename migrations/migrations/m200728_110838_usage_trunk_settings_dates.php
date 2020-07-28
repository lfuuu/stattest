<?php

use app\classes\Migration;
use app\models\UsageTrunkSettings;

/**
 * Class m200728_110838_usage_trunk_settings_dates
 */
class m200728_110838_usage_trunk_settings_dates extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(UsageTrunkSettings::tableName(), 'activation_dt', $this->dateTime());
        $this->addColumn(UsageTrunkSettings::tableName(), 'expire_dt', $this->dateTime());
        $this->addColumn(UsageTrunkSettings::tableName(), 'account_package_id', $this->integer());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(UsageTrunkSettings::tableName(), 'activation_dt');
        $this->dropColumn(UsageTrunkSettings::tableName(), 'expire_dt');
        $this->dropColumn(UsageTrunkSettings::tableName(), 'account_package_id');

    }
}
