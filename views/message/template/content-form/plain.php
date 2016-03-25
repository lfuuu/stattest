<?php

use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\classes\Html;

/** @var \app\models\message\TemplateContent $model */

$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
    'action' => Url::toRoute([
        '/message/template/edit-template-content',
        'templateId' => $templateId,
        'type' => $templateType,
        'langCode' => $templateLanguageCode,
    ]),
]);
?>

<div class="container col-xs-12" style="float: none;">
    <?= $form->field($model, 'content')->textarea(['rows' => 20, 'class' => 'form-control']); ?>

    <div class="form-group">
        <div style="text-align: right; padding-right: 0;">
            <input type="submit" value="Сохранить" class="btn btn-success" />
        </div>
    </div>

</div>

<?php
ActiveForm::end();