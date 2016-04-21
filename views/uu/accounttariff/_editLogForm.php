<?php
/**
 * Создание/редактирование универсальной услуги. Сменить/закрыть тариф
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 */

use yii\widgets\ActiveForm;

?>

<?php $form = ActiveForm::begin(); ?>
<div class="resource-tariff-form well">

    <?= $this->render('_editLogInput', [
        'formModel' => $formModel,
        'form' => $form,
    ])
    ?>

</div>
<?php ActiveForm::end(); ?>
