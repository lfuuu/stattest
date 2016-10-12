<?php
/** @var \kartik\widgets\ActiveForm $form */
/** @var int $typeId */
/** @var string $currency */
/** @var string $label */
/** @var string $value */

$currencySymbol =
    \app\models\Currency::find()
        ->select('symbol')
        ->where(['id' => $currency])
        ->scalar();
?>

<div class="col-sm-12">
    <?= $form
        ->field($model, 'bank_account_' . $currency . '[' . $typeId . ']', [
            'addon' => ['prepend' => ['content' => $currencySymbol]],
        ])
        ->textInput(['value' => (string)$model])
        ->label($label)
    ?>
</div>