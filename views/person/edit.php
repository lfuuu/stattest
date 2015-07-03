<?php

use yii\helpers\Html;
use app\helpers\MediaFileHelper;
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
                    echo $form->field($model, 'name_nominative');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'name_genitive');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'post_nominative');
                    ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'post_genitive');
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
                            MediaFileHelper::findByPattern('SIGNATURE_DIR', '*.{gif,png,jpg,jpeg}', 'assoc'),
                            [
                                'prompt' => 'Выбрать подпись',
                                'data-source' => Yii::$app->params['SIGNATURE_DIR'],
                                'data-target' => '#full_signature_file_name',
                                'class' => 'image_preview_select',
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
                            'onClick' => 'self.location = "/person";',
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
    $('.image_preview_select')
        .change(function() {
            var $source = $(this).data('source'),
                $value = $(this).find('option:selected').val(),
                $image = ($value != '' ? $('<img />').attr('src', $source + $value) : false);

            if ($(this).data('target'))
                $($(this).data('target')).html($value ? $image : '');
        })
        .trigger('change');
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