<?php
/**
 * @var app\classes\BaseView $this
 * @var app\classes\BaseView $baseView
 * @var ActiveDataProvider $dataProvider
 * @var PricelistReport $filterModel
 */

use app\classes\grid\column\universal\RegionColumn;
use app\classes\Html;
use app\models\billing\Pricelist;
use app\models\billing\PricelistReport;
use app\classes\grid\GridView;
use kartik\grid\ActionColumn;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

echo Html::formLabel($title = 'Анализ прайс-листов');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Межоператорка (отчеты)'],
        ['label' => $title, 'url' => '/voipreport/pricelist-report'],
    ],
]);

$baseView = $this;
$pricelists = Pricelist::find()->indexBy('id')->all();

$columns = [
    [
        'class' => ActionColumn::className(),
        'template' => '{show}{calculate}{update}{delete}',
        'buttons' => [
            'show' => function ($url, PricelistReport $model) use ($baseView) {
                return $baseView->render('//layouts/_actionView', [
                    'url' => Url::toRoute([
                        '/index.php',
                        'module' => 'voipreports',
                        'action' => 'pricelist_report_show',
                        'id' => $model->id,
                    ]),
                ]);
            },
            'calculate' => function ($url, PricelistReport $model) {
                return Html::a(
                    Html::tag('i', '', ['class' => 'glyphicon glyphicon-tasks']),
                    '/voipreport/pricelist-report/calculate?reportId=' . $model->id,
                    [
                        'title' => 'Калькулятор',
                        'class' => 'btn btn-link btn-xs'
                    ]
                );
            },
            'update' => function ($url, PricelistReport $model) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                    'url' => Url::toRoute([
                        '/index.php',
                        'module' => 'voipreports',
                        'action' => 'pricelist_report_edit',
                        'id' => $model->id,
                    ])
                ]);
            },
            'delete' => function ($url, PricelistReport $model) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                    'url' => Url::toRoute([
                        '/index.php',
                        'module' => 'voipreports',
                        'action' => 'pricelist_report_delete',
                        'id' => $model->id,
                    ]),
                ]);
            },
        ],
        'width' => '10%',
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'instance_id',
        'class' => RegionColumn::className(),
        'isWithEmptyText' => 'Все регионы',
        'width' => '20%',
    ],
    [
        'attribute' => 'name',
        'width' => '20%',
    ],
    [
        'attribute' => 'pricelists',
        'format' => 'raw',
        'value' => function (PricelistReport $row) use ($pricelists) {
            $row->prepareData();

            return implode(Html::tag('br'), array_map(function ($row) use ($pricelists) {
                return
                    Html::tag(
                        'div',
                        (array_key_exists($row['pricelist']->id, $pricelists) ? $pricelists[$row['pricelist']->id]->name : ''),
                        ['class' => 'col-sm-8']
                    )
                    . Html::tag('div', $row['date'] ?: 'Текущая дата', ['class' => 'col-sm-4']);
                },
                $row->getData()
            ));
        },
        'width' => '50%',
    ],
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'toggleData' => false,
    'extraButtons' => $this->render(
        '//layouts/_buttonCreate', [
            'url' => '/index.php?module=voipreports&action=pricelist_report_edit&report_type_id=' . $filterModel->report_type_id
    ]),
]);
