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
     * - addon existing function name: addon options, like icon, title etc.
     * - addon name: addon options array, like icon, title etc.
     */
    public $addons = [];

    /**
     * @var array the HTML options for the DatePicker container
     */
    private $_container = [];

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

        if ($this->_hasAddon) {
            Html::addCssClass($this->_container, 'date');
            $picker = $this->renderAddon($this->pickerButton);
            if ($this->removeButton)
                $remove = $this->renderAddon($this->removeButton, 'remove');

            $addons = [];
            if (is_array($this->addons)) {
                foreach ($this->addons as $addon => $addon_options) {
                    if (method_exists($this, $addon)) {
                        $addons[] = $this->renderAddon($this->{$addon}($addon_options));
                    }
                    else {
                        $addons[] = $this->renderAddon($addon_options);
                    }
                }
            }

            if ($this->type == self::TYPE_COMPONENT_APPEND) {
                $content = implode('', $addons) . $input . $remove . $picker;
            } else {
                $content = $picker . $remove . $input . implode('', $addons);
            }

            return Html::tag('div', $content, $this->_container);
        }

        return parent::parseMarkup($input);
    }

    private function clearButton(array $options)
    {
        return array_merge([
            'icon' => 'remove',
            'onClick' => new JsExpression("jQuery(this).on('click.kvdatepicker', function(e) {
                var datepicker = $(this).parent();
                datepicker.find('input').attr('placeholder', 'Не задано');
                datepicker.{$this->pluginName}('clearDates');
            })"),
        ], $options);
    }

    private function todayButton(array $options)
    {
        list($year, $month, $day) = explode('-', (new DateTime('now'))->format('Y-m-d'));
        return array_merge([
            'icon' => 'check',
            'title' => 'Установить дату в сегодня',
            'onClick' => new JsExpression("jQuery(this).on('click.kvdatepicker', function(e) {
                var datepicker = $(this).parent();
                datepicker.{$this->pluginName}('setDate', new Date(" . $year . ',' . ($month - 1) . ',' . $day . "));
                datepicker.{$this->pluginName}('hide');
            })"),
        ], $options);
    }

}