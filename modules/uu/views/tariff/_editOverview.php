<?php
/**
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 * @var int $editableType
 */

use app\classes\Html;
use app\modules\uu\models\AccountLogResource;

$tariff = $formModel->tariff;

if ($editableType <= \app\modules\uu\controllers\TariffController::EDITABLE_LIGHT) {
    $options = ['disabled' => 'disabled'];
} else {
    $options = [];
}

?>

<div class="well tariffResources">
    <h2>Описание тарифа</h2>
    <div class="row">
        <?= $form->field($tariff, 'overview')->textarea(['rows' => '10'] + $options)->label(false) ?>
    </div>
</div>
