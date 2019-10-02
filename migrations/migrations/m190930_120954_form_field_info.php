<?php

use app\models\dictionary\FormInfo;
use app\models\dictionary\FormInfoData;

/**
 * Class m190930_120954_form_field_info
 */
class m190930_120954_form_field_info extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(FormInfo::tableName(), [
            'id' => $this->primaryKey(),
            'form_url' => $this->string(255)
        ]);

        $this->createTable(FormInfoData::tableName(), [
            'id' => $this->primaryKey(),
            'form_id' => $this->integer(),
            'key' => $this->string(64)->notNull(),
            'url' => $this->string(1024),
        ]);

        $this->addForeignKey(
            'fk-' . FormInfoData::tableName() . '-form_id--' . FormInfo::tableName() . '-id',
            FormInfoData::tableName(), 'form_id',
            FormInfo::tableName(), 'id'
        );

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(FormInfoData::tableName());
        $this->dropTable(FormInfo::tableName());
    }
}
