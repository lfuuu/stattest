<?php

/**
 * Class m210302_134339_add_vats_resource_reserv
 */
class m210302_134339_add_vats_resource_reserv extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(
            \app\modules\uu\models\ServiceType::ID_VPBX,
            \app\modules\uu\models\ResourceModel::ID_VPBX_RESERV, [
            'name' => 'Резерв',
            'unit' => '¤',
            'min_value' => 0,
            'max_value' => 1,
        ], [], false /** обновление буем делать отдельно */
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
