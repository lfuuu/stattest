<?php

use app\models\Language;

class m160510_095022_language_append_german extends \app\classes\Migration
{
    public function up()
    {
        $tableName = Language::tableName();

        $this->update($tableName, ['name' => 'Русский'], ['code' => 'ru-RU']);
        $this->insert($tableName, [
            'code' => 'de-DE',
            'name' => 'Deutsch',
        ]);
    }

    public function down()
    {
        $tableName = Language::tableName();

        $this->update($tableName, ['name' => 'Russian'], ['code' => 'ru-RU']);
        $this->delete($tableName, ['code' => 'de-DE']);
    }
}