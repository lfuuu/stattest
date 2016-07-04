<?php
/**
 * Список DID групп
 *
 * @var app\classes\BaseView $this
 * @var DidGroupFilter $filterModel
 */

use app\classes\grid\column\universal\BeautyLevelColumn;
use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\NumberTypeColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\DidGroup;
use app\models\filter\DidGroupFilter;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'DID группы') ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Тарифы',
        ['label' => $this->title, 'url' => '/tariff/did-group/'],
    ],
]) ?>

<?php
$columns = [
    [
        'attribute' => 'id',
        'format' => 'html',
        'value' => function(DidGroup $model) {
            return Html::a(' ' . $model->id . ' ', ['/tariff/did-group/edit', 'id' => $model->id]);
        }
    ],
    [
        'label' => 'Страна',
        'attribute' => 'country_id',
        'class' => CountryColumn::className(),
        'value' => function (DidGroup $didGroup) {
            return $didGroup->city->country_id;
        }
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
        'attribute' => 'beauty_level',
        'class' => BeautyLevelColumn::className(),
    ],
    [
        'attribute' => 'number_type_id',
        'class' => NumberTypeColumn::className()
    ]
];

$linkAdd = ['url' => '/tariff/did-group/add'];
if ($filterModel->city_id) {
    $linkAdd['url']= [$linkAdd['url'], 'city_id' => $filterModel->city_id];
}


echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', $linkAdd),
    'columns' => $columns,
]);
