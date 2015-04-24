<?php

namespace app\classes\grid\filters\simplefilterfield;
use app\classes\grid\filters\SimpleFilterField;
use yii\db\Expression;

class ClientSimpleFilterField extends SimpleFilterField
{
    public $sql_field_name = 'id';
    public $control_atrrs = ['style'=>'margin-right:10px; width:400px', 'class'=>'select2'];
    
    protected function initDataset()
    {   
        $this->filterValuesQuery->subQuery = 'select id, concat(client, " ", company) as name from clients where contract_type_id in (2,8) order by company';
        $this->filterValuesQuery
            ->select('*')
            ->from('(SUB_QUERY) as clients');
    }
    

    
}