<?php

use app\classes\BaseView;
use app\classes\Html;
use app\forms\client\ContractEditForm;
use app\models\Business;
use app\models\BusinessProcess;
use app\models\BusinessProcessStatus;
use app\models\ClientContract;
use app\models\ContractType;
use app\models\Organization;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;

/** @var ActiveForm $f */
/** @var ContractEditForm $model */
/** @var BaseView $this */

$this->registerJsVariables([
    'statuses' => BusinessProcessStatus::getTree(),
    'contractTypes' => ContractType::find()->asArray()->all(),
]);

$model->federal_district = $model->getModel()->getFederalDistrictAsArray();

if ($model->business_id == Business::ITOUTSOURSING && $model->getIsNewRecord()) {
    $model->organization_id = Organization::AB_SERVICE_MARCOMNET;
}
?>

<div class="row max-screen">
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
            <div class="col-sm-4">
                <?php
                if ($model->business_id === Business::PARTNER) {
                    echo $f
                        ->field($model, 'is_lk_access')
                        ->dropDownList(ClientContract::$lkAccess);
                }
                ?>
            </div>
            <div class="col-sm-4"><?= $f
                    ->field($model, 'is_voip_with_tax')
                    ->textInput([
                        'disabled' => true,
                        'value' => ($model->is_voip_with_tax ? 'Да' : 'Нет')
                    ])
                ?></div>
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
                        ->field($model, 'is_partner_login_allow')
                        ->textInput([
                            'disabled' => true,
                            'value' => ($model->is_partner_login_allow ? 'Да' : 'Нет')
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
                                'class' =>
                                    'percent100 ' .
                                    !$model->getIsNewRecord()
                                    && $model->state != ClientContract::STATE_UNCHECKED
                                    && !Yii::$app->user->can('clients.client_type_change') ?
                                        'btn-disabled' :
                                        ''
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