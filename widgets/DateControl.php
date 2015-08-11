<?php

namespace app\widgets;

use yii\helpers\ArrayHelper;

class DateControl extends \kartik\datecontrol\DateControl
{

    /**
     * @var array extend widget settings by type
     * for example:
     * ~~~
     * options' => [
     *     'autoWidgetSettings' => [
     *         DateControl::FORMAT_DATE => [
     *             'options' => [
     *                 'pluginOptions' => [
     *                 'todayHighlight' => true,
     *                 'startDate' => 'today',
     *             ],
     *         ],
     *     ],
     * ],
     * ~~~
     * this example extend widget setting like this
     * ~~~
     * 'datecontrol' => [
     *     'autoWidgetSettings' => [
     *         'date' => ['pluginOptions'=> ['autoclose' => true] ],
     *     ],
     * ]
     */
    public $autoWidgetSettings = [];

    protected function initConfig()
    {
        parent::initConfig();
        $this->_widgetSettings = ArrayHelper::merge($this->_widgetSettings, $this->autoWidgetSettings);
    }

}