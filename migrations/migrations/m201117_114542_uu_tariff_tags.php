<?php

use app\classes\Migration;
use app\modules\uu\models\Tag;
use app\modules\uu\models\TariffTags;

/**
 * Class m201117_114542_uu_tariff_tags
 */
class m201117_114542_uu_tariff_tags extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(TariffTags::tableName(), [
            'id' => $this->primaryKey(),
            'tariff_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey('fk-' . TariffTags::tableName() . '-tag_id-' . Tag::tableName() . '-id',
            TariffTags::tableName(), 'tag_id',
            Tag::tableName(), 'id',
            'CASCADE', 'CASCADE'
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(TariffTags::tableName());
    }
}
