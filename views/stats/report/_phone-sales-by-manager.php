<style>
    .details-link {
        font-weight: bold;
        text-decoration: underline;
        cursor: pointer;
    }

    .details-link:hover {
        text-shadow: 0 0 3px #ccca95;
        text-decoration: none;
    }

    .details-table { width: 100%; }

    .details-table td,
    .details-table th {
        padding: 4px;
        text-align: center;
        border-bottom: 1px dotted #ddd;
    }

    .details-table tr:nth-child(even) {
        background-color: #eee;
    }
</style>

<script type="text/javascript">
    $(document).ready(function() {

        $('span.details-link').click(function(e) {
            if ( $(this).data().details != 'undefined')
            {
                var details = $(this).data().details;

                $.post(
                    './index_lite.php?module=stats&action=report_by_one_manager',

                    { "data" : JSON.stringify(details) },

                    function(r) {
                        var $table = $('<table class="details-table"></table>');
                        var tRowHead = $(
                            '<thead><tr>' +
                                '<th>ID</th>' +
                                '<th>Менеджер</th>' +
                                '<th>Регион</th>' +
                                '<th>Контрагент</th>' +
                                '<th>Актуально с</th>' +
                                '<th>Клиент</th>' +
                                '<th>Тип</th>' +
                                '<th>E164</th>' +
                                '<th>Количество линий</th>' +
                                '<th>Статус</th>' +
                                '<th>Адрес</th>' +
                            '</tr></thead>'
                        );

                        $table.append(tRowHead);

                        if (r.status == 'OK' && r.data.length > 0)
                        {
                            r.data.forEach(function(row) {
                                var tRow = $('<tr></tr>');

                                tRow.append('<td>' + row.id + '</td>');
                                tRow.append('<td>' + row.manager_name + '</td>');
                                tRow.append('<td>' + row.region + '</td>');
                                tRow.append('<td>' + row.contragent + '</td>');
                                tRow.append('<td>' + row.actual_from + '</td>');
                                tRow.append('<td>' + row.client + '</td>');
                                tRow.append('<td>' + row.type_id + '</td>');
                                tRow.append('<td>' + row.E164 + '</td>');
                                tRow.append('<td>' + row.no_of_lines + '</td>');
                                tRow.append('<td>' + row.status + '</td>');
                                tRow.append('<td><abbr title="' + row.address + '">?</abbr></td>');

                                $table.append(tRow);
                            });
                        }
                        else
                        {
                            beautyData = r.data;
                        }

                        $("#report_details").html('');
                        $("#report_details").dialog(
                            {
                                width: '80%',
                                height: 400,
                                open: function() {
                                    $(this).append($table);
                                }
                            });
                    },

                    'json'
                );
            }
        });

    });
</script>
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