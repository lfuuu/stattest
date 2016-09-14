<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use unclead\widgets\MultipleInput;
use app\classes\Html;
use app\models\important_events\ImportantEventsRules;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsRulesConditions;
use app\models\message\Template as MessageTemplate;
use app\classes\actions\message\SendActionFactory;

/** @var ImportantEventsRules $model */

echo Html::formLabel($model->id ? 'Редактирование источника' : 'Новый источник');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important_events/report'])],
        ['label' => 'Список источников событий', 'url' => Url::toRoute(['/important_events/sources'])],
        $model->id ? 'Редактирование источника' : 'Новый источник'
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
        <div class="col-sm-6">
            <?= $form->field($model, 'code') ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'title') ?>
        </div>
    </div>

    <div class="form-group">
        <?= $this->render('//layouts/_submitButtonSave') ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => Url::to(['important_events/sources'])]) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>