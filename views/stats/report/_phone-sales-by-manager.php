<?php
/** @var \app\classes\BaseView $this */
use app\assets\AppAsset;

/** @var array $managers */

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

/**
 * renderFile может правильно подключить эти файлы в автоматическом режиме, но т.к. он самодостаточен,
 * регистрация их в глобальном layout не происходит
 */
?>
<script type="text/javascript" src="/views/stats/report/_phone-sales-by-manager.js"></script>
<link href="/views/stats/report/_phone-sales-by-manager.css" rel="stylesheet" />

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
            <td>
                <span
                    class="details-link"
                    data-details='{ "report" : "number", "manager": "<?= $manager['data']['manager_name'] ?>", "type" : "new" }'>
                    <?= $manager['data']['number_new'] ?>
                </span>
            </td>
            <td>
                <span
                    class="details-link"
                    data-details='{ "report" : "number", "manager": "<?= $manager['data']['manager_name'] ?>", "type" : "old" }'>
                    <?= $manager['data']['number_old'] ?>
                </span>
            </td>
            <td>
                <span
                    class="details-link"
                    data-details='{ "report" : "line", "manager": "<?= $manager['data']['manager_name'] ?>", "type" : "new" }'>
                    <?= $manager['data']['line_new'] ?>
                <span>
            </td>
            <td>
                <span
                    class="details-link"
                    data-details='{ "report" : "line", "manager": "<?= $manager['data']['manager_name'] ?>", "type" : "old" }'>
                    <?= $manager['data']['line_old'] ?>
                <span>
            </td>
            <td>
                <span
                    class="details-link"
                    data-details='{ "report" : "line_free", "manager": "<?= $manager['data']['manager_name'] ?>", "type" : "new" }'>
                    <?= $manager['data']['line_free_new'] ?>
                <span>
            </td>
            <td>
                <span
                    class="details-link"
                    data-details='{ "report" : "line_free", "manager": "<?= $manager['data']['manager_name'] ?>", "type" : "old" }'>
                    <?= $manager['data']['line_free_old'] ?>
                <span>
            </td>
            <td>
                <span
                    class="details-link"
                    data-details='{ "report" : "number_8800", "manager": "<?= $manager['data']['manager_name'] ?>", "type" : "new" }'>
                    <?= $manager['data']['number_8800_new'] ?>
                <span>
            </td>
            <td>
                <span
                    class="details-link"
                    data-details='{ "report" : "number_8800", "manager": "<?= $manager['data']['manager_name'] ?>", "type" : "old" }'>
                    <?= $manager['data']['number_8800_old'] ?>
                <span>
            </td>
            <td>
                <span
                    class="details-link"
                    data-details='{ "report" : "vpbx", "manager": "<?= $manager['data']['manager_name'] ?>", "type" : "new" }'>
                    <?= $manager['data']['vpbx_new'] ?>
                <span>
            </td>
            <td>
                <span
                    class="details-link"
                    data-details='{ "report" : "vpbx", "manager": "<?= $manager['data']['manager_name'] ?>", "type" : "old" }'>
                    <?= $manager['data']['vpbx_old'] ?>
                <span>
            </td>
            <td>
                <span
                    class="details-link"
                    data-details='{ "report" : "departure", "manager": "<?= $manager['data']['manager_name'] ?>", "type" : "" }'>
                    <?= $manager['data']['departure'] ?>
                <span>
            </td>
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