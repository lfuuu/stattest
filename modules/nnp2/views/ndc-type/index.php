<?php
/**
 * Типы NDC
 *
 * @var app\classes\BaseView $this
 * @var NdcTypeFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp2\filters\NdcTypeFilter;
use app\modules\nnp2\models\NdcType;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => $this->title = 'Типы NDC', 'url' => '/nnp2/ndc-type/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, NdcType $model, $key) use ($baseView) {
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
        'format' => 'html',
        'value' => function (NdcType $ndcType) {

            $html = $ndcType->name;
            if ($ndcType->is_valid) {
                $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']);
            } else {
                $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']);
            }

            return $html;
        },
    ],
    [
        'attribute' => 'is_city_dependent',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'is_valid',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'parent_id',
        'format' => 'html',
        'value' => function (NdcType $ndcType) {
            $html = '';
            if ($parent = $ndcType->parent) {
                $html .= $parent->name;
            }

            return $html;
        },
    ],
    [
        'label' => 'Диапазон номеров',
        'format' => 'html',
        'value' => function (NdcType $ndcType) {
            return Html::a(
                Yii::t('common', 'Show'),
                Url::to(['/nnp2/number-range/', 'NumberRangeFilter[ndc_type_id]' => $ndcType->id])
            );
        }
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