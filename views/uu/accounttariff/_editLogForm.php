<?php
/**
 * Создание/редактирование универсальной услуги. Сменить/закрыть тариф
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="resource-tariff-form well">
    <?php $form = ActiveForm::begin(['id' => 'dynamic-form']); ?>

    <?= $this->render('_editLogInput', [
        'formModel' => $formModel,
        'form' => $form,
    ])
    ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('tariff', 'Change tariff'), ['class' => 'btn btn-success']) ?>
        <?= Html::submitButton(Yii::t('tariff', 'Close tariff'), ['class' => 'btn btn-danger', 'name' => 'closeTariff', 'id' => 'closeTariff']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script type='text/javascript'>
    $(function () {
        $("#closeTariff")
            .on("click", function (e, item) {
                return confirm("<?= Html::encode(Yii::t('tariff', 'Are you sure you want to close this tariff?')) ?>");
            });
    });
</script>