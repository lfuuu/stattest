<?php
/**
 * Регионы
 *
 * @var app\classes\BaseView $this
 * @var RegionFilter $filterModel
 */

use app\modules\nnp\column\CountryColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\modules\nnp\filter\RegionFilter;
use app\modules\nnp\models\Region;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Регионы') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title, 'url' => '/nnp/region/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'attribute' => 'country_code',
        'class' => CountryColumn::className(),
        'indexBy' => 'code',
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],
    [
        'label' => 'Диапазон номеров',
        'format' => 'html',
        'value' => function (Region $region) use ($baseView) {
            return $baseView->render('//layouts/_link', [
                    'url' => Url::to(['/nnp/number-range/', 'NumberRangeFilter[region_id]' => $region->id]),
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
            'update' => function ($url, Region $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, Region $model, $key) use ($baseView) {
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
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/nnp/region/new/']),
    'columns' => $columns,
]);