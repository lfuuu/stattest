<?php

use app\classes\Migration;
use app\modules\uu\models\ResourceModel;
use app\modules\uu\models\ServiceType;

/**
 * Class m210219_172418_voip_sms_options
 */
class m210219_172418_voip_sms_options extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insertResource(
            ServiceType::ID_VOIP,
            ResourceModel::ID_VOIP_SMS_SUBABSENT, [
            'name' => 'SMS. Если абонент не в сети',
            'unit' => '¤',
            'min_value' => 0,
            'max_value' => 1,
        ], [], true
        );

        $this->insertResource(
            ServiceType::ID_VOIP,
            ResourceModel::ID_VOIP_SMS_DUPMTSMS, [
            'name' => 'SMS. Отправка всегда',
            'unit' => '¤',
            'min_value' => 0,
            'max_value' => 1,
        ], [], true
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
    }
}
