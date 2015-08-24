<?php

use app\models\Region;
use app\models\SaleChannel;
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
            'sale_channel' => [
                'type' => Form::INPUT_RAW,
                'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['sale_channel'] . '</label>'
                    . Select2::widget([
                        'model' => $model,
                        'attribute' => 'sale_channel',
                        'data' => SaleChannel::getList(),
                        'options' => ['placeholder' => 'Начните вводить название'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])
                    . '</div>'
            ],
            'empty25' => ['type' => Form::INPUT_RAW,],

            'nal' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ClientAccount::$nalTypes],
            'currency' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => Currency::map()],
            'price_type' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => PriceType::getList()],
            'empty1' => ['type' => Form::INPUT_RAW,],

            'voip_disabled' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['colspan' => 3],],
            'empty15' => ['type' => Form::INPUT_RAW,],
            'empty16' => ['type' => Form::INPUT_RAW,],
            'empty17' => ['type' => Form::INPUT_RAW,],

            'credit' => ['type' => Form::INPUT_CHECKBOX, 'options' => ['id' => 'credit'], 'columnOptions' => ['style' => 'margin-top: 20px;']],
            'credit_size' => ['columnOptions' => ['id' => 'credit-size', 'style' => $model->credit > 0 ? '' : 'display:none;']],
            'empty13' => ['type' => Form::INPUT_RAW,],
            'empty14' => ['type' => Form::INPUT_RAW,],

            'voip_credit_limit' => ['columnOptions' => ['colspan' => 2], 'options' => ['style' => 'width:20%;']],
            'empty18' => ['type' => Form::INPUT_RAW,],
            'empty19' => ['type' => Form::INPUT_RAW,],
            'empty20' => ['type' => Form::INPUT_RAW,],

            'voip_credit_limit_day' => ['columnOptions' => ['colspan' => 1],],
            'voip_is_day_calc' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['colspan' => 3, 'style' => 'margin-top: 35px;'],],
            'empty21' => ['type' => Form::INPUT_RAW,],
            'empty22' => ['type' => Form::INPUT_RAW,],

            'mail_print' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;', 'colspan' => 2],],
            'is_with_consignee' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;', 'colspan' => 2], 'options' => ['id' => 'with-consignee']],
            'empty9' => ['type' => Form::INPUT_RAW,],
            'empty10' => ['type' => Form::INPUT_RAW,],

            'address_post' => ['columnOptions' => ['colspan' => 2],],
            'head_company' => ['columnOptions' => ['colspan' => 2],],
            'empty2' => ['type' => Form::INPUT_RAW,],
            'empty3' => ['type' => Form::INPUT_RAW,],

            'address_post_real' => ['columnOptions' => ['colspan' => 2],],
            'head_company_address_jur' => ['columnOptions' => ['colspan' => 2],],
            'empty4' => ['type' => Form::INPUT_RAW,],
            'empty5' => ['type' => Form::INPUT_RAW,],

            'mail_who' => ['columnOptions' => ['colspan' => 2],],
            'consignee' => ['columnOptions' => ['colspan' => 2, 'id' => 'consignee']],
            'empty7' => ['type' => Form::INPUT_RAW,],
            'empty8' => ['type' => Form::INPUT_RAW,],

            'form_type' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ClientAccount::$formTypes],
            'stamp' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;'],],
            'is_upd_without_sign' => ['type' => Form::INPUT_CHECKBOX, 'columnOptions' => ['style' => 'margin-top: 20px;'],],
            'empty26' => ['type' => Form::INPUT_RAW,],

            'bill_rename1' => ['type' => Form::INPUT_RADIO_LIST, "items" => ['yes' => 'Оказанные услуги по Договору', 'no' => 'Абонентская плата по Договору'],],
            'empty27' => ['type' => Form::INPUT_RAW,],
            'empty28' => ['type' => Form::INPUT_RAW,],
            'empty29' => ['type' => Form::INPUT_RAW,],

            'bik' => ['columnOptions' => ['colspan' => 2],],
            'corr_acc' => ['columnOptions' => ['colspan' => 2], 'options' => ['disabled' => 'disabled']],
            'empty30' => ['type' => Form::INPUT_RAW,],
            'empty31' => ['type' => Form::INPUT_RAW,],

            'pay_acc' => ['columnOptions' => ['colspan' => 2],],
            'bank_name' => ['columnOptions' => ['colspan' => 2], 'options' => ['disabled' => 'disabled']],
            'empty32' => ['type' => Form::INPUT_RAW,],
            'empty33' => ['type' => Form::INPUT_RAW,],

            'empty35' => ['type' => Form::INPUT_RAW, 'columnOptions' => ['colspan' => 2],],
            'bank_city' => ['columnOptions' => ['colspan' => 2], 'options' => ['disabled' => 'disabled']],
            'empty36' => ['type' => Form::INPUT_RAW,],
            'empty37' => ['type' => Form::INPUT_RAW,],
        ],
    ]);
    ?>
</div>

<script>
    $('#credit').on('click', function () {
        $('#credit-size').toggle();
    });
</script>