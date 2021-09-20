<?php

use app\classes\Migration;
use app\models\BusinessProcessStatus;

/**
 * Class m210920_114229_bps_end_state
 */
class m210920_114229_bps_end_state extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(
            BusinessProcessStatus::tableName(),
            'is_off_stage',
            $this->integer()->notNull()->defaultValue(0)
        );

        $this->update(
            BusinessProcessStatus::tableName(),
            ['is_off_stage' => 1],
            ['id' => [
                100, 121, 122, 123, 124, 131, 139, 146, // Мусор
                22, 29, 149, 160, // Дубликат
                42, // Расторгнут
                10, 144, // Отключенные
                27, 147, // Техотказ
                28, 44, 54, 69, 84, 99, 130, 138, 148, // Отказ
                174, // B2C Отказ абонента
                177, // Отказ БДПН - 60 дней
                183, // B2C Отказ донора в портировании,
                92, 111, // Закрытые
                42, 52, 67, 82, 129, // Расторгнут
                178, // Регион вне портирования,
                176, // B2C Не ответ абонента
            ]]
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(BusinessProcessStatus::tableName(), 'is_off_stage');
    }
}
