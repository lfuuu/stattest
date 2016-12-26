<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\modules\nnp\models\Operator;
use kartik\grid\GridView;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Html;


class NnpOperatorColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $isAddLink = true;
    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmpty = true;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = Operator::getList($this->isWithEmpty);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' nnp-operator-column';
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
            return Html::a($strValue, Operator::getUrlById($value));
        } else {
            return $strValue;
        }
    }
}