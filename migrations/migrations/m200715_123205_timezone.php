<?php

use app\classes\Migration;
use app\models\Timezone;

/**
 * Class m200715_123205_timezone
 */
class m200715_123205_timezone extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(
            Timezone::tableName(),
            [
                'order' => $this->integer()->notNull()->defaultValue(0),
                'code' => $this->string(64)->notNull(),
                'name' => $this->string(256)->notNull(),
            ],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8'
        );

        $this->addPrimaryKey('pk-' . Timezone::tableName(), Timezone::tableName(), ['code']);

        $data = [
            [0, 'America/Los_Angeles', 'America/Los_Angeles, (UTC-8)'],
            [1, 'America/Denver', 'America/Denver, (UTC-7)'],
            [2, 'America/Mexico_City', 'America/Mexico_City, (UTC-6)'],
            [3, 'America/New_York', 'America/New_York, (UTC-5)'],
            [4, 'America/Santiago', 'America/Santiago, (UTC-4)'],
            [5, 'America/Sao_Paulo', 'America/Sao_Paulo, (UTC-3)'],
            [6, 'Europe/London', 'Europe/London, (UTC+0)'],
            [7, 'UTC', 'Europe/London, (UTC+0) (old version)'],
            [8, 'Europe/Vienna', 'Europe/Vienna, (UTC+1)'],
            [9, 'Europe/Kiev', 'Europe/Kiev, (UTC+2)'],
            [10, 'Europe/Moscow', 'Europe/Moscow, (UTC+3)'],
            [11, 'Europe/Samara', 'Europe/Samara, (UTC+4)'],
            [12, 'Asia/Yekaterinburg', 'Asia/Yekaterinburg, (UTC+5)'],
            [13, 'Asia/Omsk', 'Asia/Omsk, (UTC+6)'],
            [14, 'Asia/Novosibirsk', 'Asia/Novosibirsk, (UTC+7)'],
            [15, 'Asia/Irkutsk', 'Asia/Irkutsk, (UTC+8)'],
            [16, 'Asia/Yakutsk', 'Asia/Yakutsk, (UTC+9)'],
            [17, 'Asia/Vladivostok', 'Asia/Vladivostok, (UTC+10)'],
            [18, 'Asia/Magadan', 'Asia/Magadan, (UTC+11)'],
            [19, 'Asia/Kamchatka', 'Asia/Kamchatka, (UTC+12)'],
        ];



        $this->batchInsert(Timezone::tableName(),  ['order', 'code', 'name'], $data);

    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(Timezone::tableName());
    }
}
