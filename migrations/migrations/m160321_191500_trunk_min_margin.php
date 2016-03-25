<?php

use app\models\UsageTrunkSettings;

class m160321_191500_trunk_min_margin extends \app\classes\Migration
{
    public function safeUp()
    {
        $tableName = UsageTrunkSettings::tableName();
        $this->addColumn($tableName, 'minimum_margin',
            $this->decimal(10, 5)->notNull()->defaultValue(0));

        $this->addColumn($tableName, 'minimum_margin_type',
            $this->smallInteger(6)->notNull()->defaultValue(UsageTrunkSettings::MIN_MARGIN_ABSENT));
    }

    public function safeDown()
    {
        $tableName = UsageTrunkSettings::tableName();
        $this->dropColumn($tableName, 'minimum_margin');
        $this->dropColumn($tableName, 'minimum_margin_type');
    }
}
