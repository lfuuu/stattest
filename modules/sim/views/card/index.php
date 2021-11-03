<?php
/**
 * SIM-карты. Список
 *
 * @var app\classes\BaseView $this
 * @var CardFilter $filterModel
 */

use app\classes\grid\column\universal\ActionCheckboxColumn;
use app\classes\grid\column\universal\BeautyLevelColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\NumberStatusColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\column\universal\YesNoColumn;
use app\modules\sim\columns\EntryPointColumn;
use app\modules\sim\columns\ImsiProfileColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\ClientAccount;
use app\models\DidGroup;
use app\models\Number;
use app\modules\sim\columns\CardStatusColumn;
use app\modules\sim\columns\ImsiPartnerColumn;
use app\modules\sim\filters\CardFilter;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\widgets\GridViewExport\GridViewExport;
use kartik\form\ActiveForm;
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

$form = ActiveForm::begin(['method' => 'post', 'id' => 'setStatusForm']);
if (Yii::$app->user->can('sim.write') || Yii::$app->user->can('sim.link')) {
    echo "<div>";
    echo "<div class=well style='width: 400px; float: left;'>";
    echo Html::tag('b', 'Изменить статус на') . '<br>';
    echo Html::button('Изменить', ['class' => 'btn btn-primary', 'type' => 'submit', 'style' => 'margin-left: 10px', 'name' => 'set-status']);

    echo Html::dropDownList('status', null, CardStatus::getList(true), ['class' => 'form-control pull-left', 'style' => 'width: 250px']);
    echo "</div>";
    $width = $account ? 300 : 170;

    echo "<div class=well style='width: " . $width . "px; float: left; margin-left: 10px;'>";
    echo Html::tag('b', 'Связка с ЛС') . '<br>';
    if ($account) {
        echo $this->render('//layouts/_submitButton', [
            'text' => 'Привязать',
            'glyphicon' => 'glyphicon-link',
            'params' => [
                'name' => 'set-link',
                'class' => 'btn btn-success',
                'style' => 'margin-left: 10px;',
            ],
        ]);
    }
    echo $this->render('//layouts/_submitButton', [
        'text' => 'Отвязать',
        'glyphicon' => 'glyphicon-scissors',
        'params' => [
            'name' => 'set-unlink',
            'class' => 'btn btn-warning',
            'style' => 'margin-left: 10px;',
        ],
    ]);

    echo "</div>";
    echo "<div class=well style='width: " . $width . "px; float: left; margin-left: 10px;'>";
    echo Html::tag('b', 'Перенос на другой ЛС') . '<br>';
    echo $this->render('//layouts/_submitButton', [
        'text' => 'Перенести',
        'glyphicon' => 'glyphicon-link',
        'params' => [
            'name' => 'set-transfer',
            'class' => 'btn btn-primary',
            'style' => 'margin-left: 10px;',
        ],
    ]);

    echo Html::input('number', 'newAccountId', '' ,  ['class' => 'form-control', 'placeholder' => 'Введите ЛС для трансфера', 'style' => 'margin-left: 10px; margin-top: 10px']);
    
    echo "</div>";
    echo "</div>";
}

echo "<div style='clear: both;'></div>";

$baseView = $this;
$columns = [
    [
        'class' => ActionCheckboxColumn::class,
        'name' => 'cardIccids',
        'staticValue' => false,
    ],

    [
        'attribute' => 'iccid',
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
        'label' => 'Профиль',
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

    [
        'attribute' => 'is_active',
        'class' => YesNoColumn::class,
    ],

    [
        'attribute' => 'status_id',
        'class' => CardStatusColumn::class,
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
        'label' => 'Точка подклчюения',
        'attribute' => 'entry_point_id',
        'class' => EntryPointColumn::class,
        'format' => 'html',
    ],
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

ActiveForm::end();