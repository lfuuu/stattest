<?php

use app\classes\Migration;
use app\modules\sbisTenzor\models\SBISExchangeForm;
use app\modules\sbisTenzor\models\SBISExchangeGroup;
use app\modules\sbisTenzor\models\SBISExchangeGroupForm;

/**
 * Class m250403_140737_sbis_503
 */
class m250403_140737_sbis_503 extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update(SBISExchangeGroup::tableName(), ['name' => 'Акт + Счет-фактура(2019) 5.01'], ['id' => SBISExchangeGroup::ACT_AND_INVOICE_2019]);
        $this->insert(SBISExchangeGroup::tableName(), ['id' => SBISExchangeGroup::ACT_AND_INVOICE_2025, 'name' => 'Акт + Счет-фактура(2025) 5.03']);
        $this->insert(SBISExchangeForm::tableName(), [
            'id' => SBISExchangeForm::INVOICE_2025_5_03,
            'type' => 'invoice',
            'name' => 'Счет-фактура (с 2025 г.)',
            'full_name' => 'Счет-фактура (с 2025 г.), ФНС 5.03 (с 01.04.2025)',
            'version' => '5.03',
            'knd_code' => 1115131,
            'file_pattern' => 'ON_NSCHFDOPPR_{A}_{O}_{GGGGMMDD}_{N}_0_0_0_0_0_00',
            'starts_at' => '2024-04-01 00:00:00',
            'expires_at' => null
        ]);
        $this->insert(SBISExchangeGroupForm::tableName(), [
            'id' => 6,
            'group_id' => SBISExchangeGroup::ACT_AND_INVOICE_2025,
            'form_id' => SBISExchangeForm::INVOICE_2025_5_03
        ]);
    }


    /**
     * Down
     */
    public function safeDown()
    {
        $this->update(SBISExchangeGroup::tableName(), ['name' => 'Акт + Счет-фактура(2019)'], ['id' => SBISExchangeGroup::ACT_AND_INVOICE_2019]);

        $this->delete(SBISExchangeGroupForm::tableName(), ['id' => 6]);
        $this->delete(SBISExchangeGroup::tableName(), ['id' => SBISExchangeGroup::ACT_AND_INVOICE_2025]);
        $this->delete(SBISExchangeForm::tableName(), ['id' => SBISExchangeForm::INVOICE_2025_5_03]);
    }

}