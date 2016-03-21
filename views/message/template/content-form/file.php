<?php

use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\classes\Html;

/** @var \app\models\message\TemplateContent $model */
?>

<div class="container" style="width: 100%; padding-top: 20px;">
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
        'options' => [
            'enctype' => 'multipart/form-data',
        ],
        'action' => Url::toRoute(['/message/template/edit-template-content']),
    ]);

    echo Html::hiddenInput($model->formName() . '[template_id]', $templateId);
    echo Html::hiddenInput($model->formName() . '[type]', $templateType);
    echo Html::hiddenInput($model->formName() . '[lang_code]', $templateLanguageCode);

    if ($file = $model->mediaManager->getFile()) {
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'attributes' => [
                [
                    'type' => Form::INPUT_RAW,
                    'value' => function() use ($file) {
                        return
                            Html::tag('label', 'Устновлен файл') .
                            Html::beginTag('div', ['class' => 'input-sm']) .
                                $file['name'] . ' (' . $file['size'] . ' b)' .
                            Html::endTag('div');
                    }
                ],
            ]
        ]);
    }

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            [
                'type' => Form::INPUT_RAW,
                'value' => function() use ($model) {
                    return
                        Html::tag('label', 'Укажите файл с содержанием') .
                        Html::beginTag('div', ['class' => 'file_upload form-control input-sm']) .
                            'Выбрать файл' .
                            Html::fileInput($model->formName() . '[file]', '', ['class' => 'media-manager']) .
                        Html::endTag('div') .
                        Html::tag('div', '', ['class' => 'media-manager-block']);
                }
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