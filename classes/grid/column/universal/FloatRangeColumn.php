<?php

namespace app\classes\grid\column\universal;

use Yii;


class FloatRangeColumn extends IntegerRangeColumn
{
    public $step = 0.01;
}