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

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 2,
    'attributes' => [
        'code' => ['type' => Form::INPUT_TEXT,],
        'title' => ['type' => Form::INPUT_TEXT,],
    ]
]);

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'attributes' => [
        'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
        'actions' => [
            'type' => Form::INPUT_RAW,
            'value' =>
                Html::tag(
                    'div',
                    Html::button('Отменить', [
                        'class' => 'btn btn-link',
                        'style' => 'margin-right: 15px;',
                        'onClick' => 'self.location = "' . Url::toRoute(['important_events/sources']) . '";',
                    ]) .
                    Html::submitButton('Сохранить', ['class' => 'btn btn-primary']),
                    ['style' => 'text-align: right; padding-right: 0px;']
                )
        ],
    ],
]);

ActiveForm::end();
?>