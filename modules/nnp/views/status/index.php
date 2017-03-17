<?php
/**
 * Статусы направлений
 *
 * @var app\classes\BaseView $this
 * @var StatusFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\modules\nnp\filter\StatusFilter;
use app\modules\nnp\models\Status;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

?>

<?= app\classes\Html::formLabel($this->title = 'Статусы направлений') ?>
<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title, 'url' => '/nnp/status/'],
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, Status $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, Status $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'id',
        'class' => IntegerColumn::className(),
    ],
    [
        'attribute' => 'name',
        'class' => StringColumn::className(),
    ],
    [
        'label' => 'Направления',
        'format' => 'html',
        'value' => function (Status $status) use ($baseView) {
            return $baseView->render('//layouts/_link', [
                    'url' => Url::to(['/nnp/destination/', 'DestinationFilter[status_id]' => $status->id]),
                    'text' => Yii::t('common', 'Show'),
                    'glyphicon' => 'glyphicon-list-alt',
                ]
            );
        }
    ],
];

echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/nnp/status/new/']),
    'columns' => $columns,
]);