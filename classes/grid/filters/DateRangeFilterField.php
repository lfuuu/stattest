<?php
namespace app\classes\grid\filters;

use yii\base\Object;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use Yii;
use app\classes\grid\filters\FilterField;

class DateRangeFilterField extends FilterField  
{
    const DATE_FROM = 1 ;
    const DATE_TO = 2 ;
    
    public $sql_field_name = 'b.bill_date';

    
    protected function initDataset()
    {
       $this->control_atrrs['class'] = 'datepicker';
       return null;
    }
    
    protected function applyJoin()
    {
        return null;
    }
    
    protected function applyCondition()
    {
        $date_from = $this->value[self::DATE_FROM]; 
        $date_to = $this->value[self::DATE_TO];
        $this->query->params = ['date_from' => $date_from,'date_to' => $date_to];
    }
    
    protected function initValue(){
      
       parent::initValue();
       
       if(empty($this->value))
       {
         $date_from = new \DateTime();
         $date_from->modify( 'first day of this month' );
         $this->value[self::DATE_FROM] = $date_from->format('Y-m-d');

         $date_to = new \DateTime();
         $date_to->modify( 'last day of this month' );
         $this->value[self::DATE_TO] = $date_to->format('Y-m-d');
       }
  
    }
    
    public function render() 
    {
        $date_from = Html::input(
                'text',
                $this->control_name.'['.self::DATE_FROM.']', 
                $this->value[self::DATE_FROM],
                $this->control_atrrs       
        );
        
        $date_to = Html::input(
                'text',
                $this->control_name.'['.self::DATE_TO.']', 
                $this->value[self::DATE_TO],
                $this->control_atrrs       
        );
        
        return $date_from.$date_to;
    }
    
    
    protected function isSetValue()
    {
        //применять дату ранжированя по умолчанию см InitValue
        return true; 
    }        

}

