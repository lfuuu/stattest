<?php
/**
 * Вознаграждения партнеров. Детализация
 *
 * @var \app\classes\BaseView $this
 * @var array $details
 * @var bool $isExtendsMode
 */

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
        <th>Дата включения услуги</th>
        <th>Дата оплаты счёта</th>
        <th>Сумма оказанных услуг</th>
    </tr>
    </thead>
    <tbody> 
        <?php foreach ($details as $record) : ?>
            <tr>
                <td>
                    <a href="<?= Url::toRoute(['/index.php', 'module' => 'newaccounts', 'action' => 'bill_view', 'bill' => $record['bill_no']]) ?>" target="_blank">
                        <?= $record['bill_no'] ?>
                            (<?= \app\models\Bill::$paidStatuses[$record['bill_paid']] ?>)
                    </a>
                </td>
                <td>
                    <?= $record['description']?>
                    <br/>
                    <?= $record['log'] ?>
                </td>
                <td class="text-center">
                <?= $record['actual_from'] ?>
                </td>
                <td class="text-center">
                    <?= $record['payment_date'] ?>
                </td>
                <td class="text-center">
                    <?= $record['usage_paid'] ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>