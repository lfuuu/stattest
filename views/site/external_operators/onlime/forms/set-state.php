<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;

/** @var RequestOnlimeStateForm $model */
$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>

<legend>
    Изменение состояния
</legend>

<div class="well" style="padding-top: 60px;">

    <?php
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'comment' => ['type' => Form::INPUT_TEXTAREA],
            'state_id' => ['type' => Form::INPUT_TEXT],
        ],
    ]);
    ?>

    <div style="position: fixed; bottom: 0; right: 0;">
        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'attributes' => [
                'actions' => [
                    'type' => Form::INPUT_RAW,
                    'value' =>
                        '<div class="col-md-12" style="text-align: right; padding-right: 0px;">' .
                            Html::button('Отмена', [
                                'class' => 'btn btn-link',
                                'id' => 'dialog-close',
                                'style' => 'width: 100px; margin-right: 15px;',
                            ]) .
                            Html::submitButton('OK', [
                                'class' => 'btn btn-primary',
                                'style' => 'width: 100px;',
                            ]) .
                        '</div>'
                ],
            ],
        ]);
        ?>
    </div>
</div>

<?php
ActiveForm::end();
?>

<script type="text/javascript">
    jQuery(document).ready(function() {
        $('#dialog-close').click(function() {
            window.parent.location.reload(true);
            window.parent.$dialog.dialog('close');
        });
    });
</script>