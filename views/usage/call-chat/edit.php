<?php
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use yii\helpers\Url;
use app\widgets\DateControl as CustomDateControl;
use kartik\widgets\DatePicker;
use \app\models\TariffCallChat;

$status = [
    'connecting' => 'Подключаемый',
    'working' => 'Включенный',
];


echo Html::formLabel(($model->usage ? 'Редактирование услуги' : 'Добавление услуги') . ' Звонок_чат');
echo Breadcrumbs::widget([
    'links' => [
        'homeLink' => [
            'label' => $clientAccount->company,
            'url' => ['client/view', 'id' => $clientAccount->id]
        ],
        ['label' => 'Услуга Звонок_чат', 'url' => ['usage/call-chat']],
        'Редактирование услуги'
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
            'actual_from' => [
                'type' => Form::INPUT_WIDGET,
                'widgetClass' => CustomDateControl::className(),
                'options' => [
                    'autoWidgetSettings' => [
                        DateControl::FORMAT_DATE => [
                            'class' => '\app\widgets\DatePicker',
                            'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                            'options' => [
                                'addons' => [
                                    'todayButton' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'actual_to' => [
                'type' => Form::INPUT_WIDGET,
                'widgetClass' => CustomDateControl::className(),
                'options' => [
                    'autoWidgetSettings' => [
                        DateControl::FORMAT_DATE => [
                            'class' => '\app\widgets\DatePicker',
                            'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                            'options' => [
                                'options' => [
                                    'placeholder' => $model->actual_to ?:'Не задано',
                                ],
                                'addons' => [
                                    'todayButton' => [],
                                    'clearButton' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $status],
        ],
    ]);


    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'tarif_id' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => TariffCallChat::dao()->getList($model->clientAccount->currency, true),
                'columnOptions'=>['colspan'=>3]
            ],
            'comment' => [
                'type' => Form::INPUT_TEXT
            ]

        ],
    ]);


    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::button('Отменить', [
                            'class' => 'btn btn-link',
                            'style' => 'margin-right: 15px;',
                            'onClick' => 'self.location = "' . Url::toRoute(['usage/call-chat']) . '";',
                        ]) .
                        Html::submitButton('Сохранить', ['class' => 'btn btn-primary']),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);

    ActiveForm::end();
    ?>

</div>