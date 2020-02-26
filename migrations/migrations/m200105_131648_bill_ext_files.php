<?php

use app\models\media\BillExtFiles;

/**
 * Class m200105_131648_bill_ext_files
 */
class m200105_131648_bill_ext_files extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(BillExtFiles::tableName(), [
            'id' => $this->primaryKey(),
            'bill_no' => 'varchar(32) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL',
            'user_id' => $this->integer()->notNull(),
            'ts' => $this->dateTime(),
            'comment' => $this->string(1024)->notNull()->defaultValue(''),
            'name' => $this->string(256)->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('idx-bill_no', BillExtFiles::tableName(), ['bill_no']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(BillExtFiles::tableName());
    }
}
