<?php
use kartik\grid\GridView;
use app\classes\grid\column\DataColumn;


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        [
            'class' => DataColumn::className(),
            'attribute' => 'id',
            'label' => '#',
            'width' => '100px',
        ],
        [
            'class' => DataColumn::className(),
            'attribute' => 'name',
        ],
    ],
    'panel' => [
        'heading' => 'Типы услуг',
    ],
]);
