<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\Html;
use app\classes\traits\GetListTrait;
use Yii;


class IntegerColumn extends DataColumn
{
    public $filterType = '';
    public $filter = '';
    public $isNullAndNotNull = false;

    public function __construct($config = [])
    {
        parent::__construct($config);

        if ($this->isNullAndNotNull) {
            $this->filterOptions['title'] = Yii::t('common', 'Enter {nullValue} for empty value, {notNullValue} for not empty value', ['nullValue' => GetListTrait::$isNull, 'notNullValue' => GetListTrait::$isNotNull]);
        }

        $this->filter =
            Html::activeInput('number', $this->grid->filterModel, $this->attribute, [
                'class' => 'form-control input-sm',
            ]);
        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' integer-column';
    }
}