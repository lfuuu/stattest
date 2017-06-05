<?php

use app\classes\DateTimeWithUserTimezone;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\Number;
use app\models\TariffVoip;
use app\models\TariffVoipPackage;
use app\models\User;
use app\modules\nnp\models\NdcType;
use app\widgets\DateControl as CustomDateControl;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var $clientAccount \app\models\ClientAccount */
/** @var $usage \app\models\UsageVoip */
/** @var $model \app\forms\usage\UsageVoipEditForm */
/** @var \app\classes\BaseView $this */

$types = \app\modules\uu\models\Tariff::getVoipTypesByCountryId();

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

$isPriceIncludeVat = $model->clientAccount->is_voip_with_tax;

if ($usage->actual_to == '2029-01-01') {
    $actualTo = null;
} else {
    $actualTo = new DateTime($usage->actual_to, $clientAccount->timezone);
}
$now = new DateTime('now', $clientAccount->timezone);
$date_activation = '';

$model->tariffGroupRussiaPrice   = $model->getMinByTariff($model->tariff_russia_id);
$model->tariffGroupLocalMobPrice = $model->getMinByTariff($model->tariff_local_mob_id);
$model->tariffGroupInternPrice   = $model->getMinByTariff($model->tariff_intern_id);

$model->tariff_group_russia_price == $model->tariffGroupRussiaPrice && $model->tariff_group_russia_price = null;
$model->tariff_group_local_mob_price == $model->tariffGroupLocalMobPrice && $model->tariff_group_local_mob_price  = null;
$model->tariff_group_intern_price == $model->tariffGroupInternPrice && $model->tariff_group_intern_price = null;

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

    $this->registerJsVariable('editFormId', $form->getId(), 'usage');

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

    if ($model->type_id == Number::TYPE_NUMBER) {
        $number = Number::findOne($model->did);
        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 4,
            'attributes' => [
                'ndc_type_id' => [
                    'type' => Form::INPUT_RAW,
                    'value' => '
                        <div class="form-group">
                            <label class="control-label">Тип номера</label>
                            <input type="text" class="form-control" value="' . ($model->did? $number->ndcType->name: '') . '" readonly="readonly" />
                        </div>
                    ',
                ],
                'did_group_id' => [
                    'type' => Form::INPUT_RAW,
                    'value' => '
                        <div class="form-group">
                            <label class="control-label">DID группа</label>
                            <input type="text" class="form-control" value="' . ($model->did? $number->didGroup->name: '') . '" readonly="readonly" />
                        </div>
                    ',
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
    else if ($model->type_id == Number::TYPE_7800) {
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
    } else { // line
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
                            'options' => [
                                'pluginOptions' => [
                                    'todayHighlight' => true,
                                ],
                            ],
                        ],
                    ],
                    'disabled' => true
                ],
            ],
            'disconnecting_date' => [
                'type' => Form::INPUT_WIDGET,
                'widgetClass' => CustomDateControl::className(),
                'options' => [
                    'autoWidgetSettings' => [
                        DateControl::FORMAT_DATE => [
                            'options' => [
                                'options' => [
                                    'placeholder' => 'Спустя многие годы...',
                                ],
                                'pluginOptions' => [
                                    'todayHighlight' => true,
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
            /** @var LogTarif[] $tariffHistory */
            /** @var LogTarif $item */
            foreach($tariffHistory as $item):
                if ($item->date_activation == $date_activation) {
                    continue;
                }
                $date_activation = $item->date_activation;

                $actualFrom = new DateTime($item->date_activation, $clientAccount->timezone);
                $isActive = $actualFrom <= $now && ($actualTo === null || $actualTo >= $now);
                ?>
                <tr style="<?= $isActive ? 'font-weight: bold;' : '' ?>">
                    <td nowrap><?= $actualFrom->format(DateTimeZoneHelper::DATE_FORMAT) . ' - ' . ($actualTo !== null ? $actualTo->format(DateTimeZoneHelper::DATE_FORMAT) :  '') ?></td>
                    <td width="100%">
                        <?= Html::encode($item->voipTariffMain->name) ?>
                        (<?= $item->voipTariffMain->month_number; ?>-<?= $item->voipTariffMain->month_line; ?>)
                        / Моб <?= Html::encode($item->voipTariffLocalMob? $item->voipTariffLocalMob->name_short: '') ?>
                        / МГ <?= Html::encode($item->voipTariffRussia? $item->voipTariffRussia->name_short: '') ?>
                        / МГ Моб <?= Html::encode($item->voipTariffRussiaMob? $item->voipTariffRussiaMob->name_short:'') ?>
                        / МН <?= Html::encode($item->voipTariffIntern? $item->voipTariffIntern->name_short: '') ?>

                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                        Мин платеж:

                        <?php if ($item->dest_group != 0 && $item->minpayment_group): ?>
                            Набор:
                            <?= strpos($item->dest_group, '5') !== false ? 'Моб' : '' ?>
                            <?= strpos($item->dest_group, '1') !== false ? 'МГ' : '' ?>
                            <?= strpos($item->dest_group, '2') !== false ? 'МН' : '' ?>
                            = <?= $item->minpayment_group ?> /
                        <?php endif; ?>

                        <?php if (strpos($item->dest_group, '5') === false && $item->minpayment_local_mob): ?>
                            Моб = <?= $item->minpayment_local_mob ?> /
                        <?php endif; ?>

                        <?php if (strpos($item->dest_group, '1') === false && $item->minpayment_russia): ?>
                            МГ = <?= $item->minpayment_russia ?> /
                        <?php endif; ?>

                        <?php if (strpos($item->dest_group, '2') === false && $item->minpayment_intern): ?>
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
                            $formModel = new \app\forms\usage\UsageVoipDeleteHistoryForm;
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

    $this->registerJsVariable('tariffEditFormId', $form->getId(), 'usage');

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
                'options' => ['class' => 'select2'],
                'hint' => !$model->tariff_main_id ? Html::tag('span', 'Текущее значение тарифа не установлено!',
                    ['class' => 'alert-danger']) : '',
            ],
            'tariff_main_status' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $tariffStatus,
                'options' => ['class' => 'form-reload2']
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
                    'class' => 'select2 form-reload2',
                ],
                'hint' => !$model->tariff_local_mob_id ? Html::tag('span', 'Текущее значение тарифа не установлено!',
                    ['class' => 'alert-danger']) : '',
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
                'options' => ['class' => 'form-reload2']
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
                    'class' => 'select2 form-reload2'
                ],
                'hint' => !$model->tariff_russia_id ? Html::tag('span', 'Текущее значение тарифа не установлено!',
                    ['class' => 'alert-danger']) : '',
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
                'options' => ['class' => 'form-reload2']
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
                'options' => ['class' => 'select2'],
                'hint' => !$model->tariff_russia_mob_id ? Html::tag('span', 'Текущее значение тарифа не установлено!',
                    ['class' => 'alert-danger']) : '',
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
                    'class' => 'select2 form-reload2'
                ],
                'hint' => !$model->tariff_intern_id ? Html::tag('span', 'Текущее значение тарифа не установлено!',
                    ['class' => 'alert-danger']) : '',
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
                'options' => ['class' => 'form-reload2']
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

    if ($usage->logTariff) {
        $mainTariff = TariffVoip::findOne($usage->logTariff->id_tarif);
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

<h2>Подключенные пакеты:</h2>
<table class="table table-condensed table-striped table-bordered">
    <colgroup>
        <col width="10%" />
        <col width="*" />
        <col width="15%" />
        <col width="15%" />
        <col width="5%" />
    </colgroup>
    <thead>
        <tr>
            <th>Период</th>
            <th>Тариф</th>
            <th>Добавлено</th>
            <th>Изменено</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($usagePackages as $package): ?>
            <?php
            $actualTo = (new DateTimeWithUserTimezone($package->expire_dt))->formatWithInfinity(DateTimeZoneHelper::DATE_FORMAT);
            $isActive = $package->actual_from <= $now->format(DateTimeZoneHelper::DATE_FORMAT) && $package->actual_to >= $now->format(DateTimeZoneHelper::DATE_FORMAT);
            ?>
            <tr style="<?= ($isActive ? 'font-weight: bold;' : '') . ($package->status === 'connecting' ? 'background-color: #ffe0e0;' : ''); ?>">
                <td nowrap="nowrap">
                    <?= Html::a($package->actual_from . ' - ' . $actualTo, ['/usage/voip/edit-package', 'id' => $package->id]); ?>
                </td>
                <td><?= $package->tariff->name; ?></td>
                <?php if ($packagesHistory[$package->id]):
                    $hist = $packagesHistory[$package->id];
                    ?>
                        <td>
                            <?= DateTimeZoneHelper::getDateTime($hist->ts) ?><br />
                            <?= ($hist->user ? $hist->user->name : $hist->id_user); ?>
                        </td>
                <?php else: ?>
                    <td>&nbsp;</td>
                <?php endif; ?>

                <td>
                    <?php
                    $updated = $package->lastUpdateData;
                    echo DateTimeZoneHelper::getDateTime($updated->date) . '<br />';

                    if (isset($updated->properties->user_id)) {
                        if (($user = User::findOne($updated->properties->user_id)) !== null) {
                            /** @var User $user */
                            echo $user->name;
                        }
                    }
                    ?>
                </td>

                <td align="center">
                    <?php
                    if ($package->actual_from > $now->format(DateTimeZoneHelper::DATE_FORMAT)) {
                        echo Html::a('Удалить', ['/usage/voip/detach-package', 'id' => $package->id], [
                            'class' => 'btn btn-primary btn-xs',
                            'onClick' => 'return confirm("Вы уверены, что хотите отменить пакет ?")',
                        ]);
                    }
                    else {
                        if ($package->actual_from <= $now->format(DateTimeZoneHelper::DATE_FORMAT) && $now->format(DateTimeZoneHelper::DATE_FORMAT) <= $package->actual_to) {
                            echo Html::a('Редактировать', ['/usage/voip/edit-package', 'id' => $package->id], [
                                'class' => 'btn btn-info btn-link btn-xs'
                            ]);
                        }
                    }
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2>Добавить пакет:</h2>
<?php

$formModel = new \app\forms\usage\UsageVoipAddPackageForm;
$formModel->usage_voip_id = $usage->id;
$form = ActiveForm::begin(['type' => ActiveForm::TYPE_VERTICAL]);

$formModel->actual_from = $model->today->format(DateTimeZoneHelper::DATE_FORMAT);

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
