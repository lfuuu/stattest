<?php

use app\models\Language;
use app\models\OrganizationI18N;
use \app\models\Organization;

class m160822_113544_organization_i18n extends \app\classes\Migration
{
    public function up()
    {
        $tableName = OrganizationI18N::tableName();

        $this->createTable(
            $tableName,
            [
                'organization_record_id' => $this->integer(11)->defaultValue(null),
                'lang_code' => $this->string(5)->defaultValue(Language::LANGUAGE_DEFAULT),
                'field' => $this->string(255),
                'value' => $this->string(255)
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

        $this->addForeignKey(
            'fk-' . $tableName . '-lang_code',
            $tableName,
            'lang_code',
            Language::tableName(),
            'code',
            'CASCADE',
            'CASCADE'
        );

        $this->createIndex(
            'organization_record_id-lang_code-field',
            $tableName,
            [
                'organization_record_id',
                'lang_code',
                'field'
            ],
            $isUnique = true
        );

        $organizations = Organization::find()->all();
        $insert = [];

        foreach ($organizations as $organization) {
            $insert[] = [
                $organization->id,
                Language::LANGUAGE_DEFAULT,
                'name',
                $organization->name,
            ];
            $insert[] = [
                $organization->id,
                Language::LANGUAGE_DEFAULT,
                'full_name',
                $organization->full_name,
            ];
            $insert[] = [
                $organization->id,
                Language::LANGUAGE_DEFAULT,
                'legal_address',
                $organization->legal_address,
            ];
            $insert[] = [
                $organization->id,
                Language::LANGUAGE_DEFAULT,
                'post_address',
                $organization->post_address,
            ];
        }

        if (count($insert)) {
            $chunks = array_chunk($insert, 1000);

            foreach ($chunks as $chunk) {
                $this->batchInsert($tableName, ['organization_record_id', 'lang_code', 'field', 'value'], $chunk);
            }
        }

        $organizationTable = Organization::tableName();
        $this->dropColumn($organizationTable, 'name');
        $this->dropColumn($organizationTable, 'full_name');
        $this->dropColumn($organizationTable, 'legal_address');
        $this->dropColumn($organizationTable, 'post_address');
    }

    public function down()
    {
        $tableName = OrganizationI18N::tableName();
        $this->dropTable($tableName);

        $organizationTable = Organization::tableName();
        $this->addColumn($organizationTable, 'name', $this->string(250));
        $this->addColumn($organizationTable, 'full_name', $this->string(150));
        $this->addColumn($organizationTable, 'legal_address', $this->string(150));
        $this->addColumn($organizationTable, 'post_address', $this->string(250));
    }
}