<?php

/** @var \app\forms\dictonary\tags\TagsForm $formModel */

use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$model = $formModel->model;
$this->title = !$model->isNewRecord ?
    $model->name :
    Yii::t('common', 'Create');

$currentStep = (!$model->isNewRecord ? 'Редактирование метки "' . $this->title . '"' : 'Новая метка');
echo Breadcrumbs::widget([
    'links' => [
        'Справочники',
        ['label' => 'Метки', 'url' => $cancelUrl = Url::toRoute(['/dictionary/tags'])],
        $currentStep,
    ],
]);

$tagsUsedInto = $formModel->resourcesMap($model->resourceNames);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
    ]);
    $form->field($model, 'id')->hiddenInput()->label(null);
    ?>

    <div class="row">
        <div class="col-sm-6">
            <?= $form
                ->field($model, 'name')
                ->textInput()
            ?>
        </div>

        <?php if (!empty($tagsUsedInto)) : ?>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label">Используется</label>
                    <div>
                        <?= $tagsUsedInto ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="pull-left">
        <?= $this->render('//layouts/_submitButton', [
            'text' => Yii::t('common', 'Drop'),
            'glyphicon' => 'glyphicon-trash',
            'params' => [
                'name' => 'dropButton',
                'value' => 1,
                'class' => 'btn btn-danger',
                'aria-hidden' => 'true',
                'onClick' => sprintf('return confirm("%s");', Yii::t('common', "Are you sure? It's irreversibly.")),
            ],
        ]) ?>
    </div>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . (!$model->primaryKey ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>