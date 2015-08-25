<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;

/** @var User $model */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>

<div class="row">
    <div class="col-sm-12">
        <h2>Изменение пароля</h2>

        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'password' => ['type' => Form::INPUT_PASSWORD],
                'passwordRepeat' => ['type' => Form::INPUT_PASSWORD],
                'passwordCurrent' => ['type' => Form::INPUT_PASSWORD],
            ],
        ]);
        ?>

        <div style="position: fixed; bottom: 0; right: 0px;">
            <?php
            echo Form::widget([
                'model' => $model,
                'form' => $form,
                'attributes' => [
                    'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
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
</div>

<?php
ActiveForm::end();
?>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('#dialog-close').click(function() {
        window.parent.$dialog.dialog('close');
    });
});
</script>
