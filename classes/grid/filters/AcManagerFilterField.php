<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace app\classes\grid\filters;

use yii\base\Object;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use Yii;
use app\classes\grid\filters\FilterField;


 class AcManagerFilterField extends FilterField
{

 
    protected function initDataset()
    {
        $this->dataset->select('user as id, name')
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
        $this->query->andwhere('u.user='.Yii::$app->db->quoteValue($this->value));
    }
    
    protected function isSetValue()
    {
        if ($this->value != $this->noselected_value ) return true;
    }
    

    

}

