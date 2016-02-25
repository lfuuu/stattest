<?php

namespace app\classes\grid\column;

use yii\db\ActiveRecord;

/**
 * Отображение в ячейке строкового значения из selectbox вместо ID
 *
 * @property string[] filter
 * @method mixed getDataCellValue(ActiveRecord $model, string $key, integer $index)
 */
trait ListTrait
{
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
        return isset($this->filter[$value]) ? (string) $this->filter[$value] : $value;
    }
}