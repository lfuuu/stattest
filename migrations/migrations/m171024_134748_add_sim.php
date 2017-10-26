<?php

use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use app\modules\sim\models\ImsiStatus;

/**
 * Class m171024_134748_add_sim
 */
class m171024_134748_add_sim extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $cardStatusTableName = CardStatus::tableName();
        $this->createTable($cardStatusTableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->insert($cardStatusTableName, ['id' => CardStatus::ID_DEFAULT, 'name' => 'По умолчанию']);

        $cardTableName = Card::tableName();
        $this->createTable($cardTableName, [
            'iccid' => $this->bigInteger()->notNull(),
            'imei' => $this->bigInteger(),
            'client_account_id' => $this->integer(),
            'is_active' => $this->boolean()->notNull()->defaultValue(1),
            'status_id' => $this->integer()->notNull()->defaultValue(CardStatus::ID_DEFAULT),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addPrimaryKey('pk-' . $cardTableName, $cardTableName, 'iccid');
        $this->addForeignKey('fk-' . $cardTableName . '-status_id', $cardTableName, 'status_id', $cardStatusTableName, 'id', 'RESTRICT');


        $imsiStatusTableName = ImsiStatus::tableName();
        $this->createTable($imsiStatusTableName, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->insert($imsiStatusTableName, ['id' => ImsiStatus::ID_DEFAULT, 'name' => 'По умолчанию']);

        $imsiTableName = Imsi::tableName();
        $this->createTable($imsiTableName, [
            'imsi' => $this->bigInteger()->notNull(),
            'iccid' => $this->bigInteger()->notNull(),
            'msisdn' => $this->bigInteger(),
            'did' => 'varchar(15) CHARACTER SET utf8 COLLATE utf8_bin', // для совместимости с Number
            'is_anti_cli' => $this->integer()->notNull()->defaultValue(0),
            'is_roaming' => $this->integer()->notNull()->defaultValue(0),
            'actual_from' => $this->date(),
            'is_active' => $this->boolean()->notNull()->defaultValue(1),
            'status_id' => $this->integer()->notNull()->defaultValue(ImsiStatus::ID_DEFAULT),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addPrimaryKey('pk-' . $imsiTableName, $imsiTableName, 'imsi');
        $this->addForeignKey('fk-' . $imsiTableName . '-iccid', $imsiTableName, 'iccid', $cardTableName, 'iccid', 'CASCADE');
        $this->addForeignKey('fk-' . $imsiTableName . '-status_id', $imsiTableName, 'status_id', $imsiStatusTableName, 'id', 'RESTRICT');
        $this->addForeignKey('fk-' . $imsiTableName . '-did', $imsiTableName, 'did', \app\models\Number::tableName(), 'number', 'RESTRICT');
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(Imsi::tableName());
        $this->dropTable(ImsiStatus::tableName());

        $this->dropTable(Card::tableName());
        $this->dropTable(CardStatus::tableName());
    }
}
