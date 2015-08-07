<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use app\models\Region;
use app\models\City;
use app\models\TariffNumber;
use app\models\TariffVoip;

/** @var $clientAccount \app\models\ClientAccount */
/** @var $model \app\forms\usage\UsageVoipEditForm */

$types = [
    'number' => 'Номер',
    '7800' => '7800',
    'line' => 'Линия без номера',
    'operator' => 'Оператор',
];

$noYes = [
    '0' => 'Нет',
    '1' => 'Да',
];

$tariffStatus = [
    'public' => 'Публичный',
    'special' => 'Специальный',
    'operator' => 'Оператор',
    'archive' => 'Архивный',
];

$status = [
    'connecting' => 'Подключаемый',
    'working' => 'Включенный',
];

?>

<legend>
    <?= Html::a($clientAccount->company, '/client/view?id='.$clientAccount->id) ?> ->
    <?= Html::a('Телефония', '/?module=services&action=vo_view') ?> ->
    Добавление номера
</legend>
<?php
$form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 5,
    'attributes' => [
        'type_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $types, 'options' => ['class' => 'select2 form-reload']],
        'connection_point_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> Region::dao()->getList(true, $clientAccount->country_id), 'options' => ['class' => 'select2 form-reload']],
        'city_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> City::dao()->getList(true, $clientAccount->country_id), 'options' => ['class' => 'select2 form-reload',]],
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

if ($model->type_id == 'number') {
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'connecting_date' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className()],
            'number_tariff_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => TariffNumber::dao()->getList(true, $clientAccount->country_id, $clientAccount->currency, $model->city_id), 'options' => ['class' => 'select2 form-reload']],
            'did' => ['type' => Form::INPUT_TEXT],
        ],
    ]);
} elseif ($model->type_id == '7800') {
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'connecting_date' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className()],
            'did' => ['type' => Form::INPUT_TEXT],
            'line7800_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $model->getLinesFor7800($clientAccount)],
        ],
    ]);
} else {
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 2,
        'attributes' => [
            'connecting_date' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className()],
            'did' => ['type' => Form::INPUT_TEXT, 'options' => ['readonly' => 'readonly']],
        ],
    ]);
}


echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 2,
    'attributes' => [
        'no_of_lines' => ['type' => Form::INPUT_TEXT],
        'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $status],
        'address' => ['type' => Form::INPUT_TEXT],
    ],
]);


echo Form::widget([
    'model' => $model,
    'form' => $form,
    'columns' => 4,
    'attributes' => [
        'tariff_main_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => TariffVoip::dao()->getMainList(false, $model->connection_point_id, $clientAccount->currency, $model->tariff_main_status), 'options' => ['class' => 'select2']],
        'tariff_main_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $tariffStatus, 'options' => ['class' => 'form-reload']],
        ['type' => Form::INPUT_RAW],
        ['type' => Form::INPUT_RAW],
        'tariff_local_mob_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => TariffVoip::dao()->getLocalMobList(false, $model->connection_point_id, $clientAccount->currency), 'options' => ['class' => 'select2 form-reload']],
        'tariff_group_local_mob_price' => ['type' => Form::INPUT_TEXT],
        'tariff_group_local_mob' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $noYes, 'options' => ['class' => 'form-reload']],
        ['type' => Form::INPUT_RAW],
        'tariff_russia_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => TariffVoip::dao()->getRussiaList(false, $model->connection_point_id, $clientAccount->currency), 'options' => ['class' => 'select2 form-reload']],
        'tariff_group_russia_price' => ['type' => Form::INPUT_TEXT],
        'tariff_group_russia' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $noYes, 'options' => ['class' => 'form-reload']],
        ['type' => Form::INPUT_RAW],
        'tariff_russia_mob_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => TariffVoip::dao()->getRussiaList(false, $model->connection_point_id, $clientAccount->currency), 'options' => ['class' => 'select2']],
        ['type' => Form::INPUT_RAW],
        ['type' => Form::INPUT_RAW],
        ['type' => Form::INPUT_RAW],
        'tariff_intern_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => TariffVoip::dao()->getInternList(false, $model->connection_point_id, $clientAccount->currency), 'options' => ['class' => 'select2 form-reload']],
        'tariff_group_intern_price' => ['type' => Form::INPUT_TEXT],
        'tariff_group_intern' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $noYes, 'options' => ['class' => 'form-reload']],
    ],
]);

if ($model->tariff_group_local_mob || $model->tariff_group_russia || $model->tariff_group_intern) {
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            ['type' => Form::INPUT_RAW],
            'tariff_group_price' => ['type' => Form::INPUT_TEXT],
            ['type' => Form::INPUT_RAW],
            ['type' => Form::INPUT_RAW],
        ],
    ]);
}


$attributes = [
    'actions' => [
        'type' => Form::INPUT_RAW,
        'value' =>
            '<div class="col-md-12">' .
            Html::button('Подключить', ['class' => 'btn btn-primary', 'onclick' => "submitForm('add')"]) .
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
    function submitForm(scenario) {
        $('#scenario').val(scenario);
        $('#<?=$form->getId()?>')[0].submit();
    }
    $('.form-reload').change(function() {
        submitForm('default');
    });
</script>