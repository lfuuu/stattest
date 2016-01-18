<?php
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use kartik\widgets\DatePicker;
use yii\helpers\Url;
use app\models\User;
use app\models\TariffVoip;
use app\models\TariffVoipPackage;
use app\models\Region;
use app\models\Number;
use app\widgets\DateControl as CustomDateControl;
use app\helpers\DateTimeZoneHelper;


echo Html::formLabel('Редактирование пакета');
echo Breadcrumbs::widget([
    'links' => [
        'homeLink' => [
            'label' => $model->package->clientAccount->company,
            'url' => ['client/view', 'id' => $model->package->clientAccount->id]
        ],
        ['label' => 'Телефония Номера', 'url' => Url::toRoute(['/', 'module' => 'services', 'action' => 'vo_view'])],
        ['label' => $model->package->usageVoip->E164, 'url' => Url::toRoute(['/usage/voip/edit', 'id' => $model->package->usageVoip->id])],
        'Редактирование пакета'
    ],
]);
?>

<div class="well">
    <?php
        $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

        $widgetConf = [
            'model' => $model,
            'form' => $form,
            'columns' => 4,
            'attributes' => [
                'connecting_date' => [
                    'type' => Form::INPUT_WIDGET,
                    'widgetClass' => CustomDateControl::className(),
                    'options' => [
                        'readonly' => true,
                        'disabled' => true,
                        'autoWidgetSettings' => [
                            DateControl::FORMAT_DATE => [
                                'class' => '\app\widgets\DatePicker',
                                'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                                'options' => [
                                    'pluginOptions' => [
                                        'orientation' => 'top left',
                                    ],
                                    'addons' => [
                                        'todayButton' => [],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'disconnecting_date' => [
                    'type' => Form::INPUT_WIDGET,
                    'widgetClass' => CustomDateControl::className(),
                    'options' => [
                        'autoWidgetSettings' => [
                            DateControl::FORMAT_DATE => [
                                'class' => '\app\widgets\DatePicker',
                                'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                                'options' => [
                                    'options' => [
                                        'placeholder' => $model->disconnecting_date ?: 'Не задано',
                                    ],
                                    'pluginOptions' => [
                                        'orientation' => 'top left',
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
            ]
        ];

        if (!$model->is_package_active) {
            $widgetConf['attributes']['disconnecting_date']['options']['readonly'] = 'readonly';
        }

    echo Form::widget($widgetConf);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 2,
        'attributes' => [
            'tariff' => [
                'type' => Form::INPUT_TEXT,
                'options' => [
                    'readonly' => 'readonly'
                ]
            ],
            ['type' => Form::INPUT_RAW],
        ],
    ]);

    $btns = Html::button('Ok', [
        'class' => 'btn btn-info',
        'onClick' => 'self.location = "' . Url::toRoute(['/usage/voip/edit', 'id' => $model->package->usageVoip->id]) . '";',
    ]);

    if ($model->is_package_active) {
        $btns = Html::button('Отменить', [
            'class' => 'btn btn-link',
            'style' => 'margin-right: 15px;',
            'onClick' => 'self.location = "' . Url::toRoute(['/usage/voip/edit', 'id' => $model->package->usageVoip->id]) . '";',
        ]) .
        Html::submitButton('Сохранить', ['class' => 'btn btn-primary']);
    }



    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' => Html::tag('div', $btns, ['style' => 'text-align: right; padding-right: 0px;'])
            ],
        ],
    ]);

    ActiveForm::end();
    ?>

</div>

<br />
<br />
<br />
