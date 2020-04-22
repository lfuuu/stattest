<?php

namespace app\modules\uu\column;

use app\classes\grid\column\DataColumn;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\ResourceModel;
use kartik\grid\GridView;
use Yii;


class AccountEntryTypeColumn extends DataColumn
{
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = $list = [
            '' => '',
            AccountEntry::TYPE_ID_SETUP => Yii::t('uu', AccountEntry::$names[AccountEntry::TYPE_ID_SETUP]),
            AccountEntry::TYPE_ID_PERIOD => Yii::t('uu', AccountEntry::$names[AccountEntry::TYPE_ID_PERIOD]),
            AccountEntry::TYPE_ID_MIN => Yii::t('uu', AccountEntry::$names[AccountEntry::TYPE_ID_MIN]),
            'Ресурсы' => ResourceModel::getList(null, false),
        ];

        !isset($this->filterOptions['class']) && ($this->filterOptions['class'] = '');
        $this->filterOptions['class'] .= ' account-entry-type-column';
    }
}