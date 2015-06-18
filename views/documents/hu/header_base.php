<?php
/** @var $document app\classes\documents\DocumentReport */

$company = $document->getCompany();
?>

<p>
    <b><?= $company['name']; ?></b><br />
    Adószám: <?= $company['inn']; ?><br />
    Bankszámla:<br />
    <?= nl2br($company['acc']); ?><br />
    <?= $company['bank']; ?><br />
    Telefon: <?= $company['phone']; ?><br />
    Fax: <?= $company['fax']; ?><br />
    Postázási cím: <?= $company['post_address']; ?><br />
    Cégjegyzékszám: <?= $company['reg_no']; ?><br />
    Cég email cím: <?= $company['email']; ?>
</p>