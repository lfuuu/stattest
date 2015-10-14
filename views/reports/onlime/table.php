<?php

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
?>

<?php if (count($report)): ?>
    <table id="report_table" border="1" cellspacing="2" style="border-collapse: collapse; font: normal 8pt sans-serif; padding: 2px 2px 2px 2px;">
        <thead>
            <tr>
                <th rowspan="2" width="1%">#</th>
                <th rowspan="2" width="10%"><div style="text-align: center;">Оператор</div></th>
                <th colspan="2" width="10%"><div style="text-align: center;">Номер счета</div></th>
                <th rowspan="2" width="5%"><div style="text-align: center;">Дата<br />создания заказа</div></th>
                <th colspan="<?= count($operator->products); ?>" width="1%"><div style="text-align: center;">Кол-во</div></th>
                <th rowspan="2" width="30%"><div style="text-align: center;">Клиент <br />(ФИО, телефон, адрес)</div></th>
                <th rowspan="2"><div style="text-align: center;">Серийный номер</div></th>
                <th colspan="2" width="10%"><div style="text-align: center;">Дата доставки</div></th>
                <th rowspan="2" width="20%"><div style="text-align: center;">Состояние</div></th>
            </tr>
            <tr>
                <th><div style="text-align: center;">OnLime</div></th>
                <th><div style="text-align: center;">Маркомнет Сервис</div></th>
                <?php foreach ($operator->products as $i => $product): ?>
                    <th align="center" width="3%"><div style="text-align: center;"><?= $product['name']; ?></div></th>
                <?php endforeach; ?>
                <th><div style="text-align: center;">Желаемая</div></th>
                <th><div style="text-align: center;">Фактическая</div></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report as $number => $item): ?>
                <tr>
                    <td><?= ($number + 1); ?>.</td>
                    <td><?= $item['fio_oper']; ?></td>
                    <td><?= $item['req_no']; ?></td>
                    <td>
                        <a href="<?= $billLink . $item['bill_no']; ?>" title="Просмотр заказа">
                            <?= $item['bill_no']; ?>
                        </a>
                    </td>
                    <td><?= DateTimeZoneHelper::getDateTime($item['date_creation']); ?></td>
                    <?php foreach ($operator->products as $key => $product): ?>
                        <?php
                        if (is_string($key)) {
                            $key = 'count_' . $key;
                        }
                        else {
                            $key = 'count_' . ($key + 1);
                        }
                        ?>
                        <td align="center"><?= (int) $item[$key]; ?></td>
                    <?php endforeach; ?>
                    <td>
                        <p><?= $item['fio']; ?></p>
                        <p><?= $item['phone']; ?></p>
                        <p><?= $item['address']; ?></p>
                    </td>
                    <td><?= $item['serials']; ?></td>
                    <td><?= $item['date_deliv']; ?></td>
                    <td><?= $item['date_delivered']; ?></td>
                    <td>
                        <?php
                        $last_stages = array_slice($item['stages'], count($items['stages'])-2, 2);
                        foreach ($last_stages as $stage) : ?>
                            <span style="font-size: 8pt;"><?= DateTimeZoneHelper::getDateTime($stage['date_finish_desired']); ?></span>
                            <b><?= $stage['state_name']; ?></b> <?= $stage['user_main']; ?>: <span style="background-color: #cfffcf;"> <?= $stage['comment']; ?> </span><br />
                        <?php endforeach; ?>

                        <?php if (count($item['stages']) > 2): ?>
                            <div title="История этапов <?= $item['bill_no']; ?>" class="stage-history" style="display: none;">
                                <?php foreach ($item['stages'] as $stage): ?>
                                    <span style="font-size: 8pt;"><?= DateTimeZoneHelper::getDateTime($stage['date_finish_desired']); ?></span>
                                    <b><?= $stage['state_name']; ?></b> <?= $stage['user_main']; ?>: <span style="background-color: #cfffcf;"> <?= $stage['comment']; ?> </span><br />
                                <?php endforeach; ?>
                            </div>
                            <div style="text-align: center;">
                                <?php
                                echo Html::button('вся история', [
                                    'class' => 'btn btn-success',
                                    'style' => 'padding: 2px;'
                                ]);
                                ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5"><div style="text-align: right;">Итого:</div></th>
                <?php foreach ($operator->products as $key => $product): ?>
                    <?php
                    if (is_string($key)) {
                        $key = 'count_' . $key;
                    }
                    else {
                        $key = 'count_' . ($key + 1);
                    }
                    $summary[$key] = 0;

                    foreach ($report as $number => $item):
                        $summary[$key] += $item[$key];
                    endforeach;
                    ?>
                    <th><div style="text-align: center;"><?= $summary[$key]; ?></div></th>
                <?php endforeach; ?>
                <th colspan="5"></th>
            </tr>
        </tfoot>
    </table>
<?php else: ?>
    <div class="alert alert-danger" style="text-align: center; font-weight: bold;">
        Ничего не найдено
    </div>
<?php endif; ?>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('div.stage-history').each(function () {
        var $button = $(this).next('div').find('button');
        $dialog = $(this).dialog({
            width: 450,
            height: 450,
            autoOpen: false,
            modal: true,
            resizable: false,
            draggable: false,
            closeOnEscape: true
        });

        $button
            .on('click', function () {
                $dialog.dialog('open');
            });
    });
});
</script>
