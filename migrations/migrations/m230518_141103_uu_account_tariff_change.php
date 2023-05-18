<?php

use app\classes\Migration;
use app\modules\uu\models\AccountTariffChange;

/**
 * Class m230518_141103_uu_account_tariff_change
 */
class m230518_141103_uu_account_tariff_change extends Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(AccountTariffChange::tableName(), [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'client_account_id' => $this->integer()->notNull(),
            'account_tariff_id' => $this->integer()->notNull(),
            'message' => $this->json(),
            'is_published' => $this->tinyInteger()->notNull()->defaultValue(0),
        ]);

        $this->createIndex(AccountTariffChange::tableName() . '-idx', AccountTariffChange::tableName(), ['account_tariff_id', 'is_published']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(AccountTariffChange::tableName());
    }
}
