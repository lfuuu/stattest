<?php
use app\classes\Html;
use app\models\City;
use app\models\Country;
use app\models\Currency;
use app\models\DidGroup;
use app\models\Region;
use kartik\builder\Form;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var \app\models\TariffNumber $model */

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

$optionEditDisabled = $optionCityDisabled = $optionDidGroupDisabled = [];

if (!$creatingMode) {
    $optionEditDisabled = ['disabled' => 'disabled'];
}
else {
    !(int) $model->country_id ? $optionCityDisabled = ['disabled' => 'disabled'] : false;
    !(int) $model->city_id ? $optionDidGroupDisabled = ['disabled' => 'disabled'] : false;
}

echo Html::formLabel($model->name ? 'Редактирование тарифа номера' : 'Добавление тарифа номера');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Телефония Номера', 'url' => Url::toRoute(['tariff/number'])],
        $model->name ? 'Редактирование тарифа номера' : 'Добавление тарифа номера'
    ],
]);
?>

<div class="well">
    <?php

    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'country_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => Country::dao()->getList(true), 'options' => ['id' => 'country_id', 'class' => 'select2'] + $optionEditDisabled],
            'city_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => City::dao()->getList(true, $model->country_id), 'options' => ['id' => 'city_id', 'class' => 'select2'] + $optionEditDisabled + $optionCityDisabled],
            'did_group_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => DidGroup::dao()->getList(true, $model->city_id), 'options' => ['class' => 'select2'] + $optionEditDisabled + $optionDidGroupDisabled],
            'name' => ['type' => Form::INPUT_TEXT],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'activation_fee' => ['type' => Form::INPUT_TEXT],
            'currency_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => Currency::map(), 'options' => ['class' => 'select2'] + $optionEditDisabled],
            'period' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $periods, 'options' => ['class' => 'select2']],
            'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $statuses, 'options' => ['class' => 'select2']],
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
                    Html::tag(
                        'div',
                        Html::button('Отменить', [
                            'class' => 'btn btn-link',
                            'style' => 'margin-right: 15px;',
                            'onClick' => 'self.location = "' . Url::toRoute(['tariff/number']) . '";',
                        ]) .
                        Html::button('Сохранить', [
                            'class' => 'btn btn-primary',
                            'onClick' => 'submitForm("save")'
                        ]),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
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
    $('#<?= $form->getId(); ?>').submit();
}
$('#city_id, #country_id').change(function () {
    submitForm('default');
});
</script>