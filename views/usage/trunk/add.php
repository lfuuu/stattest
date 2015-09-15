<?php
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use app\models\Region;
use app\models\billing\Trunk;

/** @var $clientAccount \app\models\ClientAccount */
/** @var $model \app\forms\usage\UsageTrunkEditForm */

$trunks = ['' => '-- Выберите Транк -- '] + Trunk::dao()->getList($model->connection_point_id);

echo Html::formLabel('Добавление транка');
echo Breadcrumbs::widget([
    'links' => [
        'homeLink' => [
            'label' => $clientAccount->company,
            'url' => ['client/view', 'id' => $clientAccount->id]
        ],
        ['label' => 'Телефония Транки', 'url' => Url::toRoute(['/', 'module' => 'services', 'action' => 'trunk_view'])],
        'Добавление транка'
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'connection_point_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> Region::dao()->getList(true, $clientAccount->country_id), 'options' => ['class' => 'select2 form-reload']],
            ['type' => Form::INPUT_RAW, 'value' => '
                <div class="form-group">
                    <label class="control-label">Страна</label>
                    <input type="text" class="form-control" value="'. $clientAccount->country->name .'" readonly>
                </div>
            '],
            ['type' => Form::INPUT_RAW, 'value' => '
                <div class="form-group">
                    <label class="control-label">Валюта</label>
                    <input type="text" class="form-control" value="'. $clientAccount->currency .'" readonly>
                </div>
            '],
        ],
    ]);


    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'trunk_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $trunks, 'options' => ['class' => 'select2']],
            'actual_from' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className()],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            '' => ['type' => Form::INPUT_RAW],
            'orig_enabled' => ['type' => Form::INPUT_CHECKBOX],
            'orig_min_payment' => ['type' => Form::INPUT_TEXT],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            '' => ['type' => Form::INPUT_RAW],
            'term_enabled' => ['type' => Form::INPUT_CHECKBOX],
            'term_min_payment' => ['type' => Form::INPUT_TEXT],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            '' => ['type' => Form::INPUT_RAW],
            'description' => ['type' => Form::INPUT_TEXT],
            'operator_id' => ['type' => Form::INPUT_TEXT],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::button('Отменить', [
                            'class' => 'btn btn-link',
                            'style' => 'margin-right: 15px;',
                            'onClick' => 'self.location = "' . Url::toRoute(['/', 'module' => 'services', 'action' => 'trunk_view']) . '";',
                        ]) .
                        Html::button('Подключить', ['class' => 'btn btn-primary', 'onClick' => "submitForm('add')"]),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);

    echo Html::hiddenInput('scenario', 'default', ['id' => 'scenario']);
    ActiveForm::end();
    ?>
</div>
<script>
    function submitForm(scenario) {
        $('#scenario').val(scenario);
        $('#<?=$form->getId()?>')[0].submit();
    }
    $('.form-reload').change(function() {
        submitForm('default');
    });
</script>