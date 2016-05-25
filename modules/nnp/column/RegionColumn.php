<?php

namespace app\modules\nnp\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\Html;
use app\modules\nnp\models\Region;
use kartik\grid\GridView;
use Yii;
use yii\db\ActiveRecord;


class RegionColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $filterType = GridView::FILTER_SELECT2;
    public $isAddLink = true;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = Region::getList(true, true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' region-column';
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
        if ($this->isAddLink && $value) {
            return Html::a($strValue, Region::getUrlById($value));
        } else {
            return $strValue;
        }
    }
}