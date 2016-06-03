<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\City;
use kartik\grid\GridView;
use kartik\select2\Select2;
use Yii;
use yii\db\ActiveRecord;
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
    public $isWithNullAndNotNull = false;
    public $reverseCheckboxAttribute = ''; // имя bool/int - поля, из которого брать галочку "кроме". Не забудьте добавить в МодельFilter соответствующее инвертирующее условие

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter = $this->filterData = City::dao()->getList($isWithEmpty = true, $this->country_id, $this->isWithNullAndNotNull);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' city-column';

        if ($this->reverseCheckboxAttribute) {
            $this->filterType = '';
            $this->filter =
                Html::activeCheckbox($this->grid->filterModel, $this->reverseCheckboxAttribute) .

                ' ' .

                Select2::widget([
                    'model' => $this->grid->filterModel,
                    'attribute' => $this->attribute,
                    'data' => $this->filter,
                ]);
        }
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
        } elseif ($this->isAddLink) {
            return Html::a($strValue, City::getUrlById($value));
        } else {
            return $strValue;
        }
    }
}