<?php
namespace app\classes\grid\filters;

use yii\base\Object;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use Yii;

/*
 * Базовый класс фильтра. Общий смысл такой - в конструктор передается объект класса Query, содержаий запрос выборки
 * Класс фильтр накладывает на объект класса Query условия фильтрации и условия джойна с со своим ключевым полем.
 * Ключевое поле содежится в НД фильтра $this->dataset. В наследниках этого класса нужно переопределить методы
 * 1. initDataset() - формирование НД для списка фильтра.
 * 2. applyJoin() - джойн НД, в котором есть ключевое поле фильтра
 * 3. applyCondition() - условие where указывающее как применять значение фильтра указанное пользователем.
 */
abstract class FilterField extends Object
{
    /** @var Query */
    public $query;
    /** @var Query */
    public $filterValuesQuery;
    public $control_name;
    public $value;
    public $noselected_value = 'noset';

    public $control_atrrs = ['style'=>'margin-right:10px'];
    
    protected static $call_count = 0;
    static $no_select = '-Не выбрано-';
    
    const QUERY_ALIAS = 'sub_query';

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
        $this->filterValuesQuery = new Query;
        $this->initDataset();
        self::$call_count++;
        $this->control_name = str_replace('\\','-',$this->className()) . self::$call_count;
        $this->initValue();
        $this->applyFilter();
    }

    public function render(){

       $no_select[$this->noselected_value] = self::$no_select;
       $options = ArrayHelper::map($this->filterValuesQuery->all(), 'id', 'name');
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
        return $this->value != $this->noselected_value && $this->value > 0;
    }
    
    abstract protected function initDataset();
    
    abstract protected function applyJoin();
    
    abstract protected function applyCondition();
    
    public function applyFilter()
    {
        if ($this->isSetValue())
        {
            $this->applyJoin();
            $this->applyCondition();
        }
    }

}

