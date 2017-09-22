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
        'class' => ActionColumn::className(),
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
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'alpha_3',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'name_rus',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'site',
        'class' => StringColumn::className(),
        'format' => 'html',
        'value' => function (Country $country) {
            return $country->site ?
                Html::a($country->site, $country->site) :
                '';
        }
    ],
    [
        'attribute' => 'in_use',
        'class' => YesNoColumn::className(),
    ],
    [
        'attribute' => 'is_show_in_lk',
        'class' => YesNoColumn::className(),
    ],
    [
        'attribute' => 'lang',
        'class' => StringColumn::className(),
    ],
    [
        'attribute' => 'currency_id',
        'class' => CurrencyColumn::className(),
    ],
    [
        'attribute' => 'prefix',
        'class' => IntegerColumn::className(),
    ],
];

echo GridViewSequence::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);