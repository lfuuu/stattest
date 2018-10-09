<?php

use app\models\BillLine;
use app\models\Bill;
use app\models\PartnerRewards;
use app\models\PartnerRewardsPermanent;

/**
 * Class m180920_114830_create_table_partner_rewards_permanent
 */
class m180920_114830_create_table_partner_rewards_permanent extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $partnerRewardsPermanentTableName = PartnerRewardsPermanent::tableName();
        $partnerRewardsTableName = PartnerRewards::tableName();
        $billTableName = Bill::tableName();
        $lineTableName = BillLine::tableName();

        $this->createTable($partnerRewardsPermanentTableName, [
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

        $this->createIndex('bill_id-line_pk', $partnerRewardsPermanentTableName, ['bill_id', 'line_pk',], $isUnique = true);
        $this->addForeignKey('fk-' . $partnerRewardsPermanentTableName . '-bill_id', $partnerRewardsPermanentTableName, 'bill_id', $billTableName, 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-' . $partnerRewardsPermanentTableName . '-line_pk', $partnerRewardsPermanentTableName, 'line_pk', $lineTableName, 'pk', 'CASCADE', 'CASCADE');

        $db = PartnerRewards::getDb();
        $columns = implode(',', ['bill_id', 'line_pk', 'created_at', 'once', 'percentage_once', 'percentage_of_fee', 'percentage_of_over', 'percentage_of_margin']);
        $this->execute("INSERT INTO {$partnerRewardsPermanentTableName} ({$columns}) (SELECT {$columns} FROM {$partnerRewardsTableName} pr);");
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(PartnerRewardsPermanent::tableName());
    }
}
