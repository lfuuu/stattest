<?php

use app\classes\Encrypt;
use app\models\ClientAccount;
use app\models\filter\voip\MonitorFilter;
use kartik\widgets\ActiveForm;
use app\classes\grid\GridView;
use yii\helpers\Html;
use kartik\builder\Form;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var MonitorFilter $searchModel */


$urlData = [
    '/voip/monitor',
    'MonitorFilter' => [
//        'range' => $searchModel->range,
//        'number_a' => $searchModel->number_a,
//        'number_b' => $searchModel->number_b,
    ]];


$breadCrumbLinks = [
    'Телефония',
    ['label' => 'Мониторинг', 'url' => $urlData],
];

?>

<?= Breadcrumbs::widget(['links' => $breadCrumbLinks]) ?>

        <div class="well">
            <?php

            $form = ActiveForm::begin([
                'action' => '/voip/monitor/',
                'method' => 'get',
            ]);

            // строка 1
            $line1Attributes = [
                'date_from' => [
                    'type' => Form::INPUT_HTML5,
                    'options' => [
                        'type' => 'datetime-local',
                    ]
                ],

                'date_to' => [
                    'type' => Form::INPUT_HTML5,
                    'options' => [
                        'type' => 'datetime-local',
                    ]
                ],

                'orig_account' => ['type' => Form::INPUT_TEXT,],
                'term_account' => ['type' => Form::INPUT_TEXT,],

                'is_with_session_time' => [
                    'type' => Form::INPUT_CHECKBOX,
                ]
            ];

            $line2Attributes = [
                ['type' => Form::INPUT_RAW],
                ['type' => Form::INPUT_RAW],
                'number_a' => ['type' => Form::INPUT_TEXT,],
                'number_b' => ['type' => Form::INPUT_TEXT,],
                ['type' => Form::INPUT_RAW],
            ];


            echo Form::widget([
                'model' => $searchModel,
                'form' => $form,
                'columns' => count($line1Attributes),
                'attributes' => $line1Attributes
            ]);

            echo Form::widget([
                'model' => $searchModel,
                'form' => $form,
                'columns' => count($line2Attributes),
                'attributes' => $line2Attributes
            ]);

            echo Html::submitButton('Поиск', [
                'class' => 'btn btn-info',
                'name' => 'check-numbers',
                'value' => 'Поиск',
                'style' => 'width: 300px',
            ]);


            ActiveForm::end();
            ?>
        </div>

<?php

function getClientAccount($accountId)
{
    static $cache = [];
    if (isset($cache[$accountId])) {
        return $cache[$accountId];
    }

    $cache[$accountId] = ClientAccount::find()->where(['id' => $accountId])->one();
    return $cache[$accountId];
}

function getAccountCell($accountId)
{
    if (!$accountId) {
        return '';
    }

    /** @var ClientAccount $account */
    $account = getClientAccount($accountId);
    if ($account) {
        return $account->getLink() . Html::tag('div', $account->getCompany(), ['class' => 'small']);
    }
    return $accountId;
}

$columns = [[
    'label' => 'Сервер',
    'value' => function ($row) {

        static $c = [];
        if (!$c) {
            $c = \app\models\Region::getList(false, \app\models\Country::RUSSIA);
        }

        return $c[$row['server_id']] ?? $row['server_id'] . ' регион';
    }
],
    [
        'attribute' => 'cdr_connect_time',
        'format' => 'raw',
        'contentOptions' => ['class' => 'text-right'],
        'value' => function ($row) {
            $time = explode(" ", $row['cdr_connect_time'])[1];
            list($time, $msecs) = explode(".", $time);

            return $time . Html::tag('span', '.' . $msecs . (strlen($msecs) < 3 ? str_repeat('0', 3 - strlen($msecs)) : ''), ['style' => 'color: lightgray']);
        }
    ],
    [
        'attribute' => 'src_number',
        'value' => function ($row) {
            return $row['orig_num_a'] ? $row['orig_num_a'] : ($row['term_num_a'] ? $row['term_num_a'] : ($row['cdr_num_a']));
        }
    ],
    [
        'attribute' => 'dst_number',
        'value' => function ($row) {
            return $row['orig_num_b'] ? $row['orig_num_b'] : ($row['term_num_b'] ? $row['term_num_b'] : ($row['cdr_num_b']));
        }
    ],
    [
        'label' => $searchModel->getAttributeLabel('orig_account'),
        'format' => 'raw',
        'value' => function ($row) {
            return getAccountCell($row['orig_account']);
        }
    ],
    [
        'label' => $searchModel->getAttributeLabel('term_account'),
        'format' => 'raw',
        'value' => function ($row) {
            return getAccountCell($row['term_account']);
        }
    ],
    [
        'label' => "Ожидание соединения / Длительность",
        'value' => function ($row) {
            return (new DateTimeImmutable($row['cdr_connect_time']))->diff(new DateTimeImmutable($row['setup_time']))->s . ' / ' . $row['session_time'];
        }
    ],
    [
        'label' => 'Транк А / Транк B',
        'format' => 'raw',
        'value' => function ($row) {
            return $row['src_route'] . ' <br> ' . $row['dst_route'];
        }
    ],
    [
        'format' => 'raw',
        'value' => function ($row) {
            return $row['server_id'] == 99 ? \app\classes\Html::tag('audio', '', [
                'preload' => 'none',
                'id' => 'audio' . $row['id'],
                'controls' => '1',
                'src' => Url::to(['/voip/monitor/load',
                    'key' => Encrypt::encodeArray([
                        'server_id' => $row['server_id'],
                        'id' => $row['cdr_id'],
                    ])
                ])
            ]) : '';
        }
    ]];

echo GridView::widget([
    'dataProvider' => $searchModel->search(),
    'filterModel' => $searchModel,
    'columns' => $columns,
    'isFilterButton' => false,
]);


