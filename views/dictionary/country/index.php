<?php
/**
 * Список стран
 *
 * @var \yii\web\View $this
 * @var CountryFilter $filterModel
 */

use app\classes\grid\column\universal\CurrencyColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\models\filter\CountryFilter;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Страны') ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => $this->title, 'url' => '/dictionary/country/'],
    ],
]) ?>

<?php
$columns = [
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
        'attribute' => 'in_use',
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
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);