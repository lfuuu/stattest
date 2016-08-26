<?php

use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\grid\column\universal\CountryColumn;
use app\models\OrganizationSettlementAccount;
use yii\widgets\Breadcrumbs;

/** @var $dataProvider ActiveDataProvider */

$baseView = $this;

echo Html::formLabel('Настройки платежных документов');

echo Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => 'Настройки платежных документов', 'url' => '/invoice-settings'],
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'customer_country_code',
            'class' => CountryColumn::class,
            'width' => '30%',
        ],
        [
            'attribute' => 'doer_country_code',
            'class' => CountryColumn::class,
            'width' => '30%',
        ],
        [
            'attribute' => 'settlement_account_type_id',
            'label' => 'Тип платежных реквизитов',
            'value' => function ($data) {
                return
                    isset(OrganizationSettlementAccount::$typesList[$data->settlement_account_type_id])
                        ? OrganizationSettlementAccount::$typesList[$data->settlement_account_type_id]
                        : Yii::t('common', '(not set)');
            },
            'width' => '20%',
        ],
        [
            'attribute' => 'vat_rate',
            'label' => 'НДС',
            'width' => '10%',
        ],
        'actions' => [
            'class' => 'kartik\grid\ActionColumn',
            'template' => Html::tag('div', '{update} {delete}', ['class' => 'text-center']),
            'buttons' =>  [
                'update' => function ($url, $model, $key) use ($baseView) {
                    return $baseView->render('//layouts/_actionEdit', [
                            'url' => Url::toRoute([
                                '/invoice-settings/edit/',
                                'customer_country_code' => $model->customer_country_code,
                                'doer_country_code' => $model->doer_country_code,
                                'settlement_account_type_id' => $model->settlement_account_type_id,
                            ]),
                        ]
                    );
                },
                'delete' => function($url, $model, $key) use ($baseView) {
                    return $baseView->render('//layouts/_actionDrop', [
                            'url' => Url::toRoute([
                                '/invoice-settings/delete/',
                                'customer_country_code' => $model->customer_country_code,
                                'doer_country_code' => $model->doer_country_code,
                                'settlement_account_type_id' => $model->settlement_account_type_id,
                            ]),
                        ]
                    );
                },
            ],
            'hAlign' => 'left',
        ]
    ],
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/invoice-settings/add/']),
    'isFilterButton' => false,
    'floatHeader' => false,
]);