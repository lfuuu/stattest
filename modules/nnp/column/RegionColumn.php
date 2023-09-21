<?php

namespace app\modules\nnp\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\modules\nnp\column\traits\ModelIsValid;
use app\modules\nnp\models\NumberRange;
use app\modules\nnp\models\Region;
use kartik\grid\GridView;
use Yii;


class RegionColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }
    use ModelIsValid;

    public $filterType = GridView::FILTER_SELECT2;
    public $isAddLink = true;
    public $countryCodes = null;
    public $isWithEmpty = true;
    public $isWithNullAndNotNull = false;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = Region::getList($this->isWithEmpty, $this->isWithNullAndNotNull, $this->countryCodes);
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
        /** @var NumberRange $model */
        $value = $this->getDataCellValue($model, $key, $index);
        $strValue = $this->defaultRenderDataCellContent($model, $key, $index);

        if ($strValue && is_numeric($strValue) && $strValue == $value && isset($model->region)) {
            // посколько регионов очень много, в селект попадают не все. Чтобы не выводить некрасивых id несколько лишних раз поднимем связанные модели
            $strValue = $model->region->name;
        }

        $htmlArray = [];

        if ($model instanceof NumberRange) {
            if ($model->region_id) {
                $this->renderSymbolIsValid($model->region, $strValue);
            }

            if ($model->region_source) {
                $htmlArray[] = Html::ellipsis($model->region_source);
            }
        }

        if (is_null($value)) {
            $htmlArray[] = Yii::t('common', '(not set)');
        } elseif ($this->isAddLink) {
            $htmlArray[] = Html::ellipsis(Html::a($strValue, Region::getUrlById($value)));
        } else {
            $htmlArray[] = Html::ellipsis($strValue);
        }

        return implode('<br>', $htmlArray);
    }
}