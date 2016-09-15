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

<div class="container col-sm-12" style="float: none;">

    <div class="form-group col-sm-12">
        <label><?= $model->getAttributeLabel('title') ?></label>
        <input type="text" name="<?= $model->formName(); ?>[title]" class="form-control" value="<?= $model->title; ?>" />
    </div>

    <?php if ($file = $model->mediaManager->getFile()): ?>
        <div class="form-group col-sm-12">
            <div class="col-sm-4">
                <label>Используемый файл</label>
            </div>
            <div class="col-sm-8" style="text-align: right; padding-bottom: 5px;">
                <a href="<?= Url::toRoute(['/templates/email/template/download-template', 'countryId' => $model->country_id, 'templateId' => $model->template_id, 'langCode' => $model->lang_code]) ?>" class="btn btn-default">
                    <i class="glyphicon glyphicon-download"></i>
                    Выгрузить шаблон
                </a>
            </div>

            <div class="col-sm-12">
                <iframe
                    src="<?= Url::toRoute(['/templates/email/template/email-template-content', 'countryId' => $model->country_id, 'templateId' => $model->template_id, 'langCode' => $model->lang_code]); ?>"
                    scrolling="auto"
                    style="border: 1px solid #D0D0D0;"
                    width="100%"
                    height="500"
                ></iframe>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-group col-sm-12">
        <label>Укажите файл с содержанием</label>
        <div class="file_upload form-control input-sm">
            Выбрать файл <input type="file" name="<?= $model->formNameKey(); ?>_filename" class="media-manager" />
        </div>
        <div class="media-manager-block"></div>
    </div>

</div>
