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
use app\models\TariffNumber;
use app\models\TariffVoipPackage;
use app\models\Region;
use app\models\VoipNumber;
use app\widgets\DateControl as CustomDateControl;
use app\helpers\DateTimeZoneHelper;

/** @var $clientAccount \app\models\ClientAccount */
/** @var $usage \app\models\UsageVoip */
/** @var $model \app\forms\usage\UsageVoipEditForm */

$types = [
    'number' => 'Номер',
    '7800' => '7800',
    'line' => 'Линия без номера',
    'operator' => 'Оператор',
];

$noYes = [
    '0' => 'Нет',
    '1' => 'Да',
];

$tariffStatus = [
    'public' => 'Публичный',
    'special' => 'Специальный',
    'transit' => 'Переходный',
    'operator' => 'Оператор',
    'test' => 'Тестовый',
    '7800' => '7800',
    'archive' => 'Архивный',
];

$status = [
    'connecting' => 'Подключаемый',
    'working' => 'Включенный',
];


echo Html::formLabel('Редактирование номера');
echo Breadcrumbs::widget([
    'links' => [
        'homeLink' => [
            'label' => $clientAccount->company,
            'url' => ['client/view', 'id' => $clientAccount->id]
        ],
        ['label' => 'Телефония Номера', 'url' => Url::toRoute(['/', 'module' => 'services', 'action' => 'vo_view'])],
        'Редактирование номера'
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
            ['type' => Form::INPUT_RAW, 'value' => '
                <div class="form-group">
                    <label class="control-label">Тип</label>
                    <input type="text" class="form-control" value="' . $types[$usage->type_id] . '" readonly="readonly" />
                </div>
            '],
            ['type' => Form::INPUT_RAW, 'value' => '
                <div class="form-group">
                    <label class="control-label">Точка подключения</label>
                    <input type="text" class="form-control" value="' . $usage->connectionPoint->name . '" readonly="readonly" />
                </div>
            '],
            ['type' => Form::INPUT_RAW, 'value' => '
                <div class="form-group">
                    <label class="control-label">Страна</label>
                    <input type="text" class="form-control" value="' . $clientAccount->country->name . '" readonly="readonly" />
                </div>
            '],
            ['type' => Form::INPUT_RAW, 'value' => '
                <div class="form-group">
                    <label class="control-label">Валюта</label>
                    <input type="text" class="form-control" value="' . $clientAccount->currency . '" readonly="readonly" />
                </div>
            '],
        ],
    ]);

    if ($model->type_id == 'number') {
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 4,
            'attributes' => [
                'number_tariff_id' => [
                    'type' => Form::INPUT_RAW,
                    'value' => '
                        <div class="form-group">
                            <label class="control-label">DID группа</label>
                            <input type="text" class="form-control" value="' . VoipNumber::findOne($model->did)->didGroup->name . '" readonly="readonly" />
                        </div>
                    ',
                ],
                'did' => ['type' => Form::INPUT_TEXT, 'options' => ['readonly' => 'readonly']],
                'no_of_lines' => ['type' => Form::INPUT_TEXT],
            ],
        ]);
    }
    else if ($model->type_id == '7800') {
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 4,
            'attributes' => [
                'did' => ['type' => Form::INPUT_TEXT, 'options' => ['readonly' => 'readonly']],
                'no_of_lines' => ['type' => Form::INPUT_TEXT],
                'line7800_id' => ['type' => Form::INPUT_TEXT, 'options' => ['disabled' => 'disabled']],
            ],
        ]);
    }
    else {
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 4,
            'attributes' => [
                'did' => ['type' => Form::INPUT_TEXT, 'options' => ['readonly' => 'readonly']],
                'no_of_lines' => ['type' => Form::INPUT_TEXT],
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

    $region = Region::findOne($model->region);
    $address_options = [
        'data-datacenter-address' => $region->datacenter->address,
    ];
    if ($model->address_from_datacenter_id) {
        $address_options = [
            'class' => 'input_help_icon',
            'placeholder' => $model->address,
        ];
        $model->address = '';
    }

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 1,
        'attributes' => [
            'address' => [
                'type' => Form::INPUT_TEXT,
                'options' => $address_options,
            ],
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
                            'onClick' => 'self.location = "' . Url::toRoute(['/', 'module' => 'services', 'action' => 'vo_view']) . '";',
                        ]) .
                        Html::button('Сохранить', ['class' => 'btn btn-primary', 'onClick' => "submitForm('edit')"]),
                        ['style' => 'text-align: right; padding-right: 0px;']
                    )
            ],
        ],
    ]);

    echo Html::hiddenInput('scenario', 'default', ['id' => 'scenario']);
    ActiveForm::end();
    ?>

    <script>
        function submitForm(scenario) {
            $('#scenario').val(scenario);
            $('#<?=$form->getId()?>')[0].submit();
            $('.form-reload').change(function() {
                submitForm('default');
            });
        }
    </script>


    <h2>История тарифов:</h2>
    <table class="table table-condensed table-striped table-bordered">
        <thead>
        <tr>
            <th>Период</th>
            <th>Тариф</th>
            <th>Добавлено</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php
            if ($usage->actual_to == '2029-01-01') {
                $actualTo = null;
            } else {
                $actualTo = new DateTime($usage->actual_to, $clientAccount->timezone);
            }
            $now = new DateTime('now', $clientAccount->timezone);
            $date_activation = '';
        ?>
        <?php foreach($tariffHistory as $item): /** var $item LogTarif */ ?>
            <?php
            if ($item->date_activation == $date_activation) {
                continue;
            }
            $date_activation = $item->date_activation;

            $actualFrom = new DateTime($item->date_activation, $clientAccount->timezone);
            $isActive = $actualFrom <= $now && ($actualTo === null || $actualTo >= $now);
            ?>
            <tr style="<?= $isActive ? 'font-weight: bold;' : '' ?>">
                <td nowrap><?= $actualFrom->format('Y-m-d') . ' - ' . ($actualTo !== null ? $actualTo->format('Y-m-d') :  '') ?></td>
                <td width="100%">
                    <?= Html::encode($item->voipTariffMain->name) ?>
                    / Моб <?= Html::encode($item->voipTariffLocalMob->name_short) ?>
                    / МГ <?= Html::encode($item->voipTariffRussia->name_short) ?>
                    / МГ Моб <?= Html::encode($item->voipTariffRussiaMob->name_short) ?>
                    / МН <?= Html::encode($item->voipTariffIntern->name_short) ?>

                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                    Мин платеж:

                    <?php if ($item->dest_group != 0 && $item->minpayment_group): ?>
                        Набор:
                        <?= strpos($item->dest_group, '5') !== false ? 'Моб' : '' ?>
                        <?= strpos($item->dest_group, '1') !== false ? 'МГ' : '' ?>
                        <?= strpos($item->dest_group, '2') !== false ? 'МН' : '' ?>
                        = <?= $item->minpayment_group ?> /
                    <?php endif; ?>

                    <?php if (strpos($item->dest_group, '5') === false && $item->minpayment_local_mob):?>
                    Моб = <?= $item->minpayment_local_mob ?> /
                    <?php endif; ?>
                    <?php if (strpos($item->dest_group, '1') === false && $item->minpayment_russia):?>
                    МГ = <?= $item->minpayment_russia ?> /
                    <?php endif; ?>
                    <?php if (strpos($item->dest_group, '2') === false && $item->minpayment_intern):?>
                    МН <?= $item->minpayment_intern ?> /
                    <?php endif; ?>
                </td>
                <td nowrap>
                    <?php
                        $user = User::findOne($item->id_user);
                        $user = $user ? $user->name : $item->id_user;
                        echo DateTimeZoneHelper::getDateTime($item->ts) . ' / ' . $user;
                    ?>
                </td>
                <td>

                    <?php
                    if ($actualFrom > $now) {
                        $formModel = new \app\forms\usage\UsageVoipDeleteHistoryForm();
                        $formModel->id = $item->id;


                        $form2 = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL, 'options' => ['style' => 'display: inline-block;']]);
                        echo Html::activeHiddenInput($formModel, 'id');
                        echo Html::submitButton('Удалить', ['class' => 'btn btn-primary btn-xs']);
                        $form2->end();
                    }
                    ?>
                </td>
            </tr>
            <?php
            $actualTo = $actualFrom;
            $actualTo->modify('-1 day');
            ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Изменить тариф:</h2>
    <?php

    $form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'tariff_change_date' => ['type' => Form::INPUT_WIDGET, 'widgetClass' => DateControl::className()],
            ['type' => Form::INPUT_RAW],
            ['type' => Form::INPUT_RAW],
            ['type' => Form::INPUT_RAW],
        ]
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 4,
        'attributes' => [
            'tariff_main_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => TariffVoip::dao()->getMainList(false, $model->connection_point_id, $clientAccount->currency, $model->tariff_main_status), 'options' => ['class' => 'select2']],
            'tariff_main_status' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $tariffStatus, 'options' => ['class' => 'form-reload2']],
            ['type' => Form::INPUT_RAW],
            ['type' => Form::INPUT_RAW],
            'tariff_local_mob_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => TariffVoip::dao()->getLocalMobList(false, $model->connection_point_id, $clientAccount->currency), 'options' => ['class' => 'select2 form-reload2']],
            'tariff_group_local_mob_price' => ['type' => Form::INPUT_TEXT],
            'tariff_group_local_mob' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $noYes, 'options' => ['class' => 'form-reload2']],
            ['type' => Form::INPUT_RAW],
            'tariff_russia_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => TariffVoip::dao()->getRussiaList(false, $model->connection_point_id, $clientAccount->currency), 'options' => ['class' => 'select2 form-reload2']],
            'tariff_group_russia_price' => ['type' => Form::INPUT_TEXT],
            'tariff_group_russia' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $noYes, 'options' => ['class' => 'form-reload2']],
            ['type' => Form::INPUT_RAW],
            'tariff_russia_mob_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => TariffVoip::dao()->getRussiaList(false, $model->connection_point_id, $clientAccount->currency), 'options' => ['class' => 'select2']],
            ['type' => Form::INPUT_RAW],
            ['type' => Form::INPUT_RAW],
            ['type' => Form::INPUT_RAW],
            'tariff_intern_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => TariffVoip::dao()->getInternList(false, $model->connection_point_id, $clientAccount->currency), 'options' => ['class' => 'select2 form-reload2']],
            'tariff_group_intern_price' => ['type' => Form::INPUT_TEXT],
            'tariff_group_intern' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $noYes, 'options' => ['class' => 'form-reload2']],
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

    $logTariff = $usage->getCurrentLogTariff();
    if ($logTariff) {
        $mainTariff = TariffVoip::findOne($logTariff->id_tarif);
        if ($mainTariff) {
            echo Form::widget([
                'model' => $model,
                'form' => $form,
                'columns' => 1,
                'attributes' => [
                    'mass_change_tariff' => ['type' => Form::INPUT_CHECKBOX, 'label' => 'Массово изменить тариф у всех услуг с тарифом "' . $mainTariff->name . '"'],
                ],
            ]);
        }
    }

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'attributes' => [
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    '<div class="col-md-12">' .
                    Html::button('Изменить тариф', ['class' => 'btn btn-primary', 'onclick' => "submitForm2('change-tariff')"]) .
                    '</div>'
            ],
        ],
    ]);


    echo Html::hiddenInput('scenario', 'default', ['id' => 'scenario2']);
    ActiveForm::end();
    ?>
</div>

<script>
    function submitForm2(scenario) {
        $('#scenario2').val(scenario);
        $('#<?=$form->getId()?>')[0].submit();
    }
    $('.form-reload2').change(function() {
        submitForm2('default');
    });
</script>


<h2>Подключенные пакеты:</h2>
<table class="table table-condensed table-striped table-bordered">
    <col width="10%" />
    <col width="* " />
    <col width="5%" />
    <thead>
        <tr>
            <th>Период</th>
            <th>Тариф</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php /*foreach ($usagePackages as $package): ?>
            <?php
            $actualTo =
                round(
                    (
                        (new DateTime($package->expire_dt))->getTimestamp() - $now->getTimestamp()
                    ) / 365 / 24 / pow(60, 2)
                ) > 20
                    ? '&#8734' :
                    $package->actual_to;

            $isActive = $package->actual_from <= $now->format('Y-m-d') && $package->actual_to >= $now->format('Y-m-d');
            ?>
            <tr style="<?= ($isActive ? 'font-weight: bold;' : ''); ?>">
                <td nowrap="nowrap"><?= $package->actual_from . ' - ' . $actualTo; ?></td>
                <td><?= $package->tariff->name; ?></td>
                <td align="center">
                    <?php
                    if ($package->actual_from > $now->format('Y-m-d')) {
                        echo Html::a('Удалить', '/usage/voip/detach-package?id=' . $package->id, [
                            'class' => 'btn btn-primary btn-xs',
                            'onClick' => 'return confirm("Вы уверены, что хотите отменить пакет ?")',
                        ]);
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach;*/ ?>
    </tbody>
</table>

<h2>Добавить пакет:</h2>
<?php

$formModel = new \app\forms\usage\UsageVoipAddPackageForm;
$formModel->usage_voip_id = $usage->id;
$form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

$formModel->actual_from = $model->today->format('Y-m-d');

echo Html::activeHiddenInput($formModel, 'usage_voip_id');

echo Form::widget([
    'model' => $formModel,
    'form' => $form,
    'columns' => 3,
    'attributes' => [
        'tariff_id' => [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => TariffVoipPackage::dao()->getMainList(true, $model->clientAccount->country->code, $model->connection_point_id, $clientAccount->currency),
            'options' => ['class' => 'select2']
        ],
        'actual_from' => [
            'type' => Form::INPUT_WIDGET,
            'widgetClass' => \app\widgets\DateControl::className(),
            'options' => [
                'autoWidgetSettings' => [
                    DateControl::FORMAT_DATE => [
                        'options' => [
                            'pluginOptions' => [
                                'todayHighlight' => true,
                                'startDate' => 'today',
                            ],
                        ],
                    ],
                ],
            ]
        ],
        'actions' => [
            'type' => Form::INPUT_RAW,
            'value' =>
                '<div class="col-md-12" style="margin-top: 20px;">' .
                    Html::submitButton('Добавить пакет', ['class' => 'btn btn-primary',]) .
                '</div>'
        ],
    ]
]);

ActiveForm::end();

?>
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<link href="/css/behaviors/text-field-help-icon.css" rel="stylesheet" />
<script type="text/javascript" src="/js/behaviors/usage-voip-address-from-datacenter.js"></script>
