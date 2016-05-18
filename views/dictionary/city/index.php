<?php
/**
 * Список городов
 *
 * @var app\classes\BaseView $this
 * @var CityFilter $filterModel
 */

use app\classes\grid\column\universal\ConnectionPointColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\City;
use app\models\filter\CityFilter;
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
$columns = [
    [
        'attribute' => 'id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
        'format' => 'html',
        'value' => function (City $city) {
            return Html::a($city->name ?: '-', $city->getUrl());
        }
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
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/city/new/']),
    'columns' => $columns,
]);