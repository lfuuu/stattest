<?php
/**
 * Диапазон номеров. Сброс привязки
 *
 * @var app\classes\BaseView $this
 */
use app\classes\Html;
use kartik\form\ActiveForm;

?>

<div class="well">
    <?php
    $form = ActiveForm::begin();
    $viewParams = [
        'form' => $form,
        'type' => ActiveForm::TYPE_INLINE,
    ];
    ?>

    Для всех отфильтрованных записей

    <?= Html::checkboxList(
        'resetOptions',
        null,
        [
            'operator' => 'операторов',
            'region' => 'регионов',
            'city' => 'городов',
        ],
        ['tag' => 'span']
    ) ?>

    <?= $this->render('//layouts/_submitButton', [
        'text' => 'отвязать их',
        'glyphicon' => 'glyphicon-refresh',
        'params' => [
            'class' => 'btn btn-warning',
        ],
    ]) ?>

    и автоматически привязать заново.

    <?php ActiveForm::end(); ?>
</div>
