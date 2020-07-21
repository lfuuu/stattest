<?php

namespace app\modules\nnp2\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\modules\nnp2\models\NdcType;
use app\modules\nnp2\models\NumberRange;
use app\modules\nnp2\models\RangeShort;
use kartik\grid\GridView;
use Yii;


class NdcTypeColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $filterType = GridView::FILTER_SELECT2;
    public $isAddLink = true;
    public $isWithEmpty = true;
    public $label = 'Тип номера';
    public $isWithNullAndNotNull = true;
    public $showVerified = false;
    public $showParent = false;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = NdcType::getList($this->isWithEmpty, $this->isWithNullAndNotNull);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' ndc-type-column';
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
            ($model instanceof RangeShort)
        ) {
            $sourceModel = $model->ndcType;
        }

        if ($sourceValue && is_numeric($sourceValue) && $sourceValue == $value && $sourceModel) {
            // посколько городов очень много, в селект попадают не все.
            // Чтобы не выводить некрасивых id несколько лишних раз поднимем связанные модели
            $sourceValue = $sourceModel->name;
        }

        $verifiedValue = '';
        if ($this->showVerified) {
            if ($sourceModel->is_valid) {
                $verifiedValue .= Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']) . '&nbsp;';
            } else {
                $verifiedValue .= Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . '&nbsp;';
            }
        }

        $mainValue = '';
        if ($this->showParent) {
            $mainModel = $sourceModel;

            $mainModel = $mainModel->parent ? : $mainModel;
            if ($mainModel->id != $sourceModel->id) {
                $mainValue .= $verifiedValue . $mainModel->name;
                $verifiedValue = '';

                if ($this->isAddLink) {
                    $mainValue = Html::ellipsis(Html::a($mainValue, NdcType::getUrlById($mainModel->id)));
                } else {
                    $mainValue = Html::ellipsis($mainValue);
                }
            }
        }

        $sourceValue =  $verifiedValue . $sourceValue;
        if (is_null($value)) {
            $sourceValue = Yii::t('common', '(not set)');
        } elseif ($this->isAddLink) {
            $sourceValue = Html::ellipsis(Html::a($sourceValue, NdcType::getUrlById($value)));
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