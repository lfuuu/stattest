<?php
/**
 * Диапазон номеров. Конвертировать фильтры в префиксы
 *
 * @var app\classes\BaseView $this
 */
use kartik\form\ActiveForm;

?>


<div class="well">
    <?php
    $form = ActiveForm::begin();
    $viewParams = [
        'form' => $form,
    ];
    ?>

    <?= $this->render('//layouts/_submitButton', [
        'text' => 'Конвертировать фильтры в префиксы',
        'glyphicon' => 'glyphicon-repeat',
        'params' => [
            'name' => 'filterToPrefixButton',
            'value' => 1,
            'class' => 'btn btn-warning',
            'aria-hidden' => 'true',
            'onClick' => sprintf('return confirm("%s");', 'Все префиксы будут заменены текущими диапазонами на основе фильтров. Это необратимо. Продолжить?'),
        ],
    ]) ?>
    <?php ActiveForm::end(); ?>

</div>
