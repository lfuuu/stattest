<?php

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\Currency;
use app\models\Region;
use app\models\Country;
use app\models\TariffVoip;
use app\models\billing\Pricelist;
use app\models\voip\Destination;

$optionDisabled = $creatingMode ? [] : ['disabled' => 'disabled'];

$currencies = ['' => '-- Валюта --'] + Currency::dao()->getList('id', true);

echo Html::formLabel($model->description ? 'Редактирование тарифа' : 'Новый тариф');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Тарифы Звонок_чат', 'url' => Url::toRoute(['tariff/call-chat'])],
        $model->description ? 'Редактирование тарифа' : 'Новый тариф'
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
            'price' => ['type' => Form::INPUT_TEXT],
            'currency_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $currencies, 'options' => ['class' => 'select2'] + $optionDisabled],
            'price_include_vat' => ['type' => Form::INPUT_CHECKBOX], // , 'options' => $optionDisabled
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 2,
        'attributes' => [
            'description' => ['type' => Form::INPUT_TEXT],
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
                            'onClick' => 'self.location = "' . Url::toRoute(['index']) . '";',
                        ]) .
                        Html::submitButton('Сохранить', ['class' => 'btn btn-primary']),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);

    echo Html::activeHiddenInput($model, 'scenario', ['id' => 'scenario']);
    ActiveForm::end();
    ?>
</div>
