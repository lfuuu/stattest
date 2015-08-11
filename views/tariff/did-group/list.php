<?php
use kartik\grid\GridView;
use app\classes\grid\column\IdColumn;
use app\classes\grid\column\NameColumn;
use app\classes\grid\column\CityColumn;
use app\classes\grid\column\BeautyLevelColumn;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        ['class' => IdColumn::className()],
        ['class' => CityColumn::className()],
        ['class' => NameColumn::className()],
        ['class' => BeautyLevelColumn::className()],
    ],
    'toolbar' =>  [
        '{toggleData}',
        '{export}',
    ],
    'panel' => [
        'heading' => 'DID группы',
    ],
]);
