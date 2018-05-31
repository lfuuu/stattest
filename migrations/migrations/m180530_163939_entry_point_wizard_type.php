<?php

use app\models\EntryPoint;
use app\models\LkWizardState;

/**
 * Class m180530_163939_entry_point_wizard_type
 */
class m180530_163939_entry_point_wizard_type extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(EntryPoint::tableName(), 'wizard_type', $this->string()->defaultValue(LkWizardState::TYPE_RUSSIA));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(EntryPoint::tableName(), 'wizard_type');
    }
}
