<?php

use app\classes\grid\column\RegionColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\traits\YesNoTraits;
use app\models\voip\StateServiceVoip;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\filter\AccountTariffFilter;
use app\models\usages\UsageInterface;
use app\models\UsageVoip;
use app\models\filter\UsageVoipFilter;
use kartik\widgets\Select2;
use yii\web\View;
use kartik\widgets\ActiveForm;
use app\models\Region;
use \app\models\User;

/** @var $filter \app\models\voip\StateServiceVoip */

$this->registerJs("var gve_targetElementName = 'address';\n", View::POS_HEAD);
$this->registerJs("var gve_targetUrl = 'sorm/numbers/save-address';\n", View::POS_HEAD);
$this->registerJsFile('js/grid_view_edit.js', ['position' => yii\web\View::POS_END]);

echo Html::formLabel('Номера. Без адреса установки оборудования.');

$form = ActiveForm::begin([
    'method' => 'get',
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($filter, 'region')->widget(Select2::className(), [
                'data' => Region::getList($isWithEmpty = true, $countryId = null, [Region::TYPE_HUB, Region::TYPE_POINT, Region::TYPE_NODE])
            ]) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($filter, 'is_b2c')->widget(Select2::className(), [
                'data' => YesNoTraits::getYesNoList(true),
            ]) ?>
        </div>

        <div class="col-md-2">
            <?= $form->field($filter, 'account_manager')->widget(Select2::className(), [
                'data' => User::getAccountManagerList(true)
            ]) ?>
        </div>
        <div class="col-md-1" style="margin-top: 20px">
            <?= Html::submitButton('Фильтровать', ['class' => 'btn btn-info']) ?>
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
                    <li>Если в ЛК адрес у услуги номера присутствует, но его нет в данном отчете, то надо подождать
                        когда пройдет синхронизация.
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div style="clear: both;"></div>

<?php

$columns = [
    [
        'label' => 'ЛС',
        'attribute' => 'clientAccount.id',
        'format' => 'html',
        'filter' => false,
        'value' => function (StateServiceVoip $state) {
            return $state->clientAccount->getLink();
        },
    ],
    [
        'label' => 'Переход в ЛК',
        'format' => 'html',
        'filter' => false,
        'value' => function (StateServiceVoip $state) {
            return \app\classes\api\ApiCore::isAvailable()
                ? Html::a(Html::tag('i', '', ['class' => 'glyphicon glyphicon-new-window']), \app\models\ClientAccount::getLinkToLk($state->client_id), ['target' => '_blank'])
                : '';
        },
    ],
    [
        'label' => 'Название контрагента',
        'attribute' => 'clientAccount.clientContractModel.clientContragent.name',
        'filter' => false,
    ],
    [
        'label' => 'Ак. Менеджер',
        'attribute' => 'clientAccount.clientContractModel.accountManagerName',
        'filter' => false,
    ],
    [
        'label' => 'Юр. адрес',
//        'attribute' => 'clientAccount.clientContractModel.clientContragent.address_jur',
        'filter' => false,
        'value' => function (StateServiceVoip $state) {
            $cg = $state->clientAccount->clientContractModel->clientContragent;
            return $cg->legal_type == \app\models\ClientContragent::PERSON_TYPE ? $cg->personModel->registration_address : $cg->address_jur;
        }
    ],
    [
        'attribute' => 'e164',
        'filter' => false,
        'format' => 'html',
        'value' => fn(StateServiceVoip $state) => Html::a($state->e164, $state->usage_id > AccountTariff::DELTA ? $state->accountTariff->getUrl() : $state->usageVoip->getUrl()),
    ],
    [
        'label' => 'Дата включения',
        'attribute' => 'actual_from',
        'filter' => false,
//        'value' => function (StateServiceVoip $state) {
//            $accountTariffHeap = $state->accountTariff->accountTariffHeap;
//            return ($accountTariffHeap && $accountTariffHeap->date_sale) ?
//                $accountTariffHeap->date_sale : '';
//        },
    ],
    [
        'label' => 'Дата отключения',
        'attribute' => 'actual_to',
        'filter' => false,
//        'value' => function (StateServiceVoip $state) {
//            $accountTariffHeap = $state->accountTariff->accountTariffHeap;
//            return ($accountTariffHeap && $accountTariffHeap->disconnect_date) ?
//                $accountTariffHeap->disconnect_date : '';
//        },
    ],
];

$columns['address'] = [
    'label' => 'Адрес',
    'attribute' => 'device_address',
    'filter' => false,
    'format' => 'raw',
    'value' => function (StateServiceVoip $state) {
        return
            '<span>' . ($state->usage_id > AccountTariff::DELTA ? $state->accountTariff->device_address : $state->usageVoip->address) . '</span>' .
            ($state->accountTariff->tariff_period_id ? '' : '<img src="/images/icons/edit.gif" role="button" data-id=' . $state->usage_id . ' class="edit pull-right" alt="Редактировать" />');
    },
    'width' => '20%',
];


echo GridView::widget([
    'dataProvider' => $filter->search(),
    'filterModel' => $filter,
    'columns' => $columns,
    'isFilterButton' => false,

//    'toolbar' => [],
//    'panel' => true,
]);

ActiveForm::end();
