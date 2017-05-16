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
        'class' => ActionColumn::className(),
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
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'country_code',
        'class' => CountryColumn::className(),
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::className(),
        'country_id' => $filterModel->country_code,
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'beauty_level',
        'class' => BeautyLevelColumn::className(),
    ],
    [
        'attribute' => 'ndc_type_id',
        'class' => NdcTypeColumn::className()
    ],
];

for ($i = 1; $i <= 9; $i++) {
    $columns[] = [
        'attribute' => 'price' . $i,
        'class' => IntegerRangeColumn::className()
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
