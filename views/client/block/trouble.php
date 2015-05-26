<?php
$troublesIsset = false;
foreach($troubles as $k=>$trouble){
    if(!in_array($trouble->stage->state_id, [2,20,21,39,40,46,47,48]))
        $troublesIsset = true;
    else
        unset($troubles[$k]);
}
if ($troublesIsset):
    ?>

    <div class="tt-troubles">
        <h2>Заявки</h2>
        <table border="1" width="100%">
            <thead>
            <tr style="background: #f7f7f7;">
                <th style="width: 10%;">№</th>
                <th style="width: 20%;">Дата создания</th>
                <th style="width: 20%;">Этап</th>
                <th style="width: 20%;">Ответ</th>
                <th style="width: 30%;">Проблема</th>
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
                    <td><?= $trouble->id ?></td>
                    <td><?= $trouble->date_creation ?></td>
                    <td><?= $trouble->stage->state->name ?></td>
                    <td><?= $trouble->stage->user_main ?></td>
                    <td><?= $trouble->problem ?></td>
                </tr>
                <tr style="background: <?= ($i % 2 == 0) ? '#dcdcdc' : '#f7f7f7'; ?>;">
                    <td><?= $trouble->subTypeLabels[$trouble->trouble_subtype] ?></td>
                    <td><?= $trouble->stage->dif_time ?></td>
                    <td><?= $trouble->client ?></td>
                    <td><?= $trouble->usage ?></td>
                    <td><?= $trouble->stage->comment ?></td>
                </tr>
                <?php $i++?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>