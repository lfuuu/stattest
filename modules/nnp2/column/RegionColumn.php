<?php

namespace app\modules\nnp2\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\modules\nnp2\models\City;
use app\modules\nnp2\models\GeoPlace;
use app\modules\nnp2\models\NumberRange;
use app\modules\nnp2\models\RangeShort;
use app\modules\nnp2\models\Region;
use kartik\grid\GridView;
use Yii;

class RegionColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $filterType = GridView::FILTER_SELECT2;
    public $isAddLink = true;
    public $countryCodes = null;
    public $isWithEmpty = true;
    public $isWithNullAndNotNull = false;
    public $showVerified = false;
    public $showParent = false;

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

        $value = $this->getDataCellValue($model, $key, $index);
        $sourceValue = $this->defaultRenderDataCellContent($model, $key, $index);

        $sourceModel = $model;
        if (
            ($model instanceof NumberRange) ||
            ($model instanceof RangeShort) ||
            ($model instanceof RangeShort) ||
            ($model instanceof GeoPlace) ||
            ($model instanceof City)
        ) {
            $sourceModel = $model->region;
        }

        if ($sourceValue && is_numeric($sourceValue) && $sourceValue == $value && $sourceModel) {
            // посколько городов очень много, в селект попадают не все.
            // Чтобы не выводить некрасивых id несколько лишних раз поднимем связанные модели
            $sourceValue = $sourceModel->name;
        }

        $verifiedValue = '';
        if ($showVerified = $this->showVerified) {
            $modelToCheck = $model;

            $alreadyValid = false;
            if ($model instanceof GeoPlace) {
                $modelToCheck = $model->region;

                $alreadyValid = $model->is_valid;
                $showVerified = !$alreadyValid;
            } elseif ($model instanceof NumberRange) {
                $modelToCheck = $model->geoPlace->region;

                $alreadyValid = $model->geoPlace->is_valid;
            }

            if ($showVerified) {
                $isValid = $alreadyValid || (!$modelToCheck || $modelToCheck->is_valid);
                if ($isValid) {
                    $verifiedValue .= Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']) . '&nbsp;';
                } else {
                    $verifiedValue .= Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . '&nbsp;';
                }
            }
        }

        $mainValue = '';
        if ($this->showParent) {
            $mainModel = $sourceModel;
            if ($model instanceof NumberRange) {
                $mainModel = $model->geoPlace->getMainParent()->region;
            }

            $mainModel = $mainModel->parent ? : $mainModel;
            if ($mainModel->id != $sourceModel->id) {
                $mainValue .= $verifiedValue . $mainModel->name;
                $verifiedValue = '';

                if ($this->isAddLink) {
                    $mainValue = Html::ellipsis(Html::a($mainValue, Region::getUrlById($mainModel->id)));
                } else {
                    $mainValue = Html::ellipsis($mainValue);
                }
            }
        }

        $sourceValue =  $verifiedValue . $sourceValue;
        if (is_null($value)) {
            $sourceValue = $verifiedValue. Yii::t('common', '(not set)');
        } elseif ($this->isAddLink) {
            $sourceValue = Html::ellipsis(Html::a($sourceValue, Region::getUrlById($value)));
        } else {
            $sourceValue = Html::ellipsis($sourceValue);
        }

        $firstLine = $sourceValue;
        $secondLine = null;

        if ($mainValue) {
            $firstLine = $mainValue;
            $secondLine = $sourceValue;
        }

        $html =
            $firstLine .
            ($secondLine ? '<br />' .Html::tag('small', $secondLine) : '')
        ;

        return Html::tag(
            'span',
            $html,
            ['class' => 'text-nowrap']
        );
    }
}