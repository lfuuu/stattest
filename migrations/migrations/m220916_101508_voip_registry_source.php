<?php

use app\classes\Migration;
use app\models\voip\Registry;
use app\models\voip\Source;

/**
 * Class m220916_101508_voip_registry_source
 */
class m220916_101508_voip_registry_source extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->alterColumn(Registry::tableName(), 'source', $this->string(32)->notNull());
        $this->addForeignKey(Registry::tableName() . '-' . Source::tableName(),
            Registry::tableName(), 'source',
            Source::tableName(), 'code',
            'RESTRICT', 'RESTRICT'
        );

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropForeignKey(Registry::tableName() . '-' . Source::tableName(), Registry::tableName());
        $this->alterColumn(\app\models\voip\Registry::tableName(),
            "source",
            "enum('portability','operator','regulator','innonet','voxbone','detached','portability_not_for_sale','operator_not_for_sale','portability_innonet','g4m','voice_connect', 'didwww', 'Onderia') DEFAULT 'portability'"
        );
    }
}
