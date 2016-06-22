<?php
/**
 * Список городов
 *
 * @var app\classes\BaseView $this
 * @var CityFilter $filterModel
 */

use app\classes\grid\column\universal\CityBillingMethodColumn;
use app\classes\grid\column\universal\ConnectionPointColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\models\City;
use app\models\filter\CityFilter;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Города') ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => $this->title, 'url' => '/dictionary/city/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'attribute' => 'id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'country_id',
        'class' => CountryColumn::className(),
    ],
    [
        'attribute' => 'connection_point_id',
        'class' => ConnectionPointColumn::className(),
    ],
    [
        'attribute' => 'voip_number_format',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'billing_method_id',
        'class' => CityBillingMethodColumn::className(),
    ],
    [
        'attribute' => 'in_use',
        'class' => YesNoColumn::className(),
    ],
    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, City $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, City $model, $key) use ($baseView) {
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
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/city/new/']),
    'columns' => $columns,
]);