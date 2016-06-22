<?php

namespace app\classes\grid\column\universal;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\select2\Select2;
use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\CityBillingMethod;

class CityBillingMethodColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $isAddLink = true;
    public $filterType = GridView::FILTER_SELECT2;
    public $billing_method_id = null;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter = $this->filterData = CityBillingMethod::getList($isWithEmpty = true);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' city-billing-method-column';
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
            return Yii::t('common', '(not set)');
        }

        if ($this->isAddLink) {
            return Html::a($strValue, CityBillingMethod::getUrlById($value));
        }

        return $strValue;
    }
}