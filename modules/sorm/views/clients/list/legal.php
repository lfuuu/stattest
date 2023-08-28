<?= app\classes\Html::formLabel($this->title = 'СОРМ: Клиенты. Юридические лица') ?>
<?= \yii\widgets\Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title, 'url' => '/sorm/clients/legal'],
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
        'attribute' => 'name_jur',
        'label' => 'Название',
        'format' => 'html',
        'value' => fn($f) => str_replace('ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ', 'ООО', $f['name_jur']),
    ],
    [
        'attribute' => '_address_nostruct',
        'label' => 'Юр. адрес',
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
];


$filterColumns = [
    [
        'attribute' => 'region_id',
        'class' => \app\modules\nnp\column\RegionColumn::class,
    ],
    [
        'attribute' => 'account_manager',
        'filterType' => \app\classes\grid\GridView::FILTER_SELECT2,
        'filter' => \app\models\User::getAccountManagerList(true),
        'class' => \app\classes\grid\column\DataColumn::class
    ],
    [
        'attribute' => 'filter_by',
        'filterType' => \app\classes\grid\GridView::FILTER_SELECT2,
        'filter' => \app\models\filter\SormClientFilter::$filterList,
        'class' => \app\classes\grid\column\DataColumn::class
    ],
    [
        'attribute' => 'is_with_error',
        'class' => \app\classes\grid\column\DataColumn::class,
        'filterType' => \app\classes\grid\GridView::FILTER_SELECT2,
        'filter' => \app\models\filter\SormClientFilter::$errList,
    ],
];


/** @var $filterModel \app\modules\sorm\filters\SormClientsFilter */
echo \app\classes\grid\GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
//    'beforeHeader' => [ // фильтры вне грида
//        'columns' => $filterColumns,
//    ],
]);

