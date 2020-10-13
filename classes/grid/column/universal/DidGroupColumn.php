<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\DidGroup;
use kartik\grid\GridView;


class DidGroupColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;
    public $country_id = null;
    public $city_id = null;
    public $ndc_type_id = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = $this->country_id && $this->ndc_type_id ? DidGroup::getList($isWithEmpty = true, $this->country_id, $this->city_id, $this->ndc_type_id) : DidGroup::getEmptyList($isWithEmpty = true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' did-group-column';
    }
}