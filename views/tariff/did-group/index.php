<?php
/**
 * Список DID групп
 *
 * @var \yii\web\View $this
 * @var DidGroupFilter $filterModel
 */

use app\classes\grid\column\universal\BeautyLevelColumn;
use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
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
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);
