<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\Country;
use app\models\NumberType;
use kartik\grid\GridView;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;


class NumberTypeColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $isAddLink = true;

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = NumberType::getList(true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' number-type-column';
    }

}