<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use kartik\grid\GridView;
use yii\db\ActiveRecord;
use Yii;

/**
 * Class DropdownColumn
 *
 * Универсальный класс для подключения своих колонок с dropdown-фильтром.
 */
class DropdownColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $filterType = GridView::FILTER_SELECT2;

    public $filter = ['' => '----'];

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
        if (is_null($value)) {
            return Yii::t('common', '(not set)');
        } else {
            return $strValue;
        }
    }
}