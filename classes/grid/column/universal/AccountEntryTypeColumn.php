<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\Resource;
use kartik\grid\GridView;
use Yii;


class AccountEntryTypeColumn extends DataColumn
{
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $tableName = AccountEntry::tableName();

        parent::__construct($config);
        $this->filter = $list = [
            '' => '',
            AccountEntry::TYPE_ID_SETUP => Yii::t('uu', AccountEntry::$names[AccountEntry::TYPE_ID_SETUP]),
            AccountEntry::TYPE_ID_PERIOD => Yii::t('uu', AccountEntry::$names[AccountEntry::TYPE_ID_PERIOD]),
            AccountEntry::TYPE_ID_MIN => Yii::t('uu', AccountEntry::$names[AccountEntry::TYPE_ID_MIN]),
            'Ресурсы' => Resource::getList(null, false),
        ];

        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' account-entry-type-column';
    }
}