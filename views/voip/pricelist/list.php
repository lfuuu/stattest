<?php
use app\models\billing\Pricelist;
use app\classes\Html;
use kartik\grid\GridView;
use kartik\grid\DataColumn;
use yii\helpers\Url;

if ($type == Pricelist::TYPE_CLIENT && $orig == 1) {
    echo Html::formLabel('Прайc-листы Клиентские Оригинация');
}
elseif ($type == Pricelist::TYPE_CLIENT && $orig == 0) {
    echo Html::formLabel('Прайc-листы Клиентские Терминация');
}
elseif ($type == Pricelist::TYPE_OPERATOR && $orig == 1) {
    echo Html::formLabel('Прайc-листы Операторские Оригинация');
}
elseif ($type == Pricelist::TYPE_OPERATOR && $orig == 0) {
    echo Html::formLabel('Прайc-листы Операторские Терминация');
}
elseif ($type == Pricelist::TYPE_LOCAL && $orig == 0) {
    echo Html::formLabel('Прайc-листы Местные Терминация');
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

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'columns' => $columns,
    'toolbar'=> [
        [
            'content' =>
                Html::a(
                    '<i class="glyphicon glyphicon-plus"></i> Добавить',
                    ['add', 'type' => $type, 'orig' => $orig, 'connectionPointId' => $connectionPointId],
                    [
                        'data-pjax' => 0,
                        'class' => 'btn btn-success btn-sm form-lnk',
                    ]
                ),
        ],
        '{toggleData}',
    ],
    'panel'=>[
        'type' => GridView::TYPE_DEFAULT,
    ],
]);