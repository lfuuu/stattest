<?php
namespace app\classes\grid\filters;

use yii\base\Object;
use app\classes\grid\Query;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use Yii;
use app\classes\grid\filters\FilterField;

abstract class SimpleFilterField extends FilterField
{
    public $sql_field_name;
    
    protected function applyJoin()
    {

    }
    
    protected function applyCondition()
    {
        $this->query->andWhere(self::QUERY_ALIAS.'.'.$this->sql_field_name . '=' . Yii::$app->db->quoteValue($this->value));
        
    }
    
    protected function isSetValue()
    {
        return ($this->value != $this->noselected_value && $this->value != null );
    }
    

    

}

