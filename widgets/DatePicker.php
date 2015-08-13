<?php

namespace app\widgets;

use Yii;
use DateTime;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;

class DatePicker extends \kartik\date\DatePicker
{

    /**
     * @var array The addon that will be prepended/appended for a  [[TYPE_COMPONENT_PREPEND]] and
     * [[TYPE_COMPONENT_APPEND]]. You can set the following array keys:
     * - part1: string, the content to prepend before the [[TYPE_COMPONENT_PREPEND]] OR
     *          before input # 1 for [[TYPE_RANGE]].
     * - part2: string, the content to prepend after the [[TYPE_COMPONENT_PREPEND]]  OR
     *          after input # 1 for [[TYPE_RANGE]].
     * - part3: string, the content to append before the [[TYPE_COMPONENT_APPEND]]  OR
     *          before input # 2 for [[TYPE_RANGE]].
     * - part4: string, the content to append after the [[TYPE_COMPONENT_APPEND]] OR
     *          after input # 2 for [[TYPE_RANGE]].
     */
    public $addon = [];

    /**
     * @var array the HTML options for the DatePicker container
     */
    private $_container = [];

    /**
     * Returns the addon to render
     *
     * @param array $options the HTML attributes for the addon
     * @param string $type whether the addon is the picker or remove
     * @return string
     */
    protected function renderAddon(&$options, $type = 'picker')
    {
        if ($options === false) {
            return '';
        }
        if (is_string($options)) {
            return $options;
        }
        $icon = ($type === 'picker') ? 'calendar' : $type;
        Html::addCssClass($options, 'input-group-addon kv-date-' . $icon);
        $icon = '<i class="glyphicon glyphicon-' . ArrayHelper::remove($options, 'icon', $icon) . '"></i>';
        $title = ArrayHelper::getValue($options, 'title', '');
        if ($title !== false && empty($title)) {
            $options['title'] = ($type === 'picker') ? Yii::t('kvdate', 'Select date') : Yii::t('kvdate', 'Clear field');
        }
        return Html::tag('span', $icon, $options);
    }

    /**
     * Parses the input to render based on markup type
     *
     * @param string $input
     * @return string
     */
    protected function parseMarkup($input)
    {
        $css = $this->disabled ? ' disabled' : '';
        if ($this->type == self::TYPE_INPUT || $this->type == self::TYPE_INLINE) {
            if (isset($this->size)) {
                Html::addCssClass($this->options, 'input-' . $this->size . $css);
            }
        } elseif ($this->type != self::TYPE_BUTTON && isset($this->size)) {
            Html::addCssClass($this->_container, 'input-group input-group-' . $this->size . $css);
        } elseif ($this->type != self::TYPE_BUTTON) {
            Html::addCssClass($this->_container, 'input-group' . $css);
        }
        if ($this->type == self::TYPE_INPUT) {
            return $input;
        }
        $part1 = $part2 = $part3 = $part4 = '';
        if (!empty($this->addon) && ($this->_hasAddon || $this->type == self::TYPE_RANGE)) {
            $part1 = ArrayHelper::getValue($this->addon, 'part1', '');
            $part2 = ArrayHelper::getValue($this->addon, 'part2', '');
            $part3 = ArrayHelper::getValue($this->addon, 'part3', '');
            $part4 = ArrayHelper::getValue($this->addon, 'part4', '');
        }
        if ($this->_hasAddon) {
            Html::addCssClass($this->_container, 'date');
            $picker = $this->renderAddon($this->pickerButton);
            $remove = $this->renderAddon($this->removeButton, 'remove');

            if ($part4 == 'today') {
                $part4 = $this->renderAddon($this->todayButton(), 'today');
            }

            if ($this->type == self::TYPE_COMPONENT_APPEND) {
                $content = $part1 . $part2 . $input . $part3 . $remove . $picker . $part4;
            } else {
                $content = $part1 . $picker . $remove . $part2 . $input . $part3 . $part4;
            }
            return Html::tag('div', $content, $this->_container);
        }
        if ($this->type == self::TYPE_BUTTON) {
            Html::addCssClass($this->_container, 'date');
            $label = ArrayHelper::remove($this->buttonOptions, 'label', self::CALENDAR_ICON);
            if (!isset($this->buttonOptions['disabled'])) {
                $this->buttonOptions['disabled'] = $this->disabled;
            }
            if (empty($this->buttonOptions['class'])) {
                $this->buttonOptions['class'] = 'btn btn-default';
            }
            $button = Html::button($label, $this->buttonOptions);
            return Html::tag('div', "{$input}{$button}", $this->_container);
        }
        if ($this->type == self::TYPE_RANGE) {
            Html::addCssClass($this->_container, 'input-daterange');
            $this->initDisability($this->options2);
            if (isset($this->form)) {
                Html::addCssClass($this->options, 'form-control kv-field-from');
                Html::addCssClass($this->options2, 'form-control kv-field-to');
                $input = $this->form->field($this->model, $this->attribute, [
                    'template' => '{input}{error}',
                    'options' => ['class' => 'kv-container-from form-control'],
                ])->textInput($this->options);
                $input2 = $this->form->field($this->model, $this->attribute2, [
                    'template' => '{input}{error}',
                    'options' => ['class' => 'kv-container-to form-control'],
                ])->textInput($this->options2);
            } else {
                if (empty($this->options2['id'])) {
                    $this->options2['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute2) : $this->getId() . '-2';
                }
                Html::addCssClass($this->options2, 'form-control');
                $input2 = $this->hasModel() ?
                    Html::activeTextInput($this->model, $this->attribute2, $this->options2) :
                    Html::textInput($this->name2, $this->value2, $this->options2);
            }
            $content = $part1 . $input . $part2 . "<span class='input-group-addon kv-field-separator'>{$this->separator}</span>" .
                $part3 . $input2 . $part4;
            return Html::tag('div', $content, $this->_container);
        }
        if ($this->type == self::TYPE_INLINE) {
            return Html::tag('div', '', $this->_container) . $input;
        }
    }

    private function todayButton()
    {
        $today = (new DateTime('now'))->format('Y-m-d');
        return [
            'icon' => 'check',
            'title' => 'Установить дату в сегодня',
            'onClick' => new JsExpression("jQuery(this).on('click.kvdatepicker', function(e) {
                e.preventDefault();
                $(this).parent().find('.kv-date-calendar').kvDatepicker('update', '" . $today . "');
            })"),
        ];
    }

}