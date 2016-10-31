<?php
/**
 * Список Телефония. Номера
 *
 * @var app\classes\BaseView $this
 * @var TariffNumberFilter $filterModel
 */

use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\DidGroup;
use app\models\filter\TariffNumberFilter;
use app\models\TariffNumber;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Телефония. Номера') ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Тарифы',
        ['label' => $this->title, 'url' => '/tariff/number/'],
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
        'attribute' => 'country_id',
        'class' => CountryColumn::className(),
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::className(),
        'country_id' => $filterModel->country_id,
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'activation_fee',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'status',
//        'class' => NumberTypeColumn::className()
    ],
    [
        'attribute' => 'currency_id',
        'class' => CurrencyColumn::className()
    ],
    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, TariffNumber $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, TariffNumber $model, $key) use ($baseView) {
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
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/tariff/number/new']),
    'columns' => $columns,
]);
