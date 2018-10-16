<?php
/**
 * Диапазон номеров. Триггер выключен
 *
 * @var app\classes\BaseView $this
 */

use kartik\form\ActiveForm;

?>


<div class="alert alert-warning">
    <p>
        Изменения не синхронизируются в биллер! <br>
        Можно выполнять массовые операции по изменению префиксов.<br>
        После этого обязательно надо полностью синхронизировать все данные в биллер.
    </p>
    <?php
    $form = ActiveForm::begin();
    echo $this->render('//layouts/_submitButton', [
        'text' => 'Синхронизировать NNP данные',
        'glyphicon' => 'glyphicon-off',
        'params' => [
            'name' => 'syncNnpAll',
            'value' => 1,
            'class' => 'btn btn-success',
            'aria-hidden' => 'true',
            'onClick' => sprintf('return confirm("%s");', 'Полностью синхронизировать все данные в биллер?'),
        ],
    ]);
    ActiveForm::end();
    ?>

</div>
