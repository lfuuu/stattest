<?php

/**
 * Class m240627_111650_bik_dadata
 */
class m240627_111650_bik_dadata extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(\app\models\Bik::tableName(), 'dadata', $this->json());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(\app\models\Bik::tableName(), 'dadata');
    }
}
