<?php

namespace app\classes\grid\column\billing;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\classes\Html;
use app\models\billing\DisconnectCause;
use kartik\grid\GridView;
use yii\db\ActiveRecord;

class DisconnectCauseColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;

    /**
     * @param [] $config
     */
    public function __construct($config = [])
    {
        $this->filter = DisconnectCause::getList(true);
        parent::__construct($config);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' disconnect-cause-column';
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

        if (is_array($this->filter) && isset($this->filter[$value])) {
            return Html::tag('abbr', (string)$this->filter[$value], ['title' => $this->filter[$value]->description]);
        }

        return $value;
    }

}