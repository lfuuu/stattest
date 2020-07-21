<?php
/**
 * Операторы
 *
 * @var app\classes\BaseView $this
 * @var OperatorFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\CountryColumn;
use app\modules\nnp2\filters\OperatorFilter;
use app\modules\nnp2\models\Operator;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => $this->title = 'Операторы', 'url' => '/nnp2/operator/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, Operator $model, $key) use ($baseView) {
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
        'attribute' => 'country_code',
        'class' => CountryColumn::class,
        'indexBy' => 'code',
    ],
    [
        'attribute' => 'group',
        'class' => \app\modules\nnp\column\OperatorTypeColumn::class,
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
        'format' => 'html',
        'value' => function (Operator $operator) {
            $html = $operator->name;
            if ($operator->is_valid) {
                $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']);
            } else {
                $html .= '&nbsp;' . Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']);
            }

            return $html;
        },
    ],
    [
        'attribute' => 'name_translit',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'is_valid',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'parent_id',
        'format' => 'html',
        'value' => function (Operator $operator) {
            $html = '';
            if ($parent = $operator->parent) {
                $html .= $parent->name;
            }

            return $html;
        },
    ],
    [
        'attribute' => 'cnt',
        'class' => IntegerRangeColumn::class,
        'format' => 'html',
        'value' => function (Operator $operator) {
            return
                $operator->cnt . ' (' .
                Html::a(
                    'диапазон',
                    Url::to([
                        '/nnp2/number-range/',
                        'NumberRangeFilter[country_code]' => $operator->country_code,
                        'NumberRangeFilter[operator_id]' => $operator->id,
                        'NumberRangeFilter[is_active]' => 1,
                    ])
                )
                . ')';
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