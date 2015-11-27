<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use unclead\widgets\MultipleInput;
use app\classes\Html;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsRulesConditions;
use app\models\message\Template as MessageTemplate;
use app\classes\actions\message\SendActionFactory;

/** @var Template $model */

echo Html::formLabel($model->id ? 'Редактирование правила' : 'Новое правило');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important-events'])],
        ['label' => 'Список правил на события', 'url' => Url::toRoute(['/important-events/rules'])],
        $model->id ? 'Редактирование правила' : 'Новое правило'
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
    'columns' => 4,
    'attributes' => [
        'title' => ['type' => Form::INPUT_TEXT,],
        'action' => [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => ['' => '- Выбрать -'] + ArrayHelper::map(SendActionFactory::me()->getActions(), 'code', 'title'),
            'options' => ['class' => 'select2'],
        ],
        'event' => [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => ImportantEvents::dao()->getList(true),
            'options' => ['class' => 'select2'],
        ],
        'message_template_id' => [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => ['' => '- Выбрать -'] + ArrayHelper::map(MessageTemplate::find()->all(), 'id', 'name'),
            'options' => ['class' => 'select2']
        ],
    ]
]);

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 4,
    'attributes' => [
        'conditions' => [
            'label' => 'Условия',
            'type' => Form::INPUT_WIDGET,
            'widgetClass' => MultipleInput::className(),
            'options' => [
                'allowEmptyList'    => false,
                'enableGuessTitle'  => true,
                'columns' => [
                    [
                        'name'  => 'property',
                        'title' => 'Свойство',
                        'enableError' => true,
                    ],
                    [
                        'name'  => 'condition',
                        'type'  => 'dropDownList',
                        'items' => ImportantEventsRulesConditions::$conditions,
                    ],
                    [
                        'name'  => 'value',
                        'title' => 'Значение',
                    ],
                ],
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
                        'onClick' => 'self.location = "' . Url::toRoute(['important-events/rules']) . '";',
                    ]) .
                    Html::submitButton('Сохранить', ['class' => 'btn btn-primary']),
                    ['style' => 'text-align: right; padding-right: 0px;']
                )
        ],
    ],
]);

ActiveForm::end();
?>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('button.append-block-btn')
        .on('click', function() {
            var parent = $(this).parents('fieldset'),
                block = parent.clone(true);
            block.find('input').val('');
            block.find('select').find('option:eq(0)').prop('selected', true);
            block.insertAfter(parent);
        });
});
</script>