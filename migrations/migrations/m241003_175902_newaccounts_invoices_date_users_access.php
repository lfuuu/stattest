<?php

/**
 * Class m241003_175902_newaccounts_invoices_date_users_access
 */
class m241003_175902_newaccounts_invoices_date_users_access extends \app\classes\Migration
{
    /**
     * Up 
     */
    public function safeUp()
    {
        $this->batchInsert('user_grant_users', ['name', 'resource', 'access'], [['birukova', 'newaccounts_invoices_date', 'access'],
                                                                           ['bnv', 'newaccounts_invoices_date', 'access'],
                                                                           ['pma', 'newaccounts_invoices_date', 'access'],
                                                                           ['isaev', 'newaccounts_invoices_date', 'access'],
                                                                           ['ava', 'newaccounts_invoices_date', 'access'],]);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $this->delete('user_grant_users', ['name' => ['birukova', 'bnv', 'pma', 'isaev', 'ava'], 'resource' => 'newaccounts_invoices_date', 'access' => 'access']);
    }
}
