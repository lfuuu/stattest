<?php
/**
 * кнопка сохранения
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 */

use app\classes\Html;

$tariff = $formModel->tariff;
?>

<div class="form-group">
    <?= Html::submitButton(Yii::t('common', $tariff->isNewRecord ? 'Create' : 'Save'), ['class' => 'btn btn-primary']) ?>
</div>
