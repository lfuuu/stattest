<?php
use app\models\billing\Pricelist;
use app\classes\Html;
use app\classes\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;

/** @var ActiveDataProvider $dataProvider */
/** @var Pricelist $filterModel */
/** @var int $connectionPointId */
/** @var int $orig */
/** @var string $type */

if ($orig == 1) {
    switch ($type) {
        case Pricelist::TYPE_CLIENT:
            echo Html::formLabel('Прайc-листы Клиентские Оригинация');
            break;

        case Pricelist::TYPE_OPERATOR:
            echo Html::formLabel('Прайc-листы Операторские Оригинация');
            break;
    }
} elseif ($orig == 0) {
    switch ($type) {
        case Pricelist::TYPE_CLIENT:
            echo Html::formLabel('Прайc-листы Клиентские Терминация');
            break;

        case Pricelist::TYPE_OPERATOR:
            echo Html::formLabel('Прайc-листы Операторские Терминация');
            break;

        case Pricelist::TYPE_LOCAL:
            echo Html::formLabel('Прайc-листы Местные Терминация');
            break;
    }
}

$columns = [
    [
        'class' => \app\classes\grid\column\RegionColumn::className(),
        'attribute' => 'connection_point_id',
        'label' => 'Точка присоединения',
        'value' => function ($data) use ($connectionPoints) {
            return $connectionPoints[ $data->region ];
        },
    ],
    [
        'attribute' => 'status',
        'filter' => ['' => '----'] + Pricelist::$states,
        'filterType' => GridView::FILTER_SELECT2,
        'label' => 'Статус',
        'value' => function ($data) {
            return Pricelist::$states[$data->status];
        },
    ],
    [
        'label' => 'Ид',
        'format' => 'raw',
        'value' => function ($data) {
            return Html::a($data->id, Url::toRoute(['voip/pricelist/edit', 'id' => $data->id]));
        },
    ],
    [
        'label' => 'Прайс-лист',
        'format' => 'raw',
        'value' => function ($data) {
            return Html::a($data->name, Url::toRoute(['voip/pricelist/edit', 'id' => $data->id]));
        },
    ],
];

if ($type == Pricelist::TYPE_OPERATOR && $orig == 0) {
    $columns[] = [
        'label' => 'Метод тарификации',
        'format' => 'raw',
        'value' => function ($data) {
            $result = $data->tariffication_by_minutes
                ? 'поминутная'
                : 'посекундная';
            if ($data->tariffication_full_first_minute) {
                $result .= ' со второй минуты';
            }
            return $result;
        },
    ];
    $columns[] = [
        'label' => 'Инициация МГМН вызова',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->initiate_mgmn_cost > 0 ?: '';
        },
    ];

    $columns[] = [
        'label' => 'Инициация зонового вызова',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->initiate_zona_cost > 0 ?: '';
        },
    ];

    $columns[] = [
        'label' => 'Глобальный прайс-лист',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->is_global > 0 ?: '';
        },
    ];
}

if ($type == Pricelist::TYPE_LOCAL) {
    $columns[] = [
        'label' => 'Местные префиксы',
        'format' => 'raw',
        'value' => function ($data) use ($networkConfigs) {
            return $networkConfigs[ $data->local_network_config_id ];
        },
    ];
}
$columns[] = [
    'label' => 'Цена',
    'format' => 'raw',
    'value' => function ($data) {
        return $data->price_include_vat ? 'С НДС' : 'Без НДС';
    },
];
$columns[] = [
    'label' => 'Файлы',
    'format' => 'raw',
    'value' => function ($data) {
        return Html::a('файлы', Url::toRoute(['voip/pricelist/files', 'pricelistId' => $data->id]));
    },
];
$columns[] = [
    'label' => 'Цены',
    'format' => 'raw',
    'value' => function ($data) {
        return Html::a('цены', Url::toRoute(['/index.php', 'module' => 'voipnew', 'action' => 'defs', 'pricelist' => $data->id]));
    },
];

$columns[] = [
    'class' => 'kartik\grid\ActionColumn',
    'template' => '<div style="text-align: center;">{delete}</div>',
    'header' => '',
    'buttons' => [
        'delete' => function($url, $model, $key) {
            return Html::a(
                '<span class="glyphicon glyphicon-trash"></span> Удаление',
                '/voip/pricelist/delete/?id=' . $model->id,
                [
                    'title' => Yii::t('kvgrid', 'Delete'),
                    'data-pjax' => 0,
                    'onClick' => 'return confirm("Вы уверены, что хотите удалить запись?")',
                ]
            );
        },
    ],
    'hAlign' => 'center',
    'width' => '7%',
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'extraButtons' => $this->render(
        '//layouts/_buttonCreate',
        [
            'url' => Url::toRoute(['add', 'type' => $type, 'orig' => $orig, 'connectionPointId' => $connectionPointId])
        ]
    ),
]);