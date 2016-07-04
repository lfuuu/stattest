<?php
/**
 * Пакеты
 *
 * @var app\classes\BaseView $this
 * @var PackageFilter $filterModel
 */

use app\classes\grid\column\billing\PricelistColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\TariffColumn;
use app\classes\grid\GridView;
use app\classes\uu\model\ServiceType;
use app\modules\nnp\column\DestinationColumn;
use app\modules\nnp\column\PackagePeriodColumn;
use app\modules\nnp\column\PackageTypeColumn;
use app\modules\nnp\filter\PackageFilter;
use app\modules\nnp\models\Package;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Пакеты') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title, 'url' => '/nnp/package/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],

    [
        'attribute' => 'tariff_id',
        'class' => TariffColumn::className(),
        'serviceTypeId' => ServiceType::ID_VOIP_PACKAGE,
        'filterOptions' => [
            'class' => 'nnp-package-tariff-column',
        ],
    ],

    [
        'attribute' => 'period_id',
        'class' => PackagePeriodColumn::className(),
    ],

    [
        'attribute' => 'package_type_id',
        'class' => PackageTypeColumn::className(),
    ],

    [
        'attribute' => 'destination_id',
        'class' => DestinationColumn::className(),
    ],

    [
        'attribute' => 'pricelist_id',
        'class' => PricelistColumn::className(),
    ],

    [
        'attribute' => 'price',
        'class' => IntegerRangeColumn::className(),
    ],

    [
        'attribute' => 'minute',
        'class' => IntegerRangeColumn::className(),
    ],

    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, Package $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, Package $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/nnp/package/new/']),
    'columns' => $columns,
]);