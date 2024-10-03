<?php

/**
 * Class m241003_175208_newaccounts_invoices_date_admin_access
 */
class m241003_175208_newaccounts_invoices_date_admin_access extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        $this->insert('user_grant_groups', ['name' => 'admin', 'resource' => 'newaccounts_invoices_date', 'access' => 'access']);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete('user_grant_groups', ['name' => 'admin', 'resource' => 'newaccounts_invoices_date', 'access' => 'access']);
    }
}
