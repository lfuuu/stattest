<?php

use app\classes\Html;
use app\models\important_events\ImportantEventsGroups;
use app\models\important_events\ImportantEventsNames;
use app\widgets\TagsSelect2\TagsSelect2;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var ImportantEventsNames $model */

echo Html::formLabel($model->code ? 'Редактирование названия' : 'Новое название');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important_events/report'])],
        ['label' => 'Список названий событий', 'url' => Url::toRoute(['/important_events/names'])],
        $model->code ? 'Редактирование названия' : 'Новое название'
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
    ]);
    echo Html::activeHiddenInput($model, 'id');
    ?>

    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($model, 'code') ?>
        </div>

        <div class="col-sm-4">
            <?= $form->field($model, 'value') ?>
        </div>

        <div class="col-sm-4">
            <?= $form
                ->field($model, 'group_id')
                ->dropDownList(
                    ImportantEventsGroups::getList($isWithEmpty = true),
                    ['class' => 'select2']
                )
            ?>
        </div>
    </div>

    <?php if ($model->id): ?>
        <div class="row">
            <div class="col-sm-6">
                <?= TagsSelect2::widget([
                    'model' => $model,
                    'attribute' => 'tags',
                ]) ?>
            </div>
            <div class="col-sm-6">
                <?= $form->field($model, 'comment') ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <div class="row">
            <div class="col-sm-3">
                <?= $this->render('//layouts/_showHistory', ['model' => $model]) ?>
            </div>
            <div class="col-sm-9 text-right">
                <?= $this->render('//layouts/_buttonCancel', ['url' => Url::to(['important_events/names'])]) ?>
                <?= $this->render('//layouts/_submitButtonSave') ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end() ?>