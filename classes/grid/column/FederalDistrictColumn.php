<?php

namespace app\classes\grid\column;

use app\classes\model\ActiveRecord;
use app\models\ClientContract;
use kartik\grid\GridView;


class FederalDistrictColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = ['' => '- Все -'] + ClientContract::$districts;
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' connection-point-column';
    }

    /**
     * Вернуть отображаемое значение ячейки
     *
     * @param ActiveRecord $model
     * @param $key
     * @param $index
     * @return mixed|string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = $this->getDataCellValue($model, $key, $index);

        if ($value === null || $value === '') {
            return '';
        }

        if (is_array($this->filterData) && isset($this->filterData[$value])) {
            return (string)$this->filterData[$value];
        }

        if (is_array($this->filter) && isset($this->filter[$value])) {
            return (string)$this->filter[$value];
        }

        if ($value = explode(',', $value)) {
            array_walk($value, function(&$item) {
                $item = ClientContract::$districts[$item];
            });
            return implode(', ', $value);
        }

        return $value;
    }
}