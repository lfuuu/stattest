<?php
/**
 * Создание/редактирование универсальной услуги. Сменить/закрыть тариф
 *
 * @var \app\classes\BaseView $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 */

use yii\widgets\ActiveForm;

?>

<?php $form = ActiveForm::begin(); ?>

<?= $this->render('_editLogInput', [
    'formModel' => $formModel,
    'form' => $form,
])
?>

<?php ActiveForm::end(); ?>
