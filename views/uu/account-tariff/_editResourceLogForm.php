<?php
/**
 * Создание/редактирование универсальной услуги. Сменить количество ресурсов
 *
 * @var \app\classes\BaseView $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var bool $isReadOnly
 */

use yii\widgets\ActiveForm;
?>

<?php $form = ActiveForm::begin(); ?>

    <?= $this->render('_editResourceLogInput', [
        'formModel' => $formModel,
        'form' => $form,
        'isReadOnly' => $isReadOnly,
    ])
    ?>

<?php ActiveForm::end(); ?>
