<?php
/**
 * Города
 *
 * @var app\classes\BaseView $this
 * @var CityFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\modules\nnp\filter\CityFilter;
use app\modules\nnp\models\City;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Города') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title, 'url' => '/nnp/city/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'attribute' => 'country_prefix',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],
    [
        'label' => 'Диапазон номеров',
        'format' => 'html',
        'value' => function (City $city) use ($baseView) {
            return $baseView->render('//layouts/_link', [
                    'url' => Url::to(['/nnp/number-range/', 'NumberRangeFilter[city_id]' => $city->id]),
                    'text' => Yii::t('common', 'Show'),
                    'glyphicon' => 'glyphicon-list-alt',
                ]
            );
        }
    ],
    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, City $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, City $model, $key) use ($baseView) {
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
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/nnp/city/new/']),
    'columns' => $columns,
]);