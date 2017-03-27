<?php
/**
 * Диапазон номеров. Триггер выключен
 *
 * @var app\classes\BaseView $this
 */
use kartik\form\ActiveForm;

?>


<div class="alert alert-danger">
    <p>
        Триггер выключен, изменения не синхронизируются в биллер! Можно выполнять массовые операции по изменению префиксов.
        После этого обязательно надо полностью синхронизировать все данные в биллер (это очень ресурсоемкая операция, поэтому выполнять можно не более 1-2 раза в день!) и включить триггер.
    </p>
    <?php
    $form = ActiveForm::begin();
    $viewParams = [
        'form' => $form,
    ];
    ?>

    <?= $this->render('//layouts/_submitButton', [
        'text' => 'Синхронизировать данные в биллер и включить триггер',
        'glyphicon' => 'glyphicon-off',
        'params' => [
            'name' => 'enableTriggerButton',
            'value' => 1,
            'class' => 'btn btn-success',
            'aria-hidden' => 'true',
            'onClick' => sprintf('return confirm("%s");', 'Полностью синхронизировать все данные в биллер? Это очень ресурсоемкая операция, поэтому выполнять можно не более 1-2 раза в день!'),
        ],
    ]) ?>
    <?php ActiveForm::end(); ?>

</div>
