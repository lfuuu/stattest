<?php

use app\models\Language;
use app\models\message\TemplateContent;
use app\models\Country;

class m160513_094306_message_template_country extends \app\classes\Migration
{
    public function up()
    {
        $tableName = TemplateContent::tableName();

        $this->addColumn($tableName, 'country_id', $this->integer(11));
        $this->addForeignKey(
            'fk-message_template_content-country_id',
            $tableName,
            'country_id',
            Country::tableName(),
            'code'
        );
        $this->dropIndex('template_id_lang_code_type', $tableName);
        $this->createIndex(
            'template_id_lang_code_type_country_id',
            $tableName,
            [
                'template_id', 'lang_code', 'type', 'country_id',
            ],
            true
        );

        $this->update($tableName, ['country_id' => Country::RUSSIA], ['lang_code' => Language::LANGUAGE_RUSSIAN]);
        $this->update($tableName, ['country_id' => Country::HUNGARY], ['lang_code' => Language::LANGUAGE_MAGYAR]);
        $this->update($tableName, ['country_id' => Country::GERMANY], ['lang_code' => Language::LANGUAGE_GERMANY]);
    }

    public function down()
    {
        $tableName = TemplateContent::tableName();

        $this->dropIndex('template_id_lang_code_type_country_id', $tableName);
        $this->createIndex(
            'template_id_lang_code_type',
            $tableName,
            [
                'template_id', 'lang_code', 'type',
            ],
            true
        );
        $this->dropForeignKey('fk-message_template_content-country_id', $tableName);
        $this->dropColumn($tableName, 'country_id');
    }
}