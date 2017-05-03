<?php

use yii\helpers\Url;

/**
 * @var \app\forms\templates\uu\InvoiceForm $model
 */
?>

<div class="container col-sm-12" style="float: none;">

    <?php if ($file = $model->fileExists()): ?>
        <div class="form-group col-sm-12">
            <div class="col-sm-4">
                <label>Используемый файл</label>
            </div>

            <div class="col-sm-8" style="text-align: right; padding-bottom: 5px;">
                <a href="<?= Url::toRoute(['/templates/uu/invoice/download-content', 'langCode' => $model->getLanguage()]) ?>" class="btn btn-default">
                    <i class="glyphicon glyphicon-download"></i>
                    Выгрузить шаблон
                </a>
            </div>

            <div class="col-sm-12">
                <iframe
                    src="<?= Url::toRoute(['/templates/uu/invoice/get-content', 'langCode' => $model->getLanguage()]) ?>"
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
            Выбрать файл <input type="file" name="<?= $model->formName(); ?>[filename][<?= $model->getLanguage() ?>]" class="media-manager" data-language="<?= $model->getLanguage() ?>" />
        </div>
        <div class="media-manager-block" data-language="<?= $model->getLanguage() ?>" style="padding: 10px;"></div>
    </div>

</div>