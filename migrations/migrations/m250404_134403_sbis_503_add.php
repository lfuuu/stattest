<?php

/**
 * Class m250404_134403_sbis_503_add
 */
class m250404_134403_sbis_503_add extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(\app\modules\sbisTenzor\models\SBISExchangeGroupForm::tableName(), [
            'group_id' => \app\modules\sbisTenzor\models\SBISExchangeGroup::ACT_AND_INVOICE_2025,
            'form_id' => \app\modules\sbisTenzor\models\SBISExchangeForm::ACT_2016_5_02,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(\app\modules\sbisTenzor\models\SBISExchangeGroupForm::tableName(), [
            'group_id' => \app\modules\sbisTenzor\models\SBISExchangeGroup::ACT_AND_INVOICE_2025,
            'form_id' => \app\modules\sbisTenzor\models\SBISExchangeForm::ACT_2016_5_02,
        ]);
    }
}
