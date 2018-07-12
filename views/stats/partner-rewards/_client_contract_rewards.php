<?php
/**
 * Текущие настройки партнерского вознаграждения
 *
 * @var \app\classes\BaseView $this
 * @var PartnerRewardsFilter $filterModel
 */

use app\helpers\DateTimeZoneHelper;
use app\models\ClientContractReward;
use app\models\filter\PartnerRewardsFilter;

?>
<div style="margin-left:15px; margin-top: 25px;">
    <h2>Текущие настройки партнерского вознаграждения</h2>
    <?php
    $clientContractRewards = ClientContractReward::getActualByContract(
        $filterModel->partner_contract_id,
        (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT),
        false
    ); ?>
    <table>
        <tr>
            <th style="width: 10%;"><h3>Дата активации</h3></th>
            <th style="width: 15%;"><h3>Используемый тип</h3></th>
            <th style="width: 5%;"><h3>Разовое</h3></th>
            <th style="width: 10%;"><h3>% От подключения</h3></th>
            <th style="width: 15%;"><h3>% От абонентской платы</h3></th>
            <th style="width: 10%;"><h3>% От превышения</h3></th>
            <th style="width: 10%;"><h3>Тип периода</h3></th>
            <th style="width: 10%;"><h3>Пролонгация</h3></th>
        </tr>
        <?php foreach ($clientContractRewards as $item) : ?>
            <tr>
                <td><?= $item['actual_from']; ?></td>
                <td><?= str_replace('usage_', '', $item['usage_type']); ?></td>
                <td><?= $item['once_only']; ?></td>
                <td><?= $item['percentage_once_only']; ?></td>
                <td><?= $item['percentage_of_fee']; ?></td>
                <td><?= $item['percentage_of_over']; ?></td>
                <td><?= $item['period_type'] === 'month' ? 'месяц' : 'всегда'; ?></td>
                <td><?= $item['period_type'] === 'month' ? (int)$item['period_month'] + 1 : '-'; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>