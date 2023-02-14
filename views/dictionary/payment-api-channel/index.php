<?php
/**
 * Список регионов
 *
 * @var app\classes\BaseView $this
 * @var PaymentApiChannelFilter $filterModel
 */

use app\classes\grid\column\universal\ConstructColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\TimeZoneColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\models\filter\PaymentApiChannelFilter;
use app\models\filter\RegionFilter;
use app\models\PaymentApiChannel;
use app\models\Region;
use app\classes\Html;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

?>
<?= Html::formLabel(PaymentApiChannel::TITLE); ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => PaymentApiChannel::TITLE, 'url' => Url::toRoute(['/dictionary/' . PaymentApiChannel::NAVIGATION])],
    ],
]) ?>


<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, PaymentApiChannel $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'id',
        'class' => IntegerColumn::class,
    ],
    [
        'attribute' => 'code',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::class,
    ],
    [
        'attribute' => 'check_organization_id',
        'class' => \app\classes\grid\column\universal\OrganizationColumn::class,
    ],
    [
        'attribute' => 'is_active',
        'class' => YesNoColumn::class,
    ],
    [
        'class' => ActionColumn::class,
        'template' => '{delete}',
        'buttons' => [
            'delete' => function ($url, PaymentApiChannel $model, $key) use ($baseView) {
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
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/'. PaymentApiChannel::NAVIGATION.'/new/']),
    'columns' => $columns,
]);