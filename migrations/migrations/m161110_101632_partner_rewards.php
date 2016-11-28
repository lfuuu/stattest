<?php

use app\models\PartnerRewards;
use app\models\Bill;
use app\models\BillLine;

class m161110_101632_partner_rewards extends \app\classes\Migration
{
    public function up()
    {
        $rewardTableName = PartnerRewards::tableName();
        $billTableName = Bill::tableName();
        $lineTableName = BillLine::tableName();

        $this->createTable($rewardTableName, [
            'id' => $this->primaryKey(),
            'bill_id' => $this->integer(10)->unsigned()->notNull(),
            'line_pk' => $this->integer(10)->unsigned()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'once' => $this->float(),
            'percentage_once' => $this->float(),
            'percentage_of_fee' => $this->float(),
            'percentage_of_over' => $this->float(),
            'percentage_of_margin' => $this->float(),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->createIndex('bill_id-line_pk', $rewardTableName, ['bill_id', 'line_pk',], $isUnique = true);

        $this->addForeignKey(
            'fk-' . $rewardTableName . '-bill_id',
            $rewardTableName,
            'bill_id',
            $billTableName,
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-' . $rewardTableName . '-line_pk',
            $rewardTableName,
            'line_pk',
            $lineTableName,
            'pk',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $tableName = PartnerRewards::tableName();

        $this->dropTable($tableName);
    }
}