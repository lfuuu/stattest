<?php

use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\classes\Html;

/** @var \app\models\message\TemplateContent $model */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
    'options' => [
        'enctype' => 'multipart/form-data',
    ],
    'action' => Url::toRoute([
        '/message/template/edit-template-content',
        'templateId' => $templateId,
        'type' => $templateType,
        'langCode' => $templateLanguageCode,
    ]),
]);
?>

<div class="container col-xs-12" style="float: none;">

    <input type="hidden" name="<?= $model->formName(); ?>[scenario]" value="file" />

    <?= $form->field($model, 'title')->textInput(); ?>

    <?php if ($file = $model->mediaManager->getFile()): ?>
        <div class="form-group">
            <label>Используемый файл</label><br />
            <iframe
                src="<?= Url::toRoute(['/message/template/email-template-content', 'templateId' => $templateId, 'langCode' => $templateLanguageCode]) ?>"
                scrolling="auto"
                style="border: 1px solid #D0D0D0;"
                width="100%"
                height="500"
            ></iframe>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <label>Укажите файл с содержанием</label>
        <div class="file_upload form-control input-sm">
            Выбрать файл <input type="file" name="<?= $model->formName(); ?>[filename]" class="media-manager" />
        </div>
        <div class="media-manager-block"></div>
    </div>

    <div class="form-group">
        <div style="text-align: right; padding-right: 0;">
            <input type="submit" value="Сохранить" class="btn btn-success" />
        </div>
    </div>

</div>

<?php
ActiveForm::end();