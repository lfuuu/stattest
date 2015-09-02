<?php

namespace app\classes\grid\column\user;

use Yii;
use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\models\UserDeparts;

class UserDepartColumn extends DataColumn
{
    public $attribute = 'depart_id';
    public $value = 'department.name';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = UserDeparts::dao()->getList(true);
        parent::__construct($config);
    }
}