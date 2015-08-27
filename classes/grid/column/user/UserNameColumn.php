<?php

namespace app\classes\grid\column\user;

use Yii;
use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;
use app\models\User;
use yii\helpers\Html;

class UserNameColumn extends DataColumn
{
    public $attribute = 'user';
    public $value = 'name';
    public $filterType = GridView::FILTER_SELECT2;

    public function __construct($config = [])
    {
        $this->filter = User::dao()->getList(true);
        parent::__construct($config);
    }

    protected function renderDataCellContent($model, $key, $index)
    {
        return Html::a(parent::getDataCellValue($model, $key, $index), ['edit', 'id' => $model->id]);
    }
}