<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\classes\Html;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsGroups;

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

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 3,
    'attributes' => [
        'code' => ['type' => Form::INPUT_TEXT,],
        'value' => ['type' => Form::INPUT_TEXT,],
        'group_id' => [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => ['' => '- Выбрать -'] + ArrayHelper::map(ImportantEventsGroups::find()->all(), 'id', 'title'),
            'options' => [
                'class' => 'select2',
            ],
        ],
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
                        'onClick' => 'self.location = "' . Url::toRoute(['important_events/names']) . '";',
                    ]) .
                    Html::submitButton('Сохранить', ['class' => 'btn btn-primary']),
                    ['style' => 'text-align: right; padding-right: 0px;']
                )
        ],
    ],
]);

ActiveForm::end();
?>