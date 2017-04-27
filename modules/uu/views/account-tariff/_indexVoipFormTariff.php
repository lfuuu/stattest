<?php
/**
 * Список универсальных услуг с пакетами. Форма. Тариф
 *
 * @var \app\classes\BaseView $this
 *
 * @var AccountTariff $accountTariffFirst
 * @var int[] $packageServiceTypeIds
 * @var AccountTariff[][] $row
 * @var ServiceType $serviceType
 * @var AccountTariff[][] $packagesList
 * @var AccountTariff $accountTariffLog
 * @var bool $isCurrent
 * @var bool $isEditable
 * @var bool $isCancelable
 * @var string $actualFromNext
 */

use app\classes\DateFunction;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

?>
<?= Html::tag($isCurrent ? 'b' : 'span', $accountTariffLog->getTariffPeriodLink()) ?>

<span class="account-tariff-log-actual-from">
    <?php
    if (!$accountTariffLog->tariff_period_id) {
        // отключен. Рядом вывести тариф до отключения
        $accountTariffLogs = $accountTariffFirst->accountTariffLogs;
        reset($accountTariffLogs);
        $accountTariffLogNext = next($accountTariffLogs);
        printf(' (%s)', $accountTariffLogNext->getTariffPeriodLink());
        unset($accountTariffLogs, $accountTariffLogNext);
    }
    ?>

    <?php
    if ($actualFromNext) {
        echo DateFunction::getDateRange(
            $accountTariffLog->actual_from,
            (new DateTime($actualFromNext))->modify('-1 day')
        );
    } else {
        echo 'с ' . Yii::$app->formatter->asDate($accountTariffLog->actual_from, DateTimeZoneHelper::HUMAN_DATE_FORMAT);
    }
    ?>
</span>

<?php !$isCurrent && $isCancelable = $isEditable = false ?>

<?= $isCancelable ?
    Html::a(
        Html::tag('i', '', [
            'class' => 'glyphicon glyphicon-erase',
            'aria-hidden' => 'true',
        ]) . ' ' .
        Yii::t('common', 'Reject'),
        [
            '/uu/account-tariff/cancel',
            'ids' => array_keys($row),
            'accountTariffHash' => $accountTariffFirst->getHash(),
        ],
        [
            'class' => 'btn btn-danger account-tariff-voip-button account-tariff-button-cancel btn-xs',
            'title' => 'Отклонить запланированную смену тарифа',
        ]
    ) : '' ?>

<?= (!$isCancelable && $isEditable) ?
    Html::button(
        Html::tag('i', '', [
            'class' => 'glyphicon glyphicon-edit',
            'aria-hidden' => 'true',
        ]) . ' ' .
        Yii::t('common', 'Change'),
        [
            'class' => 'btn btn-primary account-tariff-voip-button account-tariff-voip-button-edit btn-xs',
            'title' => 'Сменить тариф или отключить услугу',
            'data-id' => $accountTariffFirst->id,
            'data-city_id' => (int)$accountTariffFirst->city_id,
            'data-service_type_id' => (int)$serviceType->id,
        ]
    ) : ''
?>
