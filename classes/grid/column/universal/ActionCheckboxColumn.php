<?php

namespace app\classes\grid\column\universal;

use app\classes\Html;

class ActionCheckboxColumn extends \kartik\grid\CheckboxColumn
{

    /**
     * Значение равно модели
     *
     * @var bool
     */
    public $staticValue = true;

    /**
     * @inheritdoc
     */
    public function renderDataCell($model, $key, $index)
    {
        if ($this->staticValue === false) {
            return parent::renderDataCell($model, $key, $index);
        }

        $options = $this->fetchContentOptions($model, $key, $index);
        if ($this->rowHighlight) {
            $this->initPjax($this->_clientScript);
            Html::addCssClass($options, 'kv-row-select');
        }

        $this->checkboxOptions['value'] = $model;

        return Html::tag('td', $this->renderDataCellContent($model, $key, $index), $options);
    }

}