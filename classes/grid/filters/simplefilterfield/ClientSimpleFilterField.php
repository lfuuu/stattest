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
        $this->filterValuesQuery->subQuery = 'SELECT c.id, CONCAT(c.client," ",c.company) as name FROM clients c
                                              INNER JOIN newbills b ON b.client_id = c.id
                                              WHERE b.is_payed = 1 AND c.contract_type_id IN (2,8) GROUP BY c.id ORDER BY c.company';
        $this->filterValuesQuery
            ->select('*')
            ->from('(SUB_QUERY) as clients1');
    }   
}