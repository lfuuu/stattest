<?php

namespace app\classes\grid\column\important_events;

use app\classes\IpUtils;

class IpColumn extends \kartik\grid\DataColumn
{

    public
        $label = 'IP',
        $attribute = 'from_ip';

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        return !is_null($value) ? IpUtils::dtr_ntop($value) : '';
    }

}