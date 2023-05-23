<?php

use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\OrganizationColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\OrganizationSettlementAccount;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var $dataProvider ActiveDataProvider */

$baseView = $this;

echo Html::formLabel('Настройки платежных документов');
echo Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => 'Настройки платежных документов', 'url' => Url::toRoute(['/dictionary/invoice-settings/'])],
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'actions' => [
            'class' => 'kartik\grid\ActionColumn',
            'template' => Html::tag('div', '{update} {delete}', ['class' => 'text-center']),
            'buttons' => [
                'update' => function ($url, $model, $key) use ($baseView) {
                    return $baseView->render('//layouts/_actionEdit', [
                            'url' => Url::toRoute([
                                '/dictionary/invoice-settings/edit/',
                                'doerOrganizationId' => $model->doer_organization_id,
                                'customerCountryCode' => $model->customer_country_code,
                                'settlementAccountTypeId' => $model->settlement_account_type_id,
                                'vatApplyScheme' => $model->vat_apply_scheme,
                            ]),
                        ]
                    );
                },
                'delete' => function ($url, $model, $key) use ($baseView) {
                    return $baseView->render('//layouts/_actionDrop', [
                            'url' => Url::toRoute([
                                '/dictionary/invoice-settings/delete/',
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
        ],
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
                    isset(OrganizationSettlementAccount::$typesList[$data->settlement_account_type_id]) ?
                        OrganizationSettlementAccount::$typesList[$data->settlement_account_type_id] :
                        Yii::t('common', '(not set)');
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
            'value' => function ($model) {
                return \app\models\InvoiceSettings::$vatApplySchemes[$model->vat_apply_scheme];
            },
            'width' => '10%',
        ],
        [
            'attribute' => 'at_account_code',
            'label' => 'Номер счета бух. план Австрии',
            'width' => '10%',
        ],
    ],
    'extraButtons' =>
        $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/invoice-settings/add/']) .
        $this->render('//layouts/_link', [
            'text' => 'Пересчитaть ставку НДС',
            'url' => '/dictionary/invoice-settings/recalculate/',
            'glyphicon' => 'glyphicon-save',
            'params' => [
                'class' => 'btn btn-primary',
                'title' => 'Пересчитать эффективную ставку НДС по данным этой таблицы (пересчет может занять несколько минут)',
            ]
        ]),
    'isFilterButton' => false,
    'floatHeader' => false,
]);