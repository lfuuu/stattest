<?php

use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\classes\Html;
?>

<div class="container" style="width: 100%; padding-top: 20px;">
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
        'action' => Url::toRoute(['/message/template/edit-template-content', 'template_id' => $template_id]),
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::label('Тип', null, ['class' => 'control-label']) .
                        Html::input('text', 'type', $type_descr['title'], ['class' => 'form-control', 'readonly' => true]) .
                        Html::hiddenInput($model->formName() . '[type]', $type),
                        ['class' => 'form-group']
                    ),
            ],
            [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::label('Язык', null, ['class' => 'control-label']) .
                        Html::input('text', 'lang_name', $language->name, ['class' => 'form-control', 'readonly' => true]) .
                        Html::hiddenInput($model->formName() . '[lang_code]', $language->code),
                        ['class' => 'form-group']
                    ),
            ],
            'empty1' => ['type' => Form::INPUT_RAW],
            'empty2' => ['type' => Form::INPUT_RAW],
        ]
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 1,
        'attributes' => [
            'content' => [
                'type' => Form::INPUT_TEXTAREA,
                'options' => [
                    'rows' => 20,
                    'class' => $type_descr['format'] != 'plain' ? 'editor' : '',
                ],
            ],
        ]
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::submitButton('Сохранить', ['class' => 'btn btn-success']),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);

    ActiveForm::end();
    ?>
</div>