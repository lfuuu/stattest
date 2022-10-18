<?php

use app\classes\Migration;
use app\modules\uu\models\Estimation;

/**
 * Class m221014_132139_uu_account_tariff_preprice
 */
class m221014_132139_uu_account_tariff_preprice extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(Estimation::tableName(), [
            'client_account_id' => $this->integer()->notNull(),
            'account_tariff_id' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'price' => $this->decimal(13, 4),
        ]);

        $this->addPrimaryKey(
            'pk-' . Estimation::tableName(),
            Estimation::tableName(),
            ['client_account_id', 'account_tariff_id']
        );
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(Estimation::tableName());
    }
}
