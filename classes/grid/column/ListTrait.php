<?php

namespace app\classes\grid\column;

use yii\db\ActiveRecord;

/**
 * Отображение в ячейке строкового значения из selectbox вместо ID
 *
 * @property string[] $filter
 * @method mixed getDataCellValue(ActiveRecord $model, string $key, integer $index)
 */
trait ListTrait
{
    /** @var array . Список значений в том случае, когда filter не список значений, а html */
    protected $filterData = [];

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
        
        if (is_array($this->filterData) && isset($this->filterData[$value])) {
            return (string)$this->filterData[$value];
        }

        if (is_array($this->filter) && isset($this->filter[$value])) {
            return (string)$this->filter[$value];
        }

        return $value;
    }
}