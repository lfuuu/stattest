<?php

namespace app\classes\grid\column\universal;

use app\classes\grid\column\DataColumn;
use app\classes\grid\column\ListTrait;
use app\models\User;
use kartik\grid\GridView;
use Yii;
use yii\db\ActiveRecord;

class UserColumn extends DropdownColumn
{
    public $indexBy = 'user';

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->filter = User::getList(true, false, $this->indexBy);
    }
}