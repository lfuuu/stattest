<?php

use app\classes\Migration;
use app\models\danycom\Address;
use app\models\danycom\Info;
use app\models\danycom\Number;

/**
 * Class m200930_141726_dc_number
 */
class m200930_141726_dc_number extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(Number::tableName(), [
            'account_id' => $this->integer(11)->notNull(),
            'number' => $this->string(32),
            'region' => $this->string(256),
            'operator' => $this->string(256),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addPrimaryKey('pk-' . Number::tableName(), Number::tableName(), ['account_id', 'number']);

        $this->createTable(Address::tableName(), [
            'id' => $this->primaryKey(),
            'account_id' => $this->integer(11)->notNull(),
            'address' => $this->string(1024)->notNull()->defaultValue(''),
            'post_code' => $this->string(128),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->createIndex('idx-' . Address::tableName() . '-account_id', Address::tableName(), ['account_id']);

        $this->createTable(Info::tableName(), [
            'account_id' => $this->primaryKey(),
            'tariff' => $this->string(64),
            'temp' => $this->string(32),
            'delivery_type' => $this->string(128),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(Number::tableName());
        $this->dropTable(Address::tableName());
        $this->dropTable(Info::tableName());
    }
}
