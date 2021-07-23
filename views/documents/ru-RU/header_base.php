<?php
use app\models\ClientContact;

$contacts = ClientContact::find()->andWhere([
    'client_id' => $payer_company->id,
    'is_official' => 1,
    'type' => ClientContact::TYPE_FAX,
])->indexBy('id')->all() ?: [];

?>

<p>
    <b>Поставщик: <?= $organization->name; ?></b><br />
    ИНН: <?= $organization->tax_registration_id; ?>;  КПП: <?= $organization->tax_registration_reason; ?><br />
    Адрес: <?= $organization->legal_address; ?><br />
    Телефон: <?= $organization->contact_phone; ?><br />
    Факс: <?= $organization->contact_fax; ?><br />
    р/с: <?= $organization->settlementAccount->bank_account; ?> в <?= $organization->settlementAccount->bank_name; ?><br />
    к/с: <?= $organization->settlementAccount->bank_correspondent_account; ?><br />
    БИК: <?= $organization->settlementAccount->bank_bik; ?>
</p>