<?php

/** @var \app\forms\voip\RegistryForm $model */
use app\classes\Html;
use app\models\City;
use app\models\Country;
use app\models\Number;
use app\models\voip\Registry;
use app\modules\nnp\models\NumberRange;
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
    'url' => ($model->id ? ['/voip/registry/edit', 'id' => $model->id] : ['/voip/registry/add'])
];

echo Breadcrumbs::widget([
    'links' => $links
]);
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
                ]
            ],
            'city_id' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $cityList,
                'options' => [
                    'class' => 'formReload'
                ]
            ],
            'source' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => \app\classes\enum\VoipRegistrySourceEnum::$names,
            ],
            'account_id' => [
                'type' => Form::INPUT_TEXT,
            ]
        ]
    ]);

    /*
    $maskedInputWidgetConfig = [
        'type' => Form::INPUT_WIDGET,
        'widgetClass' => \app\classes\MaskedInput::className(),
        'options' => [
            'mask' => str_replace(["9", "0"], ["\\9", "9"], $model->city_number_format), //символ-маска по-умолчанию цифра "9"
            'options' => [
                'class' => 'form-control',
                'placeholder' => $model->city_number_format,
            ]
        ]
    ];
    */

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'number_type_id' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => \app\models\NumberType::getList(),
            ],
            'ndc' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => NumberRange::getNDCList($model->country_id, $model->city_id)
            ],
            'number_from' => [
                'type' => Form::INPUT_TEXT,
            ],
            'number_to' => [
                'type' => Form::INPUT_TEXT,
            ],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 1,
        'attributes' => [
            'comment' => [
                'type' => Form::INPUT_TEXT
            ]
        ]
    ]);


    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 3,
        'attributes' => [
            'check-number' => [
                'type' => Form::INPUT_RAW,
                'value' => ($model->id ? Html::submitButton('Проверить номера', [
                        'class' => 'btn btn-info',
                        'name' => 'check-numbers',
                        'value' => 'Проверить номера'
                    ])
                    . (in_array($model->registry->status, [Registry::STATUS_PARTLY, Registry::STATUS_EMPTY]) ?
                        ' ' . Html::submitButton('Залить номера', [
                            'class' => 'btn btn-success',
                            'name' => 'fill-numbers',
                            'value' => 'Залить номера'
                        ])
                        : '')
                    . ($statusInfo && isset($statusInfo[Number::STATUS_NOTSALE]) && $statusInfo[Number::STATUS_NOTSALE] ?
                        ' ' . Html::submitButton('Передать в продажу номера (' . $statusInfo[Number::STATUS_NOTSALE] . ' шт.)', [
                            'class' => 'btn btn-warning',
                            'name' => 'to-sale',
                            'value' => 'Передать в продажу номера'
                        ])
                        : '')


                    : '')
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
                                'name' => 'save',
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
            return ['class' => $model['filling'] == 'pass' ? 'warning' : 'success'];
        },
        'columns' => [
            [
                'attribute' => 'filling',
                'label' => 'Состояние',
                'value' => function ($model) {
                    return $model['filling'] == 'pass' ? 'Пропущено' : 'Заполнено';
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