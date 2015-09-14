<?php
use kartik\daterange\DateRangePicker;

$report = $operator->report->getList($dateFrom, $dateTo, $filter);
?>

<div class="well" style="padding-top: 60px;">
    <legend>Заказы</legend>

    <div style="text-align: center;">
        <form method="GET">
            <div class="col-xs-12" style="padding-bottom: 5px;">
                <?php
                echo DateRangePicker::widget([
                    'name' => 'filter[range]',
                    'presetDropdown' => true,
                    'hideInput' => true,
                    'value' => $currentRange,
                    'pluginOptions' => [
                        'format' => 'YYYY-MM-DD',
                        'separator'=>' : ',
                    ],
                    'containerOptions' => [
                        'class' => 'drp-container input-group',
                    ]
                ]);
                ?>
            </div>

            <?php foreach ($operator->modes as $mode => $params): ?>
                <button
                    type="submit"
                    name="filter[mode]"
                    value="<?= $mode; ?>"
                    class="btn <?= (isset($filter['mode']) && $filter['mode'] == $mode ? 'btn-primary' : 'btn-default'); ?>"
                    style="width: 150px;">
                    <?= $params['title']; ?>
                </button>
            <?php endforeach; ?>
        </form>

        <br />

        <?php if (count($report)): ?>
            <table id="repart_table" border="1" cellspacing="2" style="border-collapse: collapse;">
                <tbody>
                    <tr>
                        <td rowspan="2" width="1%">#</td>
                        <td rowspan="2" width="10%">Оператор</td>
                        <td colspan="2" width="10%">Номер счета</td>
                        <td rowspan="2">Дата<br />создания заказа</td>
                        <td colspan="<?= count($operator->products); ?>" width="1%">Кол-во</td>
                        <td rowspan="2" width="30%">Клиент <br />(ФИО, телефон, адрес)</td>
                        <td rowspan="2">Серийный номер</td>
                        <td colspan="2" width="10%">Дата доставки</td>
                        <td rowspan="2" width="20%">Состояние</td>
                    </tr>
                    <tr>
                        <td>OnLime</td>
                        <td>Маркомнет Сервис</td>
                        <?php foreach ($operator->products as $i => $product): ?>
                            <td align="center"><?= $product['name']; ?></td>
                        <?php endforeach; ?>
                        <td>Желаемая</td>
                        <td>Фактическая</td>
                    </tr>
                    <?php foreach ($report as $number => $item): ?>
                        <tr>
                            <td><?= ($number + 1); ?>.</td>
                            <td><?= $item['fio_oper']; ?></td>
                            <td><?= $item['req_no']; ?></td>
                            <td>
                                <a
                                    href="index_external_operators.php/site/set-state?id=<?= $item['trouble_id']; ?>"
                                    onClick="return showIframePopup(this);"
                                    data-width="400"
                                    data-height="450"
                                    title="Изменить статус">
                                        <?= $item['bill_no']; ?>
                                </a>
                            </td>
                            <td><?= $item['date_creation']; ?></td>
                            <?php foreach ($operator->products as $i => $product): ?>
                                <td align="center"><?= (int) $item['group_' . ($i +1 )]; ?></td>
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
                                <?php $last_stage = array_pop($item['stages']); ?>
                                <span style="font-size: 8pt;"><?= $last_stage['date_finish_desired']; ?></span>
                                <b><?= $last_stage['state_name']; ?></b> <?= $last_stage['user_edit']; ?>: <span style="background-color: #cfffcf;"> <?= $last_stage['comment']; ?> </span>
                            </td>
                        </tr>
                    <?php endforeach;?>
                 </tbody>
            </table>
        <?php else: ?>
            <h2>Заявок не найдено</h2>
        <?php endif; ?>
    </div>
</div>
