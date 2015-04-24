<?php

namespace app\classes\grid\filters\simplefilterfield;
use app\classes\grid\filters\SimpleFilterField;

class ManagerSimpleFilterField extends SimpleFilterField
{
    public $sql_field_name = 'manager';
    
    protected function initDataset()
    {
        $this->filterValuesQuery
            ->select('user as id, name')
            ->from('user_users')
            ->where('usergroup = "manager"')
            ->orderBy('name');
    }
    
    
}

