<?php
/**
 * Реестр номеров
 *
 * @var app\classes\BaseView $this
 * @var \app\models\filter\voip\RegistryFilter $filterModel
 */

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\grid\column\EnumColumn;
use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\voip\Registry;
use app\modules\nnp\column\NdcTypeColumn;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Реестр номеров') ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Телефония',
        ['label' => $this->title, 'url' => '/voip/registry'],
    ],
]) ?>


<?php

$recordBtns = [
    'delete' => function ($url, $model, $key) {
        return
            Html::beginForm('/voip/registry/delete') .
            Html::hiddenInput('id', $model->id) .
            Html::submitButton(
                '<span class="glyphicon glyphicon-trash"></span> Удаление',
                [
                    'title' => Yii::t('kvgrid', 'Delete'),
                    'onClick' => 'return confirm("Вы уверены, что хотите удалить запись?")',
                    'class' => 'btn btn-link btn-xs'
                ]
            ) .
            Html::endForm();
    },
];

$registryRow = new Registry();

$columns = [
    'actions' => [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '<div style="text-align: center;">{delete}</div>',
        'buttons' => $recordBtns,
        'hAlign' => 'center',
        'width' => '100px',
    ],
    [
        'attribute' => 'id',
        'format' => 'html',
        'value' => function ($model) {
            return Html::a(' ' . $model->id . ' ', ['/voip/registry/edit', 'id' => $model->id]);
        },
    ],

    [
        'attribute' => 'country_id',
        'class' => CountryColumn::class,
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::class,
    ],
    [
        'attribute' => 'source',
        'class' => EnumColumn::class,
        'enum' => VoipRegistrySourceEnum::class,
    ],
    [
        'attribute' => 'ndc_type_id',
        'class' => NdcTypeColumn::class
    ],
    [
        'attribute' => 'solution_number',
        'value' => function ($model) {
            return Html::a($model->solution_number, ['voip/number', 'NumberFilter[solution_number]' => $model->solution_number]);
        },
        'filter' => Select2::widget([
            'model' => $filterModel,
            'data' => Registry::find()->select('solution_number')->indexBy('solution_number')->column(),
            'attribute' => 'solution_number',
            'value' => $filterModel->solution_number
        ]),
        'format' => 'raw'
    ],
    [
        'attribute' => 'solution_date',
        'filter' => DatePicker::widget([
            'model' => $filterModel,
            'attribute' => 'solution_date',
            'value' => $filterModel->solution_date
        ])
    ],
    [
        'attribute' => 'numbers_count',
        'class' => IntegerRangeColumn::class
    ],
    [
        'attribute' => 'comment'
    ],
    [
        'attribute' => 'ndc',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'number_from',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'number_to',
        'class' => StringColumn::class,
    ],
    [
        'value' => function ($model) {
            return Registry::$names[$model->status];
        },
        'label' => 'Заполнение'
    ],
    [
        'attribute' => 'account_id',
        'class' => IntegerColumn::class,
        'format' => 'html',
        'value' => function ($model) {
            return Html::a('ЛС ' . $model['account_id'], ['/client/view', 'id' => $model['account_id']]);
        },
    ],
    [
        'attribute' => 'created_at',
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/voip/registry/add']),
    'rowOptions' => function ($model) {
        $status = $model->status;
        return ['class' => ($status == Registry::STATUS_EMPTY ? 'danger' : ($status == Registry::STATUS_FULL ? 'success' : 'warning'))];
    }
]);