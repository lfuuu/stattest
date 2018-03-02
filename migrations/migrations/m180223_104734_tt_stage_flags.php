<?php

use app\models\Lead;
use app\models\TroubleState;

/**
 * Class m180223_104734_tt_stage_flags
 */
class m180223_104734_tt_stage_flags extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(TroubleState::tableName(), 'is_final', $this->boolean()->notNull()->defaultValue(false));
        $this->addColumn(TroubleState::tableName(), 'is_in_popup', $this->boolean()->notNull()->defaultValue(false));

        $this->update(TroubleState::tableName(),
            ['is_final' => true],
            ['id' =>
                [
                    2,  // Закрыт
                    20, // Закрыт
                    21, // Отказ
                    39, // Закрыт
                    40, // Отказ
                    45, // Техотказ
                    46, // Отказ
                    47, // Мусор
                    48, // Включено
                    59, //Выключен,
                    60, //Выполнен,
                    61, //Закрыт
                ]
            ]
        );

        $this->update(TroubleState::tableName(), ['is_in_popup' => true], ['id' => [
            47
        ]]);


    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(TroubleState::tableName(), 'is_final');
        $this->dropColumn(TroubleState::tableName(), 'is_in_popup');
    }
}
