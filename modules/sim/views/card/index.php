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
use app\modules\sim\columns\ImsiProfileColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\DidGroup;
use app\models\Number;
use app\modules\sim\columns\CardStatusColumn;
use app\modules\sim\columns\ImsiPartnerColumn;
use app\modules\sim\filters\CardFilter;
use app\modules\sim\models\Card;
use app\widgets\GridViewExport\GridViewExport;
use kartik\grid\ActionColumn;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;

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
        'class' => ActionColumn::class,
        'template' => '{update}',
        'buttons' => [
            'update' => function ($url, Card $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => Url::to(['/sim/card/edit', 'originIccid' => $model->iccid]),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],

    [
        'attribute' => 'iccid',
        'class' => StringColumn::class,
    ],

    [
        'attribute' => 'imei',
        'class' => StringColumn::class,
    ],

    [
        'label' => 'IMSI',
        'attribute' => 'imsi',
        'class' => StringColumn::class,
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
        'class' => StringColumn::class,
        'format' => 'raw',
        'value' => function (Card $card) {
            $msisdns = [];
            $imsies = $card->imsies;
            foreach ($imsies as $imsi) {
                $msisdns[] = $imsi->msisdn ?
                    Html::a($imsi->msisdn, Number::getUrlById($imsi->msisdn)) :
                    Yii::t('common', '(not set)');
            }

            return implode(' <br>', $msisdns);
        },
    ],

    [
        'label' => 'DID',
        'attribute' => 'did',
        'class' => StringColumn::class,
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
        'class' => BeautyLevelColumn::class,
        'value' => function (Card $card) {
            $imsies = $card->imsies;
            $levels = [];
            foreach ($imsies as $imsi) {
                $levels[] = $imsi->number ? DidGroup::$beautyLevelNames[$imsi->number->beauty_level] : '';
            }
            return implode(' <br>', $levels);
        },
    ],

    [
        'label' => 'Статус номера',
        'attribute' => 'number_status',
        'class' => NumberStatusColumn::class,
        'value' => function (Card $card) {
            $imsies = $card->imsies;
            $statuses = [];
            foreach ($imsies as $imsi) {
                $statuses[] = $imsi->number ? Number::$statusList[$imsi->number->status] : '';
            }
            return implode(' <br>', $statuses);
        },
    ],

    [
        'label' => 'MVNO-партнер',
        'attribute' => 'imsi_partner',
        'class' => ImsiPartnerColumn::class,
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
        'class' => IntegerColumn::class,
        'format' => 'html',
        'value' => function (Card $card) {
            return $card->client_account_id ?
                ($card->clientAccount ? $card->clientAccount->getLink() : $card->client_account_id) :
                Yii::t('common', '(not set)');
        },
    ],

    [
        'attribute' => 'is_active',
        'class' => YesNoColumn::class,
    ],

    [
        'attribute' => 'status_id',
        'class' => CardStatusColumn::class,
    ],
    [
        'label' => 'Profile name',
        'attribute' => 'profile_id',
        'format' => 'raw',
        'filterOptions' => ['style' => 'width: 150px'],
        'class' => ImsiProfileColumn::class,
        'value' => function (Card $card) {
            $imsies = $card->imsies;
            $names = [];
            foreach ($imsies as $imsi) {
                    $names[] = $imsi->profile->name;
            }
            return implode(' <br>', $names);
        },
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