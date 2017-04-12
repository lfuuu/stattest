<?php
/**
 * Список универсальных услуг с пакетами. Форма. Ресурсы
 *
 * @var \app\classes\BaseView $this
 * @var AccountTariff $accountTariffFirst
 * @var AccountTariff[][] $row
 */

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffResourceLog;

$resources = $accountTariffFirst->resources;
if (!$resources) {
    return;
}
$dateTimeNow = $accountTariffFirst->clientAccount->getDatetimeWithTimezone(); // по таймзоне клиента

?>
<br>
<div class="well">

    <?php
    foreach ($resources as $resource) :
        if (!$resource->isEditable()) {
            continue;
        }

        $unit = $resource->getUnit();
        ?>
        <div class="row">

            <div class="col-sm-4">
                <?= Html::encode($resource->name) ?><?= $unit ? Html::encode(', ' . $unit) : '' ?>:
            </div>

            <div class="col-sm-4">
                <?php

                /** @var AccountTariffResourceLog $accountTariffResourceLog */
                $accountTariffResourceLogsQuery = $accountTariffFirst->getAccountTariffResourceLogs($resource->id);
                foreach ($accountTariffResourceLogsQuery->each() as $accountTariffResourceLog) {

                    ?>
                    <b><?= $accountTariffResourceLog->getAmount() ?></b>
                    <span class="account-tariff-log-actual-from">
                        (с <?= Yii::$app->formatter->asDate($accountTariffResourceLog->actual_from, DateTimeZoneHelper::HUMAN_DATE_FORMAT) ?>)
                    </span>
                    <?php

                    if ($accountTariffResourceLog->actual_from < $dateTimeNow->format(DateTimeZoneHelper::DATE_FORMAT)) {
                        // совсем старые не нужно выводить здесь
                        break;
                    }
                    ?>
                    ,
                    <?php
                }

                ?>
            </div>

            <div class="col-sm-4">

                <?= $accountTariffFirst->isResourceCancelable($resource) ?
                    Html::a(
                        Html::tag('i', '', [
                            'class' => 'glyphicon glyphicon-erase',
                            'aria-hidden' => 'true',
                        ]) . ' ' .
                        Yii::t('common', 'Cancel'),
                        [
                            '/uu/account-tariff/resource-cancel',
                            'ids' => array_keys($row),
                            'resourceId' => $resource->id,
                        ],
                        [
                            'class' => 'btn btn-danger account-tariff-voip-resource-button account-tariff-resource-button-cancel btn-xs',
                            'title' => 'Отменить смену количества ресурса',
                        ]
                    ) :
                    ''
                ?>

                <?= $accountTariffFirst->isResourceEditable($resource) ?
                    '' : // @todo edit
                    ''
                ?>

            </div>

        </div>
    <?php endforeach ?>
</div>
