<?php

use app\classes\partners\RewardCalculate;
use app\classes\partners\RewardsInterface;
use yii\helpers\Url;

/** @var array $details */
/** @var bool $isExtendsMode */
?>

<table class="table table-hover table-bordered table-striped">
    <colgroup>
        <col width="250" />
        <col width="*" />
        <col width="20%" />
        <col width="200" />
        <col width="200" />
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
            /**  @var RewardsInterface $rewardsHandler */
            $rewardsHandler = new RewardCalculate::$services[$record['usage_type']]([
                'clientAccountVersion' => $record['account_version'],
            ]);

            if ($rewardsHandler->isExcludeService($record['usage_id'])) {
                // Услуга исключена из вознаграждений
                continue;
            }

            // @todo Нужно сделать декоратор для получения данных об услуге
            $service = $rewardsHandler->getService($record['usage_id']);
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
                    <a href="<?= $service->helper->editLink ?>" target="_blank"><?= $service->helper->description[0] ?></a>
                </td>
                <td>
                    <a href="<?= $service->tariff->helper->editLink ?>" target="_blank"><?= $service->tariff->helper->title ?></a>
                </td>
                <td class="text-center">
                    <?= $service->activation_dt ?>
                </td>
                <td class="text-center">
                    <?= $record['usage_paid'] ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>