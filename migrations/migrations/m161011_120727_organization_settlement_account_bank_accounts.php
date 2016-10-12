<?php

use app\models\Currency;
use app\models\Organization;
use app\models\OrganizationSettlementAccount;
use app\models\OrganizationSettlementAccountProperties;

class m161011_120727_organization_settlement_account_bank_accounts extends \app\classes\Migration
{
    public function up()
    {
        $tableName = OrganizationSettlementAccountProperties::tableName();

        $this->createTable($tableName, [
            'organization_record_id' => $this->integer(11),
            'settlement_account_type_id' => $this->integer(1)->defaultValue(OrganizationSettlementAccount::SETTLEMENT_ACCOUNT_TYPE_RUSSIA),
            'property' => $this->string(255),
            'value' => $this->string(255),
        ], 'ENGINE=InnoDB CHARSET=utf8');

        $this->addForeignKey(
            'fk-' . $tableName . '-organization_r_id',
            $tableName,
            'organization_record_id',
            Organization::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            'organization_record_id-settlement_account_type_id-property',
            $tableName,
            [
                'organization_record_id',
                'settlement_account_type_id',
                'property',
            ],
            $unique = true
        );

        $properties =
            OrganizationSettlementAccount::find()
                ->select([
                    'organization_record_id',
                    'settlement_account_type_id',
                    'bank_account',
                ])
                ->each();

        $insert = [];

        foreach ($properties as $row) {
            if (!isset(OrganizationSettlementAccount::$currencyBySettlementAccountTypeId[$row->settlement_account_type_id])) {
                continue;
            }
            $property =
                'bank_account_' .
                reset(OrganizationSettlementAccount::$currencyBySettlementAccountTypeId[$row->settlement_account_type_id]);

            $insert[] = [
                $row->organization_record_id,
                $row->settlement_account_type_id,
                $property,
                $row->bank_account,
            ];
        }

        if (count($insert)) {
            $chunks = array_chunk($insert, 1000);

            foreach ($chunks as $chunk) {
                $this->batchInsert(
                    $tableName,
                    [
                        'organization_record_id', 'settlement_account_type_id', 'property', 'value'
                    ],
                    $chunk
                );
            }
        }

        $this->dropColumn(OrganizationSettlementAccount::tableName(), 'bank_account');
    }

    public function down()
    {
        $this->addColumn(OrganizationSettlementAccount::tableName(), 'bank_account', $this->string(128));

        $properties = OrganizationSettlementAccountProperties::find()->each();

        foreach ($properties as $property) {
            $propertyName = 'bank_account_' . reset(OrganizationSettlementAccount::$currencyBySettlementAccountTypeId[$property->settlement_account_type_id]);
            if ($propertyName != $property->property) {
                continue;
            }

            $settlementAccount = OrganizationSettlementAccount::findOne([
                'organization_record_id' => $property->organization_record_id,
                'settlement_account_type_id' => $property->settlement_account_type_id,
            ]);

            if (!is_null($settlementAccount)) {
                $settlementAccount->bank_account = $property->value;
                $settlementAccount->save();
            }
        }

        $this->dropTable(OrganizationSettlementAccountProperties::tableName());
    }
}