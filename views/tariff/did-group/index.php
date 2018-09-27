<?php
/**
 * Список DID групп
 *
 * @var app\classes\BaseView $this
 * @var DidGroupFilter $filterModel
 */

use app\classes\grid\column\universal\BeautyLevelColumn;
use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\models\DidGroup;
use app\models\filter\DidGroupFilter;
use app\modules\nnp\column\NdcTypeColumn;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'DID группы') ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Тарифы',
        ['label' => $this->title, 'url' => '/tariff/did-group/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, DidGroup $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, DidGroup $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'id',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'country_code',
        'class' => CountryColumn::class,
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::class,
        'country_id' => $filterModel->country_code,
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'beauty_level',
        'class' => BeautyLevelColumn::class,
    ],
    [
        'attribute' => 'ndc_type_id',
        'class' => NdcTypeColumn::class
    ],
    [
        'attribute' => 'is_service',
        'class' => YesNoColumn::class
    ],
];

for ($i = 1; $i <= 9; $i++) {
    $columns[] = [
        'attribute' => 'price' . $i,
        'class' => IntegerRangeColumn::class
    ];
}

$linkAdd = ['url' => ['/tariff/did-group/new']];
if ($filterModel->country_code) {
    $linkAdd['url'] += ['country_code' => $filterModel->country_code];
}
if ($filterModel->city_id) {
    $linkAdd['url'] += ['city_id' => $filterModel->city_id];
}

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', $linkAdd) . $this->render('//layouts/_buttonLink', [
            'url' => '/tariff/did-group/apply',
            'text' => 'Применить',
            'class' => 'btn-warning',
        ]),
    'columns' => $columns,
]);
