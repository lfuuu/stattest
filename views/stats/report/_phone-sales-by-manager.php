<?php
/**
 * @var $managers array
 */

$amount = [
    'number_new' => 0,
    'number_old' => 0,
    'line_new' => 0,
    'line_old' => 0,
    'line_free_new' => 0,
    'line_free_old' => 0,
    'number_8800_new' => 0,
    'number_8800_old' => 0,
    'vpbx_new' => 0,
    'vpbx_old' => 0,
    'departure' => 0,
];
?>
<h3>Статистика продажи телефонных номеров по аккаунт-менеджерам</h3>
<table class="sale-report">
    <thead>
    <tr>
        <th rowspan="2">Менеджер</th>
        <th colspan="2">Номера</th>
        <th colspan="2">Соединительные линии</th>
        <th colspan="2">Линии без номера</th>
        <th colspan="2">8800</th>
        <th colspan="2">ВАТС</th>
        <th rowspan="2">Выезды</th>
    </tr>
    <tr>
        <th>Новые</th>
        <th>Допродажи</th>
        <th>Новые</th>
        <th>Допродажи</th>
        <th>Новые</th>
        <th>Допродажи</th>
        <th>Новые</th>
        <th>Допродажи</th>
        <th>Новые</th>
        <th>Допродажи</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($managers as $manager): ?>
        <tr>
            <td><?= $manager['name'] ?></td>
            <td><?= $manager['data']['number_new'] ?></td>
            <td><?= $manager['data']['number_old'] ?></td>
            <td><?= $manager['data']['line_new'] ?></td>
            <td><?= $manager['data']['line_old'] ?></td>
            <td><?= $manager['data']['line_free_new'] ?></td>
            <td><?= $manager['data']['line_free_old'] ?></td>
            <td><?= $manager['data']['number_8800_new'] ?></td>
            <td><?= $manager['data']['number_8800_old'] ?></td>
            <td><?= $manager['data']['vpbx_new'] ?></td>
            <td><?= $manager['data']['vpbx_old'] ?></td>
            <td><?= $manager['data']['departure'] ?></td>
        </tr>
        <?php foreach ($amount as $k => &$v) {
            if (isset($manager['data'][$k])) {
                $v += $manager['data'][$k];
            }
        } ?>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <tr>
        <td><b>Итого:</b></td>
        <td><b><?= $amount['number_new'] ?></b></td>
        <td><b><?= $amount['number_old'] ?></b></td>
        <td><b><?= $amount['line_new'] ?></b></td>
        <td><b><?= $amount['line_old'] ?></b></td>
        <td><b><?= $amount['line_free_new'] ?></b></td>
        <td><b><?= $amount['line_free_old'] ?></b></td>
        <td><b><?= $amount['number_8800_new'] ?></b></td>
        <td><b><?= $amount['number_8800_old'] ?></b></td>
        <td><b><?= $amount['vpbx_new'] ?></b></td>
        <td><b><?= $amount['vpbx_old'] ?></b></td>
        <td><b><?= $amount['departure'] ?></b></td>
    </tr>
    </tfoot>
</table>

<style>
    .sale-report th{
        text-align: center;
    }

    .sale-report td, .sale-report th{
        padding: 5px;
    }

    .sale-report tbody tr:nth-child(2n){
        background: #e5e5e5;
    }

    .sale-report thead, .sale-report tfoot{
        background: #FFFEBE;
    }
</style>