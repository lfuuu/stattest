<?php

namespace app\classes\grid\column;

use kartik\daterange\DateRangePicker;
use Yii;


class DateRangePickerColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filterType = DateRangePicker::className();

        $this->filterWidgetOptions = [
            'presetDropdown' => true,
            'hideInput' => true,
            'pluginOptions' => [
                'locale' => [
                    'format' => 'DD.MM.YYYY',
                    'separator' => ' - ',
                ],
            ],
            'containerOptions' => [
                'style' => 'width:50px; overflow: hidden;',
                'class' => 'drp-container input-group',
            ],
            'pluginEvents' => [
                'cancel.daterangepicker' => 'function(e, picker) { picker.element.find("input").val("").trigger("change"); }',
            ],
        ];
    }
}