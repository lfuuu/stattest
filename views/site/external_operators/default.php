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
                <!--tr id="tr_head">
                    <td rowspan="2" width="1%">#</td>
                    <td rowspan="2" width="10%">Оператор</td>
                    <td colspan="2" width="10%">Номер счета</td>
                    <td rowspan="2" width="10%">Дата<br>создания заказа</td>
                    <td colspan="<?= count($operator->products); ?>" width="1%">Кол-во</td>
                    <td rowspan="2" width="30%">Клиент <br>(ФИО, телефон, адрес)</td>
                    <td colspan="2" width="10%">Дата доставки</td>
                    <td rowspan="2" width="20%">Этапы</td>
                </tr-->
                <tr>
                    <td rowspan="2">#</td>
                    <td rowspan="2">Оператор</td>
                    <td colspan="2">Номер счета</td>
                    <td rowspan="2">Дата<br />создания заказа</td>
                    <td colspan="<?= count($operator->products); ?>">Кол-во</td>
                    <td rowspan="2">Клиент <br />(ФИО, телефон, адрес)</td>
                    <td rowspan="2">Серийный номер</td>
                    <td colspan="2">Дата доставки</td>
                    <td rowspan="2">Этапы</td>
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
                <?php foreach ($operator->report->getList($dateFrom, $dateTo, $filter) as $number => $item): ?>
                    <tr>
                        <td><?= ($number + 1); ?>.</td>
                        <td><?= $item['fio_oper']; ?></td>
                        <td><?= $item['req_no']; ?></td>
                        <td><?= $item['bill_no']; ?></td>
                        <td><?= $item['date_creation']; ?></td>
                        <?php foreach ($operator->products as $i => $product): ?>
                            <td align="center"><?= $item['group_' . ($i +1 )]; ?></td>
                        <?php endforeach; ?>
                        <td>
                            <p><?= $item['fio']; ?></p>
                            <p><?= $item['$i.phone']; ?></p>
                            <p><?= $item['address']; ?></p>
                        </td>
                        <td>
                        </td>
                        <td><?= $item['date_deliv']; ?></td>
                        <td><?= $item['date_delivered']; ?></td>
                        <td>
                        </td>
                    </tr>
                <?php endforeach;?>
                <tr>
                    <td>0</td>
                    <td>1</td>
                    <td>2</td>
                    <td>3</td>
                    <td>4</td>
                    <td>5</td>
                    <td>6</td>
                    <td>7</td>
                    <td>8</td>
                    <td>9</td>
                    <td>10</td>
                    <td>11</td>
                    <td>12</td>
                    <td>13</td>
                    <td>14</td>
                    <td>15</td>
                </tr>

            </tbody>
        </table>
    </div>
</div>
