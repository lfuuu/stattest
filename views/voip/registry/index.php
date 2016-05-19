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
use app\classes\grid\column\universal\NumberTypeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\voip\Registry;
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
    'delete' => function($url, $model, $key) {
        return
        Html::beginForm('/voip/registry/delete').
            Html::hiddenInput('id', $model->id).
            Html::submitButton(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'onClick' => 'return confirm("Вы уверены, что хотите удалить запись?")',
                'class' => 'btn btn-link btn-xs'
            ]
        ).
        Html::endForm();
    },
];

$registryRow = new Registry();

$columns = [
    [
        'attribute' => 'id',
        'format' => 'html',
        'value' => function($model) {
            return Html::a(' ' . $model->id . ' ', ['/voip/registry/edit', 'id' => $model->id]);
        },
    ],

    [
        'attribute' => 'country_id',
        'class' => CountryColumn::className(),
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::className(),
    ],
    [
        'attribute' => 'source',
        'class' => EnumColumn::className(),
        'enum' => VoipRegistrySourceEnum::className(),
    ],
    [
        'attribute' => 'number_type_id',
        'class' => NumberTypeColumn::className(),
    ],
    [
        'attribute' => 'number_from',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'number_to',
        'class' => StringColumn::className(),
    ],
    [
        'value' => function($model) {
            return Registry::$names[$model->status];
        },
        'label' => 'Заполнение'
    ],
    [
        'attribute' => 'account_id',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function($model) {
            return Html::a('ЛС ' . $model['account_id'], ['/client/view', 'id' => $model['account_id']]);
        },
    ],
    [
        'attribute' => 'created_at',
    ],
    'actions' => [
        'class' => 'kartik\grid\ActionColumn',
        'template' => '<div style="text-align: center;">{delete}</div>',
        'buttons' => $recordBtns,
        'hAlign' => 'center',
        'width' => '100px',
    ]
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/voip/registry/add']),
    'rowOptions' => function($model){
        $status = $model->status;
        return ['class' => ($status == Registry::STATUS_EMPTY ? 'danger' : ($status == Registry::STATUS_FULL ? 'success' : 'warning'))];
    }
]);