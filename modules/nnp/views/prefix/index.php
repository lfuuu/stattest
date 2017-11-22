<?php
/**
 * Префиксы
 *
 * @var app\classes\BaseView $this
 * @var PrefixFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\nnp\column\DestinationColumn;
use app\modules\nnp\filters\PrefixFilter;
use app\modules\nnp\models\Prefix;
use app\widgets\GridViewExport\GridViewExport;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title = 'Префиксы', 'url' => '/nnp/prefix/'],
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
        'label' => 'Направления (+)',
        'attribute' => 'addition_prefix_destination',
        'class' => DestinationColumn::className(),
        'isAddLink' => false,
        'format' => 'html',
        'value' => function (Prefix $prefix) {
            $htmlArray = [];
            foreach ($prefix->additionPrefixDestinations as $prefixDestination) {
                $destination = $prefixDestination->destination;
                $htmlArray[] = Html::a($destination->name, $destination->getUrl());
            }

            return implode('<br />', $htmlArray);
        }
    ],

    [
        'label' => 'Направления (-)',
        'attribute' => 'subtraction_prefix_destination',
        'class' => DestinationColumn::className(),
        'isAddLink' => false,
        'format' => 'html',
        'value' => function (Prefix $prefix) {
            $htmlArray = [];
            foreach ($prefix->subtractionPrefixDestinations as $prefixDestination) {
                $destination = $prefixDestination->destination;
                $htmlArray[] = Html::a($destination->name, $destination->getUrl());
            }

            return implode('<br />', $htmlArray);
        }
    ],

    [
        'label' => 'Диапазон номеров',
        'format' => 'html',
        'value' => function (Prefix $prefix) {
            return Html::a(
                Yii::t('common', 'Show'),
                Url::to(['/nnp/number-range/', 'NumberRangeFilter[prefix_id]' => $prefix->id])
            );
        }
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/nnp/prefix/new/']),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);