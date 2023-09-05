<?php

namespace app\modules\nnp\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\modules\nnp\column\traits\ModelIsValid;
use app\modules\nnp\models\City;
use app\modules\nnp\models\NumberRange;
use kartik\grid\GridView;
use Yii;

class CityColumn extends DataColumn
{

    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }
    use ModelIsValid;

    public $isAddLink = true;
    public $filterType = GridView::FILTER_SELECT2;
    public $countryCodes = null;
    public $regionIds = null;
    public $isWithNullAndNotNull = true;
    public $isWithEmpty = true;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->filter = $this->filterData = City::getList(
            $this->isWithEmpty,
            $this->isWithNullAndNotNull,
            $this->countryCodes,
            $this->regionIds
        );
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
        /** @var NumberRange $model */
        $value = $this->getDataCellValue($model, $key, $index);
        $strValue = $this->defaultRenderDataCellContent($model, $key, $index);

        if ($strValue && is_numeric($strValue) && $strValue == $value && isset($model->city)) {
            // посколько городов очень много, в селект попадают не все. Чтобы не выводить некрасивых id несколько лишних раз поднимем связанные модели
            $strValue = $model->city->name;
        }

        $this->renderSymbolIsValid($model->city, $strValue);

        $htmlArray = [];

        if ($model instanceof NumberRange && $model->city_source) {
            $htmlArray[] = Html::ellipsis($model->city_source);
        }

        if (is_null($value)) {
            $htmlArray[] = Yii::t('common', '(not set)');
        } elseif ($this->isAddLink) {
            $htmlArray[] = Html::ellipsis(Html::a($strValue, City::getUrlById($value)));
        } else {
            $htmlArray[] = Html::ellipsis($strValue);
        }

        return implode('<br>', $htmlArray);
    }
}
