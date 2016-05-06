<?php

use app\models\LkWizardState;

class m160506_083710_eur_to_euro extends \app\classes\Migration
{
    public function up()
    {
        $this->alterColumn(LkWizardState::tableName(), "type", "enum('eur','euro', 'mcn') NOT NULL DEFAULT 'mcn'");
        $this->update(LkWizardState::tableName(), ["type" => 'euro'], ['type' => 'eur']);
        $this->alterColumn(LkWizardState::tableName(), "type", "enum('euro', 'mcn') NOT NULL DEFAULT 'mcn'");
    }

    public function down()
    {
        $this->alterColumn(LkWizardState::tableName(), "type", "enum('eur','euro', 'mcn') NOT NULL DEFAULT 'mcn'");
        $this->update(LkWizardState::tableName(), ["type" => 'eur'], ['type' => 'euro']);
        $this->alterColumn(LkWizardState::tableName(), "type", "enum('eur', 'mcn') NOT NULL DEFAULT 'mcn'");    }
}