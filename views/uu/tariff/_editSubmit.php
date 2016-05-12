<?php
/**
 * кнопка сохранения
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\TariffForm $formModel
 * @var \yii\widgets\ActiveForm $form
 */

use app\classes\Html;
use yii\helpers\Url;

$tariff = $formModel->tariff;
?>

<div class="form-group">

    <?= Html::submitButton(
        Html::tag('i', '', [
            'class' => 'glyphicon glyphicon-save',
            'aria-hidden' => 'true',
        ]) . ' ' .
        Yii::t('common', $tariff->isNewRecord ? 'Create' : 'Save'),
        [
            'class' => 'btn btn-primary',
        ]) ?>

    <?= Html::a(
        Html::tag('i', '', [
            'class' => 'glyphicon glyphicon-level-up',
            'aria-hidden' => 'true',
        ]) . ' ' .
        Yii::t('common', 'Cancel'),
        Url::to(['uu/tariff', 'serviceTypeId' => $formModel->tariff->service_type_id]),
        [
            'class' => 'btn btn-link btn-cancel',
        ]
    ) ?>
</div>
