<?php
use kartik\grid\GridView;
use app\classes\Html;
use app\classes\grid\column\IdColumn;
use app\classes\grid\column\NameColumn;
use app\classes\grid\column\CityColumn;
use app\classes\grid\column\BeautyLevelColumn;

echo Html::formLabel('DID группы');

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
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
