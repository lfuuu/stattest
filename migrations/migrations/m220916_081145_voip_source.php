<?php

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\Migration;
use app\models\voip\Source;

/**
 * Class m220916_081145_voip_source
 */
class m220916_081145_voip_source extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(Source::tableName(), [
            'code' => $this->string(32)->notNull(),
            'name' => $this->string(256)->notNull(),
            'is_service' => $this->tinyInteger()->notNull()->defaultValue(0),
            'order' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->addPrimaryKey(Source::tableName() . '-pk', Source::tableName(), 'code');

        $counter = 0;
        $batchInsert = [];
        array_walk(VoipRegistrySourceEnum::$names, function ($item, $key) use (&$counter, &$batchInsert) {
            $batchInsert[] = [$key, $item, (int)isset(VoipRegistrySourceEnum::$service[$key]), $counter++];
        });

        $this->batchInsert(Source::tableName(), ['code', 'name', 'is_service', 'order'], $batchInsert);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(Source::tableName());
    }
}