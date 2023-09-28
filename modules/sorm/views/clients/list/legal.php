<?php

/** @var $filterModel \app\modules\sorm\filters\ClientsFilter */

?><?= app\classes\Html::formLabel($this->title = 'СОРМ: Клиенты. Юридические лица. ' . ($filterModel->is_b2c ? '(B2C)' : '(B2B/OTT)')) ?>
<?= \yii\widgets\Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title, 'url' => '/sorm/clients/legal'],
    ],
]) ?>

<?php

include "fn.php";

$form = \kartik\widgets\ActiveForm::begin([
    'id' => 'pForm',
    'method' => 'POST',
    'type' => \kartik\widgets\ActiveForm::TYPE_VERTICAL,
]);
?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($filterModel, 'filter_region_id')->widget(\kartik\widgets\Select2::className(), [
                'data' => \app\models\Region::getList($isWithEmpty = true, $countryId = null, [\app\models\Region::TYPE_HUB, \app\models\Region::TYPE_POINT, \app\models\Region::TYPE_NODE])
            ]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($filterModel, 'is_b2c')->widget(\kartik\widgets\Select2::className(), [
                'data' => \app\classes\traits\YesNoTraits::getYesNoList(true),
            ]) ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($filterModel, 'account_manager')->widget(\kartik\widgets\Select2::className(), [
                'data' => \app\models\User::getAccountManagerList(true)
            ]) ?>
        </div>
        <div class="col-md-1" style="margin-top: 20px">
            <?= \app\classes\Html::submitButton('Фильтровать', ['class' => 'btn btn-info']) ?>
        </div>
        <div class="col-md-1" style="margin-top: 20px">
            &nbsp;
        </div>
        <div class="col-md-4" style="margin-top: 20px">
            <div class="well text-info">
                <h1>Инструкция:</h1>
                <p>Отчет показывает на какой услуге не заполнен "Адрес установки оборудования" (далее Адрес).</p>
                <ul>
                    <li>Если номер выключен, Адрес можно заполнить из данного отчета</li>
                    <li>Если номер включен, Адрес можно заполнить только из ЛК</li>
                    <li>Если в ЛК адрес у услуги номера присутствует, но его нет в данном отчете, то надо подождать когда пройдет синхронизация.</li>
                </ul>
            </div>
        </div>
    </div>

    <div style="clear: both;"></div>

<?php

$columns = [
    [
        'label' => '(У)ЛС',
        'value' => fn($f) => \app\classes\Html::a($f['account_id'], '/client/view?id=' . $f['account_id']),
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
        'value' => fn($f) => \app\classes\Html::a(addressIndicator($f['_state_address_nostruct']), '/sorm/address?hash=' . md5($f['_address_nostruct'])) .
            ($f['_state_address_nostruct'] != 'ok' ? \app\classes\Html::a($f['_address_nostruct'], '/sorm/address?hash=' . md5($f['_address_nostruct'])) : $f['_address_nostruct']),
    ],
    [
        'attribute' => '_address_device_nostruct',
        'label' => 'Адрес установки оборудования',
        'format' => 'html',
        'value' => fn($f) => \app\classes\Html::a(addressIndicator($f['_state_address_device_nostruct']), '/sorm/address?hash=' . md5($f['_address_device_nostruct'])) .
            ($f['_state_address_device_nostruct'] != 'ok' ? \app\classes\Html::a($f['_address_device_nostruct'], '/sorm/address?hash=' . md5($f['_address_device_nostruct'])) : $f['_address_device_nostruct']),
    ],
    [
        'label' => 'Регион',
        'attribute' => 'region_id',
        'class' => \app\classes\grid\column\universal\RegionColumn::class,
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


/** @var $filterModel \app\modules\sorm\filters\ClientsFilter */
echo \app\classes\grid\GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,
    'isFilterButton' => false,
//    'beforeHeader' => [ // фильтры вне грида
//        'columns' => $filterColumns,
//    ],
]);

\kartik\widgets\ActiveForm::end();