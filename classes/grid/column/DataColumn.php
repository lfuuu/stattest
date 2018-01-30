<?php
namespace app\classes\grid\column;

use Yii;


class DataColumn extends \kartik\grid\DataColumn
{
    const EMPTY_VALUE_ID = -1; //Id записи, которой нет

    public $filterInputOptions = ['class' => 'form-control input-sm', 'id' => null];

}