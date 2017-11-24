<?php

use app\models\EntryPoint;
use app\models\LkWizardState;

/**
 * Class m171123_102835_slovak_wizard
 */
class m171123_102835_slovak_wizard extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(LkWizardState::tableName(), 'type', "enum('" . LkWizardState::TYPE_HUNGARY . "','" . LkWizardState::TYPE_RUSSIA . "','" . LkWizardState::TYPE_SLOVAK . "') NOT NULL DEFAULT '" . LkWizardState::TYPE_RUSSIA . "'");
        $this->addColumn(LkWizardState::tableName(), 'is_rules_accept_ip', $this->boolean()->notNull()->defaultValue(false));
        $this->dropColumn(EntryPoint::tableName(), 'wizard_type');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->alterColumn(LkWizardState::tableName(), 'type', "enum('" . LkWizardState::TYPE_HUNGARY . "','" . LkWizardState::TYPE_RUSSIA . "') NOT NULL DEFAULT '" . LkWizardState::TYPE_RUSSIA . "'");
        $this->dropColumn(LkWizardState::tableName(), 'is_rules_accept_ip');
        $this->addColumn(EntryPoint::tableName(), 'wizard_type', $this->string()->notNull()->defaultValue(LkWizardState::TYPE_RUSSIA));
    }
}
