<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use kartik\builder\Form;
use \app\models\ClientContract;
use kartik\widgets\DatePicker;

?>

<div class="row">
    <div class="col-sm-12">
        <h2><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> договора</h2>

        <?php $f = ActiveForm::begin(); ?>

        <div class="row" style="width: 1100px;">
            <?php

            echo '<div>';
            echo Form::widget([
                'model' => $model,
                'form' => $f,
                'columns' => 1,
                'attributeDefaults' => [
                    'container' => ['class' => 'col-sm-12'],
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'contragent_id' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => $model->getContragentListBySuperId()],
                ],
            ]);
            echo Form::widget([
                'model' => $model,
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
                        'value' => function() use ($f, $model){
                            $res = '<div class="col-sm-12">';
                            $res .= $f->field($model, 'state')->begin();
                            $res .= Html::activeLabel($model,'state', ['class' => 'control-label']); //label
                            $res .= Html::activeDropDownList($model, 'state', ClientContract::$states, ['class' => 'form-control '.$model->state]); //Field
                            $res .= Html::error($model,'state', ['class' => 'help-block', 'encode' => false]); //error
                            $res .= $f->field($model, 'state')->end();
                            $res .= '</div>';
                            return $res;
                        },
                    ],
                    'empty2' => [
                        'type' => Form::INPUT_RAW,
                        'value' => ''
                    ],
                    'organization_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $model->getOrganizationsList()],
                    'manager' => [
                        'type' => Form::INPUT_RAW,
                        'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['manager'] . '</label>'
                            . Select2::widget([
                                'model' => $model,
                                'attribute' => 'manager',
                                'data' => \app\models\User::getManagerList(),
                                'options' => ['placeholder' => 'Начните вводить фамилию'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ])
                            . '</div>'
                    ],
                    'account_manager' => [
                        'type' => Form::INPUT_RAW,
                        'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['account_manager'] . '</label>'
                            . Select2::widget([
                                'model' => $model,
                                'attribute' => 'account_manager',
                                'data' => \app\models\User::getAccountManagerList(),
                                'options' => ['placeholder' => 'Начните вводить фамилию'],
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


            <div class="row">
                <div class="col-sm-4">
                    <div class="col-sm-12" type="textInput">
                        <label class="control-label" for="deferred-date">Сохранить на</label>
                        <?php $months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сенября', 'октября', 'ноября', 'декабря']; ?>
                        <?= Html::dropDownList('deferred-date', null,
                            (Yii::$app->request->get('date') ? [Yii::$app->request->get('date') => 'Дату из истории'] : [])
                            +
                            [
                                date('Y-m-d', time()) => 'Текущую дату',
                                date('Y-m-01', strtotime('- 1 month')) => 'С 1го ' . $months[date('m', strtotime('- 1 month')) - 1],
                                date('Y-m-01', strtotime('+ 1 month')) => 'С 1го ' . $months[date('m', strtotime('+ 1 month')) - 1],
                                '' => 'Выбраную дату'
                            ],
                            ['class' => 'form-control', 'style' => 'margin-bottom: 20px;', 'name' => 'deferred-date', 'id' => 'deferred-date']); ?>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="col-sm-12" type="textInput">
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
            <div class="col-sm-12 form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-default', 'id' => 'buttonSave']); ?>
            </div>
            <?php ActiveForm::end(); ?>

            <?php if (!$model->isNewRecord): ?>
                <div class="col-sm-12 form-group">
                    <a href="#" onclick="return showVersion({ClientContract:<?= $model->id ?>}, true);">Версии</a><br/>
                    <?= Html::button('∨', ['style' => 'border-radius: 22px;', 'class' => 'btn btn-default showhistorybutton', 'onclick' => 'showHistory({ClientContract:' . $model->id . '})']); ?>
                    <span>История изменений</span>
                </div>
            <?php endif; ?>
        </div>

        <script>
            $(function () {
                $('#deferred-date-input').parent().parent().hide();
            });

            $('#buttonSave').on('click', function (e) {
                $('#type-select .btn').not('.btn-primary').each(function () {
                    $($(this).data('tab')).remove();
                });
                if ($("#deferred-date option:selected").is('option:last'))
                    $('#deferred-date option:last').val($('#deferred-date-input').val()).select();
                return true;
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
</div>