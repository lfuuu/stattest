<?php

use app\modules\uu\models\ServiceType;

/**
 * Class m180328_101459_uu_rename_vps
 */
class m180328_101459_uu_rename_vps extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $table = ServiceType::tableName();
        $this->update($table, ['name' => 'VPS'], ['id' => ServiceType::ID_VPS]);
        $this->update($table, ['name' => 'VPS. Доп. услуги'], ['id' => ServiceType::ID_VPS_LICENCE]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $table = ServiceType::tableName();
        $this->update($table, ['name' => 'VM collocation'], ['id' => ServiceType::ID_VPS]);
        $this->update($table, ['name' => 'VM collocation. Доп. услуги'], ['id' => ServiceType::ID_VPS_LICENCE]);
    }
}
