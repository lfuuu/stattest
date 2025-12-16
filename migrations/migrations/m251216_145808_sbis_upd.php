<?php

use app\classes\Migration;
use app\modules\sbisTenzor\models\SBISExchangeForm;
use app\modules\sbisTenzor\models\SBISExchangeGroup;
use app\modules\sbisTenzor\models\SBISExchangeGroupForm;

/**
 * Class m251216_145808_sbis_upd
 */
class m251216_145808_sbis_upd extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(SBISExchangeGroup::tableName(), ['id' => SBISExchangeGroup::UPD_2023, 'name' => 'УПД(2023) 5.03']);
        $this->insert(SBISExchangeForm::tableName(), [
            'id' => SBISExchangeForm::UPD_2023_5_03,
            'type' => 'upd',
            'name' => 'УПД (с 2023 г.)',
            'full_name' => 'УПД (с 2023 г.), ФНС 5.03 (с 01.01.2026)',
            'version' => '5.03',
            'knd_code' => 1115131,
            'file_pattern' => 'ON_NSCHFDOPPR_{A}_{O}_{GGGGMMDD}_{N}_0_0_0_0_0_00',
            'starts_at' => '2025-12-01 00:00:00',
            'expires_at' => null
        ]);
        $this->insert(SBISExchangeGroupForm::tableName(), [
            'id' => 8,
            'group_id' => SBISExchangeGroup::UPD_2023,
            'form_id' => SBISExchangeForm::UPD_2023_5_03
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(SBISExchangeGroupForm::tableName(), ['id' => 8]);
        $this->delete(SBISExchangeGroup::tableName(), ['id' => SBISExchangeGroup::UPD_2023]);
        $this->delete(SBISExchangeForm::tableName(), ['id' => SBISExchangeForm::UPD_2023_5_03]);
    }
}
