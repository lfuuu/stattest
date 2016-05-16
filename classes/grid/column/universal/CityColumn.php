<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\City;
use kartik\grid\GridView;
use Yii;
use yii\helpers\Html;


class CityColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $isAddLink = true;
    public $filterType = GridView::FILTER_SELECT2;
    public $country_id = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = City::dao()->getList(true, $this->country_id);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' city-column';
    }

    /**
     * Вернуть отображаемое значение ячейки
     *
     * @param ActiveRecord $model
     * @param string $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = $this->getDataCellValue($model, $key, $index);
        $strValue = $this->defaultRenderDataCellContent($model, $key, $index);
        if ($this->isAddLink) {
            return Html::a($strValue, '/dictionary/city/?CityFilter[id]=' . $value);
        } else {
            return $strValue;
        }
    }
}