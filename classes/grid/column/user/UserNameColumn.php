<?php

namespace app\classes\grid\column\user;

use app\classes\grid\column\DataColumn;
use app\models\User;
use kartik\grid\GridView;
use yii\helpers\Html;

class UserNameColumn extends DataColumn
{
    public $attribute = 'user';
    public $value = 'name';
    public $filterType = GridView::FILTER_SELECT2;

    /**
     * UserNameColumn constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->filter = User::getList($isWithEmpty = true);
        parent::__construct($config);
    }

    /**
     * @param mixed $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index) .
            ' (' . $model->user . ')';
        return \Yii::$app->user->can('users.change') ? Html::a($value, ['edit', 'id' => $model->id]) : $value;
    }
}