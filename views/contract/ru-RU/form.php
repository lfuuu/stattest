<?php

use yii\helpers\Html;
use app\models\ClientContractReward;
use kartik\widgets\Select2;
use kartik\builder\Form;
use app\models\ClientContract;

?>

<div class="row" style="width: 1100px;">
    <?php

    $model->federal_district = $model->getModel()->getFederalDistrictAsArray();

    echo Form::widget([
        'model' => $model,
        'form' => $f,
        'columns' => 3,
        'attributeDefaults' => [
            'container' => ['class' => 'col-sm-12'],
            'type' => Form::INPUT_TEXT
        ],
        'attributes' => array_merge([
                'business_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => \app\models\Business::getList(),
                    'options' => ['disabled' =>
                        !$model->getIsNewRecord()
                        && $model->state != ClientContract::STATE_UNCHECKED
                        && !Yii::$app->user->can('clients.client_type_change')
                    ]
                ],
                ['type' => Form::INPUT_RAW],
                [
                    'type' => Form::INPUT_RAW,
                    'value' =>  ($model->business_id == \app\models\Business::PARTNER) ? '
                                <div class="col-sm-12 form-group">
                                    <label class="control-label" for="">Доступ к ЛК</label>
                                    '. Html::dropDownList('lk_access', null, ClientContract::$lkAccess, ['disabled' => true, 'class' => 'form-control'])
                                .'</div>':'',
                ],

                'business_process_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => \app\models\BusinessProcess::getList(),
                    'options' => ['disabled' =>
                        !$model->getIsNewRecord()
                        && $model->state != ClientContract::STATE_UNCHECKED
                        && !Yii::$app->user->can('clients.restatus')
                        && !Yii::$app->user->can('clients.client_type_change')
                    ]
                ],
                'manager' => [
                    'type' => Form::INPUT_RAW,
                    'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['manager'] . '</label>'
                        . Select2::widget([
                            'model' => $model,
                            'attribute' => 'manager',
                            'data' => [],
                            'options' => [
                                'placeholder' => 'Начните вводить фамилию',
                                'data-current-value' => $model->manager ?: 0,
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])
                        . '</div>'
                ],
                ['type' => Form::INPUT_RAW],

                'business_process_status_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => \app\models\BusinessProcessStatus::getList(),
                    'options' => ['disabled' => !Yii::$app->user->can('clients.restatus')]
                ],
                'account_manager' => [
                    'type' => Form::INPUT_RAW,
                    'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['account_manager'] . '</label>'
                        . Select2::widget([
                            'model' => $model,
                            'attribute' => 'account_manager',
                            'data' => [],
                            'options' => [
                                'placeholder' => 'Начните вводить фамилию',
                                'data-current-value' => $model->account_manager ?: 0,
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])
                        . '</div>'
                ],
                'organization_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $model->getOrganizationsList()],
            ]
            , (
            (isset($contragents) && isset($contragentsOptions))
                ? ['contragent_id' =>
                [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $contragents,
                    'options' => [
                        'options' => $contragentsOptions,
                    ],
                    'columnOptions' => ['colspan' => 3],

                ],
                ['type' => Form::INPUT_RAW],
                ['type' => Form::INPUT_RAW],
            ]
                : []
            )
            , [
                'state' => [
                    'type' => Form::INPUT_RAW,
                    'value' => function () use ($f, $model) {
                        $res = '<div class="col-sm-12">';
                        $res .= $f->field($model, 'state')->begin();
                        $res .= Html::activeLabel($model, 'state', ['class' => 'control-label']); //label
                        $res .= Html::activeDropDownList(
                            $model,
                            'state', $model->model->statusesForChange(),
                            [
                                'class' => 'form-control ' . $model->state,
                            ]
                        ); //Field
                        $res .= Html::error($model, 'state', ['class' => 'help-block', 'encode' => false]); //error
                        $res .= $f->field($model, 'state')->end();
                        $res .= '</div>';
                        return $res;
                    },
                ],
            ]
            , (
            ($model->business_id == \app\models\Business::OPERATOR)
                ? [
                'contract_type_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => \app\models\ContractType::getList(),

                ],
                'financial_type' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => \app\models\ClientContract::$financialTypes,
                    'options' => ['disabled' =>
                        !$model->getIsNewRecord()
                        && $model->state != ClientContract::STATE_UNCHECKED
                        && !Yii::$app->user->can('clients.client_type_change')]
                ],
                [
                    'type' => Form::INPUT_RAW,
                    'columnOptions' => ['colspan' => 2],
                    'value' =>
                        '<div class=col-sm-12>'
                        . $f->field($model, 'federal_district')->checkboxButtonGroup(
                            \app\models\ClientContract::$districts,
                            ['style' => 'width:100%;',
                                'class' =>
                                    !$model->getIsNewRecord()
                                    && $model->state != ClientContract::STATE_UNCHECKED
                                    && !Yii::$app->user->can('clients.client_type_change')
                                        ? 'btn-disabled'
                                        : ''
                            ]
                        )
                        . '</div>'
                ],
                ['type' => Form::INPUT_RAW],
            ]
                : (
            ($model->business_id == \app\models\Business::PARTNER)
                ? [
                'contract_type_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => \app\models\ContractType::getList(),

                ],
                ['type' => Form::INPUT_RAW],
            ]
                : [
                ['type' => Form::INPUT_RAW],
                ['type' => Form::INPUT_RAW],
            ]
            )
            )
        )
    ]);
    ?>
    <?php if ($model->business_id == \app\models\Business::PARTNER) : ?>
        <?php foreach ($model->rewards as $usage => $reward) : ?>
            <fieldset>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="col-sm-12">
                            <h2><?= ClientContractReward::$usages[$usage] ?></h2>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="">Разовое</label>

                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?= $reward->once_only ?>"
                                           name="ContractEditForm[rewards][<?= $usage ?>][once_only]">

                                    <div class="input-group-addon">руб.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="">От абонентской платы</label>

                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?= $reward->percentage_of_fee ?>"
                                           name="ContractEditForm[rewards][<?= $usage ?>][percentage_of_fee]">

                                    <div class="input-group-addon">%</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <label class="control-label" for="">От превышенния</label>

                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?= $reward->percentage_of_over ?>"
                                           name="ContractEditForm[rewards][<?= $usage ?>][percentage_of_over]">

                                    <div class="input-group-addon">%</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="row">
                                <div class="col-sm-9">
                                    <div class="form-group">
                                        <label class="control-label" for="">Период выплат</label>
                                        <select class="form-control period-type"
                                                name="ContractEditForm[rewards][<?= $usage ?>][period_type]">
                                            <?= Html::renderSelectOptions($reward->period_type, ClientContractReward::$period) ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-3" style="padding: 0;<?= ($reward->period_type == ClientContractReward::PERIOD_ALWAYS ? 'display: none;' : '') ?>">
                                    <div class="form-group">
                                        <label class="control-label" for="">&nbsp;</label>
                                        <input type="text" value="<?= $reward->period_month ?>" class="form-control"
                                               name="ContractEditForm[rewards][<?= $usage ?>][period_month]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        <?php endforeach; ?>
    <?php endif; ?>

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
