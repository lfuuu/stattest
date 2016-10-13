<?php
/** @var $document app\classes\documents\DocumentReport */
?>

<p>
    <b><?= $organization->name; ?></b><br />
    Adószám: <?= $organization->tax_registration_id; ?><br />
    Bankszámla:<br />
    <?= nl2br($organization->settlementAccount->bank_account); ?><br />
    <?= $organization->settlementAccount->bank_name; ?> SWIFT <?= $organization->settlementAccount->bank_bik; ?><br />
    Telefon: <?= $organization->contact_phone; ?><br />
    Fax: <?= $organization->contact_fax; ?><br />
    Postázási cím: <?= $organization->post_address; ?><br />
    Cégjegyzékszám: <?= $organization->registration_id; ?><br />
    Email cím: <?= $organization->contact_email; ?>
</p>