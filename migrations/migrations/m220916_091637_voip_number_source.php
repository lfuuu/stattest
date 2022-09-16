<?php

use app\classes\Migration;
use app\models\Number;
use app\models\voip\Source;

/**
 * Class m220916_091637_voip_number_source
 */
class m220916_091637_voip_number_source extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(Number::tableName(), 'source', $this->string(32)->notNull());
        $this->addForeignKey(Number::tableName() . '-' . Source::tableName(),
            Number::tableName(), 'source',
            Source::tableName(), 'code',
            'RESTRICT', 'RESTRICT'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey(Number::tableName() . '-' . Source::tableName(), Number::tableName());
        $this->alterColumn(\app\models\voip\Number::tableName(),
            "source",
            "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale','portability_innonet','g4m','voice_connect', 'didwww', 'Onderia') DEFAULT 'portability'"
        );
    }
}
