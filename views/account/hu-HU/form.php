<?php

use app\classes\Html;
use app\models\ClientAccountOptions;
use app\models\Region;
use app\models\PriceType;
use kartik\widgets\Select2;
use kartik\builder\Form;
use app\models\ClientAccount;
use app\models\Currency;
?>

<div class="row" style="width: 1100px;">
    <?php
    echo Form::widget([
        'model' => $model,
        'form' => $f,
        'columns' => 4,
        'attributeDefaults' => [
            'container' => ['class' => 'col-sm-12'],
            'type' => Form::INPUT_TEXT
        ],
        'attributes' => [
            'region' => [
                'type' => Form::INPUT_RAW,
                'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['region'] . '</label>'
                    . Select2::widget([
                        'model' => $model,
                        'attribute' => 'region',
                        'data' => Region::getList(),
                        'options' => ['placeholder' => 'Начните вводить название'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])
                    . '</div>'
            ],
            'timezone_name' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => Region::getTimezoneList()],
            ['type' => Form::INPUT_RAW,],
            ['type' => Form::INPUT_RAW,],

            'nal' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ClientAccount::$nalTypes],
            'currency' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => Currency::map()],
            'price_type' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => PriceType::getList()],
            ['type' => Form::INPUT_RAW,],

            'credit' => ['columnOptions' => ['id' => 'credit-size']],
            ['type' => Form::INPUT_RAW,],
            'lk_balance_view_mode' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ClientAccount::$balanceViewMode],
            'account_version' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ClientAccount::$versions],

            'voip_credit_limit_day' => ['columnOptions' => ['colspan' => 1],],
            [
                'type' => Form::INPUT_RAW,
                'value' => function ($data) use ($f, $model) {
                    $voipCreditLimitDayWhen = (array)$data->model->getOption(ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_WHEN);
                    $voipCreditLimitDayValue = (array)$data->model->getOption(ClientAccountOptions::OPTION_VOIP_CREDIT_LIMIT_DAY_VALUE);

                    $result = [];

                    if (count($voipCreditLimitDayWhen)) {
                        $result[] = 'Дата пересчета: ' . array_shift($voipCreditLimitDayWhen);
                    }

                    if (count($voipCreditLimitDayValue)) {
                        $result[] = 'Пересчитанное значение: ' . array_shift($voipCreditLimitDayValue);
                    }

                    return $f->field($data, 'voip_is_day_calc')->checkbox() . implode(Html::tag('br'), $result);
                },
                'columnOptions' => [
                    'colspan' => 1,
                    'style' => 'margin-top: 35px;'
                ],
            ],
            'voip_credit_limit' => ['columnOptions' => ['colspan' => 1]],
            'anti_fraud_disabled' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['colspan' => 1, 'style' => 'margin-top: 35px;'],],
            ['type' => Form::INPUT_RAW,],

            ['type' => Form::INPUT_RAW,],
            ['type' => Form::INPUT_RAW,],
            ['type' => Form::INPUT_RAW,],

            [
                'type' => Form::INPUT_RAW,
                'columnOptions' => [
                    'colspan' => 2,
                ],
                'value' => function() use ($f, $model) {
                    return
                        $f
                            ->field($model, 'options[mail_delivery_variant]', [
                                'options' => [
                                    'class' => 'col_sm_12',
                                    'style' => 'margin-left: 15px;',
                                ],
                            ])
                            ->dropDownList([
                                'undefined' => 'Не определились / Не отправляем',
                                'payment' => 'Платная рассылка почтой РФ',
                                'by_self' => 'Самовывоз',
                            ])
                            ->label('Тип рассылки документов');
                },
            ],
            'options[black_list]' => [
                'type' => Form::INPUT_CHECKBOX,
                'label' => 'Черный список',
                'columnOptions' => ['style' => 'margin-top: 20px;',]
            ],
            'is_with_consignee' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;',], 'options' => ['id' => 'with-consignee']],
            ['type' => Form::INPUT_RAW,],

            'address_post' => ['columnOptions' => ['colspan' => 2],],
            'head_company' => ['columnOptions' => ['colspan' => 2],],
            ['type' => Form::INPUT_RAW,],
            ['type' => Form::INPUT_RAW,],

            'address_post_real' => ['columnOptions' => ['colspan' => 2],],
            'head_company_address_jur' => ['columnOptions' => ['colspan' => 2],],
            ['type' => Form::INPUT_RAW,],
            ['type' => Form::INPUT_RAW,],

            'mail_who' => ['columnOptions' => ['colspan' => 2],],
            'consignee' => ['columnOptions' => ['colspan' => 2, 'id' => 'consignee']],
            ['type' => Form::INPUT_RAW,],
            ['type' => Form::INPUT_RAW,],

            'form_type' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ClientAccount::$formTypes],
            'stamp' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;'],],
            'is_upd_without_sign' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;'],],
            ['type' => Form::INPUT_RAW,],

            'bill_rename1' => ['type' => Form::INPUT_RADIO_LIST, "items" => ['yes' => 'Оказанные услуги по Договору', 'no' => 'Абонентская плата по Договору'],],
            ['type' => Form::INPUT_RAW,],
            ['type' => Form::INPUT_RAW,],
            ['type' => Form::INPUT_RAW,],

            'bik' => ['columnOptions' => ['colspan' => 2],],
            'corr_acc' => ['columnOptions' => ['colspan' => 2], 'options' => ['disabled' => 'disabled']],
            ['type' => Form::INPUT_RAW,],
            ['type' => Form::INPUT_RAW,],

            'pay_acc' => ['columnOptions' => ['colspan' => 2],],
            'bank_name' => ['columnOptions' => ['colspan' => 2], 'options' => ['disabled' => 'disabled']],
            ['type' => Form::INPUT_RAW,],
            ['type' => Form::INPUT_RAW,],

            ['type' => Form::INPUT_RAW, 'columnOptions' => ['colspan' => 2],],
            'bank_city' => ['columnOptions' => ['colspan' => 2], 'options' => ['disabled' => 'disabled']],
            ['type' => Form::INPUT_RAW,],
            ['type' => Form::INPUT_RAW,],
        ],
    ]);
    ?>
</div>
