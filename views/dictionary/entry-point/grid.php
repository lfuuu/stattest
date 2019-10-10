<?php

use app\classes\grid\column\universal\AccountVersionColumn;
use app\classes\grid\column\universal\SiteColumn;
use app\classes\grid\column\universal\YesNoColumn;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\OrganizationColumn;
use app\classes\grid\column\universal\CurrencyColumn;
use yii\widgets\Breadcrumbs;

/** @var $dataProvider ActiveDataProvider */

$baseView = $this;

echo Html::formLabel('Точки входа');
echo Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => 'Точки входа', 'url' => Url::toRoute(['/dictionary/entry-point'])],
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
                                '/dictionary/entry-point/edit',
                                'id' => $model->id,
                            ]),
                        ]
                    );
                },
                'delete' => function ($url, $model, $key) use ($baseView) {
                    return $baseView->render('//layouts/_actionDrop', [
                            'url' => Url::toRoute([
                                '/dictionary/entry-point/delete',
                                'id' => $model->id,
                            ]),
                        ]
                    );
                },
            ],
            'hAlign' => 'left',
        ],
        [
            'attribute' => 'code',
        ],
        [
            'attribute' => 'name'
        ],
        [
            'attribute' => 'organization_id',
            'class' => OrganizationColumn::class,
        ],
        [
            'attribute' => 'country_id',
            'class' => CountryColumn::class,
        ],
        [
            'attribute' => 'currency_id',
            'class' => CurrencyColumn::class
        ],

        [
            'attribute' => 'name_prefix',
        ],
        [
            'attribute' => 'site_id',
            'class' => SiteColumn::class
        ],
        [
            'attribute' => 'account_version',
            'class' => AccountVersionColumn::class,
        ],
        [
            'label' => 'Лимиты (кредит / суточный / суточныйМН)',
            'value' => function (\app\models\EntryPoint $model) {
                return $model->credit . ' / ' . $model->voip_credit_limit_day . ' / ' . $model->voip_limit_mn_day;
            }
        ],
        [
            'attribute' => 'is_default',
            'class' => YesNoColumn::class,
        ],
    ],
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/entry-point/add']),
    'isFilterButton' => false,
    'floatHeader' => false,
]);