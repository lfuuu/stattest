<?php

use yii\helpers\Html;
use app\helpers\FileHelper;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\forms\person\PersonForm;

/** @var $model PersonForm */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);
?>

<h2>Персона</h2>

<div class="container well" style="width: 100%; padding-top: 20px;">
    <fieldset style="width: 100%;">
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'name_nominativus');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'name_genitivus');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'post_nominativus');
                    ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'post_genitivus');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'signature_file_name')
                        ->dropDownList(
                            FileHelper::findByPattern('SIGNATURE_DIR', '*.{gif,png,jpg,jpeg}', 'assoc'),
                            [
                                'prompt' => 'Выбрать подпись',
                            ]
                        )
                        ->label('Подпись');
                    ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label>Предпросмотр подписи</label>
                        <div id="full_signature_file_name" class="image_preview"></div>
                    </div>
                </div>
            </div>
        </div>

        <div style="height: 25px;">&nbsp;</div>
    </fieldset>

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
                        Html::button('Отменить', [
                            'class' => 'btn btn-link modal-form-close',
                            'style' => 'margin-right: 15px;',
                            'id' => 'dialog-close',
                        ]) .
                        Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) .
                    '</div>'
            ],
        ],
    ]);
    ActiveForm::end();
    ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('select[name="PersonForm[signature_file_name]"')
        .change(function() {
            var $value = $(this).find('option:selected').val(),
                $image = $('<img />').attr('src', $value);

            $('#full_signature_file_name').html($value !== '' ? $image : '');
        })
        .trigger('change');

    $('form:eq(0)').submit(function(e){
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: $(this).serialize(),
            dataType: 'json',
            async: false,
            success: function() {
                $('#dialog-close').trigger('click');
            }
        });
        return false;
    });

    $('#dialog-close').click(function() {
        window.parent.$dialog.dialog('close');
    });

    window.parent.$dialog.on('dialogclose', function(event, ui) {
        window.parent.$.pjax.reload({container: '#PersonList'});
    });
});
</script>

<style type="text/css">
    .image_preview {
        position: relative;
        border: 1px solid;
        background-color: #FFFFFF;
        text-align: center;
        vertical-align: bottom;
        width: 250px;
        height: 250px;
        margin: 0 auto;
        overflow: hidden;
    }
    .image_preview img {
        position: absolute;
        margin: auto;
        top: -200px;
        bottom: -200px;
        left: -200px;
        right: -200px;
    }
</style>