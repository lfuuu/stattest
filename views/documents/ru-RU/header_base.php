<?php

use app\models\ClientContact;

$contacts = ClientContact::find()->andWhere([
    'client_id' => $payer_company->id,
    'is_official' => 1,
    'type' => ClientContact::TYPE_FAX,
])->indexBy('id')->all() ?: [];

?>

<p>
    <b>Поставщик: <?= $organization->name; ?></b><br/>
    ИНН: <?= $organization->tax_registration_id; ?>; КПП: <?= $organization->tax_registration_reason; ?><br/>
    Адрес: <?= $organization->legal_address; ?><br/>
    Телефон: <?= $organization->contact_phone; ?><br/>
    Факс: <?= $organization->contact_fax; ?><br/>
    <?php if ($payer_company->id == 138949) : ?>
        р/с: 40702810200000008372 в АО «Всероссийский банк развития регионов»<br/>
        к/с: 30101810900000000880<br/>
        БИК: 044525880
    <?php else: ?>
        р/с: <?= $organization->settlementAccount->bank_account; ?> в <?= $organization->settlementAccount->bank_name; ?>
        <br/>
        к/с: <?= $organization->settlementAccount->bank_correspondent_account; ?><br/>
        БИК: <?= $organization->settlementAccount->bank_bik; ?>
    <?php endif; ?>
</p>