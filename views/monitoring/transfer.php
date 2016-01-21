<?php
use app\classes\Html;

echo Html::formLabel('Перемещаемые услуги');

foreach ($result as $usageTitle => $records) {
    if (!count($records)) {
        continue;
    }
    ?>
    <label><?= $usageTitle; ?></label>
    <table class="table table-bordered table-striped table-condensed table-hover">
        <colgroup>
            <col width="20%" />
            <col width="150" />
            <col width="20%" />
            <col width="100" />
            <col width="100" />
            <col width="150" />
            <col width="20%" />
            <col width="100" />
            <col width="100" />
        </colgroup>
        <tr>
            <th rowspan="2" style="text-align: center; vertical-align: middle;">Услуга</th>
            <th colspan="4" style="border-right: 2px solid #A5A5A5;">Перемещена от</th>
            <th colspan="4">Перемещена к</th>
        </tr>
        <tr>
            <th>ID услуги</th>
            <th>Клиент</th>
            <th>Работает с</th>
            <th style="border-right: 2px solid #A5A5A5;">Работает до</th>
            <th>ID услуги</th>
            <th>Клиент</th>
            <th>Работает с</th>
            <th>Работает до</th>
        </tr>
        <?php
        foreach ($records as $usage):
            list($description) = $usage->helper->description;
            ?>
            <tr>
                <td><?= $description; ?></td>
                <td><?= Html::a($usage->helper->transferedFrom->id, $usage->helper->transferedFrom->id, ['target' => '_blank']); ?></td>
                <td><?= Html::a($usage->helper->transferedFrom->clientAccount->contragent->name, ['/client/view', 'id' => $usage->helper->transferedFrom->clientAccount->id], ['target' => '_blank']); ?></td>
                <td><?= $usage->helper->transferedFrom->actual_from; ?></td>
                <td style="border-right: 2px solid #A5A5A5;"><?= $usage->helper->transferedFrom->actual_to; ?></td>
                <td><?= Html::a($usage->id, $usage->helper->editLink, ['target' => '_blank']); ?></td>
                <td><?= Html::a($usage->clientAccount->contragent->name, ['/client/view', 'id' => $usage->clientAccount->id], ['target' => '_blank']); ?></td>
                <td><?= $usage->actual_from; ?></td>
                <td><?= $usage->actual_to; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}