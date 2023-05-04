<?php
/**
 * свойства услуги для телефонии
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\classes\Html;
use app\models\billing\Locks;
use app\models\billing\StatsAccount;
use app\models\City;
use app\models\mtt_raw\MttRaw;
use app\modules\uu\models\ServiceType;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;

$accountTariff = $formModel->accountTariff;
$number = $accountTariff->number;

$helpConfluenceVoip = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP));
$helpConfluenceCalls = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP_PACKAGE_CALLS));
$helpConfluenceInternet = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP_PACKAGE_INTERNET));
?>

<div class="row">

    <?php // город ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'city_id')
            ->widget(Select2::class, [
                'data' => City::getList($isWithEmpty = true, $number ? $number->country_code : null),
                'disabled' => true,
            ])
            ->label($accountTariff->getAttributeLabel('city_id') . $helpConfluenceVoip)
        ?>
    </div>

    <?php // номер ?>
    <div class="col-sm-2">
        <label><?= $accountTariff->getAttributeLabel('voip_number') . $helpConfluenceVoip ?></label>
        <div>
            <?= $number ?
                Html::a($accountTariff->voip_number, $number->getUrl()) :
                $accountTariff->voip_number ?>
        </div>
    </div>

    <?php if ($number && $number->isMobileMcn()) : ?>

        <div class="col-sm-3">
            <label>Статистика <?= $helpConfluenceInternet ?></label>
            <div>
                <?= $this->render('//layouts/_buttonLink', [
                    'url' => Url::toRoute([
                        '',
                        'module' => 'stats',
                        'action' => 'voip',
                        'phone' => 'usage_' . $accountTariff->number->region . '_' . $accountTariff->voip_number,
                        'date_from' => (new DateTime('now'))->modify('first day of this month')->format(\app\helpers\DateTimeZoneHelper::DATE_FORMAT_EUROPE),
                        'date_to' => (new DateTime('now'))->modify('last day of this month')->format(\app\helpers\DateTimeZoneHelper::DATE_FORMAT_EUROPE),
                        'timezone' => 'Europe/Moscow',
                        'detality' => 'day',
                        'destination' => 'all',
                        'tariff_id' => '',
                        'direction' => 'both',
                    ]),
                    'text' => 'Телефония',
                ]) ?>
                <?= $this->render('//layouts/_buttonLink', [
                    'url' => Url::toRoute([
                        '/voip/sms',
                        'SmsFilter[from_datetime]' => (new DateTime('now'))->modify('first day of this month')->format(\app\helpers\DateTimeZoneHelper::DATE_FORMAT),
                        'SmsFilter[to_datetime]' => (new DateTime('now'))->modify('last day of this month')->format(\app\helpers\DateTimeZoneHelper::DATE_FORMAT),
                        'SmsFilter[group_by]' => 'day',
                        'SmsFilter[number]' => $accountTariff->voip_number,
                    ]),
                    'text' => 'SMS',
                ]) ?>
                <?= $this->render('//layouts/_buttonLink', [
                    'url' => Url::toRoute([
                        '/voip/data-raw',
                        'number_service_id' => $accountTariff->id,
                        'fromDate' => (new DateTime('now'))->modify('first day of this month')->format(\app\helpers\DateTimeZoneHelper::DATE_FORMAT),
                        'toDate' => (new DateTime('now'))->modify('last day of this month')->format(\app\helpers\DateTimeZoneHelper::DATE_FORMAT),
                        'groupBy' => 'day'
                    ]),
                    'text' => 'Мобильный интернет',
                ]) ?>
            </div>
        </div>

    <?php endif ?>

</div>

<div class="row">
    <div class="col-sm-6">
        <?= $form->field($accountTariff, 'device_address')
            ->textInput(['disabled' => 'disabled'])
            ->label($accountTariff->getAttributeLabel('device_address') . $helpConfluenceVoip)
        ?>
    </div>
</div>