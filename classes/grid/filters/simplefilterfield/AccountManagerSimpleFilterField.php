<?php

namespace app\classes\grid\filters\simplefilterfield;
use app\classes\grid\filters\SimpleFilterField;

class AccountManagerSimpleFilterField extends SimpleFilterField
{
    public $sql_field_name = 'account_manager';
    
    protected function initDataset()
    {     
        $this->filterValuesQuery
            ->select('account_manager AS id, account_manager AS name')
            ->from('clients')
            ->groupBy('account_manager')
            ->orderBy('account_manager');
    }
    
}