<?php

/**
 * Class m191211_193022_bik_clean_bank
 */
class m191211_193022_bik_clean_bank extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\Bik::tableName(), 'bank', $this->string(50));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\Bik::tableName(), 'bank');
    }
}
