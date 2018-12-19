<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\HistoryChanges;
use kartik\grid\GridView;


class HistoryActionColumn extends DataColumn
{
    // Отображение в ячейке строкового значения из selectbox вместо ID
    use ListTrait;

    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = [
            '' => '----',

            HistoryChanges::ACTION_INSERT => 'Добавить',
            HistoryChanges::ACTION_UPDATE => 'Редактировать',
            HistoryChanges::ACTION_DELETE => 'Удалить',

        ];
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' history-action-column';
    }
}