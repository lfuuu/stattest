<?php

use app\models\ClientContragent;
use app\models\Country;
use app\models\Language;

class m170111_133219_client_contragent_language extends \app\classes\Migration
{

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $contragentTableName = ClientContragent::tableName();
        $countryTableName = Country::tableName();

        $this->addColumn($contragentTableName, 'lang_code', $this->string(5)->defaultValue(Language::LANGUAGE_DEFAULT));

        $this->execute('
            UPDATE
                ' . $contragentTableName . ' contragent
                LEFT JOIN ' . $countryTableName . ' ON country.code = contragent.country_id
            SET
                contragent.lang_code = country.lang;
        ');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $tableName = ClientContragent::tableName();

        $this->dropColumn($tableName, 'lang_code');
    }

}
