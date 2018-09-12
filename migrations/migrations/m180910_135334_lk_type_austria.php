<?php

use app\models\LkWizardState;

/**
 * Class m180910_135334_lk_type_austria
 */
class m180910_135334_lk_type_austria extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(LkWizardState::tableName(), 'type', "enum('euro','mcn','slovak','austria') NOT NULL DEFAULT 'mcn'");
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update(LkWizardState::tableName(), ['type' => LkWizardState::TYPE_HUNGARY], ['type' => 'austria']);
        $this->alterColumn(LkWizardState::tableName(), 'type', "enum('euro','mcn','slovak') NOT NULL DEFAULT 'mcn'");
    }
}
