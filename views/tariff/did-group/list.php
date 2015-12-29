<?php
use app\classes\grid\column\BeautyLevelColumn;
use app\classes\grid\column\CityColumn;
use app\classes\grid\column\IdColumn;
use app\classes\grid\column\NameColumn;
use app\classes\Html;
use kartik\grid\GridView;

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
    'toolbar' => [
        '{toggleData}',
        '{export}',
    ],
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
