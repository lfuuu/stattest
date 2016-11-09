<?php

use kartik\widgets\ActiveForm;
use app\classes\Html;
use kartik\widgets\Select2;
use kartik\builder\Form;
use app\forms\client\ContractEditForm;
use app\models\ClientContract;
use app\models\ContractType;
use app\models\ClientContractReward;
use app\models\Business;
use app\models\BusinessProcess;
use app\models\BusinessProcessStatus;

/** @var ActiveForm $f */
/** @var ContractEditForm $model */

$model->federal_district = $model->getModel()->getFederalDistrictAsArray();
?>

<div class="row" style="width: 1100px;">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-4">
                <?= $f
                    ->field($model, 'business_id')
                    ->dropDownList(Business::getList(), [
                        'disabled' =>
                            !$model->getIsNewRecord()
                            && $model->state !== ClientContract::STATE_UNCHECKED
                            && !Yii::$app->user->can('clients.client_type_change')
                    ])
                ?>
            </div>
            <div class="col-sm-4"></div>
            <div class="col-sm-4">
                <?php
                if ($model->business_id === Business::PARTNER) {
                    echo $f
                        ->field($model, 'lk_access')
                        ->dropDownList(ClientContract::$lkAccess, [
                            'disabled' => true,
                        ]);
                }
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <?= $f
                    ->field($model, 'business_process_id')
                    ->dropDownList(BusinessProcess::getList(), [
                        'disabled' =>
                            !$model->getIsNewRecord()
                            && $model->state !== ClientContract::STATE_UNCHECKED
                            && !Yii::$app->user->can('clients.restatus')
                            && !Yii::$app->user->can('clients.client_type_change')
                    ])
                ?>
            </div>
            <div class="col-sm-4">
                <?= $f
                    ->field($model, 'manager')
                    ->widget(Select2::className(), [
                        'data' => [],
                        'options' => [
                            'placeholder' => 'Начните вводить фамилию',
                            'data-current-value' => $model->manager ?: 0,
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])
                ?>
            </div>
            <div class="col-sm-4">
                <?php
                if ($model->business_id === Business::TELEKOM) {
                    echo $f
                        ->field($model, 'partner_login_allow')
                        ->textInput([
                            'disabled' => true,
                            'value' => ($model->partner_login_allow ? 'Да' : 'Нет')
                        ]);
                }
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <?= $f
                    ->field($model, 'business_process_status_id')
                    ->dropDownList(BusinessProcessStatus::getList(), [
                        'disabled' => !Yii::$app->user->can('clients.restatus')
                    ])
                ?>
            </div>
            <div class="col-sm-4">
                <?= $f
                    ->field($model, 'account_manager')
                    ->widget(Select2::className(), [
                        'data' => [],
                        'options' => [
                            'placeholder' => 'Начните вводить фамилию',
                            'data-current-value' => $model->account_manager ?: 0,
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])
                ?>
            </div>
            <div class="col-sm-4">
                <?= $f
                    ->field($model, 'organization_id')
                    ->dropDownList($model->getOrganizationsList())
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <?php
                if (isset($contragents, $contragentsOptions)) {
                    echo $f
                        ->field($model, 'contragent_id')
                        ->dropDownList($contragents, [
                            'options' => $contragentsOptions,
                        ]);
                }
                ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <?= $f
                    ->field($model, 'state')
                    ->dropDownList($model->model->statusesForChange())
                ?>
            </div>
            <?php switch ($model->business_id) {
                case Business::OPERATOR: {
                    echo Html::beginTag('div', ['class' => 'col-sm-4']);
                        echo $f
                            ->field($model, 'contract_type_id')
                            ->dropDownList(ContractType::getList());
                    echo Html::endTag('div');

                    echo Html::beginTag('div', ['class' => 'col-sm-4']);
                        echo $f
                            ->field($model, 'financial_type')
                            ->dropDownList(ClientContract::$financialTypes, [
                                'disabled' =>
                                    !$model->getIsNewRecord()
                                    && $model->state !== ClientContract::STATE_UNCHECKED
                                    && !Yii::$app->user->can('clients.client_type_change')
                            ]);
                    echo Html::endTag('div');

                    echo Html::beginTag('div', ['class' => 'col-sm-12']);
                        echo $f
                            ->field($model, 'federal_district')
                            ->checkboxButtonGroup(ClientContract::$districts, [
                                'style' => 'width:100%;',
                                'class' =>
                                    !$model->getIsNewRecord()
                                    && $model->state != ClientContract::STATE_UNCHECKED
                                    && !Yii::$app->user->can('clients.client_type_change')
                                        ? 'btn-disabled'
                                        : ''
                            ]);
                    echo Html::endTag('div');
                    break;
                }

                case Business::PARTNER: {
                    echo Html::beginTag('div', ['class' => 'col-sm-4']);
                    echo $f
                        ->field($model, 'contract_type_id')
                        ->dropDownList(ContractType::getList());
                    echo Html::endTag('div');
                    break;
                }
            }
            ?>
        </div>
    </div>
</div>

<script>
    $(function () {
        var statuses = <?= json_encode(\app\models\BusinessProcessStatus::getTree()) ?>;
        var contractTypes = <?= json_encode(\app\models\ContractType::find()->asArray()->all()) ?>;
        var s1 = $('#contracteditform-business_id');
        var s2 = $('#contracteditform-business_process_id');
        var s3 = $('#contracteditform-business_process_status_id');
        var s4 = $('#contracteditform-contract_type_id');

        var vals2 = s2.val();
        s2.empty();
        $(statuses.processes).each(function (k, v) {
            if (s1.val() == v['up_id'])
                s2.append('<option ' + (v['id'] == vals2 ? 'selected' : '') + ' value="' + v['id'] + '">' + v['name'] + '</option>');
        });

        var vals3 = s3.val();
        s3.empty();
        $(statuses.statuses).each(function (k, v) {
            if (s2.val() == v['up_id'])
                s3.append('<option ' + (v['id'] == vals3 ? 'selected' : '') + ' value="' + v['id'] + '">' + v['name'] + '</option>');
        });

        var vals4 = s4.val();
        if (s4) {
            s4.empty();
            s4.append('<option value="0"><?= Yii::t('contract', 'notDefined') ?></option>');
            $(contractTypes).each(function (k, v) {
                if (s2.val() == v['business_process_id'])
                    s4.append('<option value="' + v['id'] + '" ' + (v['id'] == vals4 ? 'selected' : '') + '>' + v['name'] + '</option>');
            });
        }

        s1.on('change', function () {
            var form = $(this).closest('form');
            $('<input type="hidden" name="notSave" value="1">').appendTo(form);
            form.submit();
        });

        s2.on('change', function () {
            s3.empty();
            $(statuses.statuses).each(function (k, v) {
                if (s2.val() == v['up_id'])
                    s3.append('<option value="' + v['id'] + '" ' + (v['id'] == vals3 ? 'selected' : '') + '>' + v['name'] + '</option>');
            });
            if (s4) {
                s4.empty();
                $(contractTypes).each(function (k, v) {
                    if (s2.val() == v['business_process_id'])
                        s4.append('<option value="' + v['id'] + '" ' + (v['id'] == vals4 ? 'selected' : '') + '>' + v['name'] + '</option>');
                });
            }
        });

        $('.btn-disabled').on('click', function (e) {
            return false;
        });

        $('.period-type').on('change', function () {
            var month = $(this).parent().parent().next();
            if ($(this).val() == '<?= ClientContractReward::PERIOD_MONTH ?>') {
                month.show();
            } else {
                month.hide();
            }
        })
    });
</script>
