<?php

namespace app\modules\sim\columns\PhoneHistory;

use app\classes\grid\column\DataColumn;
use app\classes\traits\GetListTrait;
use kartik\grid\GridView;

class StateColumn extends DataColumn
{
    protected static $list = [
        'Активный',
        'Завершен успешно',
        'Завершен неудачно',
        'Ошибка инициализации',
    ];

    public $filterType = GridView::FILTER_SELECT2;
    public $isWithEmpty = true;
    public $isWithNullAndNotNull = false;

    public static function isValid($value)
    {
        return in_array($value, self::$list);
    }

    /**
     * StatusColumn constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = GetListTrait::getEmptyList($this->isWithEmpty, $this->isWithNullAndNotNull) + array_combine(self::$list, self::$list);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' sim-phones-history-state-column';
    }

    protected function renderDataCellContent($model, $key, $index)
    {
        $html = sprintf("%s&nbsp;%s", $model->state ? : '-', $model->code);

        return $html;
    }
}