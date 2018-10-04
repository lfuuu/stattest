<?php

use app\models\Trouble;
use app\models\TroubleRoistat;

/**
 * Class m180924_120614_add_columns_to_trouble
 */
class m180924_120614_add_columns_to_trouble extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $troubleRoistatTableName = TroubleRoistat::tableName();
        $this->createTable($troubleRoistatTableName, [
            'id' => $this->primaryKey(),
            'trouble_id' => $this->integer()->notNull(),
            'roistat_visit' => $this->integer(),
            'roistat_price' => $this->float(),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->createIndex('trouble_id_idx', $troubleRoistatTableName, 'trouble_id', true);

        $troubleTableName = Trouble::tableName();
        $this->addColumn($troubleTableName, 'updated_at', $this->dateTime());
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(Trouble::tableName(), 'updated_at');
        $this->dropTable(TroubleRoistat::tableName());
    }
}
