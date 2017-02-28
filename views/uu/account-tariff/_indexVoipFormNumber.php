<?php
/**
 * Список универсальных услуг с пакетами. Форма. Номера
 *
 * @var \yii\web\View $this
 *
 * @var AccountTariff $accountTariffFirst
 * @var AccountTariff[][] $row
 */

use app\classes\Html;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\ServiceType;

?>

<?php /** @var AccountTariff $accountTariff */ ?>
<?php foreach ($row as $accountTariff) : ?>
    <div>

        <?= Html::checkbox('AccountTariff[ids][]', $checked = true, ['value' => $accountTariff->id, 'class' => 'collapse']) ?>

        <?php if ($accountTariff->service_type_id == ServiceType::ID_TRUNK) : ?>
            <?= Html::a('<span class="glyphicon glyphicon-random" aria-hidden="true"></span> Маршрутизация', ['/usage/trunk/edit', 'id' => $accountTariff->id]) ?>
        <?php else : ?>
            <?= Html::a(
                $accountTariff->voip_number ?: Yii::t('common', '(not set)'),
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
        echo \app\classes\DateFunction::getDateRange(end($accountTariffLogs)->actual_from, $firstAccountTariffLog->actual_from);
    } else {
        echo 'с ' . Yii::$app->formatter->asDate(end($accountTariffLogs)->actual_from, 'medium');
    }

    unset($firstAccountTariffLog);
    ?>

</span>
