<?php
/**
 * Свойства услуги для телефонии. Пакеты
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var ActiveForm $form
 */

use app\models\City;
use app\modules\nnp\models\PackageMinute;
use app\modules\uu\models\ServiceType;
use kartik\form\ActiveForm;
use kartik\select2\Select2;

$accountTariff = $formModel->accountTariff;
$accountTariffParent = $accountTariff->prevAccountTariff;
$number = $accountTariffParent ? $accountTariffParent->number : null;

$helpConfluence = $this->render('//layouts/_helpConfluence', ServiceType::getHelpConfluenceById(ServiceType::ID_VOIP_PACKAGE_CALLS));
?>

<div class="row">

    <?php // город ?>
    <div class="col-sm-2">
        <?= $form->field($accountTariff, 'city_id')
            ->widget(Select2::class, [
                'data' => City::getList($isWithEmpty = true, $number ? $number->country_code : null),
                'disabled' => true,
            ])
            ->label($accountTariff->getAttributeLabel('city_id') . $helpConfluence) ?>
    </div>

    <?php // кол-во потраченных минут по пакету минут ?>
    <div class="col-sm-10">
        <?php
        $minutesStatistic = $accountTariff->getMinuteStatistic();
        foreach ($minutesStatistic as $minuteStatistic) {
            $packageMinuteId = $minuteStatistic['i_nnp_package_minute_id'];
            $minute = (int)($minuteStatistic['i_used_seconds'] / 60);
            $second = $minuteStatistic['i_used_seconds'] % 60;

            $packageMinute = PackageMinute::findOne(['id' => $packageMinuteId]);
            printf('Направление <b>%s</b>: потрачено <b>%d</b> мин. %d сек. из <b>%d</b> мин.', $packageMinute->destination->name, $minute, $second, $packageMinute->minute);
        }
        ?>
    </div>

</div>

