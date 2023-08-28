<?= app\classes\Html::formLabel($this->title = 'СОРМ: Клиенты. Физические лица') ?>
<?= \yii\widgets\Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title, 'url' => '/sorm/clients/person'],
    ],
]) ?>

<?php

include "fn.php";

$columns = [
    [
        'label' => '(У)ЛС',
        'value' => fn($f) => \app\classes\Html::a($f['id'], '/client/view?id=' . $f['id']),
        'format' => 'html',
    ],
    [
        'attribute' => 'legal_type_id',
        'label' => 'Тип юр. лица',
        'class' => \app\classes\grid\column\universal\DropdownColumn::class,
        'filter' => ['1' => 'Юр. лицо', '0' => 'Физ. лицо'],
    ],
    [
        'label' => 'Ф',
        'format' => 'html',
        'value' => fn($f) => fioInd($f['f']),
    ],
    [
        'label' => 'И',
        'format' => 'html',
        'value' => fn($f) => fioInd($f['i']),
    ],
    [
        'label' => 'О',
        'format' => 'html',
        'value' => fn($f) => fioInd($f['o']),
    ],
    [
        'attribute' => '_address_nostruct',
        'label' => 'Адрес регистрации',
        'format' => 'html',
        'value' => fn($f) => \app\classes\Html::a(addressIndicator($f['_state_address_nostruct']) , '/sorm/address?hash=' . md5($f['_address_nostruct'])) .
            ($f['_state_address_nostruct'] != 'ok' ? \app\classes\Html::a($f['_address_nostruct'], '/sorm/address?hash=' . md5($f['_address_nostruct'])) : $f['_address_nostruct']),
    ],
    [
        'attribute' => '_address_device_nostruct',
        'label' => 'Адрес установки оборудования',
        'format' => 'html',
        'value' => fn($f) => \app\classes\Html::a(addressIndicator($f['_state_address_device_nostruct']) , '/sorm/address?hash=' . md5($f['_address_device_nostruct'])) .
            ($f['_state_address_device_nostruct'] != 'ok' ? \app\classes\Html::a($f['_address_device_nostruct'], '/sorm/address?hash=' . md5($f['_address_device_nostruct'])) : $f['_address_device_nostruct']),

    ],
    [
        'label' => 'Паспорт',
        'value' => fn($f) => ($f['document_name'] . ': ' . fioInd($f['passport_serial'], 4, 1) . ' / ' . fioInd($f['passport_number'], 6, 1) . ' / ' . fioInd($f['passport_issued_date'], 10, 2) . ' / ' . fioInd($f['passport_issued'])),
        'format' => 'html',
    ]
];


/** @var $filterModel \app\modules\sorm\filters\SormClientsFilter */
echo \app\classes\grid\GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
]);

