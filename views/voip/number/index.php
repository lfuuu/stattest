<?php
/**
 * Список номеров
 *
 * @var app\classes\BaseView $this
 * @var NumberFilter $filterModel
 * @var int $currentClientAccountId
 */

use app\classes\grid\column\universal\BeautyLevelColumn;
use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\DidGroupColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\IsNullAndNotNullColumn;
use app\classes\grid\column\universal\NumberStatusColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\filter\voip\NumberFilter;
use app\modules\nnp\column\NdcTypeColumn;
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

    <!-- div class="well">
        <?= '' /*$this->render('_indexGroupEdit', [
            'city_id' => $filterModel->city_id,
            'currentClientAccountId' => $currentClientAccountId,
        ]) */ ?>
    </div -->

<?php
$baseView = $this;
$month0 = (new DateTimeImmutable())->modify('first day of this month');
$month1 = $month0->modify('-1 month');
$month2 = $month1->modify('-1 month');

$columns = [
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
    [
        'attribute' => 'number',
        'class' => StringColumn::className(),
    ],
    [
        'label' => 'Звонков за ' . Yii::$app->formatter->asDate($month2, 'php:m'),
        'attribute' => 'calls_per_month_2',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'label' => 'Звонков за ' . Yii::$app->formatter->asDate($month1, 'php:m'),
        'attribute' => 'calls_per_month_1',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'label' => 'Звонков за ' . Yii::$app->formatter->asDate($month0, 'php:m'),
        'attribute' => 'calls_per_month_0',
        'class' => IntegerRangeColumn::className(),
    ],
    [
        'attribute' => 'usage_id',
        'class' => IsNullAndNotNullColumn::className(),
        'format' => 'html',
        'value' => function (\app\models\Number $number) {
            return $number->usage_id ?
                $number->usage ?
                    Html::a(
                        Html::encode($number->usage_id),
                        $number->usage->getUrl()
                    ) : $number->usage_id
                :
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
        'label' => 'Страна',
        'attribute' => 'country_id',
        'format' => 'html',
        'class' => CountryColumn::className(),
        'value' => function (\app\models\Number $number) {
            return $number->country_code ?
                $number->country->getLink()
                : Yii::t('common', '(not set)');
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
        'attribute' => 'imsi',
        'class' => IsNullAndNotNullColumn::className(),
    ],
    [
        'attribute' => 'ndc_type_id',
        'class' => NdcTypeColumn::className()
    ],
    [
        'attribute' => 'beauty_level',
        'class' => BeautyLevelColumn::className(),
    ],
    [
        'attribute' => 'did_group_id',
        'class' => DidGroupColumn::className(),
        'value' => function (\app\models\Number $number) {
            $didGroup = $number->didGroup;
            return Html::a($didGroup->name, $didGroup->getUrl());
        },
    ],
    [
        'attribute' => 'number_tech',
        'class' => StringColumn::className(),
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/voip/registry/add/']),
    'columns' => $columns,
]);
