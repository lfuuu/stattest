<?php
use app\models\ClientContact;
use yii\helpers\ArrayHelper;

$contacts = ClientContact::find()->andWhere([
    'client_id' => $payer_company->id,
    'is_official' => 1,
    'is_active' => 1,
    'type' => ClientContact::TYPE_FAX,
])->indexBy('id')->all() ?: [];

?>

<p>
    Адрес доставки счета: <?= $payer_company['address_post']; ?><br />
    Факс для отправки счета:
    <?= implode('; ', $contacts); ?>
</p>

<p>
    <b>Поставщик: <?= $organization->name; ?></b><br />
    ИНН: <?= $organization->tax_registration_id; ?>;  КПП: <?= $organization->tax_registration_reason; ?><br />
    Адрес: <?= $organization->legal_address; ?><br />
    Телефон: <?= $organization->contact_phone; ?><br />
    Факс: <?= $organization->contact_fax; ?><br />
    р/с: <?= $organization->settlementAccount->getBankAccount(); ?> в <?= $organization->settlementAccount->bank_name; ?><br />
    к/с: <?= $organization->settlementAccount->bank_correspondent_account; ?><br />
    БИК: <?= $organization->settlementAccount->bank_bik; ?>
</p>