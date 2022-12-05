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
?>

<div class="well">
    <h2>Комментарий</h2>
    <div class="row">
        <?= $form->field($tariff, 'comment')->textarea(['rows' => '2'])->label(false) ?>
    </div>
</div>
