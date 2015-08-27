<?php

namespace app\classes\grid\column\user;

use Yii;
use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\models\UserGroups;

class UserGroupColumn extends DataColumn
{
    public $attribute = 'usergroup';
    public $value = 'group.comment';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = UserGroups::dao()->getList(true);
        parent::__construct($config);
    }
}