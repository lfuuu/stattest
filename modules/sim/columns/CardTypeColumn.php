<?php

namespace app\modules\sim\columns;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\CardType;
use kartik\grid\GridView;
use Yii;


class CardTypeColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait {
        ListTrait::renderDataCellContent as defaultRenderDataCellContent;
    }

    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmpty = true;
    public $isWithNullAndNotNull = false;

    /**
     * StatusColumn constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = CardType::getList($this->isWithEmpty, $this->isWithNullAndNotNull);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' sim-card-type-column';
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

        return $strValue;
    }
}