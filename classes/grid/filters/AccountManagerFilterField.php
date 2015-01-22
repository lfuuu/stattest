<?php
namespace app\classes\grid\filters;

use yii\base\Object;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use Yii;
use app\classes\grid\filters\FilterField;

class AccountManagerFilterField extends FilterField
{
    protected function initDataset()
    {
        $this->filterValuesQuery
            ->select('user as id, name')
            ->from('user_users')
            ->where('usergroup IN ("account_managers")')
            ->orderBy('name');
    }
    
    protected function applyJoin()
    {
        $this->query->innerJoin('user_users u','u.user ='.self::QUERY_ALIAS.'.manager');
    }
    
    protected function applyCondition()
    {
        $this->query->andWhere('u.user='.Yii::$app->db->quoteValue($this->value));
    }
    
    protected function isSetValue()
    {
       // var_dump($this->value); exit;
        return ($this->value != $this->noselected_value && $this->value != null );
    }
    

    

}

