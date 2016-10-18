<?php

use app\models\InvoiceSettings;

class m161014_141802_invoice_settings_change extends \app\classes\Migration
{
    public function up()
    {
        $tableName = InvoiceSettings::tableName();

        $this->dropTable($tableName);

        $this->createTable($tableName, [
            'doer_organization_id' => $this->integer(11),
            'customer_country_code' => $this->integer(4)->defaultValue(null),
            'vat_apply_scheme' => $this->integer()->defaultValue(1),
            'settlement_account_type_id' => $this->integer(),
            'vat_rate' => $this->integer(6),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->createIndex(
            'org_id-customer_country-settlement_account-scheme',
            $tableName,
            [
                'doer_organization_id',
                'customer_country_code',
                'settlement_account_type_id',
                'vat_apply_scheme'
            ],
            $isUnique = true
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
    }

    public function down()
    {
        $tableName = InvoiceSettings::tableName();

        $this->dropTable($tableName);

        $this->createTable(
            $tableName,
            [
                'customer_country_code' => $this->integer(4)->defaultValue(null),
                'doer_country_code' => $this->integer(11)->defaultValue(null),
                'settlement_account_type_id' => $this->integer(1),
                'vat_rate' => $this->integer(6),
                'contragent_type' => $this->string(20)->defaultValue('*'),
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
}