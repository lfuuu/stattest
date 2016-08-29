<?php

use app\models\User;
use app\models\HistoryVersion;

class m160812_110357_history_version_who_when extends \app\classes\Migration
{
    public function up()
    {
        $tableName = HistoryVersion::tableName();

        $this->addColumn($tableName, 'user_id', $this->integer(11)->defaultValue(NULL));
        $this->addForeignKey(
            'fk-' . $tableName . '-user_id',
            $tableName,
            'user_id',
            User::tableName(),
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function down()
    {
        $tableName = HistoryVersion::tableName();

        $this->dropForeignKey('fk-' . $tableName . '-user_id', $tableName);
        $this->dropColumn($tableName, 'user_id');
    }
}