<?php
/**
 * Список стран
 *
 * @var app\classes\BaseView $this
 * @var CountryFilter $filterModel
 */

use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\Country;
use app\models\filter\CountryFilter;
use app\widgets\GridViewSequence\GridViewSequence;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => $this->title = 'Страны', 'url' => '/dictionary/country/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, Country $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'code',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'alpha_3',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'name_rus',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'site',
        'class' => StringColumn::class,
        'format' => 'html',
        'value' => function (Country $country) {
            return $country->site ?
                Html::a($country->site, $country->site) :
                '';
        }
    ],
    [
        'attribute' => 'in_use',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'is_show_in_lk',
        'class' => YesNoColumn::class,
    ],
    [
        'attribute' => 'lang',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'currency_id',
        'class' => CurrencyColumn::class,
    ],
    [
        'attribute' => 'prefix',
        'class' => IntegerColumn::class,
    ],
];

echo GridViewSequence::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);