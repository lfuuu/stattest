<?php
/**
 * Список универсальных услуг с пакетами. Форма. Тариф с историей
 *
 * @var \yii\web\View $this
 *
 * @var AccountTariff $accountTariffFirst
 * @var int[] $packageServiceTypeIds
 * @var AccountTariff[][] $row
 * @var ServiceType $serviceType
 * @var AccountTariff[][] $packagesList
 */

use app\classes\Html;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\classes\uu\model\ServiceType;

?>
<div class="well">

    <?php
    $isEditable = $accountTariffFirst->tariff_period_id;
    $isDefault = $accountTariffFirst->tariff_period_id && $accountTariffFirst->tariffPeriod->tariff->is_default;
    $isCancelable = $accountTariffFirst->isCancelable();
    $actualFromNext = '';

    /** @var AccountTariffLog[] $accountTariffLogs */
    $accountTariffLogs = $accountTariffFirst->accountTariffLogs;
    $accountTariffLog = array_shift($accountTariffLogs);

    ?>
    <div>
        <?php
        // текущий тариф
        echo $this->render('_indexVoipFormTariff', [
            'accountTariffFirst' => $accountTariffFirst,
            'packageServiceTypeIds' => $packageServiceTypeIds,
            'row' => $row,
            'serviceType' => $serviceType,
            'packagesList' => $packagesList,
            'accountTariffLog' => $accountTariffLog,
            'actualFromNext' => $actualFromNext,
            'isEditable' => $isEditable,
            'isDefault' => $isDefault,
            'isCancelable' => $isCancelable,
            'isCurrent' => true,
        ]);
        $actualFromNext = $accountTariffLog->actual_from;

        // показать/скрыть историю
        $divId = 'accountTariffLogs' . $accountTariffFirst->id;
        if (count($accountTariffLogs)) {
            echo $this->render('//layouts/_toggleButton', ['divSelector' => '#' . $divId]);
        }
        ?>
    </div>
    <?php

    // предыдущие тарифы (история)
    ?>
    <div id="<?= $divId ?>" style="display: none;">
        <?php
        foreach ($accountTariffLogs as $accountTariffLog) {
            echo Html::tag(
                'div',
                $this->render('_indexVoipFormTariff',
                    [
                        'accountTariffFirst' => $accountTariffFirst,
                        'packageServiceTypeIds' => $packageServiceTypeIds,
                        'row' => $row,
                        'serviceType' => $serviceType,
                        'packagesList' => $packagesList,
                        'accountTariffLog' => $accountTariffLog,
                        'actualFromNext' => $actualFromNext,
                        'isEditable' => $isEditable,
                        'isDefault' => $isDefault,
                        'isCancelable' => $isCancelable,
                        'isCurrent' => false,
                    ]
                )
            );
            $actualFromNext = $accountTariffLog->actual_from;
        }
        ?>
    </div>
</div>