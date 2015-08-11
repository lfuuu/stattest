<?php

namespace app\classes\grid\column;

use app\models\Currency;
use kartik\grid\GridView;

class CurrencyColumn extends DataColumn
{

    public $attribute = 'currency_id';
    public $value = 'currency.id';
    public $label = 'Валюта';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = ['' => ' ---- '] + Currency::dao()->getList($columnName = 'id', false);
        parent::__construct($config);
    }

}