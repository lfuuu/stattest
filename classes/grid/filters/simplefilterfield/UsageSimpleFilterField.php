<?php

namespace app\classes\grid\filters\simplefilterfield;
use app\classes\grid\filters\SimpleFilterField;

class UsageSimpleFilterField extends SimpleFilterField
{
    public $sql_field_name = 'service';
    
    protected function initDataset()
    {
        $this->filterValuesQuery
            ->select('service AS id, service AS name')
            ->from('newbill_lines')
            ->where('service NOT IN ("1C","all4net","bill_monthlyadd","")')
            ->groupBy('service')
            ->orderBy('service'); 
    }
    
}

