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
            /**  @var RewardsInterface $usageClass */
            $usageClass = RewardCalculate::$services[$record['usage_type']];
            $usage = $usageClass::getUsage($record['usage_id'], $record['account_version']);
            if (!$usage) {
                continue;
            }
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
                    <a href="<?= $usage->helper->editLink ?>" target="_blank"><?= $usage->helper->description[0] ?></a>
                </td>
                <td>
                    <a href="<?= $usage->tariff->helper->editLink ?>" target="_blank"><?= $usage->tariff->helper->title ?></a>
                </td>
                <td class="text-center">
                    <?= $usage->activation_dt ?>
                </td>
                <td class="text-center">
                    <?= $record['usage_paid'] ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>