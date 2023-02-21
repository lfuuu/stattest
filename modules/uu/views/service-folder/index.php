<?php
/**
 * Список типов услуг
 *
 * @var \app\classes\BaseView $this
 * @var ServiceTypeFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\modules\uu\filter\ServiceTypeFilter;
use app\modules\uu\models\ServiceType;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        'Статусы для уровней цен в услуге',
        [
            'label' => $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(0)),
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
            'update' => function ($url, ServiceType $model, $key) use ($baseView) {

                if ($model->id == ServiceType::ID_VOIP) {
                    return '';
                }

                return $baseView->render('//layouts/_actionEdit', [
                        'url' => Url::to(['/uu/service-folder/edit', 'service_type_id' => $model->id]),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'id',
        'label' => 'Услуга',
        'isOnlyTopLevelStatuses' => true,
        'class' => \app\modules\uu\column\ServiceTypeColumn::class
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);