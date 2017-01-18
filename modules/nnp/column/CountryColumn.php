<?php

namespace app\modules\nnp\column;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\Html;
use app\modules\nnp\models\Country;
use kartik\grid\GridView;
use Yii;
use yii\db\ActiveRecord;


class CountryColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $isAddLink = true;
    public $filterType = GridView::FILTER_SELECT2;
    public $indexBy = 'code';
    public $isWithEmpty = true;

    /**
     * CountryColumn constructor.
     *
     * @param array $config
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = Country::getList($this->isWithEmpty, $isWithNullAndNotNull = false, $this->indexBy);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' country-column';
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
            return Html::a($strValue, Country::getUrlById($value));
        } else {
            return $strValue;
        }
    }
}