<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\voip\Prefixlist;
use app\models\billing\Geo;
use app\models\billing\GeoCountry;
use app\models\billing\GeoOperator;

$optionDisabled = $creatingMode ? [] : ['disabled' => 'disabled'];

$countries = ['0' => '-- Страна --'] + GeoCountry::dao()->getList(true);

$regions = Geo::dao()->getRegionList();
$regionsOptions = [];
foreach ($regions as $region) {
    $regionsOptions[ $region['region'] ] = [
        'data-country-id' => $region['country'],
    ];
}
$regions = ['0' => '-- Регион --'] + ArrayHelper::map($regions, 'region', 'region_name');

$cities = Geo::dao()->getCitiesList();
$citiesOptions = [];
foreach ($cities as $city) {
    $citiesOptions[ $city['city'] ] = [
        'data-country-id' => $city['country'],
        'data-region-id' => $city['region'],
    ];
}
$cities = ['0' => '-- Город --'] + ArrayHelper::map($cities, 'city', 'city_name');

$model->operators = explode(',', $model->operators);
$operators = GeoOperator::dao()->getList();

$model->exclude_operators = $creatingMode ? 0 : $model->exclude_operators;
$model->type_id = $creatingMode ? 1 : $model->type_id;
?>

<legend>
    <?= Html::a('Списки префиксов', '/voip/prefixlist'); ?> ->
    <?= ($model->name ? Html::encode($model->name) : 'Новый список'); ?>
</legend>

<div class="well">
    <?php

    $form = ActiveForm::begin([
        'id' => 'PrefixlistForm',
        'type' => ActiveForm::TYPE_VERTICAL,
        'enableClientValidation' => true,
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'name' => ['type' => Form::INPUT_TEXT],
            'type_id' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div style="margin-top: 23px;">' .
                    $form
                        ->field($model, 'type_id')
                        ->radioButtonGroup(
                            Prefixlist::$types, [
                                'class' => 'btn-group-sm',
                                'itemOptions' => ['labelOptions' => ['class' => 'btn btn-default']]
                            ]
                        )
                        ->label('') .
                    '</div>'
            ],
            'sub_type' => [
                'type' => Form::INPUT_RAW,
                'value' => '<div class="type-id" data-value="3" style="margin-top: 23px; display: none;">' .
                    $form
                        ->field($model, 'sub_type')
                        ->radioButtonGroup(
                            Prefixlist::$roslink_types, [
                                'class' => 'btn-group-sm',
                                'itemOptions' => ['labelOptions' => ['class' => 'btn btn-default']]
                            ]
                        )
                        ->label('') .
                    '</div>'
            ],
        ]
    ]);
    ?>

    <div class="type-id" data-value="1" style="display: none;">
        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 1,
            'attributes' => [
                'prefixes' => ['type' => Form::INPUT_TEXT, 'options' => ['class' => 'select2-tag-support']],
            ],
        ]);
        ?>
    </div>

    <div class="type-id" data-value="3" style="display: none;">
        <?php
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'country_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $countries,
                    'options' => [
                        'class' => 'select2 chained-select',
                        'data-chained' => '[name*="region_id"], [name*="city_id"]',
                        'data-chained-tag' => '[data-country-id="?"]',
                    ]
                ],
                'region_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $regions,
                    'options' =>
                        [
                            'class' => 'select2 chained-select',
                            'options' => $regionsOptions,
                            'data-chained' => '[name*="city_id"]',
                            'data-chained-tag' => '[data-region-id="?"]',
                        ]
                ],
                'city_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $cities,
                    'options' =>
                    [
                        'class' => 'select2',
                        'options' => $citiesOptions
                    ]
                ],
            ],
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 2,
            'attributes' => [
                'operators' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $operators, 'options' => ['class' => 'select2', 'multiple' => 'multiple']],
                'exclude_operators' => [
                    'type' => Form::INPUT_RAW,
                    'value' =>
                        '<div style="margin-top: 23px;">' .
                        $form
                            ->field($model, 'exclude_operators')
                            ->radioButtonGroup(
                                [0 => 'Только выбранные', 1 => 'Все кроме выбранных'], [
                                    'class' => 'btn-group-sm',
                                    'itemOptions' => ['labelOptions' => ['class' => 'btn btn-default']]
                                ]
                            )
                            ->label('') .
                        '</div>'
                ],
            ],
        ]);
    ?>
    </div>

    <?php
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

<script type="text/javascript" src="/js/jquery.chained.js"></script>
<script type="text/javascript" src="/js/behaviors/prefixlist.js"></script>