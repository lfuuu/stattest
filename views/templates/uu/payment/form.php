<?php

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use kartik\select2\Select2;
use yii\helpers\Url;

/**
 * @var \app\forms\templates\uu\PaymentForm $formModel
 * @var \yii\base\Widget $form
 */

$countryCode = $formModel->getCountryCode();

$model = $formModel->getTemplate() ? : $formModel->getTemplateDefault();
?>

<div class="container col-sm-12" style="float: none;">

    <?php if ($model): ?>
        <div class="form-group col-sm-12" id="<?='model-' . $countryCode?>">
            <div class="col-sm-3">
                <label>Версия<?php if ($model->is_default): ?>
                        <span class="text-success"> (по умолчанию)</span>
                    <?php endif; ?>
                    <?php if (!$model->is_active): ?>
                        <span class="error"> (не используется)</span>
                    <?php endif; ?>:</label>

                <?php
                    echo $form->field($model, 'id')->widget(Select2::class, [
                        'data' => $model->getAllVersionList(),
                        'options' => [
                            'id' => 'version-' . $countryCode,
                            'name' => 'version-' . $countryCode,
                        ],
                        'pluginEvents' => [
                            'change' => 'function() {
                                var id = $(this).val();
                                if (id > 0) {
                                    location.href = "' . '/templates/uu/payment' . '?id=" + id;
                                } else {
                                    $("#model-' . $countryCode . '").addClass("hidden");
                                }
                            }',
                        ],
                    ])->label(false);
                ?>
            </div>

            <div class="col-sm-2" style="text-align: right;">
                Дата создания:<br />
                Дата последней правки:<br />
                Изменил:
            </div>

            <div class="col-sm-2">
                <?= DateTimeZoneHelper::getDateTime($model->created_at) ?><br />
                <?= DateTimeZoneHelper::getDateTime($model->updated_at) ?><br />
                <?= $model->updatedBy->user ?>
            </div>

            <div class="col-sm-2">
                <?php if ($model->is_default): ?>
                    <br />
                    <?php
                        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']);
                        echo Html::tag('b', ' По умолчанию');
                    ?>
                <?php else: ?>
                    <a href="<?= Url::toRoute([
                        '/templates/uu/payment/set-default',
                        'id' => $model->id,
                    ]) ?>" class="btn btn-info">
                        <i class="glyphicon glyphicon-plus"></i>
                        По умолчанию
                    </a>
                <?php endif; ?>
            </div>

            <div class="col-sm-1">
                <?php if ($model->is_active): ?>
                    <a href="<?= Url::toRoute([
                        '/templates/uu/payment/delete',
                        'id' => $model->id,
                    ]) ?>" onclick="return confirm('Вы действительно хотите удалить шаблон?');" class="btn btn-info">
                        <i class="glyphicon glyphicon-remove-circle"></i>
                        Удалить
                    </a>
                <?php else: ?>
                    <a href="<?= Url::toRoute([
                        '/templates/uu/payment/restore',
                        'id' => $model->id,
                    ]) ?>" class="btn btn-success">
                        <i class="glyphicon glyphicon-remove-circle"></i>
                        Восстановить
                    </a>
                <?php endif; ?>


            </div>

            <div class="col-sm-2" style="text-align: right; padding-bottom: 5px;">
                <a href="<?= Url::toRoute([
                    '/templates/uu/payment/download-content',
                    'id' => $model->id,
                ]) ?>" class="btn btn-info">
                    <i class="glyphicon glyphicon-download"></i>
                    Выгрузить шаблон
                </a>
            </div>

            <div class="col-sm-12">
                <iframe
                    src="<?= Url::toRoute([
                        '/templates/uu/payment/get-content',
                        'id' => $model->id,
                    ]) ?>"
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
            Выбрать файл <input type="file" name="<?= $formModel->formName(); ?>[filename][<?= $formModel->getCountryCode() ?>]" class="media-manager" data-country="<?= $formModel->getCountryCode() ?>" />
        </div>
        <div class="media-manager-block" data-country="<?= $formModel->getCountryCode() ?>" style="padding: 10px;"></div>
    </div>

</div>