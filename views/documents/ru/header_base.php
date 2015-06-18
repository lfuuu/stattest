<?php
use app\models\ClientContact;

/** @var $document app\classes\documents\DocumentReport */

$contact = ClientContact::dao()->GetContact($document->bill->clientAccount->id, true);
$company = $document->getCompany();
?>

<p>
Адрес доставки счета: <?= $document->bill->clientAccount->address_post; ?><br />
Факс для отправки счета:
<?php foreach ($contact['fax'] as $position => $item ): ?>
    <?php if ($position > 0): ?>; <?php endif; ?>
    <?= $item['data']; ?>
<?php endforeach; ?>
</p>

<p>
    <b>Поставщик: <?= $company['name']; ?></b><br />
    ИНН: <?= $company['inn']; ?>;  КПП: <?= $company['kpp']; ?><br />
    Адрес: <?= $company['address']; ?><br />
    Телефон: <?= $company['phone']; ?><br />
    Факс: <?= $company['fax']; ?><br />
    р/с: <?= $company['acc']; ?> в <?= $company['bank']; ?><br />
    к/с: <?= $company['kor_acc']; ?><br />
    БИК: <?= $company['bik']; ?>
</p>