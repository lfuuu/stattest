<?php

/**
 * @var \app\forms\voip\RegistryForm $model
 * @var bool $creatingMode
 * @var array $checkList
 * @var array $statusInfo
 */
use app\classes\Html;
use app\models\City;
use app\models\Country;
use app\models\Number;
use app\models\voip\Registry;
use app\modules\nnp\models\NdcType;
use kartik\builder\Form;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;


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

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 4,
            'attributes' => [
                'country_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $countryList,
                    'options' => [
                            'class' => 'formReload'
                        ] + ($isEditable ? [] : $readonlyOptions),
                ],
                'city_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $cityList,
                    'options' => [
                            'class' => 'formReload'
                        ] + ($isEditable ? [] : $readonlyOptions),
                ],
                'source' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => \app\classes\enum\VoipRegistrySourceEnum::$names,
                    'options' => $isEditable ? [] : $readonlyOptions,
                ],
                'account_id' => [
                    'type' => Form::INPUT_TEXT,
                    'options' => $isEditable ? [] : $readonlyOptions,
                ]
            ]
        ]);

        $maskedInputWidgetConfig = [
            'type' => Form::INPUT_WIDGET,
            'widgetClass' => \app\classes\MaskedInput::className(),
            'options' => [
                'mask' => $model->city_number_format,
                'options' => [
                        'class' => 'form-control',
                    ] + ($isEditable ? [] : $readonlyOptions),
            ]
        ];


        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 4,
            'attributes' => [
                'ndc_type_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => NdcType::getList(),
                    'options' => [
                            'class' => 'formReload',
                        ] + ($isEditable ? [] : $readonlyOptions),
                ],
                'ndc' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $model->ndcList,
                    'options' => [
                            'class' => 'formReload'
                        ] + ($isEditable ? [] : $readonlyOptions),
                ],
                'number_from' => $maskedInputWidgetConfig,
                'number_to' => $maskedInputWidgetConfig,
            ],
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 2,
            'attributes' => [
                'comment' => [
                    'type' => Form::INPUT_TEXT,
                    'options' => $isEditable ? [] : $readonlyOptions,
                ],
                'operator' => [
                    'type' => Form::INPUT_TEXT,
                    'options' => $readonlyOptions, // опредляется автоматически из ННП
                ],
            ]
        ]);


        $value = '';
        if ($model->id && $isSubmitable) {
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
        }

        if ($isSubmitable) {
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
                            $isEditable ?
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
                                            'name' => 'save',
                                            'value' => 'Сохранить'
                                        ]),
                                    ['style' => 'text-align: right; padding-right: 0px;']
                                ) :
                                ''
                    ],
                ],
            ]);
        }

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
            return ['class' => $model['filling'] === 'pass' ? 'warning' : 'success'];
        },
        'columns' => [
            [
                'attribute' => 'filling',
                'label' => 'Состояние',
                'value' => function ($model) {
                    return $model['filling'] === 'pass' ? 'Пропущено' : 'Заполнено';
                }
            ],
            [
                'attribute' => 'start',
                'label' => 'Начало периода'
            ],
            [
                'attribute' => 'end',
                'label' => 'Конец периода'
            ],
            [
                'attribute' => 'count',
                'label' => 'Количество',
                'value' => function ($model) {
                    return ($model['end'] - $model['start']) + 1;
                }
            ],
        ],
    ]);

} ?>

<?php if ($model->id): ?>
    <div class="col-sm-12 form-group">
        <?= $this->render('//layouts/_showVersion', ['model' => $model->registry]) ?>
        <?= $this->render('//layouts/_showHistory', ['model' => $model->registry]) ?>
    </div>
<?php endif; ?>