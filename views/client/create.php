<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use app\models\Region;
use app\models\SaleChannel;
use app\models\PriceType;
use kartik\widgets\Select2;
use \app\models\ClientContract;
use app\models\ClientContragent;
use app\models\ClientAccount;
use app\models\Currency;
use yii\web\JsExpression;

?>
<div class="row">
    <div class="col-sm-12">
        <h2>Создание клиента</h2>

        <?php $f = ActiveForm::begin(); ?>

        <div class="col-sm-8" style="margin-bottom: 20px; text-align: center;">
            <div class="btn-group" id="type-select">
                <button type="button" class="btn btn-default" data-tab="#legal">Юр. лицо</button>
                <button type="button" class="btn btn-default" data-tab="#ip">ИП</button>
                <?= Html::activeHiddenInput($contragent, 'legal_type') ?>
                <button type="button" class="btn btn-default" data-tab="#person">Физ. лицо</button>
            </div>
        </div>
        <?= Html::activeHiddenInput($contragent, 'super_id') ?>
        <div id="fs-tabs" class="row" style="width: 1100px;">
            <?php
            echo '<div id="legal" class="tab-pane col-sm-12">';
            echo Form::widget([
                'model' => $contragent,
                'form' => $f,
                'columns' => 2,
                'options'=> ['style'=>'width:100%;'],
                'columnOptions' => ['class' => 'col-sm-6'],
                'attributeDefaults' => [
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'name' => [],
                    'address_jur' => [],
                    'name_full' => [],
                ],
            ]);
            echo Form::widget([
                'model' => $contragent,
                'form' => $f,
                'columns' => 2,
                'columnOptions' => ['class' => 'col-sm-6'],
                'options' => ['style' => 'width:50%; padding-right: 15px; float: left;'],
                'attributeDefaults' => [
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'inn' => [],
                    'kpp' => [],
                    'okvd' => [],
                    'ogrn' => [],
                    'opf' => [],
                    'okpo' => [],
                ],
            ]);
            echo Form::widget([
                'model' => $contragent,
                'form' => $f,
                'columns' => 1,
                'options' => ['style' => 'width:50%; padding-left: 15px; '],
                'attributeDefaults' => [
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'tax_regime' => [
                        'type' => Form::INPUT_DROPDOWN_LIST,
                        'items' => ClientContragent::$taxRegtimeTypes,
                        'container' => ['style' => 'width:50%;']
                    ],
                    'position' => [],
                    'fio' => [],
                ],
            ]);

            echo '</div>';

            echo '<div id="ip" class="tab-pane col-sm-12">';
            echo Form::widget([
                'model' => $contragent,
                'form' => $f,
                'columns' => 1,
                'options' => ['style' => 'width:50%; padding-right: 15px; float: left;'],
                'attributeDefaults' => [
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'last_name' => [],
                    'first_name' => [],
                    'middle_name' => [],
                ],
            ]);
            echo Form::widget([
                'model' => $contragent,
                'form' => $f,
                'columns' => 1,
                'columnOptions' => ['class' => 'col-sm-12'],
                'options' => ['style' => 'width:50%; padding-left: 15px;'],
                'attributeDefaults' => [
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'registration_address' => [],
                ],
            ]);

            echo Form::widget([
                'model' => $contragent,
                'form' => $f,
                'columns' => 1,
                'columnOptions' => ['class' => 'col-sm-12'],
                'options' => ['style' => 'width:50%; padding-left: 15px;'],
                'attributeDefaults' => [
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'tax_regime' => [
                        'type' => Form::INPUT_DROPDOWN_LIST,
                        'items' => ClientContragent::$taxRegtimeTypes,
                        'container' => ['style' => 'width:50%;']
                    ],
                ],
            ]);

            echo Form::widget([
                'model' => $contragent,
                'form' => $f,
                'columns' => 2,
                'options' => ['style' => 'clear:both; width:50%; padding-right: 15px;'],
                'columnOptions' => ['class' => 'col-sm-6'],
                'attributeDefaults' => [
                    'container' => [
                        'type' => Form::INPUT_TEXT
                    ],
                ],
                'attributes' => [
                    'inn' => [],
                    'ogrn' => [],
                    'opf' => [],
                    'okpo' => [],
                ],
            ]);

            echo '</div>';

            echo '<div id="person" class="tab-pane col-sm-12">';
            echo Form::widget([
                'model' => $contragent,
                'form' => $f,
                'columns' => 1,
                'options' => ['style' => 'width:50%; padding-right: 15px; float: left;'],
                'attributeDefaults' => [
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'last_name' => [],
                    'first_name' => [],
                    'middle_name' => [],
                ],
            ]);
            echo Form::widget([
                'model' => $contragent,
                'form' => $f,
                'columns' => 2,
                'columnOptions' => ['class' => 'col-sm-6'],
                'options' => ['style' => 'width:50%; padding-left: 15px; padding-right: 15px;'],
                'attributeDefaults' => [
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'passport_serial' => [],
                    'passport_number' => [],
                ],
            ]);
            echo Form::widget([
                'model' => $contragent,
                'form' => $f,
                'columns' => 1,
                'columnOptions' => ['class' => 'col-sm-12'],
                'options' => ['style' => 'width:50%; padding-left: 15px; padding-right: 15px;'],
                'attributeDefaults' => [
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'passport_date_issued' => [
                        'type' => Form::INPUT_WIDGET,
                        'widgetClass' => '\kartik\widgets\DatePicker',
                        'columnOptions' => ['class' => 'col-sm-6'],
                        'options' => [
                            'removeButton' => false,
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'startDate' => '-40y',
                                'endDate' => '+1y',
                            ],
                        ],
                    ],
                    'passport_issued' => [],
                    'registration_address' => [],
                ],
            ]);

            echo '</div>';

            echo '<div class="col-sm-12">';
            echo Form::widget([
                'model' => $contragent,
                'form' => $f,
                'columns' => 1,
                'columnOptions' => ['class' => 'col-sm-12'],
                'options' => ['style' => 'width:50%; padding-right: 15px;'],
                'attributeDefaults' => [
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'country_id' => [
                        'type' => Form::INPUT_DROPDOWN_LIST,
                        'widgetClass' => '\kartik\widgets\Select2',
                        'container' => [
                            'style' => 'width:50%; padding-right: 15px;'
                        ],
                        'items' => \app\models\Country::getList()
                    ],
                ],
            ]);

            echo '</div>';
            ?>
        </div>

        <div class="row" style="width: 1100px;">
            <?php

            echo '<div>';
            echo Form::widget([
                'model' => $contract,
                'form' => $f,
                'columns' => 3,
                'attributeDefaults' => [
                    'container' => ['class' => 'col-sm-12'],
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'contract_type_id' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => \app\models\ClientContractType::getList()],
                    //'state' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ClientContract::$states],
                    'state' => [
                        'type' => Form::INPUT_RAW,
                        'value' => function() use ($f, $contract){
                            $res = '<div class="col-sm-12">';
                            $res .= $f->field($contract, 'state')->begin();
                            $res .= Html::activeLabel($contract,'state', ['class' => 'control-label']); //label
                            $res .= Html::activeDropDownList($contract, 'state', ClientContract::$states, ['class' => 'form-control '.$contract->state]); //Field
                            $res .= Html::error($contract,'state', ['class' => 'help-block', 'encode' => false]); //error
                            $res .= $f->field($contract, 'state')->end();
                            $res .= '</div>';
                            return $res;
                        },
                    ],
                    'empty2' => [
                        'type' => Form::INPUT_RAW,
                        'value' => ''
                    ],
                    'organization_id' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => $contract->getOrganizationsList()],
                    'manager' => [
                        'type' => Form::INPUT_RAW,
                        'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $contract->attributeLabels()['manager'] . '</label>'
                            . Select2::widget([
                                'model' => $contract,
                                'attribute' => 'manager',
                                'data' => [],
                                'options' => [
                                    'placeholder' => 'Начните вводить фамилию',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])
                            . '</div>'
                    ],
                    'account_manager' => [
                        'type' => Form::INPUT_RAW,
                        'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $contract->attributeLabels()['account_manager'] . '</label>'
                            . Select2::widget([
                                'model' => $contract,
                                'attribute' => 'account_manager',
                                'data' => [],
                                'options' => [
                                    'placeholder' => 'Начните вводить фамилию',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ])
                            . '</div>'
                    ],
                ],
            ]);

            echo '</div>';
            ?>

            <?php

            echo '<div>';
            echo Form::widget([
                'model' => $account,
                'form' => $f,
                'columns' => 4,
                'attributeDefaults' => [
                    'container' => ['class' => 'col-sm-12'],
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'region' => [
                        'type' => Form::INPUT_RAW,
                        'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $account->attributeLabels()['region'] . '</label>'
                            . Select2::widget([
                                'model' => $account,
                                'attribute' => 'region',
                                'data' => Region::getList(),
                                'options' => ['placeholder' => 'Начните вводить название'],
                            ])
                            . '</div>'
                    ],
                    'timezone_name' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => Region::getTimezoneList()],
                    'sale_channel' => [
                        'type' => Form::INPUT_RAW,
                        'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $account->attributeLabels()['sale_channel'] . '</label>'
                            . Select2::widget([
                                'model' => $account,
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
                    'credit_size' => ['columnOptions' => ['id' => 'credit-size', 'style' => $account->credit > 0 ? '' : 'display:none;']],
                    'empty13' => ['type' => Form::INPUT_RAW,],
                    'empty14' => ['type' => Form::INPUT_RAW,],

                    'voip_credit_limit' => ['columnOptions' => ['colspan' => 2], 'options' => ['style' => 'width:20%;']],
                    'empty18' => ['type' => Form::INPUT_RAW,],
                    'empty19' => ['type' => Form::INPUT_RAW,],
                    'empty20' => ['type' => Form::INPUT_RAW,],

                    'voip_credit_limit_day' => ['columnOptions' => ['colspan' => 1], 'options' => ['value' => 1000]],
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

                ],
            ]);

            echo '</div>';
            ?>

            <div class="col-sm-12 form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave', 'name' => 'save']); ?>
            </div>
        </div>


        <?php ActiveForm::end(); ?>


        <script>
            $(document).ready(function () {
                var b = $('#type-select .btn[data-tab="#' + $('#type-select input').val() + '"]');
                if (b.length < 1) {
                    b = $('#type-select .btn').first();
                    $('#type-select input').val(b.data('tab').replace("#", ""));
                }
                b.addClass('btn-primary').removeClass('btn-default');
                $('.tab-pane').hide();
                $($('#type-select .btn-primary').data('tab')).show();

                $('#credit').on('click', function () {
                    $('#credit-size').toggle();
                });
            });

            $('#type-select .btn').on('click', function () {
                var oldT = $('#type-select .btn-primary').data('tab');
                var newT = $(this).data('tab');
                $(oldT + ' .form-control').each(function () {
                    $(newT + ' .form-control[name="' + $(this).attr('name') + '"]').val($(this).val());
                });

                $('#contragenteditform-legal_type').val($(newT).attr('id'));

                $('#type-select .btn').removeClass('btn-primary').addClass('btn-default');
                $(this).addClass('btn-primary');
                $('.tab-pane').hide();
                $(newT).show();
            });

            $('#buttonSave').on('click', function (e) {
                $('#type-select .btn').not('.btn-primary').each(function () {
                    $($(this).data('tab')).remove();
                });


                return true;
            });

            $('#legal #contragenteditform-name').on('blur', function () {
                if ($('#legal #contragenteditform-name_full').val() == '')
                    $('#legal #contragenteditform-name_full').val($(this).val());
            });
            $('#legal #contragenteditform-name_full').on('blur', function () {
                if ($('#legal #contragenteditform-name').val() == '')
                    $('#legal #contragenteditform-name').val($(this).val());
            });

            $('#legal #contragenteditform-address_jur').on('blur', function () {
                if ($('#legal #contragenteditform-address_post').val() == '')
                    $('#legal #contragenteditform-address_post').val($(this).val());
            });
            $('#legal #contragenteditform-address_post').on('blur', function () {
                if ($('#legal #contragenteditform-address_jur').val() == '')
                    $('#legal #contragenteditform-address_jur').val($(this).val());
            });
        </script>
    </div>
</div>

<script type="text/javascript" src="/js/behaviors/managers_by_contract_type.js"></script>