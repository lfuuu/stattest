<?php

use app\modules\uu\models\ServiceType;

/**
 * Class m180306_090118_add_uu_sms
 */
class m180306_090118_add_uu_sms extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->update(ServiceType::tableName(),
            ['parent_id' => ServiceType::ID_VOIP, 'name' => 'Телефония. Пакет СМС'],
            ['id' => ServiceType::ID_VOIP_PACKAGE_SMS]
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->update(ServiceType::tableName(),
            ['parent_id' => null, 'name' => 'SMS'],
            ['id' => ServiceType::ID_VOIP_PACKAGE_SMS]
        );
    }
}
