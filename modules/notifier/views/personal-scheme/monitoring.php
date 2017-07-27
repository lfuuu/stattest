<?php

use app\classes\grid\column\universal\ActionCheckboxColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\ClientAccount;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var \app\classes\BaseView $this */
/** @var array $dataProvider */

echo Html::formLabel('Мониторинг расхождения персональных схем');

echo Breadcrumbs::widget([
    'links' => [
        'Mailer',
        ['label' => 'Мониторинг расхождения персональных схем', 'url' => Url::toRoute(['/notifier/personal-scheme/monitoring'])],

    ],
]);

$baseView = $this;

$form = ActiveForm::begin();

echo $this->render('//layouts/_submitButton', [
    'text' => 'Синхронизировать',
    'glyphicon' => 'glyphicon-retweet',
    'params' => [
        'class' => 'btn btn-primary pull-right',
        'style' => 'clear: both; margin-bottom: 20px;',
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => ActionCheckboxColumn::className(),
            'name' => 'clientAccountIds',
            'headerOptions' => [
                'class' => 'kartik-sheet-style'
            ],
        ],
        [
            'label' => 'Лицевой счет',
            'format' => 'raw',
            'value' => function ($clientAccountId) {
                $clientAccount = ClientAccount::findOne($clientAccountId);
                return $clientAccountId . ': ' . Html::a(
                        $clientAccount->contragent->name,
                        ['client/view', 'id' => $clientAccountId],
                        ['target' => '_blank']
                    );
            },
        ],
    ],
    'isFilterButton' => false,
    'floatHeader' => false,
    'panel' => '',
    'export' => false,
]);

ActiveForm::end();