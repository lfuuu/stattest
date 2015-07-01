<?php
use app\models\ClientContact;

/** @var $document app\classes\documents\DocumentReport */

$contact = ClientContact::dao()->GetContact($document->bill->clientAccount->id, true);

$organization = $document->getOrganization();

$payer_company = $document->getPayer();
?>

<p>
Адрес доставки счета: <?= $payer_company['address_post']; ?><br />
Факс для отправки счета:
<?php foreach ($contact['fax'] as $position => $item ): ?>
    <?php if ($position > 0): ?>; <?php endif; ?>
    <?= $item['data']; ?>
<?php endforeach; ?>
</p>

<p>
    <b>Поставщик: <?= $organization->name; ?></b><br />
    ИНН: <?= $organization->tax_registration_id; ?>;  КПП: <?= $organization->tax_registration_reason; ?><br />
    Адрес: <?= $organization->legal_address; ?><br />
    Телефон: <?= $organization->contact_phone; ?><br />
    Факс: <?= $organization->contact_fax; ?><br />
    р/с: <?= $organization->bank_account; ?> в <?= $organization->bank_name; ?><br />
    к/с: <?= $organization->bank_correspondent_account; ?><br />
    БИК: <?= $organization->bank_bik; ?>
</p>