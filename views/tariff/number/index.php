<?php
use app\classes\grid\column\IdColumn;
use app\classes\grid\column\NameColumn;
use app\classes\grid\column\CountryColumn;
use app\classes\grid\column\CityColumn;
use kartik\grid\DataColumn;
use yii\helpers\Html;

echo \kartik\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => [
        ['class' => IdColumn::className()],
        ['class' => CityColumn::className()],
        ['class' => NameColumn::className()],
        ['class' => DataColumn::className(), 'attribute' => 'activation_fee', 'label' => 'Подключение'],
        ['class' => DataColumn::className(), 'attribute' => 'periodical_fee', 'label' => 'Абонетская плата'],
        ['class' => DataColumn::className(), 'attribute' => 'status', 'label' => 'Статус'],
        ['class' => CountryColumn::className()],
        ['class' => DataColumn::className(), 'attribute' => 'currency_id', 'label' => 'Валюта'],
    ],
    'toolbar' =>  [
        ['content'=> Html::a('<i class="glyphicon glyphicon-plus"></i> Добавить', ['add'], ['data-pjax'=>0, 'class' => 'btn btn-success btn-sm']),],
        '{toggleData}',
        '{export}',
    ],
    'panel' => [
        'heading' => 'Тарифы -> Телефония Номера',
    ],
]);
