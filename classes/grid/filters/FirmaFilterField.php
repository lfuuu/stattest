<?php
namespace app\classes\grid\filters;

use yii\base\Object;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use Yii;
use app\classes\grid\filters\FilterField;

class FirmaFilterField extends FilterField
{
    protected function initDataset()
    {
        $this->filterValuesQuery
            ->select('firma as id, firma as name')
            ->from('clients')
            ->where('firma!="         "')
            ->groupby('firma');
    }
    
    protected function applyJoin()
    {
        $this->query->innerJoin('clients firma_clients','firma_clients.id ='.self::QUERY_ALIAS.'.id');
    }
    
    protected function applyCondition()
    {
        $this->query->andWhere('firma_clients.firma='.Yii::$app->db->quoteValue($this->value));  
    }
    
    protected function isSetValue()
    {
        // var_dump($this->value); exit;
        return ($this->value != $this->noselected_value && $this->value != null );
    }
    

    

}

