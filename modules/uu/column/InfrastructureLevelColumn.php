<?php

namespace app\modules\uu\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\models\Datacenter;
use app\modules\uu\models\AccountTariff;
use kartik\grid\GridView;
use Yii;


class InfrastructureLevelColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $isAddLink = true;
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = AccountTariff::getInfrastructureLevelList($isWithEmpty = true, $isWithNullAndNotNull = false);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' datacenter-column';
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
            return Html::a($strValue, Datacenter::getUrlById($value));
        }

        return $strValue;
    }

}