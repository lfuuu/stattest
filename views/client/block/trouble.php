<?php
$troublesIsset = false;
foreach ($troubles as $k => $trouble) {
    if (!in_array($trouble->stage->state_id, [2, 20, 21, 39, 40, 46, 47, 48]))
        $troublesIsset = true;
    else
        unset($troubles[$k]);
}
if ($troublesIsset):
    ?>

    <div class="tt-troubles">
        <div class="row" style="padding-left: 15px;">
            <h2>Заявки</h2>
            <table border="1" width="100%">
                <thead>
                <tr style="background: #f7f7f7;">
                    <th>№</th>
                    <th>Дата создания</th>
                    <th>Этап</th>
                    <th>Ответ</th>
                    <th>Проблема</th>
                </tr>
                <tr style="background: #f7f7f7;">
                    <th>Тип заявки</th>
                    <th>В работе</th>
                    <th>Клиент</th>
                    <th>Услуга</th>
                    <th>Последний коментарий</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($troubles as $k => $trouble) : ?>
                    <tr style="background: <?= ($i % 2 == 0) ? '#dcdcdc' : '#f7f7f7'; ?>;">
                        <td><a href="/index.php?module=tt&action=view&id=<?= $trouble->id ?>"><?= $trouble->id ?></a></td>
                        <td><?= $trouble->date_creation ?></td>
                        <td><?= $trouble->stage->state->name ?></td>
                        <td><?= $trouble->stage->user_main ?></td>
                        <td><?= $trouble->problem ?></td>
                    </tr>
                    <tr style="background: <?= ($i % 2 == 0) ? '#dcdcdc' : '#f7f7f7'; ?>;">
                        <td><?= $trouble->subTypeLabels[$trouble->trouble_subtype] ?></td>
                        <td><?= $trouble->stage->dif_time ?></td>
                        <td><?= $trouble->client ?></td>
                        <td><a href="/?module=newaccounts&action=bill_view&bill=<?= $trouble->usage ?>"><?= $trouble->usage ?></td>
                        <td><?= $trouble->stage->comment ?></td>
                    </tr>
                    <?php $i++ ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>