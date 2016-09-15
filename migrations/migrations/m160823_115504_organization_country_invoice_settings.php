<?php

use app\models\InvoiceSettings;

class m160823_115504_organization_country_invoice_settings extends \app\classes\Migration
{
    public function up()
    {
        $tableName = InvoiceSettings::tableName();

        $this->createTable(
            $tableName,
            [
                'customer_country_code' => $this->integer(4)->defaultValue(null),
                'doer_country_code' => $this->integer(11)->defaultValue(null),
                'settlement_account_type_id' => $this->integer(1),
                'vat_rate' => $this->integer(6),
            ],
            'ENGINE=InnoDB CHARSET=utf8'
        );

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

    public function down()
    {
        $tableName = InvoiceSettings::tableName();
        $this->dropTable($tableName);
    }
}