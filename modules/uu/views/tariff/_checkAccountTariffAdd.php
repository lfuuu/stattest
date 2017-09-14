<?php
/**
 * Проверить возможность подключения текущему юзеру
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 * @var \app\models\ClientAccount $clientAccount
 */

use app\classes\Html;
use app\modules\uu\models\ServiceType;

if (!$clientAccount) {
    return;
}

$tariff = $formModel->tariff;
if ($tariff->isNewRecord) {
    return;
}

$errors = [];
if ($clientAccount->currency != $tariff->currency) {
    $errors[] = 'валюта не совпадает';
}

if (!array_key_exists($tariff->service_type_id, ServiceType::$packages)
    && $clientAccount->is_postpaid != $tariff->is_postpaid
) {
    $errors[] = 'предоплата/постоплата не совпадает';
}

if ($clientAccount->is_voip_with_tax != $tariff->is_include_vat) {
    $errors[] = 'с/без НДС не совпадает';
}

if ($tariff->service_type_id != ServiceType::ID_VOIP_PACKAGE_CALLS
    && !array_key_exists($clientAccount->contract->organization_id, $tariff->organizations)
) {
    $errors[] = 'организация не совпадает';
}

if ($errors) {
    echo Html::tag('div', 'Этот тариф не может быть подключен текущему ЛС, потому что ' . implode(', ', $errors), ['class' => 'alert alert-danger']);
} else {
    echo Html::tag('div', 'Этот тариф может быть подключен текущему ЛС', ['class' => 'alert alert-success']);
}
