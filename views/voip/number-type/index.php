<?php
/**
 * Список типов номеров
 *
 * @var app\classes\BaseView $this
 * @var NumberTypeFilter $filterModel
 */

use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\models\filter\NumberTypeFilter;
use app\models\NumberType;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Тип номера') ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Телефония',
        ['label' => $this->title, 'url' => '/voip/number-type/'],
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
        'label' => 'Страны',
        'attribute' => 'number_type_country_id',
        'class' => CountryColumn::className(),
        'isAddLink' => false,
        'format' => 'html',
        'value' => function (NumberType $numberType) {
            return implode('<br />', $numberType->numberTypeCountries);
        },
    ],
    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, NumberType $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, NumberType $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'options' => [
            'class' => 'text-center',
        ],
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/voip/number-type/new/']),
    'columns' => $columns,
]);