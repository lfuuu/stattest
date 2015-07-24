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
                    <th>Ответ.</th>
                    <th>Проблема</th>
                </tr>
                <tr style="background: #f7f7f7;">
                    <th>Тип заявки</th>
                    <th>В работе</th>
                    <th>Клиент / Кто создал</th>
                    <th>Услуга</th>
                    <th>Последний коментарий</th>
                </tr>
                </thead>
                <tbody>
                <?php $i=1; ?>
                <?php foreach ($troubles as $k => $trouble) : ?>
                    <tr style="border-top: 2px solid black; background: <?= $i%2==0 ? '#f9f9f9' : 'white' ?>;">
                        <td><b><a href="/index.php?module=tt&action=view&id=<?= $trouble->id ?>"><?= $trouble->id ?></a></b></td>
                        <td><?= $trouble->date_creation ?></td>
                        <td><?= $trouble->stage->state->name ?></td>
                        <td><?= $trouble->stage->user_main ?></td>
                        <td><?= $trouble->problem ?></td>
                    </tr>
                    <tr style=" background: <?= $i%2==0 ? '#f9f9f9' : 'white' ?>;">
                        <td><?= $trouble->subTypeLabels[$trouble->trouble_subtype] ?></td>
                        <td><?= $trouble->stage->dif_time ? $trouble->stage->dif_time : '0' ?></td>
                        <td><a href="/client/view?id=<?= $trouble->account->id ?>"><?= $trouble->client ?></a> / <?= \app\models\User::findOne(['user' => $trouble->user_author])->name ?>(<?= $trouble->user_author ?>)</td>
                        <td colspan=1 align=center style='font-size:85%;{if !$r.service && $r.bill_no && $r.is_payed == 1}background-color: #ccFFcc;{/if}'>
                            <?php if($trouble->server_id) : ?>
                                <a href='./?module=routers&action=server_pbx_apply&id={$r.server_id}'>
                                    <?= $trouble->usage; ?>
                                </a>
                            <?php elseif($trouble->service) : ?>
                                <a href='pop_services.php?table=<?= $trouble->service ?>&id=<?= $trouble->service_id ?>'>
                                    <?= $trouble->usage; ?>
                                </a>
                            <?php elseif($trouble->bill_no) : ?>
                            <a href="/?module=newaccounts&action=bill_view&bill=<?= $trouble->usage ?>"><?= $trouble->usage ?></td>
                        <?php endif; ?>
                        </td>
                        <td><?= $trouble->lastNotEmptyComment ?></td>
                    </tr>
                    <?php $i++ ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>