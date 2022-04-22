<?php

use app\classes\Html;
use app\modules\uu\models\AccountTariffLogAdd;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use kartik\form\ActiveForm;


if (
    $filterModel->tariff_period_id <= 0 ||
    !($accountTariffFirst = $filterModel->search()->query->one())
    || !in_array($accountTariffFirst->service_type_id, [ServiceType::ID_VOIP, ServiceType::ID_VOIP_PACKAGE_CALLS, ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY, ServiceType::ID_VOIP_PACKAGE_SMS])
) {
    return '';
}

$serviceTypeMap = [
        ServiceType::ID_VOIP => ServiceType::ID_VOIP_PACKAGE_CALLS,
        ServiceType::ID_VOIP_PACKAGE_CALLS => ServiceType::ID_VOIP_PACKAGE_CALLS,
        ServiceType::ID_VOIP_PACKAGE_SMS => ServiceType::ID_VOIP_PACKAGE_SMS,
        ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY => ServiceType::ID_VOIP_PACKAGE_INTERNET_ROAMABILITY,
];

$serviceTypePackageId = $serviceTypeMap[$service_type_id];

$accountTariffLog = new AccountTariffLogAdd();
$accountTariffLog->actual_from = $accountTariffFirst->getDefaultActualFrom();

/** @var AccountTariff $accountTariff */
$accountTariff = $accountTariffFirst;
$accountTariffVoip = $accountTariffFirst;
$clientAccount = $accountTariff->clientAccount;
$serviceTypeId = $accountTariff->service_type_id ?: $this->serviceTypeId;
$countryId = $clientAccount->getUuCountryId();

$id = mt_rand(0, 1000000);

?>

<?php $form = ActiveForm::begin(); ?>

<div class="well">
    <label>Массовое подключение пакетов</label>

    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($accountTariffLog, 'tariff_period_id')
                ->widget(\kartik\select2\Select2::class, [
                    'data' => TariffPeriod::getList(
                        $defaultTariffPeriodId,
                        $serviceTypePackageId,
                        $clientAccount->currency,
                        $countryId,
                        $voipCountryIdTmp = null,
                        $accountTariff->city_id,
                        true,
                        false,
                        $statusId = null,
                        $clientAccount->is_voip_with_tax,
                        $clientAccount->contract->organization_id,
                        $accountTariff->number->ndc_type_id
                    ),
                    'options' => [
                        'id' => 'accountTariffTariffPeriod' . $id,
                        'class' => 'accountTariffTariffPeriod',
                    ],
                ])
                ->label('Тариф-Пакет');
            ?>
        </div>
        <div class="col-sm-2">
            <?= $form->field($accountTariffLog, 'actual_from')
                ->widget(\kartik\widgets\DatePicker::class, [
                    'removeButton' => false,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'startDate' => date(\app\helpers\DateTimeZoneHelper::DATE_FORMAT),
                        'todayHighlight' => true,
                    ],
                    'options' => [
                        'id' => 'AddPackageActualFrom' . $id,
                    ]
                ])
                ->label($accountTariffLog->getAttributeLabel('actual_from_utc'))
            ?>
        </div>

        <div class="col-sm-6">
            &nbsp;
        </div>
        <div class="col-sm-2">
            <?= Html::submitButton(
                Html::tag('i', '', [
                    'class' => 'glyphicon glyphicon-add',
                    'aria-hidden' => 'true',
                ]) . ' ' .
                Yii::t('tariff', 'Add Package'),
                [
                    'class' => 'btn btn-success',
                    'name' => 'AddPackageButton',
                    'id' => 'AddPackageButton' . $id,
                ]
            ) ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
