<?php

namespace app\widgets\select_multiply;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\InputWidget;
use yii\base\NotSupportedException;

class SelectMultiply extends InputWidget
{

    public $items = [];
    public $clientOptions = [];

    /**
     * @var bool
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#the-filter1
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#the-filter2
     */
    public $filter = false;

    /**
     * @var bool
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#the-multiple-items
     */
    public $multiple = false;

    /**
     * @var string
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#constructor
     */
    public $selectAllText = 'Выбрать все';
    public $allSelected = 'Все выбрано';
    public $countSelected = 'Выбрано # из %';
    public $noMatchesFound = 'Ничего не найдено';

    /**
     * @var int
     * @see http://wenzhixin.net.cn/p/multiple-select/docs/#the-multiple-items
     */
    public $multipleWidth = 80;

    public function init()
    {
        parent::init();

        $this->clientOptions = array_merge(
            $this->clientOptions,
            [
                'filter' => $this->filter,
                'multiple' => $this->multiple,
                'multipleWidth' => $this->multipleWidth,
                'selectAllText' => $this->selectAllText,
                'allSelected' => $this->allSelected,
                'countSelected' => $this->countSelected,
                'noMatchesFound' => $this->noMatchesFound,
            ]
        );
    }

    public function run()
    {
        if ($this->hasModel()) {
            if (array_key_exists('value', $this->options)) {
                if (!isset($this->model->{$this->attribute})) {
                    throw new NotSupportedException("Unable to set value of the property '{$this->attribute}'.");
                }
                $buffer = $this->model->{$this->attribute};
                $this->model->{$this->attribute} = $this->options['value'];
                unset($this->options['value']);
            }

            $output = Html::activeListBox($this->model, $this->attribute, $this->items, $this->options);

            if (isset($buffer)) {
                $this->model->{$this->attribute} = $buffer;
            }
        }
        else {
            $output = Html::listBox($this->name, $this->value, $this->items, $this->options);
        }

        $js = 'jQuery("#' . $this->options['id'] . '").multipleSelect(' . Json::htmlEncode($this->clientOptions) . ');';

        $view = $this->getView();
        Asset::register($view);
        $view->registerJs($js);

        return $output;
    }

}