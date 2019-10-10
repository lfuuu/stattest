<?php

use kartik\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\classes\Html;
use app\models\important_events\ImportantEventsRules;

/** @var ImportantEventsRules $model */

echo Html::formLabel($model->id ? 'Редактирование группы' : 'Новая группа');
echo Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important_events/report'])],
        ['label' => 'Названия событий', 'url' => Url::toRoute(['/important_events/names'])],
        ['label' => 'Список групп', 'url' => Url::toRoute(['/important_events/groups'])],
        $model->id ? 'Редактирование группы' : 'Новая группа'
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
        <div class="col-sm-12">
            <?= $form->field($model, 'title') ?>
        </div>
    </div>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => Url::to(['important_events/groups'])]) ?>
        <?= $this->render('//layouts/_submitButtonSave') ?>
    </div>

    <?php ActiveForm::end() ?>
</div>