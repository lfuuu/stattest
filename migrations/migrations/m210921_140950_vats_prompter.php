<?php

/**
 * Class m210921_140950_vats_prompter
 */
class m210921_140950_vats_prompter extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        // throw new \Exception('Please start manually');
        $this->insertResource(
            \app\modules\uu\models\ServiceType::ID_VPBX,
            \app\modules\uu\models\ResourceModel::ID_VPBX_PROMPTER, [
            'name' => 'Cуфлер',
            'unit' => '¤',
            'min_value' => 0,
            'max_value' => 1,
        ], [
            \app\models\Currency::RUB => 399
            ], true
        );

        $this->insertResource(
            \app\modules\uu\models\ServiceType::ID_VPBX,
            \app\modules\uu\models\ResourceModel::ID_VPBX_OPERATOR_ASSESSMENT, [
            'name' => 'Оценка оператора',
            'unit' => '¤',
            'min_value' => 0,
            'max_value' => 1,
        ], [\app\models\Currency::RUB => 399], true
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
