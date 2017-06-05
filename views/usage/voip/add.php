<?php

use app\classes\Html;
use app\models\City;
use app\models\Country;
use app\models\DidGroup;
use app\models\Number;
use app\models\TariffVoip;
use app\modules\nnp\models\NdcType;
use app\widgets\DateControl as CustomDateControl;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var $clientAccount \app\models\ClientAccount */
/** @var $model \app\forms\usage\UsageVoipEditForm */
/** @var \app\classes\BaseView $this */

if (\app\modules\uu\models\AccountTariff::isUuAccount()) {
    return [];
}

$types = \app\modules\uu\models\Tariff::getVoipTypesByCountryId();

$noYes = [
    '0' => 'Нет',
    '1' => 'Да',
];

$status = [
    'connecting' => 'Подключаемый',
    'working' => 'Включенный',
];

$isPriceIncludeVat = $model->clientAccount->is_voip_with_tax;

$model->tariffGroupRussiaPrice = $model->getMinByTariff($model->tariff_russia_id);
$model->tariffGroupLocalMobPrice = $model->getMinByTariff($model->tariff_local_mob_id);
$model->tariffGroupInternPrice = $model->getMinByTariff($model->tariff_intern_id);

$model->tariff_group_russia_price == $model->tariffGroupRussiaPrice && $model->tariff_group_russia_price = null;
$model->tariff_group_local_mob_price == $model->tariffGroupLocalMobPrice && $model->tariff_group_local_mob_price = null;
$model->tariff_group_intern_price == $model->tariffGroupInternPrice && $model->tariff_group_intern_price = null;


echo Html::formLabel('Добавление номера');
echo Breadcrumbs::widget([
    'links' => [
        'homeLink' => [
            'label' => $clientAccount->company,
            'url' => ['client/view', 'id' => $clientAccount->id]
        ],
        ['label' => 'Телефония Номера', 'url' => Url::toRoute(['/', 'module' => 'services', 'action' => 'vo_view'])],
        'Добавление номера'
    ],
]);
?>

<div class="well">
    <?php
    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

    $this->registerJsVariables([
        'editFormId' => $form->getId(),
        'tariffEditFormId' => '',
    ], 'usage');

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'type_id' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $types,
                'options' => [
                    'class' => 'select2 form-reload'
                ]
            ],
            'city_id' => NdcType::isCityDependent($model->ndc_type_id) ?
                [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => City::getList(
                        $isWithEmpty = true,
                        $model->country_id,
                        $isWithNullAndNotNull = false,
                        $isUsedOnly = false
                    ),
                    'options' => [
                        'class' => 'select2 form-reload',
                    ]
                ] : ['type' => Form::INPUT_RAW],
            'country_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => Country::getList($isWithEmpty = false), 'options' => ['class' => 'select2 form-reload',]],
            [
                'type' => Form::INPUT_RAW,
                'value' => '
                <div class="form-group">
                    <label class="control-label">Валюта</label>
                    <input type="text" class="form-control" value="' . $clientAccount->currency . '" readonly>
                </div>
            '
            ],
        ],
    ]);

    if ($model->type_id === Number::TYPE_NUMBER) {
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 4,
            'attributes' => [
                'ndc_type_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => NdcType::getList(),
                    'options' => ['class' => 'select2 form-reload'],
                ],
                'did_group_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => DidGroup::getList($isWithEmpty = true, $model->country_id, $model->city_id, $model->ndc_type_id),
                    'options' => ['class' => 'select2 form-reload'],
                ],
                'did' => ['type' => Form::INPUT_TEXT],
                'no_of_lines' => ['type' => Form::INPUT_TEXT],
            ],
        ]);
    } elseif ($model->type_id == Number::TYPE_7800) {
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 4,
            'attributes' => [
                'ndc_type_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => NdcType::getList(),
                    'options' => ['class' => 'select2 form-reload'],
                ],
                'did' => [
                    'type' => Form::INPUT_TEXT
                ],
                'no_of_lines' => [
                    'type' => Form::INPUT_TEXT
                ],
                'line7800_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $model->getLinesFor7800($clientAccount)
                ],
            ],
        ]);
    } else {
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 4,
            'attributes' => [
                'ndc_type_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => NdcType::getList(),
                    'options' => ['class' => 'select2 form-reload'],
                ],
                'did' => [
                    'type' => Form::INPUT_TEXT,
                    'options' => [
                        'readonly' => 'readonly'
                    ]
                ],
                'no_of_lines' => [
                    'type' => Form::INPUT_TEXT
                ],
            ],
        ]);
    }

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'connecting_date' => [
                'type' => Form::INPUT_WIDGET,
                'widgetClass' => CustomDateControl::className(),
                'options' => [
                    'autoWidgetSettings' => [
                        DateControl::FORMAT_DATE => [
                            'options' => [
                                'pluginOptions' => [
                                    'todayHighlight' => true,
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            'status' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $status
            ],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 1,
        'attributes' => [
            'address' => [
                'type' => Form::INPUT_TEXT,
            ],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'tariff_main_id' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => TariffVoip::getList(
                    TariffVoip::DEST_LOCAL_FIXED,
                    $isPriceIncludeVat,
                    $isWithEmpty = false,
                    $model->connection_point_id,
                    $clientAccount->currency,
                    $model->tariff_main_status,
                    $model->ndc_type_id
                ),
                'options' => ['class' => 'select2']
            ],
            'tariff_main_status' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => TariffVoip::$statuses,
                'options' => ['class' => 'form-reload']
            ],
            ['type' => Form::INPUT_RAW],
            ['type' => Form::INPUT_RAW],
            'tariff_local_mob_id' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => TariffVoip::getList(
                    TariffVoip::DEST_LOCAL_MOBILE,
                    $isPriceIncludeVat,
                    $isWithEmpty = false,
                    $model->connection_point_id,
                    $clientAccount->currency
                    //$model->ndc_type_id
                ),
                'options' => [
                    'class' => 'select2 form-reload'
                ]
            ],
            'tariff_group_local_mob_price' => [
                'type' => Form::INPUT_TEXT,
                'hint' => 'Гарантированный платеж в тарифе: ' . (float)$model->tariffGroupLocalMobPrice,
                'options' => [
                    'placeholder' => sprintf("%0.2f", $model->tariffGroupLocalMobPrice)
                ],
            ],
            'tariff_group_local_mob' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $noYes,
                'options' => ['class' => 'form-reload']
            ],
            ['type' => Form::INPUT_RAW],
            'tariff_russia_id' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => TariffVoip::getList(
                    TariffVoip::DEST_RUSSIA,
                    $isPriceIncludeVat,
                    $isWithEmpty = false,
                    $model->connection_point_id,
                    $clientAccount->currency
                    //$model->ndc_type_id
                ),
                'options' => [
                    'class' => 'select2 form-reload'
                ]
            ],
            'tariff_group_russia_price' => [
                'type' => Form::INPUT_TEXT,
                'hint' => 'Гарантированный платеж в тарифе: ' . (float)$model->tariffGroupRussiaPrice,
                'options' => [
                    'placeholder' => sprintf("%0.2f", $model->tariffGroupRussiaPrice)
                ],
            ],
            'tariff_group_russia' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $noYes,
                'options' => ['class' => 'form-reload']
            ],
            ['type' => Form::INPUT_RAW],
            'tariff_russia_mob_id' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => TariffVoip::getList(
                    TariffVoip::DEST_RUSSIA,
                    $isPriceIncludeVat,
                    $isWithEmpty = false,
                    $model->connection_point_id,
                    $clientAccount->currency
                    //$model->ndc_type_id
                ),
                'options' => ['class' => 'select2']
            ],
            ['type' => Form::INPUT_RAW],
            ['type' => Form::INPUT_RAW],
            ['type' => Form::INPUT_RAW],
            'tariff_intern_id' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => TariffVoip::getList(
                    TariffVoip::DEST_INTERNATIONAL,
                    $isPriceIncludeVat,
                    $isWithEmpty = false,
                    $model->connection_point_id,
                    $clientAccount->currency
                    //$model->ndc_type_id
                ),
                'options' => [
                    'class' => 'select2 form-reload'
                ]
            ],
            'tariff_group_intern_price' => [
                'type' => Form::INPUT_TEXT,
                'hint' => 'Гарантированный платеж в тарифе: ' . (float)$model->tariffGroupInternPrice,
                'options' => [
                    'placeholder' => sprintf("%0.2f", $model->tariffGroupInternPrice),
                ],
            ],
            'tariff_group_intern' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $noYes,
                'options' => ['class' => 'form-reload']
            ],
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

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'count_numbers' => [
                'type' => Form::INPUT_TEXT,
            ],
            ['type' => Form::INPUT_RAW],
            ['type' => Form::INPUT_RAW],
            ['type' => Form::INPUT_RAW]
        ],
    ]);


    $attributes = [
        'actions' => [
            'type' => Form::INPUT_RAW,
            'value' =>
                Html::tag(
                    'div',
                    Html::button('Отменить', [
                        'class' => 'btn btn-link',
                        'style' => 'margin-right: 15px;',
                        'onClick' => 'self.location = "' . Url::toRoute(['/', 'module' => 'services', 'action' => 'vo_view']) . '";',
                    ]) .
                    Html::button('Подключить', ['class' => 'btn btn-primary', 'onClick' => "submitForm('add')"]),
                    ['style' => 'text-align: right; padding-right: 0px;']
                )
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
</div>
