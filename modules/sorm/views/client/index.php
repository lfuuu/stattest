<?= app\classes\Html::formLabel($this->title = 'СОРМ: Клиенты') ?>
<?= \yii\widgets\Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title, 'url' => '/sorm/client/'],
    ],
]) ?>

<?php

function addressIndicator($state)
{
    switch ($state) {
        case 'added':
            return \app\classes\Html::tag('span', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . ' ';
        case 'ok':
            return \app\classes\Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok text-success']) . ' ';
        default:
            return ' (' . $state . ') ';
    }
}

function fioInd($f, $strLen = null)
{
    $f = trim($f);
    if (!$f) {
        return \app\classes\Html::tag('span', '&nbsp;?&nbsp;', ['style' => ['color' => 'white', 'background-color' => 'red']]);
    }

    if ($strLen && mb_strlen($f) != $strLen) {
        if (mb_strlen($f) > $strLen) {
            $len = 1;
        } else {
            $len = $strLen - mb_strlen($f);
        }
        return \app\classes\Html::tag('span', $f.str_repeat('?', $len), ['style' => ['color' => 'black', 'background-color' => 'yellow']]);
    }

    return $f;
}

$columns = [
    [
        'attribute' => 'id',
        'value' => fn($f) => \app\classes\Html::a($f['id'], '/client/view?id=' . $f['id']),
        'format' => 'html',
    ],
    [
        'attribute' => 'legal_type_id',
        'class' => \app\classes\grid\column\universal\DropdownColumn::class,
        'filter' => ['1' => 'Юр. лицо', '0' => 'Физ. лицо'],
    ],
    [
        'attribute' => 'name_jur',
        'format' => 'html',
        'value' => fn($f) => ($f['legal_type_id'] == '0' ? fioInd($f['f']) . ' / ' . fioInd($f['i']) . ' / ' . fioInd($f['o']) : str_replace('ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ', 'ООО', $f['name_jur'])),
    ],
    [
        'attribute' => '_address_nostruct',
        'format' => 'html',
        'value' => fn($f) => addressIndicator($f['_state_address_nostruct']) . ($f['_state_address_nostruct'] != 'ok' ? \app\classes\Html::a($f['_address_nostruct'], '/sorm/address?hash=' . md5($f['_address_nostruct'])) : $f['_address_nostruct']),
    ],
    [
        'attribute' => '_address_device_nostruct',
        'format' => 'html',
        'value' => fn($f) => addressIndicator($f['_state_address_device_nostruct']) . $f['_address_device_nostruct'],
    ],
    [
        'value' => fn($f) => ($f['document_name']. ': ' . fioInd($f['passport_serial'], 4) . ' / ' . fioInd($f['passport_number'], 6). ' / ' . fioInd($f['passport_issued_date']) . ' / ' . fioInd($f['passport_issued'])),
        'format' => 'html',
    ]
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

