<?php

use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\OrganizationColumn;
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
            'attribute' => 'doer_organization_id',
            'class' => OrganizationColumn::class,
            'width' => '30%',
        ],
        [
            'attribute' => 'customer_country_code',
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
        [
            'attribute' => 'vat_apply_scheme',
            'label' => 'Тип клиента',
            'value' => function($model) {
                return \app\models\InvoiceSettings::$vatApplySchemes[$model->vat_apply_scheme];
            },
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
                                'doerOrganizationId' => $model->doer_organization_id,
                                'customerCountryCode' => $model->customer_country_code,
                                'settlementAccountTypeId' => $model->settlement_account_type_id,
                                'vatApplyScheme' => $model->vat_apply_scheme,
                            ]),
                        ]
                    );
                },
                'delete' => function($url, $model, $key) use ($baseView) {
                    return $baseView->render('//layouts/_actionDrop', [
                            'url' => Url::toRoute([
                                '/invoice-settings/delete/',
                                'doerOrganizationId' => $model->doer_organization_id,
                                'customerCountryCode' => $model->customer_country_code,
                                'settlementAccountTypeId' => $model->settlement_account_type_id,
                                'vatApplyScheme' => $model->vat_apply_scheme,
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