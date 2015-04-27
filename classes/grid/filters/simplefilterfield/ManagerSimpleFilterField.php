<?php

namespace app\classes\grid\filters\simplefilterfield;
use app\classes\grid\filters\SimpleFilterField;

class ManagerSimpleFilterField extends SimpleFilterField
{
    public $sql_field_name = 'manager';
    
    protected function initDataset()
    {     
        $this->filterValuesQuery
            ->select('manager AS id, manager AS name')
            ->from('clients')
            ->groupBy('manager')
            ->orderBy('manager');
    }
    
    
}

