<?php
/**
 * SIM-карты. Список
 *
 * @var app\classes\BaseView $this
 * @var CardStatusFilter $filterModel
 */

use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\modules\sim\columns\CardStatusColumn;
use app\modules\sim\filters\CardStatusFilter;
use app\modules\sim\models\Card;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        $this->title = 'SIM-карты',
    ],
]) ?>

<?php
$baseView = $this;
$columns = [
    [
        'class' => ActionColumn::className(),
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, Card $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, Card $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],

    [
        'attribute' => 'iccid',
        'class' => IntegerColumn::className(),
    ],

    [
        'attribute' => 'imei',
        'class' => IntegerColumn::className(),
    ],

    [
        'label' => 'IMSI',
        'format' => 'raw',
        'value' => function (Card $card) {
            $ids = [];
            $imsies = $card->imsies;
            foreach ($imsies as $imsi) {
                $ids[] = $imsi->imsi;
            }

            if (!$ids) {
                return Yii::t('common', '(not set)');
            }

            return implode(' <br>', $ids);
        },
    ],

    [
        'label' => 'MSISDN',
        'format' => 'raw',
        'value' => function (Card $card) {
            $msisdns = [];
            $imsies = $card->imsies;
            foreach ($imsies as $imsi) {
                $msisdns[] = $imsi->msisdn ?: Yii::t('common', '(not set)');
            }

            return implode(' <br>', $msisdns);
        },
    ],

    [
        'label' => 'DID',
        'format' => 'raw',
        'value' => function (Card $card) {
            $dids = [];
            $imsies = $card->imsies;
            foreach ($imsies as $imsi) {
                $dids[] = $imsi->did ?: Yii::t('common', '(not set)');
            }

            return implode(' <br>', $dids);
        },
    ],

    [
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function (Card $card) {
            return $card->client_account_id ?
                $card->clientAccount->getLink() :
                Yii::t('common', '(not set)');
        },
    ],

    [
        'attribute' => 'is_active',
        'class' => YesNoColumn::className(),
    ],

    [
        'attribute' => 'status_id',
        'class' => CardStatusColumn::className(),
    ],
];

$dataProvider = $filterModel->search();

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/sim/card/new/']),
    'columns' => $columns,
    'exportWidget' => GridViewExport::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $filterModel,
        'columns' => $columns,
    ]),
]);