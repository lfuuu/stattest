<?php

use yii\helpers\ArrayHelper;
use app\classes\Html;

?>

<label>Отчет по использованию пакетов на номере</label>

<?php if (!count($report)): ?>
    <div style="text-align: center; color: red; font-weight: bold;">
        Данных по пакетам нет
    </div>
<?php else: ?>
    <table class="table table-condensed">
        <thead>
            <tr>
                <th rowspan="2">Номер</th>
                <th rowspan="2" valign="top">Название пакета</th>
                <th rowspan="2" style="text-align: center;">Абонентская плата</th>
                <th colspan="2" style="text-align: center;">Минут</th>
                <th rowspan="2" style="text-align: center;">Стоимость минуты в пакете</th>
                <th rowspan="2" style="text-align: center;">Минимальный платеж</th>
            </tr>
            <tr>
                <th style="text-align: center;">Всего</th>
                <th style="text-align: center;">Осталось</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report as $package): ?>
                <?php
                list($description) = $package->helper->description;
                $stat = $package->getStat($filter->date_range_from, $filter->date_range_to);
                ?>
                <tr>
                    <td><?= Html::a($package->usageVoip->E164, $package->usageVoip->helper->editLink, ['target' => '_blank']); ?></td>
                    <td><?= $description; ?></td>
                    <td align="center"><?= $package->tariff->periodical_fee; ?></td>
                    <td align="center"><?= $package->tariff->minutes_count; ?></td>
                    <td align="center"><?= ($package->tariff->minutes_count - floor(array_sum(ArrayHelper::getColumn($stat, 'used_seconds')) / 60)); ?></td>
                    <td align="center"><?= ceil($package->tariff->periodical_fee / $package->tariff->minutes_count); ?></td>
                    <td align="center"><?= $package->tariff->min_payment; ?></td>
                    <td></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>