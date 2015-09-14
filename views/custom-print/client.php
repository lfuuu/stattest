ID <?= $account->id; ?><br />
<?= $account->contragent->name; ?><br />
Менеджер: <?= $account->contract->managerName; ?><br />
Аккаунт менеджер: <?= $account->contract->accountManagerName; ?><br /><br />

Юридический адрес: <?= $account->contragent->address_jur; ?><br />

<table border="0" cellspacing="2" cellpadding="2" style="border-collapse: collapse; margin-top: 15px; margin-bottom: 15px;">
    <thead>
        <tr>
            <th>Название услуги</th>
            <th>Адрес подключения</th>
            <th>Параметры</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($services as $service => $usages): ?>
            <?php foreach($usages as $usage): ?>
                <?php
                list($fulltext) = $usage::getTransferHelper($usage)->getTypeDescription();
                ?>
                <tr>
                    <td><?= $usage::getTransferHelper($usage)->getTypeTitle(); ?></td>
                    <td><?= $usage->address; ?></td>
                    <td><?= $fulltext; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>

Комментарии:<br />
<?php foreach ($account->contract->comments as $comment): ?>
    <?php if ($comment->is_publish): ?>
        <b><?= $comment->user; ?> <?= $comment->ts; ?>:</b>
        <?= $comment->comment; ?>
        <br />
    <?php endif; ?>
<?php endforeach; ?>

<br />Телефон:
<?php foreach ($account->allContacts as $contact):
    if ($contact->type != 'phone' || !$contact->is_active || !$contact->data):
        continue;
    endif;
    echo $contact->data . ($contact->comment ? '&nbsp-&nbsp'. $contact->comment : '') . '; &nbsp;';
endforeach;
?>

<style type="text/css">
th {
    border: 1px solid #CCCCCC;
    padding: 3px 4px 3px 4px;
    background: #F0F0F0;
}
td {
    border: 1px solid #CCCCCC;
    padding: 3px 4px 3px 4px;
}
</style>

