<?php

use app\models\Sorm7800;

/**
 * Class m190516_145708_sorm7800
 */
class m190516_145708_sorm7800 extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(Sorm7800::tableName(), [
            'region_id' => $this->integer(),
            'number' => $this->bigInteger(),
            'created_at' => $this->dateTime()
        ]);

        $this->addPrimaryKey('pk-region-number', Sorm7800::tableName(), ['region_id', 'number']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(Sorm7800::tableName());
    }
}
