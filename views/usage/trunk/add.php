<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use app\models\Region;

/** @var $clientAccount \app\models\ClientAccount */
/** @var $model \app\forms\usage\UsageTrunkEditForm */

?>

<legend>
    <?= Html::a($clientAccount->company, '/?module=clients&id='.$clientAccount->id) ?> ->
    <?= Html::a('Телефония Транки', '/?module=services&action=trunk_view') ?> ->
    Добавление Транка
</legend>
<?php
$form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 3,
    'attributes' => [
        'connection_point_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> Region::dao()->getList(true, $clientAccount->country_id), 'options' => ['class' => 'select2']],
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
    'columns' => 1,
    'attributes' => [
        'trunk_name' => ['type' => Form::INPUT_TEXT],
        'actual_from' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className(), 'saveTimezone' => $clientAccount->timezone_name],
    ],
]);


$attributes = [
    'actions' => [
        'type' => Form::INPUT_RAW,
        'value' =>
            '<div class="col-md-12">' .
            Html::button('Подключить', ['class' => 'btn btn-primary', 'onclick' => "jerasoftSubmitForm('add')"]) .
            '</div>'
    ],
];

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'attributes' => $attributes,
]);

echo Html::hiddenInput('scenario', 'default', ['id' => 'scenario']);
ActiveForm::end();
?>
<script>
    function jerasoftSubmitForm(scenario) {
        $('#scenario').val(scenario);
        $('#<?=$form->getId()?>')[0].submit();
    }
    $('.form-reload').change(function() {
        jerasoftSubmitForm('default');
    });
</script>