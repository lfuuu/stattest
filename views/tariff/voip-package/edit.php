<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\Currency;
use app\models\Region;
use app\models\Country;
use app\models\TariffVoip;
use app\models\billing\Pricelist;
use app\models\voip\Destination;

$optionDisabled = $creatingMode ? [] : ['disabled' => 'disabled'];

$countries = Country::dao()->getList(true);

$connectionPoints = Region::find()->orderBy('name')->all();
$connectionPointsOptions = [];
foreach ($connectionPoints as $connectionPoint) {
    $connectionPointsOptions[$connectionPoint->id] = [
        'data-country-id' => $connectionPoint->country_id
    ];
}
$connectionPoints = ['' => '-- Точка подключения --'] + ArrayHelper::map($connectionPoints, 'id', 'name');

$destinations = Destination::dao()->getList(true);
$currencies = ['' => '-- Валюта --'] + Currency::dao()->getList('id', true);

$priceLists =
    Pricelist::find()
        ->select(['id', 'name', 'price_include_vat'])
        ->andWhere(['orig' => 1, 'local' => 0])
        ->orderBy('region desc, name asc')
        ->all();
$priceListsOptions = [];
foreach ($priceLists as $priceList) {
    $priceListsOptions[$priceList->id] = [
        'data-type' => $priceList->price_include_vat == 1 ? 1 : 0
    ];
}
$priceLists = ['0' => '-- Прайс-лист --'] + ArrayHelper::map($priceLists, 'id', 'name');
?>

<div class="well">
    <legend>Тарифы IP Телефонии - Пакеты -> <?= ($model->name ? Html::encode($model->name) : 'Новый тариф'); ?></legend>
    <?php

    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'country_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $countries, 'options' => ['class' => 'select2'] + $optionDisabled],
            'connection_point_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $connectionPoints, 'options' =>
                [
                    'class' => 'select2',
                    'options' => $connectionPointsOptions,
                ] + $optionDisabled
            ],
            'destination_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $destinations, 'options' => ['class' => 'select2']],
            'currency_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $currencies, 'options' => ['class' => 'select2'] + $optionDisabled],
            'virtual_type' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div style="margin-top: 23px;">' .
                        '<div class="form-group">' .
                            '<div id="tariff-voip-package" class="btn-group-sm btn-group" data-toggle="buttons">' .
                                '<label class="btn btn-default' . (!$model->pricelist_id ? ' active' : '') . '">' .
                                    '<input type="radio" name="virtual_type" value="minutes"' . (!$model->pricelist_id ? ' checked="checked"' : '') . ' /> Предоплаченные минуты' .
                                '</label>' .
                                '<label class="btn btn-default' . ($model->pricelist_id ? ' active' : '') . '">' .
                                    '<input type="radio" name="virtual_type" value="pricelist"' . ($model->pricelist_id ? ' checked="checked"' : '') . ' /> Прайс-лист' .
                                '</label>' .
                            '</div>'.
                        '</div>' .
                    '</div>'
            ],
            'price_include_vat' => ['type' => Form::INPUT_CHECKBOX], // , 'options' => $optionDisabled
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 2,
        'attributes' => [
            'name' => ['type' => Form::INPUT_TEXT],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'periodical_fee' => ['type' => Form::INPUT_TEXT],
            'minutes_count' => ['type' => Form::INPUT_TEXT, 'options' => ['data-type' => 'minutes']],
            'min_payment' => ['type' => Form::INPUT_TEXT, 'options' => ['data-type' => 'pricelist']],
            'pricelist_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $priceLists, 'options' => [
                'data-type' => 'pricelist',
                'class' => 'select2',
                'options' => $priceListsOptions,
            ]],
        ],
    ]);

    /*
    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'is_virtual' => ['type' => Form::INPUT_CHECKBOX],
            'is_testing' => ['type' => Form::INPUT_CHECKBOX],
            'price_include_vat' => ['type' => Form::INPUT_CHECKBOX],
        ],
    ]);
    */

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div class="col-md-offset-2 col-md-10" style="text-align: right;">' .
                    Html::a(
                        'Отмена',
                        ['index'],
                        [
                            'class' => 'btn btn-default btn-sm',
                            'style' => 'margin-right: 15px;',
                        ]
                    ) .
                    Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) .
                    '</div>'
            ],
        ],
    ]);

    echo Html::activeHiddenInput($model, 'scenario', ['id' => 'scenario']);
    ActiveForm::end();
    ?>
</div>

<script type="text/javascript" src="/js/behaviors/tariff-voip-package.js"></script>
<script type="text/javascript" src="/js/behaviors/pricelist-voip-filter.js"></script>
<script type="text/javascript" src="/js/behaviors/connection-point-voip-filter.js"></script>