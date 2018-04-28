<?php

use app\models\UserRight;

/**
 * Class m180427_123332_pay_import_rights
 */
class m180427_123332_pay_import_rights extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert(UserRight::tableName(), [
            'resource' => 'newaccounts_import_payments',
            'comment' => 'Импорт платежей',
            'values' => 'read,write',
            'values_desc' => 'чтение,редактирование',
            'order' => 0,
        ]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete(UserRight::tableName(), [
            'resource' => 'newaccounts_import_payments'
        ]);
    }
}
