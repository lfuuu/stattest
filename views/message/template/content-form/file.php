<?php

use yii\helpers\Url;

/**
 * @var \app\models\message\TemplateContent $model
 * @var int $templateCountryId
 * @var int $templateId
 * @var string $templateLanguageCode
 * @var string $templateType
 */
?>

<div class="container col-xs-12" style="float: none;">

    <div class="form-group">
        <label><?= $model->getAttributeLabel('title') ?></label>
        <input type="text" name="<?= $model->formName(); ?>[title]" class="form-control" value="<?= $model->title; ?>" />
    </div>

    <?php if ($file = $model->mediaManager->getFile()): ?>
        <div class="form-group">
            <label>Используемый файл</label><br />
            <iframe
                src="<?= Url::toRoute(['/message/template/email-template-content', 'countryId' => $model->country_id, 'templateId' => $model->template_id, 'langCode' => $model->lang_code]); ?>"
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
            Выбрать файл <input type="file" name="<?= $model->formNameKey(); ?>_filename" class="media-manager" />
        </div>
        <div class="media-manager-block"></div>
    </div>

</div>