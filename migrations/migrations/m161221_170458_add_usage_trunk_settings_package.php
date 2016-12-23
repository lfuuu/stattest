<?php

use app\classes\uu\model\Tariff;
use app\models\UsageTrunkSettings;

/**
 * Class m161221_170458_addUsageTrunkSettingsPackage
 */
class m161221_170458_add_usage_trunk_settings_package extends \app\classes\Migration
{
    /**
     * Up
     */
    public function up()
    {
        $tableName = UsageTrunkSettings::tableName();
        $fieldName = 'package_id';
        $this->addColumn($tableName, $fieldName, $this->integer());
        $this->addForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName, $fieldName, Tariff::tableName(), 'id', 'RESTRICT');
    }

    /**
     * Down
     */
    public function down()
    {
        $tableName = UsageTrunkSettings::tableName();
        $fieldName = 'package_id';
        $this->dropForeignKey('fk-' . $tableName . '-' . $fieldName, $tableName);
        $this->dropColumn($tableName, $fieldName);
    }
}