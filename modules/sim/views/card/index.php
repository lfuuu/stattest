<?php
/**
 * SIM-карты. Список
 *
 * @var app\classes\BaseView $this
 * @var CardFilter $filterModel
 */

use app\classes\grid\column\universal\BeautyLevelColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\NumberStatusColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\sim\columns\CardStatusColumn;
use app\modules\sim\columns\ImsiPartnerColumn;
use app\modules\sim\filters\CardFilter;
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
        'class' => StringColumn::className(),
    ],

    [
        'attribute' => 'imei',
        'class' => StringColumn::className(),
    ],

    [
        'label' => 'IMSI',
        'attribute' => 'imsi',
        'class' => StringColumn::className(),
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
        'attribute' => 'msisdn',
        'class' => StringColumn::className(),
        'format' => 'raw',
        'value' => function (Card $card) {
            $msisdns = [];
            $imsies = $card->imsies;
            foreach ($imsies as $imsi) {
                $msisdns[] = $imsi->msisdn ?
                    Html::a($imsi->msisdn, \app\models\Number::getUrlById($imsi->msisdn)) :
                    Yii::t('common', '(not set)');
            }

            return implode(' <br>', $msisdns);
        },
    ],

    [
        'label' => 'DID',
        'attribute' => 'did',
        'class' => StringColumn::className(),
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
        'label' => 'Красивость',
        'attribute' => 'beauty_level',
        'class' => BeautyLevelColumn::className(),
        'value' => function (Card $card) {
            $imsi = reset($card->imsies);
            return $imsi->number->beauty_level;
        },
    ],

    [
        'label' => 'Статус номера',
        'attribute' => 'number_status',
        'class' => NumberStatusColumn::className(),
        'value' => function (Card $card) {
            $imsi = reset($card->imsies);
            return $imsi->number->status;
        },
    ],

    [
        'label' => 'MVNO-партнер',
        'attribute' => 'imsi_partner',
        'class' => ImsiPartnerColumn::className(),
        'format' => 'raw',
        'value' => function (Card $card) {
            $ids = [];
            $imsies = $card->imsies;
            foreach ($imsies as $imsi) {
                if (!$imsi->partner_id) {
                    continue;
                }

                $ids[] = $imsi->partner->getLink();
            }

            if (!$ids) {
                return Yii::t('common', '(not set)');
            }

            return implode(' <br>', $ids);
        },
    ],

    [
        'attribute' => 'client_account_id',
        'class' => IntegerColumn::className(),
        'format' => 'html',
        'value' => function (Card $card) {
            return $card->client_account_id ?
                ($card->clientAccount ? $card->clientAccount->getLink() : $card->client_account_id) :
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