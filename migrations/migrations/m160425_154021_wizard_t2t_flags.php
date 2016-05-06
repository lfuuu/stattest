<?php

use app\models\ClientContragentPerson;
use app\models\LkWizardState;

class m160425_154021_wizard_t2t_flags extends \app\classes\Migration
{
    public function up()
    {
        $this->addColumn(LkWizardState::tableName(), 'is_rules_accept_legal',
            $this->integer(1)->notNull()->defaultValue(0));
        $this->addColumn(LkWizardState::tableName(), 'is_rules_accept_person',
            $this->integer(1)->notNull()->defaultValue(0));
        $this->addColumn(LkWizardState::tableName(), 'is_contract_accept',
            $this->integer(1)->notNull()->defaultValue(0));
        $this->delete(LkWizardState::tableName(), ['type' => 'eur']);
        $this->execute("ALTER TABLE " . LkWizardState::tableName() . " modify `type` enum('eur','mcn') NOT NULL DEFAULT 'mcn'");
    }

    public function down()
    {
        $this->dropColumn(LkWizardState::tableName(), 'is_rules_accept_legal');
        $this->dropColumn(LkWizardState::tableName(), 'is_rules_accept_person');
        $this->dropColumn(LkWizardState::tableName(), 'is_contract_accept');
        $this->execute("ALTER TABLE " . LkWizardState::tableName() . " modify `type` enum('t2t','mcn') NOT NULL DEFAULT 'mcn'");
    }
}