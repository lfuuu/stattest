<?php
use app\modules\uu\models\AccountTariff;
use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\UsageVoip;

/**
 * Class m170224_080039_account_nds
 */
class m170224_080039_account_nds extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->addColumn(ClientAccount::tableName(),
            'effective_vat_rate',
            $this->integer()->notNull()->defaultValue(0));

        $this->update(ClientContract::tableName(), ['organization_id' => 1], ['organization_id' => 0]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropColumn(ClientAccount::tableName(), 'effective_vat_rate');
    }
}
