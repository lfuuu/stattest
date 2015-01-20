<?php

/* 
 * Базовый класс фильтра. Общий смысл такой - в конструктор передается объект класса Query, содержаий запрос выборки
 * Класс фильтр накладывает на объект класса Query условия фильтрации и условия джойна с со своим ключевым полем. 
 * Ключевое поле содежится в НД фильтра $this->dataset. В наследниках этого класса нужно переопределить методы 
 * 1. initDataset() - формирование НД для списка фильтра.
 * 2. applyJoin() - джойн НД, в котором есть ключевое поле фильтра
 * 3. applyCondition() - условие where указывающее как применять значение фильтра указанное пользователем.
 */
namespace app\classes\grid\filters;

use yii\base\Object;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use Yii;


abstract class FilterField extends Object
{
    public $query; 
    public $value;
    public $noselected_value = 'noset';
    public $dataset;
    public $control_name;
    
    public $control_atrrs = ['style'=>'margin-right:10px'];
    
    static $call_count = 0;
    static $no_select = '-Не выбрано-';
    
    const QUERY_ALIAS = 'sub_query';
    
    public function __construct($config = array()) {
        self::$call_count++;
        parent::__construct($config);
    }
    
    protected function initValue(){

        if (!empty(Yii::$app->request->post($this->control_name))) {
            $this->value = Yii::$app->request->post($this->control_name);
            if(!empty($this->value))
            {
                Yii::$app->session->set($this->control_name, $this->value);
            }
        }
        else if (!empty(Yii::$app->session->get($this->control_name)))
        {
            $this->value = Yii::$app->session->get($this->control_name);
        }
    }
    
    public function init() {
        parent::init();
        $this->dataset = new Query;
        $this->initDataset();
        $this->control_name = str_replace('\\','-',$this->className()).self::$call_count;
        $this->initValue();
        $this->applyFilter();
    }

    public function render(){

       $no_select[$this->noselected_value] = self::$no_select;
       $options = ArrayHelper::map($this->dataset->all(), 'id', 'name');
       $options = array($this->noselected_value => self::$no_select) + $options;
       
       return Html::dropDownList(
                $this->control_name, 
                $this->value,
                $options,
                $this->control_atrrs
        );
        
    }
    
    protected function isSetValue()
    {
        if ($this->value != $this->noselected_value && $this->value > 0) return true;
    }
    
    abstract protected function initDataset();
    
    abstract protected function applyJoin();
    
    abstract protected function applyCondition();
    
    public function applyFilter()
    {
      if($this->isSetValue())
      {
          $this->applyJoin();
          $this->applyCondition();
      }
    }


}

