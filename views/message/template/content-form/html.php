<?php

use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\classes\Html;

/** @var \app\models\message\TemplateContent $model */
?>

<div class="container col-xs-12" style="float: none;">
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
        'action' => Url::toRoute([
            '/message/template/edit-template-content',
            'templateId' => $templateId,
            'type' => $templateType,
            'langCode' => $templateLanguageCode,
        ]),
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'title' => ['type' => Form::INPUT_TEXT],
        ]
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'content' => [
                'type' => Form::INPUT_TEXTAREA,
                'options' => [
                    'rows' => 20,
                    'class' => 'form-control editor',
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