<?php

use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$record = $formModel->record;

$this->title = Yii::t('common', 'Create');

if (!$record->isNewRecord) {
    $this->title = $record->name;
}

echo Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => 'Методы биллингования', 'url' => $cancelUrl = '/dictionary/city-billing-methods'],
        $this->title
    ],
]) ?>

<div class="well">
    <?php
    $form = ActiveForm::begin();
    $viewParams = [
        'formModel' => $formModel,
        'form' => $form,
    ];
    ?>

    <?php
    if ($formModel->validateErrors) {
        Yii::$app->session->setFlash('error', $formModel->validateErrors);
    }
    ?>

    <div class="row">

        <div class="col-sm-12">
            <?= $form->field($record, 'name')->textInput() ?>
        </div>

    </div>

    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($record->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?php if (!$record->isNewRecord) : ?>
            <?= $this->render('//layouts/_submitButtonDrop') ?>
        <?php endif ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
