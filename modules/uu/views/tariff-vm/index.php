<?php
/**
 * Список VPS-тарифов
 *
 * @var \app\classes\BaseView $this
 * @var TariffVmFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\modules\uu\filter\TariffVmFilter;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffVm;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

$this->title = Yii::t('tariff', 'Tariff VPS');
?>

<?= Breadcrumbs::widget([
    'links' => [
        [
            'label' => $this->title .
                $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VPS)),
            'encode' => false,
        ],
    ],
]) ?>

<?php
// базовые столбцы
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, TariffVm $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
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
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],
    [
        'class' => ActionColumn::class,
        'template' => '{delete}',
        'buttons' => [
            'delete' => function ($url, TariffVm $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/uu/tariff-vm/new/']),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);