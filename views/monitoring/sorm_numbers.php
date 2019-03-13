<?php

use app\classes\grid\column\RegionColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\filter\AccountTariffFilter;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;
use app\models\filter\UsageVoipFilter;
use yii\web\View;

use kartik\widgets\ActiveForm;
use kartik\builder\Form;

use app\models\Region;

/** @var $filterModelSearch AccountTariffFilter */
/** @var $filterModel AccountTariffFilter */
/** @var $filterModelOld UsageVoipFilter */

$this->registerJs("var gve_targetElementName = 'address';\n", View::POS_HEAD);
$this->registerJs("var gve_targetUrl = 'monitoring/save-address';\n", View::POS_HEAD);
$this->registerJsFile('js/grid_view_edit.js',  ['position' => yii\web\View::POS_END]);

echo Html::formLabel('СОРМ Номера');

?>

<div class="col-xs-4">
<?php

$form = ActiveForm::begin([
    'method' => 'get',
    'type' => ActiveForm::TYPE_VERTICAL,
]);

echo Form::widget([
    'model' => $filterModelSearch,
    'form' => $form,
    'attributes' => [
        'region_id' => [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => Region::getList($isWithEmpty = true, $countryId = null, [Region::TYPE_HUB, Region::TYPE_POINT, Region::TYPE_NODE]),
        ],
    ],
]);

?>

    <div class="form-group">
        <?= Html::submitButton('Фильтровать', ['class' => 'btn btn-info']) ?>
    </div>

<?php
ActiveForm::end();
?>
</div>
<div style="clear: both;"></div>

<?php

$columns = [
    [
        'label'     => 'ЛС',
        'attribute' => 'clientAccount.id',
        'format' => 'html',
        'filter'    => false,
        'value' => function (AccountTariff $accountTariff) {
            return $accountTariff->clientAccount->getLink();
        },
    ],
    [
        'label'     => 'Название контрагента',
        'attribute' => 'clientAccount.contragent.name',
        'filter'    => false,
    ],
    [
        'label'     => 'Юр. адрес',
        'attribute' => 'clientAccount.contragent.address_jur',
        'filter'    => false,
    ],
    [
        'attribute' => 'region_id',
        'class'     => RegionColumn::class,
        'filter'    => false,
    ],
    [
        'attribute' => 'voip_number',
        'filter'    => false,
    ],
    [
        'label' => 'Дата продажи, utc',
        'attribute' => 'date_sale',
        'filter'    => false,
        'value' => function (AccountTariff $accountTariff) {
            $accountTariffHeap = $accountTariff->accountTariffHeap;
            return ($accountTariffHeap && $accountTariffHeap->date_sale) ?
                $accountTariffHeap->date_sale : '';
        },
    ],
    [
        'label' => 'Дата отключения, utc',
        'attribute' => 'disconnect_date',
        'filter'    => false,
        'value' => function (AccountTariff $accountTariff) {
            $accountTariffHeap = $accountTariff->accountTariffHeap;
            return ($accountTariffHeap && $accountTariffHeap->disconnect_date) ?
                $accountTariffHeap->disconnect_date : '';
        },
    ],
];
$columns['address'] = [
    'label' => 'Адрес',
    'attribute' => 'device_address',
    'filter'    => false,
    'format' => 'raw',
    'value' => function (AccountTariff $accountTariff) {
        return
            '<span>' . $accountTariff->device_address . '</span>' .
            '<img src="/images/icons/edit.gif" role="button" data-id=' . $accountTariff->id . ' class="edit pull-right" alt="Редактировать" />';
    },
    'width' => '20%',
];

$dataProvider = $filterModel->search();
echo GridView::widget([
    'dataProvider' => $filterModel->search(),
    'filterModel' => $filterModel,
    'columns' => $columns,

    'toolbar' => [],
    'panel'=> false,
]);


?>
<br />
<?php
// --------- Old
echo Html::formLabel('СОРМ Номера (старые)');

$columns = [
    [
        'label'     => 'ЛС',
        'attribute' => 'client_id',
        'format' => 'html',
        'filter'    => false,
        'value' => function (UsageVoip $accountTariff) {
            return $accountTariff->clientAccount->getLink();
        },
    ],
    [
        'label'     => 'Название контрагента',
        'attribute' => 'clientAccount.contragent.name',
        'filter'    => false,
    ],
    [
        'label'     => 'Юр. адрес',
        'attribute' => 'clientAccount.contragent.address_jur',
        'filter'    => false,
    ],
    [
        'label'     => 'Регион',
        'attribute' => 'region',
        'value'     => 'regionName',
        'filter'    => false,
    ],
    [
        'label'     => 'Номер',
        'attribute' => 'E164',
        'filter'    => false,
    ],
    [
        'label' => 'Дата продажи, utc',
        'attribute' => 'date_sale',
        'filter'    => false,
        'value' => function (UsageVoip $accountTariff) {
            return $accountTariff->actual_from;
        },
    ],
    [
        'label' => 'Дата отключения, utc',
        'attribute' => 'disconnect_date',
        'filter'    => false,
        'value' => function (UsageVoip $accountTariff) {
            return $accountTariff->actual_to < UsageInterface::MAX_POSSIBLE_DATE ? $accountTariff->actual_to : '';
        },
    ],
];
$columns['address'] = [
    'label' => 'Адрес',
    'attribute' => 'address',
    'filter'    => false,
    'format' => 'raw',
    'value' => function (UsageVoip $accountTariff) {
        return
            '<span>' . $accountTariff->address . '</span>' .
            '<img src="/images/icons/edit.gif" role="button" data-id=' . $accountTariff->id . ' class="edit pull-right" alt="Редактировать" />';
    },
    'width' => '20%',
];

echo GridView::widget([
    'dataProvider' => $filterModelOld->search(),
    'filterModel' => $filterModelOld,
    'columns' => $columns,

    'toolbar' => [],
    'panel'=> false,
]);

