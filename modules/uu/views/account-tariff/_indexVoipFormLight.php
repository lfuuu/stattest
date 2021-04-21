<?php
/**
 * Список универсальных услуг с пакетами. Форма
 *
 * @var \app\classes\BaseView $this
 *
 * @var AccountTariff $accountTariffFirst
 * @var int[] $packageServiceTypeIds
 * @var AccountTariff[][] $row
 * @var ServiceType $serviceType
 * @var AccountTariff[][] $packagesList
 */

use app\classes\Html;
use app\modules\uu\forms\AccountTariffEditForm;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;

$params = [
    'accountTariffFirst' => $accountTariffFirst,
    'packageServiceTypeIds' => $packageServiceTypeIds,
    'row' => $row,
    'serviceType' => $serviceType,
    'packagesList' => $packagesList,
    'isShowAddPackage' => false,
    'form' => null,
    'tagName' => 'span',
];
$panelBodyId = 'panel-body-' . $accountTariffFirst->id;
?>

<div class="col-sm-4">

    <div class="panel panel-<?= $accountTariffFirst->tariff_period_id ? $accountTariffFirst->serviceType->getColorClass() : 'default' ?> account-tariff-voip">
        <div class="panel-heading">
            <?php
            $formModel = new AccountTariffEditForm([
                'id' => $accountTariffFirst->id,
            ]);
            ?>
            <?php // город ?>
            <h2 class="panel-title">
                <?= Html::checkbox(null, $checked = true, ['class' => 'check-all collapse', 'title' => 'Отметить всё']) ?>

                <?= $accountTariffFirst->serviceType ?
                    Html::a($accountTariffFirst->serviceType->name, [
                        '/uu/account-tariff',
                        'serviceTypeId' => $accountTariffFirst->service_type_id,
                        'AccountTariffFilter[client_account_id]' => $accountTariffFirst->client_account_id,
                    ]) :
                    '' ?>
                <?= $accountTariffFirst->region ? ' (' . $accountTariffFirst->region->name . ')' : '' ?>
                <?= !in_array($accountTariffFirst->service_type_id, ServiceType::$onlyRegionGroup) && $accountTariffFirst->city ? ' (' . $accountTariffFirst->city->name . ')' : '' ?>

                <?= $accountTariffFirst->isActive() ?
                    '' :
                    $this->render('//layouts/_toggleButton', ['divSelector' => '#' . $panelBodyId])
                ?>
            </h2>

        </div>

        <div class="panel-body<?= $accountTariffFirst->isActive() ? '' : ' collapse' ?>" id="<?= $panelBodyId ?>">

            <div class="account-tariff-voip-numbers">
                <?php // номера ?>
                <?= $this->render('_indexVoipFormNumber', $params) ?>
            </div>

            <?php // текущий тариф ?>
            <?= $accountTariffFirst->tariff_period_id ?
                $accountTariffFirst->tariffPeriod->getName() :
                '' ?>

        </div>
    </div>
</div>