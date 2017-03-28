<?php
use app\classes\uu\model\ServiceType;
use app\classes\uu\model\TariffStatus;

/**
 * Class m170328_160336_add_uu_status
 */
class m170328_160336_add_uu_status extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->batchInsert(
            TariffStatus::tableName(),
            ['name', 'service_type_id'],
            [
                ['ОТТ 1', ServiceType::ID_VOIP],
                ['ОТТ 2', ServiceType::ID_VOIP],
                ['ОТТ 3', ServiceType::ID_VOIP],
            ]
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        TariffStatus::deleteAll(['name' => ['ОТТ 1', 'ОТТ 2', 'ОТТ 3']]);
    }
}
