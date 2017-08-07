<?php

use app\classes\Html;
use app\helpers\monitoring\TransferredServiceInterface;
?>

<?php if (!count($services)): ?>
    <div class="row text-center">
        <div class="label label-info" style="padding: 10px;">
            Перемещаемых услуг не найдено
        </div>
    </div>
<?php else: ?>
    <table class="table table-bordered table-striped table-condensed table-hover">
        <colgroup>
            <col width="15%" />
            <col width="150" />
            <col width="20%" />
            <col width="250" />
            <col width="250" />
            <col width="150" />
            <col width="20%" />
            <col width="250" />
            <col width="250" />
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
        /** @var TransferredServiceInterface $serviceDecorator */
        foreach ($services as $serviceDecorator): ?>
            <tr>
                <td><?= $serviceDecorator->getDescription(); ?></td>
                <td>
                    <?= Html::a($serviceDecorator->getSourceId(), $serviceDecorator->getSourceUrl(),['target' => '_blank']); ?>
                </td>
                <td>
                    <?= Html::a(
                        $serviceDecorator->getSourceClientAccount()->contragent->name,
                        ['/client/view', 'id' => $serviceDecorator->getSourceClientAccount()->id],
                        ['target' => '_blank']
                    ); ?>
                </td>
                <td><?= $serviceDecorator->getSourceActualFrom(); ?></td>
                <td style="border-right: 2px solid #A5A5A5;"><?= $serviceDecorator->getSourceActualTo(); ?></td>
                <td><?= Html::a($serviceDecorator->getResultId(), $serviceDecorator->getResultUrl(), ['target' => '_blank']); ?></td>
                <td>
                    <?= Html::a(
                        $serviceDecorator->getResultClientAccount()->contragent->name,
                        ['/client/view', 'id' => $serviceDecorator->getResultClientAccount()->id],
                        ['target' => '_blank']);
                    ?>
                </td>
                <td><?= $serviceDecorator->getResultActualFrom(); ?></td>
                <td><?= $serviceDecorator->getResultActualTo(); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>