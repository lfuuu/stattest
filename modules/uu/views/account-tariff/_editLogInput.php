<?php
/**
 * Создание/редактирование универсальной услуги. Сменить/закрыть тариф с определенной даты
 *
 * @var BaseView $this
 * @var AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\BaseView;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\TroubleRoistat;
use app\modules\uu\forms\AccountTariffForm;
use app\modules\uu\models\Period;
use app\modules\uu\models\ServiceType;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\widgets\ActiveForm;

$accountTariff = $formModel->accountTariff;
$accountTariffLog = $formModel->accountTariffLog;
$clientAccount = $accountTariff->clientAccount;
$channels = TroubleRoistat::CHANNELS;
?>

<div class="row">
    <?php
    $defaultTariffPeriodId = null;
    $tariffPeriods = $formModel->getAvailableTariffPeriods($defaultTariffPeriodId, true);

    $accountTariffLog->tariff_period_id = $accountTariff->tariff_period_id; // текущий тариф
    !$accountTariffLog->tariff_period_id && $defaultTariffPeriodId && $accountTariffLog->tariff_period_id = $defaultTariffPeriodId; // иначе (при создании) дефолтный

    if ($accountTariffLog->actual_from >= Period::OPEN_DATE) {
        $accountTariffLog->actual_from = date('Y-m-d', strtotime('first day of next month'));
    }

    $id = mt_rand(0, 1000000); // чтобы на одной странице можно было несколько объектов показывать

    $this->registerJsVariables([
        'rndId' => $id,
        'confirmText' => Html::encode(Yii::t('tariff', 'Are you sure you want to close this tariff?'))
    ]);

    ?>
    <?php
    $isPackage = array_key_exists($accountTariff->service_type_id, ServiceType::$packages);
    $isShowTariffPeriodList = $accountTariff->isNewRecord || !$isPackage;
    if ($isShowTariffPeriodList) :
        ?>
        <div class="col-sm-6">
            <?= $form->field($accountTariffLog, 'tariff_period_id')
                ->widget(Select2::class, [
                    'data' => $tariffPeriods,
                    'options' => [
                        'id' => 'accountTariffTariffPeriod' . $id,
                        'class' => 'accountTariffTariffPeriod',
                    ],
                ])
                ->label(($isPackage ? 'Пакет' : 'Тариф') . '/период');
            ?>
        </div>
    <?php else : ?>
        <?= $form->field($accountTariffLog, 'tariff_period_id')->hiddenInput()->label(false) ?>
    <?php endif; ?>


    <div class="col-sm-2">
        <?= $accountTariff->isNewRecord && $accountTariff->service_type_id == ServiceType::ID_VOIP ? '' : $form->field($accountTariffLog, 'actual_from')
            ->widget(DatePicker::class, [
                'removeButton' => false,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'yyyy-mm-dd',
                    'startDate' => date(DateTimeZoneHelper::DATE_FORMAT),
                    'todayHighlight' => true,
                ],
            ])
            ->label($isShowTariffPeriodList ? $accountTariffLog->getAttributeLabel('actual_from_utc') : false)
        // <div class="text-danger">Если сегодня, то отменить нельзя!</div>
        ?>
    </div>

    <?php if (!$accountTariff->isNewRecord) : ?>

        <?php if (!$isPackage) : ?>

            <div class="col-sm-6">
                <?= Html::submitButton(
                    Html::tag('i', '', [
                        'class' => 'glyphicon glyphicon-edit',
                        'aria-hidden' => 'true',
                    ]) . ' ' .
                    Yii::t('tariff', 'Change tariff'),
                    [
                        'class' => 'btn btn-primary',
                        'data-old-tariff-period-id' => $accountTariff->tariff_period_id,
                        'id' => 'changeTariffButton' . $id,
                    ]
                ) ?>
            </div>

        <?php endif ?>

        <div class="col-sm-6">
            <?= Html::submitButton(
                Html::tag('i', '', [
                    'class' => 'glyphicon glyphicon-trash',
                    'aria-hidden' => 'true',
                ]) . ' ' .
                Yii::t('tariff', 'Close tariff'),
                [
                    'class' => 'btn btn-danger closeTariff',
                    'name' => 'closeTariff',
                    'id' => 'closeTariffButton' . $id,
                ]
            ) ?>
        </div>

    <?php elseif($formModel->isShowRoistatVisit()): ?>
        <div class="col-sm-4">
            <label>Создать заявку на подключение</label>
            <?= Select2::widget([
                'data' => array_slice($channels, 0, 1, true) + ['not-create' => 'Не создавать'] + array_slice($channels, 1, count($channels) - 1, true),
                'name' => 'channel',
                'options' => [
                    'id' => 'trouble-roistat-channel'
                ]
            ]) ?>
        </div>
    <?php endif ?>

</div>