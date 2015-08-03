<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use kartik\builder\Form;
use \app\models\ClientContragent;

?>
<div class="row">
    <div class="col-sm-12">
        <h2><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> контрагента</h2>

        <?php $f = ActiveForm::begin(); ?>

        <div class="col-sm-8" style="margin-bottom: 20px; text-align: center;">
            <div class="btn-group" id="type-select">
                <button type="button" class="btn btn-default" data-tab="#legal">Юр. лицо</button>
                <button type="button" class="btn btn-default" data-tab="#ip">ИП</button>
                <?= Html::activeHiddenInput($model, 'legal_type') ?>
                <button type="button" class="btn btn-default" data-tab="#person">Физ. лицо</button>
            </div>
        </div>
        <?= Html::activeHiddenInput($model, 'super_id') ?>
        <div id="fs-tabs" class="row" style="width: 1100px;">
                <?php
                echo '<div id="legal" class="tab-pane col-sm-12">';
                echo Form::widget([
                    'model' => $model,
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
                    'model' => $model,
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
                    'model' => $model,
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
                    'model' => $model,
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
                    'model' => $model,
                    'form' => $f,
                    'columns' => 1,
                    'columnOptions' => ['class' => 'col-sm-12'],
                    'options' => ['style' => 'width:50%; padding-left: 15px;'],
                    'attributeDefaults' => [
                        'type' => Form::INPUT_TEXT
                    ],
                    'attributes' => [
                        'address_jur' => [],
                    ],
                ]);

                echo Form::widget([
                    'model' => $model,
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
                    'model' => $model,
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
                    'model' => $model,
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
                    'model' => $model,
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
                    'model' => $model,
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
                    'model' => $model,
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


                <div class="col-sm-6">
                    <div class="row">
                        <div class="col-sm-6">
                            <div type="textInput">
                                <label class="control-label" for="deferred-date">Сохранить на</label>
                                <?php $months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сенября', 'октября', 'ноября', 'декабря']; ?>
                                <?= Html::dropDownList('deferred-date', null,
                                    (Yii::$app->request->get('date') ? [Yii::$app->request->get('date') => 'Дату из истории'] : [])
                                    +
                                    [
                                        date('Y-m-d', time()) => 'Текущую дату',
                                        date('Y-m-01', strtotime('- 1 month')) => 'С 1го ' . $months[date('m', strtotime('- 1 month')) - 1],
                                        date('Y-m-01') => 'С 1го ' . $months[date('m') - 1],
                                        date('Y-m-01', strtotime('+ 1 month')) => 'С 1го ' . $months[date('m', strtotime('+ 1 month')) - 1],
                                        '' => 'Выбраную дату'
                                    ],
                                    ['class' => 'form-control', 'style' => 'margin-bottom: 20px;', 'name' => 'deferred-date', 'id' => 'deferred-date']); ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div type="textInput">
                                <label class="control-label" for="deferred-date-input">Выберите дату</label>
                                <?= DatePicker::widget(
                                    [
                                        'name' => 'kartik-date-3',
                                        'value' => Yii::$app->request->get('date') ? Yii::$app->request->get('date') : date('Y-m-d', time()),
                                        'removeButton' => false,
                                        'pluginOptions' => [
                                            'autoclose' => true,
                                            'format' => 'yyyy-mm-dd',
                                            'startDate' => '-5y',
                                        ],
                                        'id' => 'deferred-date-input'
                                    ]
                                ); ?>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="col-sm-12 form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-default', 'id' => 'buttonSave']); ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php if (!$model->isNewRecord): ?>
        <div class="col-sm-12 form-group">
            <a href="#" onclick="return showVersion({ClientContragent:<?= $model->id ?>}, true);">Версии</a><br/>
            <?= Html::button('∨', ['style' => 'border-radius: 22px;', 'class' => 'btn btn-default showhistorybutton', 'onclick' => 'showHistory({ClientContragent:' . $model->id . ', ClientContragentPerson:' . $model->getPersonId() . '})']); ?>
            <span>История изменений</span>
        </div>
    <?php endif; ?>

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
            $('#deferred-date-input').parent().parent().hide();
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
            if ($("#deferred-date option:selected").is('option:last'))
                $('#deferred-date option:last').val($('#deferred-date-input').val()).select();
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

        $('#deferred-date').on('change', function () {
            var datepicker = $('#deferred-date-input');
            if ($("option:selected", this).is('option:last')) {
                datepicker.parent().parent().show();
            }
            else {
                datepicker.parent().parent().hide();
            }
        });
    </script>
</div>