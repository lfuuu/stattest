<?php

namespace app\classes\grid\column;

use kartik\daterange\DateRangePicker;
use Yii;


class DateRangePickerColumn extends DataColumn
{
    public $name = '';
    public $value = '';
    public $filterType = '';
    public $filter = '';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filterType = DateRangePicker::className();

        $this->filterWidgetOptions = [
            'name' => $this->name,
            'presetDropdown' => true,
            'hideInput' => true,
            'value' => $this->value,
            'pluginOptions' => [
                'locale' => [
                    'format' => 'YYYY-MM-DD',
                    'separator' => ' : ',
                ],
            ]
        ];

        $this->filter = DateRangePicker::widget($this->filterWidgetOptions);
    }
}