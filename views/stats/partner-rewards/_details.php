<?php
/**
 * Вознаграждения партнеров. Детализация
 *
 * @var \app\classes\BaseView $this
 * @var array $details
 * @var bool $isExtendsMode
 */

use app\classes\partners\handler\AHandler;
use app\classes\partners\RewardCalculate;
use app\dao\BillDao;
use app\helpers\usages\UsageHelperInterface;
use app\modules\uu\models\AccountTariff;
use yii\helpers\Url;

?>

<table class="table table-hover table-bordered table-striped">
    <colgroup>
        <col width="250"/>
        <col width="*"/>
        <col width="20%"/>
        <col width="200"/>
        <col width="200"/>
    </colgroup>
    <thead>
    <tr>
        <th>Счет</th>
        <th>Услуга</th>
        <th>Тариф</th>
        <th>Дата включения услуги</th>
        <th>Сумма оказанных услуг</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($details as $record) :
        if ($record['usage_type'] == BillDao::UU_SERVICE) {
            // для УУ определить соответствующий тип неуниверсальной услуги
            $accountTariff = AccountTariff::findOne(['id' => $record['usage_id']]);
            $serviceType = $accountTariff ? $accountTariff->serviceType : null;
            $service = $serviceType ? $serviceType->getUsageName() : null;
        } else {
            $service = $record['usage_type'];
        }

        $rewardClassName = RewardCalculate::$services[$service];
        /**  @var AHandler $rewardsHandler */
        $rewardsHandler = new $rewardClassName([
            'clientAccountVersion' => $record['account_version'],
        ]);

        if ($rewardsHandler->isExcludeService($record['usage_id'])) {
            // Услуга исключена из вознаграждений. Например, 800-ые номера
            continue;
        }

        $service = $rewardsHandler->getService($record['usage_id']);
        /** @var UsageHelperInterface $helper */
        $helper = $service->helper;
        ?>
        <tr>
            <td>
                <a
                        href="<?= Url::toRoute(['/index.php', 'module' => 'newaccounts', 'action' => 'bill_view', 'bill' => $record['bill_no']]) ?>"
                        target="_blank">
                    <?= $record['bill_no'] ?>
                    <?php if ($isExtendsMode) : ?>
                        (<?= \app\models\Bill::$paidStatuses[$record['bill_paid']] ?>)
                    <?php endif; ?>
                </a>
            </td>
            <td>
                <a href="<?= $helper->editLink ?>" target="_blank"><?= $helper->description[0] ?></a>
            </td>
            <td>
                <?= $helper->getTariffDescription() ?>
            </td>
            <td class="text-center">
                <?= $helper->getActivationDt() ?>
            </td>
            <td class="text-center">
                <?= $record['usage_paid'] ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>