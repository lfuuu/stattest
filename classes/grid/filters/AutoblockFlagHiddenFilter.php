<?php
namespace app\classes\grid\filters;

use yii\base\Object;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use Yii;
use app\classes\grid\filters\FilterField;

class AutoblockFlagHiddenFilter extends FilterField
{
    protected function initDataset()
    {
       return null;
    }
    
    protected function applyJoin()
    {
        return null;
    }
    
    protected function applyCondition()
    {
        
        $pg_query = new Query;
        //пример запроса к постгесу, главное что бы выдавал ид. (выборка от балды, сделана только для проверки)
        $pg_query->select('id')->from('clients')->where('address_post LIKE "%усачев%"');
        
        //наложение условия
        $ids = implode(',',$pg_query->column());     
        $this->query->andWhere(self::QUERY_ALIAS.'.id in ('.$ids.')');

    }
    
    public function render() {
        return null;
    }
    
    protected function isSetValue()
    {
        return true; //учитывая что наложени фильтра не зависит от пользователя условие будет применено всегда
    }
    

    

}

