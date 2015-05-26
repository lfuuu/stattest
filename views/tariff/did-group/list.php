<?php
use app\classes\grid\GridView;
use app\classes\grid\column\IdColumn;
use app\classes\grid\column\NameColumn;
use app\classes\grid\column\CityColumn;
use yii\helpers\Html;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        ['class' => IdColumn::className()],
        ['class' => CityColumn::className()],
        ['class' => NameColumn::className()],
    ],
    'toolbar' =>  [
        '{toggleData}',
        '{export}',
    ],
    'panel' => [
        'heading' => 'DID группы',
    ],
]);
