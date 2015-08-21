<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\Currency;
use app\models\Region;
use app\models\Country;
use app\models\DidGroup;
use app\models\City;

$statuses = [
    'public' => 'Публичный',
    'special' => 'Специальный',
    'transit' => 'Переходный',
    'test' => 'Тестовый',
    '7800' => '7800',
    'archive' => 'Архивный',
];
$periods = [
    'month' => 'Месяц',
];


$optionDisabled = $creatingMode ? [] : ['disabled' => 'disabled'];
?>

<div class="well">
    <legend>Тарифы -> Телефония Номер -> <?=Html::encode($model->name)?></legend>
    <?php

    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'city_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> City::dao()->getListWithCountries(true), 'options' => ['id' => 'city_id', 'class' => 'select2'] + $optionDisabled ],
            'connection_point_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> Region::dao()->getList(true), 'options' => ['class' => 'select2'] + $optionDisabled ],
            'country_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> Country::dao()->getList(true), 'options' => ['id' => 'country_id', 'class' => 'select2'] + $optionDisabled ],
            'activation_fee' => ['type' => Form::INPUT_TEXT],
            'periodical_fee' => ['type' => Form::INPUT_TEXT],
            'currency_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> Currency::map(), 'options' => ['class' => 'select2'] + $optionDisabled ],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 2,
        'attributes' => [
            'name' => ['type' => Form::INPUT_TEXT],
            'period' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> $periods, 'options' => ['class' => 'select2'] ],
            'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> $statuses, 'options' => ['class' => 'select2'] ],
            'did_group_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items'=> DidGroup::dao()->getList(true, $model->city_id), 'options' => ['class' => 'select2'] ],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div class="col-md-offset-2 col-md-10">' .
                    Html::button('Сохранить', ['class'=>'btn btn-primary', 'onclick' => "submitForm('save')"]) . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
                    Html::a('Отмена', ['index'], ['class'=>'btn btn-default btn-sm']) .
                    '</div>'
            ],
        ],
    ]);

    echo Html::activeHiddenInput($model, 'scenario', ['id' => 'scenario']);
    ActiveForm::end();
    ?>
</div>
<script>
    function submitForm(scenario) {
        $('#scenario').val(scenario);
        $('#<?=$form->getId()?>').submit();
    }
    $('#city_id').change(function() {
        submitForm('default');
    });
</script>
