<?php
use app\models\Business;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\Country;
use app\models\TaxVoipSettings;

/**
 * Class m170315_111636_contract_is_with_tax
 */
class m170315_111636_contract_is_with_tax extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->createTable(TaxVoipSettings::tableName(), [
            'id' => $this->primaryKey(),
            'business_id' => $this->integer()->notNull()->defaultValue(0),
            'country_id' => $this->integer()->notNull()->defaultValue(0),
            'is_with_tax' => $this->integer()->notNull()->defaultValue(1)
        ]);

        $this->addForeignKey('fk-country', TaxVoipSettings::tableName(), 'country_id', Country::tableName(), 'code', 'RESTRICT');
        $this->addForeignKey('fk-business', TaxVoipSettings::tableName(), 'business_id', Business::tableName(), 'id', 'RESTRICT');

        $this->batchInsert(TaxVoipSettings::tableName(), ['business_id', 'country_id', 'is_with_tax'], [
            [Business::TELEKOM, Country::RUSSIA, 1],
            [Business::OTT, Country::RUSSIA, 1],
            [Business::INTERNAL_OFFICE, Country::RUSSIA, 1],
        ]);

        $this->addColumn(ClientContract::tableName(), 'is_voip_with_tax', $this->integer()->notNull()->defaultValue(0));
        $this->addColumn(ClientAccount::tableName(), 'is_voip_with_tax', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->dropTable(TaxVoipSettings::tableName());
        $this->dropColumn(ClientContract::tableName(), 'is_voip_with_tax');
        $this->dropColumn(ClientAccount::tableName(), 'is_voip_with_tax');
    }
}
