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


 class RegionFilterField extends FilterField
{


    protected function initDataset()
    {
        $this->filterValuesQuery
            ->select('id, name')
            ->from('regions');
    }
    
    protected function applyJoin()
    {
        $this->query->innerJoin('clients cc','cc.id ='.self::QUERY_ALIAS.'.id');
    }
    
    protected function applyCondition()
    {
        $this->query->andwhere('cc.region='.$this->value);
    }
    

    

}

