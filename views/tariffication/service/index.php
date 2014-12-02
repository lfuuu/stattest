<?php
use yii\helpers\Html;
use app\classes\grid\GridView;
use app\classes\grid\column\IdColumn;
use app\classes\grid\column\NameColumn;
use app\classes\grid\column\ServiceTypeColumn;


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        ['class' => IdColumn::className()],
        ['class' => NameColumn::className()],
        ['class' => ServiceTypeColumn::className()],
    ],
    'toolbar' =>  [
        ['content'=> Html::a('<i class="glyphicon glyphicon-plus"></i> Добавить', ['add'], ['data-pjax'=>0, 'class' => 'btn btn-success btn-sm']),],
        '{toggleData}',
        '{export}',
    ],
    'panel' => [
        'heading' => 'Услуги',
    ],
]);
