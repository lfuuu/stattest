<?php
use kartik\daterange\DateRangePicker;
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
                    'value' => Yii::$app->request->get('range'),
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

            <button type="submit" name="filter[mode]" class="btn btn-default" style="width: 150px;">Новый</button>
            <button type="submit" name="filter[mode]" value="work" class="btn btn-default" style="width: 150px;">В работе</button>
            <button type="submit" name="filter[mode]" class="btn btn-default" style="width: 150px;">К поступлению</button>
            <button type="submit" name="filter[mode]" class="btn btn-default" style="width: 150px;">Отложенный</button>
            <button type="submit" name="filter[mode]" class="btn btn-default" style="width: 150px;">Выполнен</button>
            <button type="submit" name="filter[mode]" value="close" class="btn btn-default" style="width: 150px;">Закрыт</button>
            <button type="submit" name="filter[mode]" value="reject" class="btn btn-default" style="width: 150px;">Отказ</button>
        </form>

        <br />
        <table id="repart_table" border="1" cellspacing="2" style="border-collapse: collapse;">
            <tbody>
                <tr id="tr_head">
                    <td rowspan="2" width="1%">#</td>
                    <td rowspan="2" width="10%">Оператор</td>
                    <td colspan="2" width="10%">Номер счета</td>
                    <td rowspan="2" width="10%">Дата<br>создания заказа</td>
                    <td colspan="<?= count($operator->products) + 1; ?>" width="1%">Кол-во</td>
                    <td colspan="1" width="10%">Номер купона</td>
                    <td rowspan="2" width="30%">Клиент <br>(ФИО, телефон, адрес)</td>
                    <td colspan="2" width="10%">Дата доставки</td>
                    <td rowspan="2" width="20%">Этапы</td>
                </tr>
                <tr id="tr_head">
                    <td>OnLime</td>
                    <td>Маркомнет Сервис</td>
                    <?php foreach ($operator->products as $product) :?>
                        <td><?= $product['name']; ?></td>
                    <?php endforeach; ?>
                    <td>Серийный номер</td>
                    <td>Желаемая</td>
                    <td>Фактическая</td>
                </tr>
                <?php foreach ($operator->report->getList($dateFrom, $dateTo, $filter) as $number => $item): ?>
                    <tr>
                        <td rowspan=2><?= $number; ?>.</td>
                        <td rowspan=2><?= $item['fio_oper']; ?></td>
                        <td rowspan=2><?= $item['req_no']; ?></td>
                        <td rowspan=2><?= $item['bill_no']; ?></td>
                        <td rowspan=2><?= $item['date_creation']; ?></td>
                        <?php foreach ($operator->products as $i => $product): ?>
                            <td rowspan=2 align=center><?= $item['group_' . ($i +1 )]; ?></td>
                        <?php endforeach; ?>
                        <td colspan=1>{if $i.coupon}{$i.coupon}{/if}</td>
                        <td rowspan=2>
                            <p><?= $item['fio']; ?></p>
                            <p><?= $item['$i.phone']; ?></p>
                            <p><?= $item['address']; ?></p>
                        </td>
                        <td rowspan=2><?= $item['date_deliv']; ?></td>
                        <td rowspan=2><?= $item['date_delivered']; ?></td>
                        <td rowspan=2>
                        </td>
                    </tr>
                    <tr>
                        <td>{$i.serials|replace:",":",<br>"}</td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </div>
</div>
