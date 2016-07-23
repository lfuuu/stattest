<?php

use app\models\IpBlock;

class m160719_085646_ip_block extends \app\classes\Migration
{
    public function up()
    {
        $this->createTable(IpBlock::tableName(), [
            'ip' => $this->string(32),
            'block_time' => $this->dateTime(),
            'unblock_time' => $this->dateTime()
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addPrimaryKey('pk_ip', IpBlock::tableName(), 'ip');
        $this->createIndex('idx_unblock_time', IpBlock::tableName(), 'unblock_time');
    }

    public function down()
    {
        $this->dropTable(IpBlock::tableName());
    }
}