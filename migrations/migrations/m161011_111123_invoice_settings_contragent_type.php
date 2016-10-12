<?php

use app\models\InvoiceSettings;

class m161011_111123_invoice_settings_contragent_type extends \app\classes\Migration
{
    public function up()
    {
        $tableName = InvoiceSettings::tableName();

        $this->addColumn($tableName, 'contragent_type', $this->string(20)->defaultValue(InvoiceSettings::CONTRAGENT_TYPE_DEFAULT));

        $this->dropForeignKey('fk-' . $tableName . '-customer_country_code', $tableName);
        $this->dropForeignKey('fk-' . $tableName . '-doer_country_code', $tableName);
        $this->dropIndex('customer_country-doer_country-settlement_account_type_id', $tableName);

        $this->setForeignKeys();

        $this->createIndex(
            'customer_country-doer_country-sa_type_id-contragent_type',
            $tableName,
            [
                'customer_country_code',
                'doer_country_code',
                'settlement_account_type_id',
                'contragent_type',
            ],
            $isUnique = true
        );
    }

    public function down()
    {
        $tableName = InvoiceSettings::tableName();

        $this->dropForeignKey('fk-' . $tableName . '-customer_country_code', $tableName);
        $this->dropForeignKey('fk-' . $tableName . '-doer_country_code', $tableName);
        $this->dropIndex('customer_country-doer_country-sa_type_id-contragent_type', $tableName);

        $this->dropColumn($tableName, 'contragent_type');

        $this->setForeignKeys();

        $this->createIndex(
            'customer_country-doer_country-settlement_account_type_id',
            $tableName,
            [
                'customer_country_code',
                'doer_country_code',
                'settlement_account_type_id'
            ],
            $isUnique = true
        );
    }

    private function setForeignKeys()
    {
        $tableName = InvoiceSettings::tableName();

        $this->addForeignKey(
            'fk-' . $tableName . '-customer_country_code',
            $tableName,
            'customer_country_code',
            \app\models\Country::tableName(),
            'code',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-' . $tableName . '-doer_country_code',
            $tableName,
            'doer_country_code',
            \app\models\Country::tableName(),
            'code',
            'SET NULL',
            'CASCADE'
        );
    }
}