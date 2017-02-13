<?php

namespace app\widgets\MultipleInput;

use app\classes\Html;

class MultipleInputProfit extends MultipleInput
{

    /**
     * Список доступных для выбора значений
     *
     * @var array
     */
    public $variants = [];

    /**
     * Css класс присваиваемый полю "Значение"
     *
     * @var string
     */
    public $valueClass = 'multiple-value';

    /**
     * Css класс присваиваемый полю "Выбор"
     *
     * @var string
     */
    public $variantsClass = 'multiple-variant';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Executes the widget.
     */
    public function run()
    {
        if ($this->hasModel()) {
            $valueField = Html::activeInput(
                'number',
                $this->model,
                $this->attribute . '[value]',
                [
                    'class' => 'form-control ' . $this->valueClass,
                ] + $this->options
            );
            $selectField = Html::activeDropDownList(
                $this->model,
                $this->attribute . '[variant]',
                $this->variants,
                [
                    'class' => 'form-control ' . $this->variantsClass,
                ]
            );
        } else {
            $valueField = Html::input(
                'number',
                $this->name . '[value]',
                $this->value,
                [
                    'class' => 'form-control ' . $this->valueClass,
                ] + $this->options
            );
            $selectField = Html::dropDownList(
                $this->name . '[variant]',
                $this->value,
                $this->variants,
                [
                    'class' => 'form-control ' . $this->variantsClass,
                ]
            );
        }

        echo Html::beginTag('div', ['class' => 'col-sm-12']);

            echo Html::tag('div', $valueField, ['class' => 'col-sm-6']);
            echo Html::tag('div', $selectField, ['class' => 'col-sm-6']);

        echo Html::endTag('div');
    }

}
