<?php
/**
 * Список городов
 *
 * @var app\classes\BaseView $this
 * @var NumberFilter $filterModel
 */

use app\classes\grid\column\universal\BeautyLevelColumn;
use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\DidGroupColumn;
use app\classes\grid\column\universal\IsNullAndNotNullColumn;
use app\classes\grid\column\universal\NumberStatusColumn;
use app\classes\grid\column\universal\StringColumn;
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
        <?= $filterModel->city_id ?
            $this->render('_indexGroupEdit', ['city_id' => $filterModel->city_id]) :
            'Для группового редактирование отфильруйте минимум по городу'
        ?>
    </div>

<?php
$baseView = $this;
$columns = [
    [
        'attribute' => 'number',
        'class' => StringColumn::className(),
        'filterOptions' => [
            'title' => 'Допустимы цифры, _ или . (одна любая цифра), % или * (любая последовательность цифр, в том числе пустая строка)',
        ],
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
        }
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
    'columns' => $columns,
]);