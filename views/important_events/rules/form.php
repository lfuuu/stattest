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

echo Html::formLabel($model->id ? 'Редактирование правила' : 'Новое правило');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important_events/report'])],
        ['label' => 'Список правил на события', 'url' => Url::toRoute(['/important_events/rules'])],
        $model->id ? 'Редактирование правила' : 'Новое правило'
    ],
]);

$eventsList = ['' => '- Выбрать -'];

foreach (ImportantEventsNames::find()->all() as $event) {
    $eventsList[$event->group->title][$event->code] = $event->value;
}
?>

<div class="well">
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
    ]);
    echo Html::activeHiddenInput($model, 'id');
    ?>

    <div class="row">
        <div class="col-sm-3">
            <?= $form->field($model, 'title') ?>
            <?= $form->field($model, 'event')->dropDownList($eventsList, ['class' => 'select']) ?>
        </div>
        <div class="col-sm-3">
            <?= $form
                ->field($model, 'action')
                ->dropDownList(
                    ['' => '- Выбрать -'] + ArrayHelper::map(SendActionFactory::me()->getActions(), 'code', 'title'),
                    ['class' => 'select2']
                )
            ?>
            <?= $form
                ->field($model, 'message_template_id')
                ->dropDownList(
                    ['' => '- Выбрать -'] + ArrayHelper::map(MessageTemplate::find()->all(), 'id', 'name'),
                    ['class' => 'select2']
                )
            ?>
        </div>
        <div class="col-sm-6">
            <?= $form
                ->field($model, 'conditions')
                ->label('Условия')
                ->widget(MultipleInput::className(), [
                    'allowEmptyList' => true,
                    'enableGuessTitle' => true,
                    'columns' => [
                        [
                            'name' => 'property',
                            'title' => 'Свойство',
                            'enableError' => true,
                        ],
                        [
                            'name' => 'condition',
                            'type' => 'dropDownList',
                            'items' => ImportantEventsRulesConditions::$conditions,
                        ],
                        [
                            'name' => 'value',
                            'title' => 'Значение',
                        ],
                    ],
                ])
            ?>
        </div>
    </div>

    <div class="form-group">
        <?= $this->render('//layouts/_submitButtonSave') ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => Url::to(['important_events/rules'])]) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>

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