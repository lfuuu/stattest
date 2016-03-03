
<label>Отчет по звонкам в пакете на номере</label>

<?php if (!count($report)): ?>
    <div style="text-align: center; color: red; font-weight: bold;">
        Данных по звонам в пакете нет
    </div>
<?php else: ?>
    <table class="table table-condensed">
        <thead>
            <tr>
                <th>Id</th>
                <th>Дата/время</th>
                <th>Исходящий номер</th>
                <th>Направление</th>
                <th>Входящий номер</th>
                <th>Время разговора</th>
                <th>Стоимость</th>
                <th>Назначение</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($report as $record): ?>
                <tr>
                    <td><?= $record['id']; ?></td>
                    <td><?= $record['tsf1']; ?></td>
                    <td><?= (isset($record['src_number']) ? $record['src_number'] : ''); ?></td>
                    <td style="color: <?= (isset($record['orig']) && $record['orig'] === false ? 'blue' : 'green'); ?>;">
                        <?php if (isset($record['orig']) && $record['orig'] === false): ?>
                            &darr;&nbsp;входящий
                        <?php elseif (isset($item['orig']) && $item['orig'] == true): ?>
                            &uarr;&nbsp;исходящий
                        <?php endif; ?>
                    </td>
                    <td><?= (isset($record['dst_number']) ? $record['dst_number'] : ''); ?></td>
                    <td><b><?= $record['tsf2']; ?></b></td>
                    <td><?= $record['price']; ?></td>
                    <td><?= $record['geo']; ?></td>
                </tr>
            <?php endforeach ;?>
        </tbody>
    </table>
<?php endif; ?>