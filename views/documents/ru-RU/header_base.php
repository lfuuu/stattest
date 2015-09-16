<?php
use app\models\ClientContact;
use yii\helpers\ArrayHelper;

/** @var $document app\classes\documents\DocumentReport */

$contacts = ClientContact::find()->andWhere([
    'client_id' => $document->bill->clientAccount->id,
    'is_official' => 1,
    'is_active' => 1,
    'type' => ClientContact::TYPE_FAX,
]);
$contacts = ArrayHelper::map($contacts, 'id', 'data');
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
    р/с: <?= $organization->bank_account; ?> в <?= $organization->bank_name; ?><br />
    к/с: <?= $organization->bank_correspondent_account; ?><br />
    БИК: <?= $organization->bank_bik; ?>
</p>