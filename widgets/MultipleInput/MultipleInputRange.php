<?php

namespace app\widgets\MultipleInput;

use app\classes\Html;

class MultipleInputRange extends MultipleInput
{

    /**
     * Параметр указывающий шаг для range
     *
     * @var int
     */
    public $step = 1;

    /**
     * Css класс присваиваемый полю "С" / "До"
     *
     * @var string
     */
    public $rangeClass = 'multiple-range';

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
        $rangeLabelFrom = Html::tag('span', 'От', ['style' => 'display: inline-block; padding-top: 8px;']);
        $rangeLabelTo = Html::tag('span', 'До', ['style' => 'display: inline-block; padding-top: 8px;']);

        if ($this->hasModel()) {
            $rangeFieldFrom = $rangeFieldTo = Html::activeInput(
                'number',
                $this->model,
                $this->attribute . '[]',
                [
                    'class' => 'form-control ' . $this->rangeClass,
                    'step' => $this->step,
                ] + $this->options
            );
        } else {
            $rangeFieldFrom = $rangeFieldTo = Html::input(
                'number',
                $this->name . '[]',
                $this->value,
                [
                    'class' => 'form-control ' . $this->rangeClass,
                    'step' => $this->step,
                ] + $this->options
            );
        }

        echo Html::beginTag('div', ['class' => 'col-sm-12']);

            echo Html::tag('div', $rangeLabelFrom, ['class' => 'pull-left']);
            echo Html::tag('div', $rangeFieldFrom, ['class' => 'col-sm-5']);
            echo Html::tag('div', $rangeLabelTo, ['class' => 'pull-left']);
            echo Html::tag('div', $rangeFieldTo, ['class' => 'col-sm-5']);

        echo Html::endTag('div');
    }

}
