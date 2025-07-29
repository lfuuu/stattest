<?php
/**
 * Список универсальных услуг с пакетами. Форма. Номера
 *
 * @var \app\classes\BaseView $this
 *
 * @var AccountTariff $accountTariffFirst
 * @var AccountTariff[][] $row
 * @var string $tagName
 */

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\helpers\Url;

?>

<?php /** @var AccountTariff $accountTariff */ ?>
<?php foreach ($row as $accountTariff) : ?>
    <<?= $tagName ?>>

    <?= Html::checkbox('AccountTariff[ids][]', $checked = true, ['value' => $accountTariff->id, 'class' => 'collapse']) ?>

    <?php if ($accountTariff->service_type_id == ServiceType::ID_TRUNK && $accountTariff->trunk_type_id != AccountTariff::TRUNK_TYPE_MULTITRUNK) : ?>
        <?= Html::a('<span class="glyphicon glyphicon-random" aria-hidden="true"></span> Маршрутизация', ['/usage/trunk/edit', 'id' => $accountTariff->id]) ?>
    <?php elseif ($accountTariff->service_type_id == ServiceType::ID_ESIM) : ?>
        <?= Html::a('<span class="glyphicon glyphicon-credit-card" aria-hidden="true"></span>', $accountTariff->getUrl()) . '&nbsp;' ?>
        <?php

        $isShowEditable = false;
        if ($accountTariff->iccid) {
            if ($tagName == 'div' && $accountTariff->iccid_saved_at_utc) {
                $saved = DateTimeZoneHelper::getUtcDateTime($accountTariff->iccid_saved_at_utc);
                $savedModif = $saved->modify('+1 day');
                $now = DateTimeZoneHelper::getUtcDateTime();

                if ($savedModif > $now) {
                    $isShowEditable = true;
                }
            }
        } elseif ($tagName == 'div') {
            $isShowEditable = true;
        }

        if ($isShowEditable && \Yii::$app->user->can('sim.write')) {
            echo Html::input('text', 'esim' . $accountTariff->id, (string)$accountTariff->iccid, ['data-id' => $accountTariff->id, 'class' => 'esim', 'autocomplete' => 'off', 'data-last-saved' => (string)$accountTariff->iccid]);
        } else {
            echo Html::tag('span', Html::a($accountTariff->iccid, ['/sim/card/edit', 'originIccid' => $accountTariff->iccid]), ['class' => 'esim-saved-value']);
        }

        ?>
    <?php else : ?>
        <?= Html::a(
            $accountTariff->voip_number ?
                Html::tag('span', $accountTariff->voip_number . (
                    ($isNumberNotVerified = $accountTariff->voip_number && $accountTariff->number->status == \app\models\Number::STATUS_NOT_VERFIED) ? Html::tag('sup', 'нв') : ''),
                    $isNumberNotVerified ? ['style' => ['color' => 'gray'], 'title' => 'Не верифицирован'] : [])
                :
                (
                ($accountTariff->service_type_id == ServiceType::ID_ONE_TIME && ($accountLogResources = $accountTariff->accountLogResources)) ?
                    // стоимость разовой услуги
                    reset($accountLogResources)->price :

                    // id УУ
                    $accountTariff->id
                ),
            $accountTariff->getUrl()
        ) .
        // если у номера есть симка - показываем
//        ($accountTariff->service_type_id == ServiceType::ID_VOIP && $accountTariff->number->imsi ? ' (' . Html::tag('small', $accountTariff->number->imsiModel->link) . ')' : '') .
        // Отключенную ВАТС можно разархивировать
        (
        ($accountTariff->isVpbxUnzippable()) ?
            $this->render('//layouts/_buttonLink', [
                'url' => Url::to(['/usage/vpbx/dearchive', 'accountId' => $accountTariff->client_account_id, 'usageId' => $accountTariff->id]),
                'text' => '',
                'title' => 'Разархивировать ВАТС',
                'glyphicon' => 'glyphicon-resize-full',
                'class' => 'btn-xs btn-default',
            ]) : ''
        )
        ?>
    <?php endif ?>

    </<?= $tagName ?>>
<?php endforeach; ?>


<span class="account-tariff-log-actual-from">

    <?php
    $accountTariffLogs = $accountTariffFirst->accountTariffLogs;
    $firstAccountTariffLog = reset($accountTariffLogs);
    if (!$firstAccountTariffLog->tariff_period_id) {
        echo \app\classes\DateFunction::getDateRange(
            end($accountTariffLogs)->actual_from,
            (new DateTime($firstAccountTariffLog->actual_from))->modify('-1 day')
        );
    } else {
        echo 'с ' . Yii::$app->formatter->asDate(end($accountTariffLogs)->actual_from, DateTimeZoneHelper::HUMAN_DATE_FORMAT);
    }

    unset($firstAccountTariffLog);
    ?>

</span>
