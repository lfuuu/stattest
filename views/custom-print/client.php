ID <?= $account->id; ?><br />
<?= $account->contragent->name; ?><br />
Менеджер: <?= $account->contract->managerName; ?><br />
Аккаунт менеджер: <?= $account->contract->accountManagerName; ?><br /><br />

Юридический адрес: <?= $account->contragent->address_jur; ?><br />
Адрес подключения:<br /><br />

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
    echo $contact->data . ($contact->comment ? '&nbsp-&nbsp'. $contact->comment : '');
endforeach;
?>