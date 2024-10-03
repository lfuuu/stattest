<?php

/**
 * Class m241003_174503_newaccounts_invoices_date
 */
class m241003_174503_newaccounts_invoices_date extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert('user_rights', ['resource' => 'newaccounts_invoices_date', 'comment' => 'Редактирование даты выставления счетов', 'values' => 'access', 'values_desc' => 'доступ', 'order' => 0]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete('user_rights', ['resource' => 'newaccounts_invoices_date', 'comment' => 'Редактирование даты выставления счетов', 'values' => 'access', 'values_desc' => 'доступ', 'order' => 0]);
    }
}
