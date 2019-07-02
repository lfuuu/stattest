<?php

use app\models\Number;

/**
 * Class m190702_160507_number_original_beauty
 */
class m190702_160507_number_original_beauty extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(Number::tableName(), 'original_beauty_level', $this->smallInteger()->notNull()->defaultValue(\app\models\DidGroup::BEAUTY_LEVEL_STANDART));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Number::tableName(), 'original_beauty_level');
    }
}
