<?php

/**
 * @var \app\forms\voip\RegistryForm $model
 * @var bool $creatingMode
 * @var array $checkList
 * @var array $statusInfo
 */

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\Html;
use app\models\billing\Trunk;
use app\models\City;
use app\models\Country;
use app\models\Number;
use app\models\voip\Registry;
use app\modules\nnp\models\NdcType;
use kartik\builder\Form;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;


$numbersWithoutRegistry = $checkList
    ? array_column(array_filter($checkList, function ($item) {
        return $item['filling'] == 'fill' && !$item['registry_id'];
    }), 'end', 'start')
    : [];

$countryList = Country::getList();
$cityLabelList = $cityList = City::getList($isWithEmpty = false, $model->country_id, $isWithNullAndNotNull = false, $isUsedOnly = false);

if ($model->registry && $model->country_id != $model->registry->country_id) {
    $cityLabelList = City::getList($isWithEmpty = false, $model->registry->country_id, $isWithNullAndNotNull = false, $isUsedOnly = false);
}


$title = $model->id ? 'Редактирование записи №' . $model->registry->id . ' (' . $model->registry->number_from . ' - ' . $model->registry->number_to . ')' : 'Новая запись';

echo Html::formLabel($title);

$links = ['Телефония', ['label' => 'Реестр номеров', 'url' => '/voip/registry']];
if ($model->id) {
    $links[] = [
        'label' => $countryList[$model->registry->country_id],
        'url' => ['voip/registry', 'RegistryFilter' => ['country_id' => $model->registry->country_id]]
    ];

    $links[] = [
        'label' => $cityLabelList[$model->registry->city_id],
        'url' => [
            'voip/registry',
            'RegistryFilter' => ['country_id' => $model->registry->country_id, 'city_id' => $model->registry->city_id]
        ]
    ];
}
$links[] = [
    'label' => $title,
    'url' => $model->id ? ['/voip/registry/edit', 'id' => $model->id] : ['/voip/registry/add']
];

echo Breadcrumbs::widget([
    'links' => $links
]);

$isEditable = $model->registry ? $model->registry->isEditable() : true;
$isSubmitable = $model->registry ? $model->registry->isSubmitable() : true;
$readonlyOptions = [
    'readonly' => true,
    'disabled' => true
];
?>

    <div class="well">
        <?php

        $form = ActiveForm::begin([
            'id' => 'RegistryForm',
            'type' => ActiveForm::TYPE_VERTICAL,
            'enableClientValidation' => true,
        ]);

        $this->registerJsVariable('registryFormId', $form->getId());

        // строка 1
        $line1Attributes = [];

        $line1Attributes['country_id'] = [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => $countryList,
            'options' => [
                    'class' => 'formReload select2'
                ] + ($isEditable ? [] : $readonlyOptions),
        ];

        if (NdcType::isCityDependent($model->ndc_type_id)) {
            $line1Attributes['city_id'] = [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $cityList,
                'options' => [
                        'class' => 'formReload select2'
                    ] + ($isEditable ? [] : $readonlyOptions),
            ];
        }

        $line1Attributes['source'] = [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => \app\models\voip\Source::getList(),
            'options' => [
                    'class' => 'formReload select2',
                ] + ($isEditable ? [] : $readonlyOptions),

        ];

        $line1Attributes['account_id'] = [
            'type' => Form::INPUT_TEXT,
            'options' => [
                    'class' => 'formReloadOnLostFocus'
                ] + ($isEditable ? [] : $readonlyOptions),
        ];

        // Добавление скрытого поля - ННП-оператор
        echo $form->field($model, 'nnp_operator_id')
            ->hiddenInput()
            ->label(false);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line1Attributes),
            'attributes' => $line1Attributes
        ]);

        // строка 2
        $maskedInputWidgetConfig = [
            'type' => Form::INPUT_WIDGET,
            'widgetClass' => \app\classes\MaskedInput::class,
            'options' => [
                'mask' => $model->city_number_format,
                'options' => [
                        'class' => 'form-control',
                    ] + ($isEditable ? [] : $readonlyOptions),
            ],
        ];

        $model->number_from = ' ' . trim($model->number_from);
        $model->number_to = ' ' . trim($model->number_to);

        $line2Attributes = [
            'ndc_type_id' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => NdcType::getList(),
                'options' => [
                        'class' => 'formReload select2',
                    ] + ($isEditable ? [] : $readonlyOptions),
            ],
            'ndc' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $model->ndcList,
                'options' => [
                        'class' => 'formReload select2'
                    ] + ($isEditable ? [] : $readonlyOptions),
            ],
            'number_from' => $maskedInputWidgetConfig,
            'number_to' => $maskedInputWidgetConfig,
        ];

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line2Attributes),
            'attributes' => $line2Attributes
        ]);

        // строка 3
        $line3Attributes = [];

        $line3Attributes['comment'] = [
            'type' => Form::INPUT_TEXT,
        ];

        //

        $line3Attributes['nnp_operator_name'] = [
            'type' => Form::INPUT_TEXT,
            'options' => $readonlyOptions, // определяется автоматически из ННП
        ];

        if ($model->source == VoipRegistrySourceEnum::OPERATOR_NOT_FOR_SALE) {
            $line3Attributes['fmc_trunk_id'] = [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => ($model->account_id ? Trunk::dao()->getList(['accountId' => $model->account_id], $isWithEmpty = true) : ['' => '----']),
                'options' => ($isEditable ? [] : $readonlyOptions)
            ];
        }

        if ($model->source == VoipRegistrySourceEnum::REGULATOR || $model->source == VoipRegistrySourceEnum::PORTABILITY_NOT_FOR_SALE) {
            $line3Attributes['mvno_partner_id'] = [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => app\modules\sim\models\ImsiPartner::getList($isWithEmpty = true),
                'options' => ($isEditable ? [] : $readonlyOptions)
            ];
        }

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line3Attributes),
            'attributes' => $line3Attributes
        ]);

        //строка 4
        $line4Attributes = [];
        $line4Attributes['solution_number'] = [
            'type' => Form::INPUT_TEXT,
            'options' => $isEditable ? [] : $readonlyOptions,
        ];
        $line4Attributes['solution_date'] = [
            'type' => Form::INPUT_WIDGET,
            'widgetClass' => DatePicker::class,
            'options' => $isEditable ? [] : $readonlyOptions,
        ];
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 6,
            'attributes' => $line4Attributes
        ]);

        $value = '';
        if ($model->id) {
            $value .= Html::submitButton('Проверить номера', [
                'class' => 'btn btn-info',
                'name' => 'check-numbers',
                'value' => 'Проверить номера'
            ]);

            if (in_array($model->registry->status, [Registry::STATUS_PARTLY, Registry::STATUS_EMPTY])) {
                $value .= ' ' . Html::submitButton('Залить номера', [
                        'class' => 'btn btn-success',
                        'name' => 'fill-numbers',
                        'value' => 'Залить номера'
                    ]);
            }

            if ($statusInfo && isset($statusInfo[Number::STATUS_NOTSALE]) && $statusInfo[Number::STATUS_NOTSALE]) {
                $value .= ' ' . Html::submitButton('Передать в продажу номера (' . $statusInfo[Number::STATUS_NOTSALE] . ' шт.)', [
                        'class' => 'btn btn-warning',
                        'name' => 'to-sale',
                        'value' => 'Передать в продажу номера'
                    ]);
            }

            if ($numbersWithoutRegistry) {
                $value .= ' ' . Html::submitButton('Прикрепить к реестру', [
                        'class' => 'btn btn-info',
                        'name' => 'attach-to-registry',
                        'value' => $numbersWithoutRegistry
                    ]);
            }
        }

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'check-number' => [
                    'type' => Form::INPUT_RAW,
                    'value' => $value,
                ],
                'id' => [
                    'type' => Form::INPUT_RAW,
                    'value' => Html::activeHiddenInput($model, 'id')
                ],

                'actions' => [
                    'type' => Form::INPUT_RAW,
                    'value' =>
                        Html::tag(
                            'div',
                            Html::button('Отменить', [
                                'class' => 'btn btn-link',
                                'style' => 'margin-right: 15px;',
                                'onClick' => 'self.location = "' . Url::toRoute(['voip/registry']) . '";',
                            ]) .
                            Html::submitButton('Сохранить',
                                [
                                    'class' => 'btn btn-primary',
                                    'name' => $submitName,
                                    'value' => 'Сохранить'
                                ]),
                            ['style' => 'text-align: right; padding-right: 0px;']
                        )
                ],
            ],
        ]);

        ActiveForm::end();
        ?>
    </div>

<?php if ($checkList) {

    $provider = new \yii\data\ArrayDataProvider([
        'allModels' => $checkList,
        'sort' => [
            'attributes' => ['filling', 'start', 'end'],
        ],
        'pagination' => [
            'pageSize' => 50,
        ],
    ]);

    echo \app\classes\grid\GridView::widget([
        'panelHeadingTemplate' => '<div class="pull-left">{summary}</div>',
        'dataProvider' => $provider,
        'rowOptions' => function ($model) {
            return ['class' => $model['filling'] === 'fill' ? ($model['registry_id'] && !$model['is_alien_registry'] ? 'success' : 'danger') : 'warning'];
        },
        'columns' => [
            [
                'attribute' => 'filling',
                'label' => 'Состояние',
                'value' => function ($model) {
                    switch ($model['filling']) {
                        case 'pass':
                            return 'Пропущено';
                        case 'fill':
                            return 'Заполнено';
                    }
                    return '';
                }
            ],
            [
                'attribute' => 'start',
                'value' => function ($model) {
                    if ($model['filling'] === 'pass') {
                        return $model['start'];
                    }
                    return Html::a($model['start'], ['voip/number', 'NumberFilter[number]' => $model['start']]);
                },
                'label' => 'Начало периода',
                'format' => 'raw'
            ],
            [
                'attribute' => 'end',
                'value' => function ($model) {
                    if ($model['filling'] === 'pass') {
                        return $model['end'];
                    }
                    return Html::a($model['end'], ['voip/number', 'NumberFilter[number]' => $model['end']]);
                },
                'label' => 'Конец периода',
                'format' => 'raw'
            ],
            [
                'attribute' => 'count',
                'label' => 'Кол-во',
                'value' => function ($model) {
                    return ($model['end'] - $model['start']) + 1;
                }
            ],
            [
                'attribute' => 'registry_id',
                'label' => 'Реестр',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model['filling'] == 'fill' ? ($model['registry_id'] ?
                        Html::a(
                            'Реестр #' . $model['registry_id'],
                            ['voip/registry/edit', 'id' => $model['registry_id']],
                            $model['is_alien_registry'] ? ['class' => 'label label-danger'] : [])
                        : null)
                        : '';
                }
            ]
        ],
    ]);

} ?>

<?php if ($model->id): ?>
    <div class="col-sm-12 form-group">
        <?= $this->render('//layouts/_showVersion', ['model' => $model->registry]) ?>
        <?= $this->render('//layouts/_showHistory', ['model' => $model->registry]) ?>
    </div>
<?php endif; ?>