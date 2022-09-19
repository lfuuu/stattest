<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\model\ActiveRecord;
use app\models\voip\Source;
use kartik\grid\GridView;


class SourceColumn extends DataColumn
{
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }


    // Отображение в ячейке строкового значения из selectbox вместо ID
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = ['' => '----'] + Source::getList();
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' source-column';
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
        if (is_null($value)) {
            return \Yii::t('common', '(not set)');
        } else {
            return $strValue;
        }
    }
}