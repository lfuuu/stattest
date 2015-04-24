<?php

namespace app\classes\grid\filters\simplefilterfield;
use app\classes\grid\filters\SimpleFilterField;

class AccountManagerSimpleFilterField extends SimpleFilterField
{
    public $sql_field_name = 'account_manager';
    
    protected function initDataset()
    {
        $this->filterValuesQuery
            ->select('user as id, name')
            ->from('user_users')
            ->where('usergroup = "account_managers"')
            ->orderBy('name');
    }
    
}