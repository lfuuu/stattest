<?php
/**
 * Страны
 *
 * @var app\classes\BaseView $this
 * @var CountryFilter $filterModel
 */

use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\filter\CountryFilter;
use app\modules\nnp\models\Country;
use app\widgets\GridViewExport\GridViewExport;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Страны') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title, 'url' => '/nnp/country/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'attribute' => 'code',
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
        'attribute' => 'alpha_3',
        'class' => StringColumn::className(),
    ],
    [
        'label' => 'Диапазон номеров',
        'format' => 'html',
        'value' => function (Country $country) {
            return Html::a(
                Yii::t('common', 'Show'),
                Url::to(['/nnp/number-range/', 'NumberRangeFilter[country_code]' => $country->code])
            );
        }
    ]
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