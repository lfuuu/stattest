<?php
/**
 * Список универсальных услуг с пакетами. Форма. Номера
 *
 * @var \app\classes\BaseView $this
 *
 * @var AccountTariff $accountTariffFirst
 * @var AccountTariff[][] $row
 */

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

?>

<?php /** @var AccountTariff $accountTariff */ ?>
<?php foreach ($row as $accountTariff) : ?>
    <div>

        <?= Html::checkbox('AccountTariff[ids][]', $checked = true, ['value' => $accountTariff->id, 'class' => 'collapse']) ?>

        <?php if ($accountTariff->service_type_id == ServiceType::ID_TRUNK) : ?>
            <?= Html::a('<span class="glyphicon glyphicon-random" aria-hidden="true"></span> Маршрутизация', ['/usage/trunk/edit', 'id' => $accountTariff->id]) ?>
        <?php else : ?>
            <?= Html::a(
                $accountTariff->voip_number ?: 'id' . $accountTariff->id,
                $accountTariff->getUrl()
            ) ?>
        <?php endif ?>

    </div>
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
