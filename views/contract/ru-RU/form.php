<?php

use app\classes\BaseView;
use app\dao\PartnerDao;
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

<div class="row">

    <div class="col-sm-4">
        <?= $f->field($model, 'business_id')
            ->widget(Select2::className(), [
                'data' => Business::getList(),
                'options' => [
                    'disabled' => !$model->getIsNewRecord()
                        && $model->state !== ClientContract::STATE_UNCHECKED
                        && !Yii::$app->user->can('clients.client_type_change'),
                ],
            ])
        ?>
    </div>

    <div class="col-sm-4">
        <?php
        if ($model->business_id === Business::PARTNER) {
            echo $f->field($model, 'is_lk_access')
                ->widget(Select2::className(), [
                    'data' => ClientContract::$lkAccess,
                ]);
        }
        ?>
    </div>
    <div class="col-sm-4">
        <?= $f->field($model, 'is_voip_with_tax')
            ->textInput([
                'disabled' => true,
                'value' => ($model->is_voip_with_tax ? 'Да' : 'Нет')
            ])
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <?= $f->field($model, 'business_process_id')
            ->widget(Select2::className(), [
                'data' => BusinessProcess::getList(),
                'options' => [
                    'disabled' => !$model->getIsNewRecord()
                        && $model->state !== ClientContract::STATE_UNCHECKED
                        && !Yii::$app->user->can('clients.restatus')
                        && !Yii::$app->user->can('clients.client_type_change')
                ],
            ])
        ?>
    </div>
    <div class="col-sm-4">
        <?= $f->field($model, 'manager')
            ->widget(Select2::className(), [
                'data' => ['' => '----'],
                'options' => [
                    'data-current-value' => $model->manager ?: 0,
                ],
            ])
        ?>
    </div>
    <div class="col-sm-4">
        <?php
        if ($model->business_id === Business::TELEKOM) {
            echo $f->field($model, 'is_partner_login_allow')
                ->textInput([
                    'disabled' => true,
                    'value' => ($model->is_partner_login_allow ? 'Да' : 'Нет'),
                ]);
        }
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <?= $f->field($model, 'business_process_status_id')
            ->widget(Select2::className(), [
                'data' => BusinessProcessStatus::getList(),
                'options' => [
                    'disabled' => !Yii::$app->user->can('clients.restatus'),
                ],
            ])
        ?>
    </div>
    <div class="col-sm-4">
        <?= $f->field($model, 'account_manager')
            ->widget(Select2::className(), [
                'data' => ['' => '----'],
                'options' => [
                    'data-current-value' => $model->account_manager ?: 0,
                ],
            ])
        ?>
    </div>
    <div class="col-sm-4">
        <?= $f->field($model, 'organization_id')
            ->widget(Select2::className(), [
                'data' => $model->getOrganizationsList(),
            ])
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <?php
        if (isset($contragents, $contragentsOptions)) {
            echo $f->field($model, 'contragent_id')
                ->widget(Select2::className(), [
                    'data' => $contragents,
                    'options' => $contragentsOptions,
                ]);
        }
        ?>
    </div>

    <div class="col-sm-4">
        <?= $f->field($model, 'partner_contract_id')
            ->widget(Select2::className(), [
                'data' => ClientContract::dao()->getPartnerList($isWithEmpty = true),
            ])
            ->label(
                $model->getAttributeLabel('partner_contract_id') .
                $this->render('//layouts/_helpConfluence', PartnerDao::getHelpConfluence())
            )
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <?= $f->field($model, 'state')
            ->widget(Select2::className(), [
                'data' => $model->model->statusesForChange(),
            ])
        ?>
    </div>

    <?php switch ($model->business_id) {
        case Business::OPERATOR:
            {
                ?>
                <div class="col-sm-4">
                    <?= $f->field($model, 'contract_type_id')
                        ->widget(Select2::className(), [
                            'data' => ContractType::getList($model->business_process_id, $isWithEmpty = true),
                        ])
                    ?>
                </div>

                <div class="col-sm-4">
                    <?= $f->field($model, 'financial_type')
                        ->widget(Select2::className(), [
                            'data' => ClientContract::$financialTypes,
                            'options' => [
                                'disabled' =>
                                    !$model->getIsNewRecord()
                                    && $model->state !== ClientContract::STATE_UNCHECKED
                                    && !Yii::$app->user->can('clients.client_type_change')
                            ],
                        ])
                    ?>
                </div>

                <div class="col-sm-12">
                    <?= $f->field($model, 'federal_district')
                        ->checkboxButtonGroup(ClientContract::$districts, [
                            'class' =>
                                'percent100 ' .
                                !$model->getIsNewRecord()
                                && $model->state != ClientContract::STATE_UNCHECKED
                                && !Yii::$app->user->can('clients.client_type_change') ?
                                    'btn-disabled' :
                                    ''
                        ])
                    ?>
                </div>
                <?php
                break;
            }

        case Business::PARTNER:
            {
                ?>
                <div class="col-sm-4">
                    <?= $f->field($model, 'contract_type_id')
                        ->widget(Select2::className(), [
                            'data' => ContractType::getList($model->business_process_id, $isWithEmpty = true),
                        ])
                    ?>
                </div>
                <?php
                break;
            }
    }
    ?>
</div>