<?php
/**
 * Список городов
 *
 * @var app\classes\BaseView $this
 * @var NumberFilter $filterModel
 * @var int $currentClientAccountId
 */

use app\classes\grid\column\universal\BeautyLevelColumn;
use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\DidGroupColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\IsNullAndNotNullColumn;
use app\classes\grid\column\universal\NumberStatusColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\filter\voip\NumberFilter;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Номера') ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Телефония',
        ['label' => $this->title, 'url' => '/voip/number/'],
    ],
]) ?>

    <div class="well">
        <?= $this->render('_indexGroupEdit', [
            'city_id' => $filterModel->city_id,
            'currentClientAccountId' => $currentClientAccountId,
        ]) ?>
    </div>

<?php
$baseView = $this;
$monthMinus3 = (new DateTimeImmutable())->modify('-3 month');
$monthMinus2 = (new DateTimeImmutable())->modify('-2 month');
$monthMinus1 = (new DateTimeImmutable())->modify('-1 month');

$columns = [
    [
        'attribute' => 'number',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'label' => 'Кол-во звонков',
        'headerOptions' => ['colspan' => 3],
        'filter' => Yii::$app->formatter->asDate($monthMinus3, 'php:M'),
        'value' => function (\app\models\Number $number) use ($monthMinus3) {
            return $number->getCallsWithoutUsagesByMonth($monthMinus3->format('m'));
        },
    ],
    [
        'label' => 'Кол-во звонков',
        'headerOptions' => ['class' => 'hidden'], // потому что colspan
        'filter' => Yii::$app->formatter->asDate($monthMinus2, 'php:M'),
        'value' => function (\app\models\Number $number) use ($monthMinus2) {
            return $number->getCallsWithoutUsagesByMonth($monthMinus2->format('m'));
        }
    ],
    [
        'label' => 'Кол-во звонков',
        'headerOptions' => ['class' => 'hidden'], // потому что colspan
        'filter' => Yii::$app->formatter->asDate($monthMinus1, 'php:M'),
        'value' => function (\app\models\Number $number) use ($monthMinus1) {
            return $number->getCallsWithoutUsagesByMonth($monthMinus1->format('m'));
        },
    ],
    [
        'attribute' => 'usage_id',
        'class' => IsNullAndNotNullColumn::className(),
        'format' => 'html',
        'value' => function (\app\models\Number $number) {
            return $number->usage_id ?
                Html::a(
                    Html::encode($number->usage_id),
                    $number->usage->getUrl()
                ) :
                Yii::t('common', '(not set)');
        },
    ],
    [
        'attribute' => 'client_id',
        'class' => IntegerColumn::className(),
        'isNullAndNotNull' => true,
        'format' => 'html',
        'value' => function (\app\models\Number $number) {
            return $number->client_id ?
                $number->clientAccount->getLink() :
                Yii::t('common', '(not set)');
        },
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::className(),
    ],
    [
        'attribute' => 'status',
        'class' => NumberStatusColumn::className(),
    ],
    [
        'attribute' => 'beauty_level',
        'class' => BeautyLevelColumn::className(),
    ],
    [
        'attribute' => 'did_group_id',
        'class' => DidGroupColumn::className(),
    ],
    [
        'class' => ActionColumn::className(),
        'template' => '{update}', // {delete}
        'buttons' => [
            'update' => function ($url, \app\models\Number $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, \app\models\Number $model, $key) use ($baseView) {
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
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/voip/registry/add/']),
    'columns' => $columns,
]);