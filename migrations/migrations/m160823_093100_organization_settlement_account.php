<?php

use app\models\Organization;
use app\models\OrganizationSettlementAccount;

class m160823_093100_organization_settlement_account extends \app\classes\Migration
{
    public function up()
    {
        $tableName = OrganizationSettlementAccount::tableName();

        $this->createTable(
            $tableName,
            [
                'organization_record_id' => $this->integer(11),
                'settlement_account_type_id' => $this->integer(1)->defaultValue(OrganizationSettlementAccount::SETTLEMENT_ACCOUNT_TYPE_RUSSIA),
                'bank_name' => $this->string(255),
                'bank_address' => $this->string(255),
                'bank_account' => $this->string(128),
                'bank_correspondent_account' => $this->string(64),
                'bank_bik' => $this->string(20),
            ],
            'ENGINE=InnoDB CHARSET=utf8'
        );

        $this->addForeignKey(
            'fk-' . $tableName . '-organization_record_id',
            $tableName,
            'organization_record_id',
            Organization::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex('bank_name-settlement_account_type_id', $tableName, ['bank_name', 'settlement_account_type_id']);
        $this->createIndex('bank_account', $tableName, ['bank_account']);
        $this->createIndex('bank_correspondent_account', $tableName, ['bank_correspondent_account']);
        $this->createIndex('bank_bik', $tableName, ['bank_bik']);

        $organizations = Organization::find()->all();
        $insert = [];

        foreach ($organizations as $organization) {
            $insert[] = [
                $organization->id,
                !empty($organization->bank_swift)
                    ? OrganizationSettlementAccount::SETTLEMENT_ACCOUNT_TYPE_SWIFT
                    : OrganizationSettlementAccount::SETTLEMENT_ACCOUNT_TYPE_RUSSIA,
                $organization->bank_name,
                $organization->bank_account,
                $organization->bank_correspondent_account,
                $organization->bank_bik,
            ];
        }

        if (count($insert)) {
            $chunks = array_chunk($insert, 1000);

            foreach ($chunks as $chunk) {
                $this->batchInsert(
                    $tableName,
                    [
                        'organization_record_id', 'settlement_account_type_id',
                        'bank_name', 'bank_account', 'bank_correspondent_account', 'bank_bik',
                    ],
                    $chunk
                );
            }
        }

        $organizationTable = Organization::tableName();
        $this->dropColumn($organizationTable, 'bank_name');
        $this->dropColumn($organizationTable, 'bank_account');
        $this->dropColumn($organizationTable, 'bank_correspondent_account');
        $this->dropColumn($organizationTable, 'bank_bik');
        $this->dropColumn($organizationTable, 'bank_swift');
    }

    public function down()
    {
        $organizationTable = Organization::tableName();

        $this->addColumn($organizationTable, 'bank_name', $this->string(255));
        $this->addColumn($organizationTable, 'bank_account', $this->string(128));
        $this->addColumn($organizationTable, 'bank_correspondent_account', $this->string(64));
        $this->addColumn($organizationTable , 'bank_bik', $this->string(20));
        $this->addColumn($organizationTable , 'bank_swift', $this->string(11));

        foreach (OrganizationSettlementAccount::find()->each() as $record) {
            Yii::$app->db->createCommand(
                'UPDATE ' . $organizationTable . ' SET
                    bank_name = :bank_name,
                    bank_account = :bank_account,
                    bank_correspondent_account = :bank_correspondent_account,
                    bank_bik = :bank_bik,
                    bank_swift = :bank_swift
                WHERE
                    id = :organization_record_id
                ', [
                    'bank_name' => $record->bank_name,
                    'bank_account' => $record->bank_account,
                    'bank_correspondent_account' => $record->bank_correspondent_account,
                    'bank_bik' => $record->bank_bik,
                    'bank_swift' => $record->bank_swift,
                    'organization_record_id' => $record->organization_record_id,
                ]
            )->execute();
        }

        $tableName = OrganizationSettlementAccount::tableName();
        $this->dropTable($tableName);
    }
}