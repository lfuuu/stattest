<?php
use app\classes\grid\column\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\IdColumn;
use app\classes\grid\column\NameColumn;
use app\classes\Html;
use kartik\grid\DataColumn;
use kartik\grid\GridView;

echo Html::formLabel('Телефония Номера');

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        ['class' => IdColumn::className()],
        [
            'attribute' => 'country_id',
            'class' => CountryColumn::className(),
            'label' => 'Страна',
        ],
        ['class' => CityColumn::className()],
        ['class' => NameColumn::className()],
        ['class' => DataColumn::className(), 'attribute' => 'activation_fee', 'label' => 'Подключение'],
        ['class' => DataColumn::className(), 'attribute' => 'status', 'label' => 'Статус'],
        ['class' => DataColumn::className(), 'attribute' => 'currency_id', 'label' => 'Валюта'],
    ],
    'toolbar' => [
        ['content' => Html::a('<i class="glyphicon glyphicon-plus"></i> Добавить', ['add'], ['data-pjax' => 0, 'class' => 'btn btn-success btn-sm']),],
        '{toggleData}',
        '{export}',
    ],
    'panel' => [
        'type' => GridView::TYPE_DEFAULT,
    ],
]);
