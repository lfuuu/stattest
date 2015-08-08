<?php

use app\models\Trouble;
use app\models\User;
use app\models\Bill;

$troublesIsset = false;
$serversTroubles = [];

foreach ($troubles as $k => $trouble) {
    if (!in_array($trouble->stage->state_id, [2, 20, 21, 39, 40, 46, 47, 48])) {
        $troublesIsset = true;
        if ($trouble->server_id == Trouble::SERVER_TROUBLE_TYPE) {
            $serversTroubles[] = $trouble;
            unset($troubles[$k]);
        }
    }
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
                <tr style="background: #F7F7F7;">
                    <th>№</th>
                    <th>Дата создания</th>
                    <th>Этап</th>
                    <th>Ответ.</th>
                    <th>Проблема</th>
                </tr>
                <tr style="background: #F7F7F7;">
                    <th>Тип заявки</th>
                    <th>В работе</th>
                    <th>Кто создал</th>
                    <th>Услуга</th>
                    <th>Последний коментарий</th>
                </tr>
                </thead>
                <tbody>
                <?php $i=1; ?>
                <?php foreach ($troubles as $k => $trouble) : ?>
                    <?php
                    $is_payed = 0;
                    if ($trouble->bill_no):
                        $bill = Bill::findOne(['bill_no' => $trouble->bill_no]);
                        $is_payed = $bill->is_payed;
                    endif;
                    ?>

                    <tr style="border-top: 2px solid black; background: <?= ($i % 2 == 0 ? '#F9F9F9' : '#FFFFFF'); ?>;">
                        <td><b><a href="/index.php?module=tt&action=view&id=<?= $trouble->id; ?>"><?= $trouble->id; ?></a></b></td>
                        <td><?= $trouble->date_creation; ?></td>
                        <td><?= $trouble->stage->state->name; ?></td>
                        <td><?= $trouble->stage->user_main; ?></td>
                        <td><?= $trouble->problem; ?></td>
                    </tr>
                    <tr style=" background: <?= ($trouble->is_important ? '#F4C0C0' : ($i % 2 == 0 ? '#F9F9F9' : '#FFFFFF')); ?>;">
                        <td><?= $trouble->subTypeLabels[$trouble->trouble_subtype]; ?></td>
                        <td><?= ($trouble->stage->dif_time ? $trouble->stage->dif_time : '0'); ?></td>
                        <td><?= User::findOne(['user' => $trouble->user_author])->name; ?>(<?= $trouble->user_author; ?>)</td>
                        <td colspan="1" align="center" style="font-size:85%;<?= (!$trouble->service && $is_payed == 1 ? ' background-color: #CCFFCC;' : ''); ?>">
                            <?php if($trouble->service) : ?>
                                <a href='pop_services.php?table=<?= $trouble->service; ?>&id=<?= $trouble->service_id; ?>'>
                                    <?= $trouble->usage; ?>
                                </a>
                            <?php elseif($trouble->bill_no) : ?>
                            <a href="/?module=newaccounts&action=bill_view&bill=<?= $trouble->usage; ?>"><?= $trouble->usage; ?></td>
                        <?php endif; ?>
                        </td>
                        <td><?= $trouble->lastNotEmptyComment; ?></td>
                    </tr>
                    <?php $i++; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php if (count($serversTroubles)): ?>
    <div class="tt-troubles">
        <div class="row" style="padding-left: 15px;">
            <h2>Серверные заявки</h2>
            <table border="1" width="100%">
                <thead>
                <tr style="background: #F7F7F7;">
                    <th>№</th>
                    <th>Дата создания</th>
                    <th>Этап</th>
                    <th>Ответ.</th>
                    <th>Проблема</th>
                </tr>
                <tr style="background: #F7F7F7;">
                    <th>Тип заявки</th>
                    <th>В работе</th>
                    <th>Кто создал</th>
                    <th>Услуга</th>
                    <th>Последний коментарий</th>
                </tr>
                </thead>
                <tbody>
                <?php $i=1; ?>
                <?php foreach ($serversTroubles as $k => $trouble) : ?>
                    <?php
                    $is_payed = 0;
                    if ($trouble->bill_no):
                        $bill = Bill::findOne(['bill_no' => $trouble->bill_no]);
                        $is_payed = $bill->is_payed;
                    endif;
                    ?>

                    <tr style="border-top: 2px solid black; background: <?= ($i % 2 == 0 ? '#F9F9F9' : '#FFFFFF'); ?>;">
                        <td><b><a href="/index.php?module=tt&action=view&id=<?= $trouble->id; ?>"><?= $trouble->id; ?></a></b></td>
                        <td><?= $trouble->date_creation; ?></td>
                        <td><?= $trouble->stage->state->name; ?></td>
                        <td><?= $trouble->stage->user_main; ?></td>
                        <td><?= $trouble->problem; ?></td>
                    </tr>
                    <tr style=" background: <?= ($trouble->is_important ? '#F4C0C0' : ($i % 2 == 0 ? '#F9F9F9' : '#FFFFFF')); ?>;">
                        <td><?= $trouble->subTypeLabels[$trouble->trouble_subtype]; ?></td>
                        <td><?= ($trouble->stage->dif_time ? $trouble->stage->dif_time : '0'); ?></td>
                        <td><?= User::findOne(['user' => $trouble->user_author])->name; ?>(<?= $trouble->user_author; ?>)</td>
                        <td colspan="1" align="center" style="font-size:85%;<?= (!$trouble->service && $is_payed == 1 ? ' background-color: #CCFFCC;' :'' ); ?>">
                            <?php if($trouble->server_id): ?>
                                <a href="./?module=routers&action=server_pbx_apply&id=<?= $trouble->server_id; ?>">
                                    <?= $trouble->usage; ?>
                                </a>
                            <?php elseif($trouble->service): ?>
                                <a href="pop_services.php?table=<?= $trouble->service; ?>&id=<?= $trouble->service_id; ?>">
                                    <?= $trouble->usage; ?>
                                </a>
                            <?php elseif($trouble->bill_no): ?>
                                <a href="/?module=newaccounts&action=bill_view&bill=<?= $trouble->usage; ?>"><?= $trouble->usage; ?></td>
                            <?php endif; ?>
                        </td>
                        <td><?= $trouble->lastNotEmptyComment; ?></td>
                    </tr>
                    <?php $i++; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
