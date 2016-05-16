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
            Html::hiddenInput('id', $model['id']).
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

$registryRow = new \app\models\voip\Registry();

$columns = [
    [
        'attribute' => 'id',
        'format' => 'html',
        'value' => function($model) {
            return Html::a(' ' . $model['id'] . ' ', ['/voip/registry/edit', 'id' => $model['id']]);
        },
        'label' => $registryRow->getAttributeLabel('id')
    ],

    [
        'attribute' => 'country_id',
        'class' => CountryColumn::className(),
        'label' => $registryRow->getAttributeLabel('country_id')
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::className(),
        'label' => $registryRow->getAttributeLabel('city_id')
    ],
    [
        'attribute' => 'source',
        'class' => EnumColumn::className(),
        'enum' => VoipRegistrySourceEnum::className(),
        'label' => $registryRow->getAttributeLabel('source')
    ],
    [
        'attribute' => 'number_type_id',
        'class' => NumberTypeColumn::className(),
        'label' => $registryRow->getAttributeLabel('number_type_id')
    ],
    [
        'attribute' => 'number_from',
        'class' => StringColumn::className(),
        'label' => $registryRow->getAttributeLabel('number_from')
    ],
    [
        'attribute' => 'number_to',
        'class' => StringColumn::className(),
        'label' => $registryRow->getAttributeLabel('number_to')
    ],
    [
        'value' => function($model) {
            return ($model['count'] == 0 ? 'Пусто' : (($model['number_to'] - $model['number_from'] +1) == $model['count'] ? 'Заполнено' : 'Частично'));
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
        'label' => $registryRow->getAttributeLabel('account_id')
    ],
    [
        'attribute' => 'created_at',
        'label' => $registryRow->getAttributeLabel('created_at')
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
        return ['class' => ($model['count'] == 0 ? 'danger' : (($model['number_to'] - $model['number_from'] +1) == $model['count'] ? 'success' : 'warning'))];
    }
]);