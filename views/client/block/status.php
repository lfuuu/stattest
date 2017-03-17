<?php

/** @var \app\classes\BaseView $this */

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\BusinessProcessStatus;
use app\models\LkWizardState;
use kartik\builder\Form;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;

$this->registerJsVariables([
    'statuses' => BusinessProcessStatus::getTree(),
    'openedBlock' => (!isset($_COOKIE['openedBlock']) || $_COOKIE['openedBlock'] != 'statuses'),
]);

$currentBusinessProcessStatus = BusinessProcessStatus::findOne($contractForm->business_process_status_id);
?>
<div class="status-block">
    <?php
    $f = ActiveForm::begin([
        'action' => Url::toRoute([
            'contract/edit',
            'id' => $contractForm->id,
            'childId' => $account->id,
            'returnTo' => Url::toRoute(['client/view', 'id' => $account->id, 'childId' => $contractForm->id]),
        ])
    ]);
    ?>
    <div class="row" style="background-color: <?= isset($currentBusinessProcessStatus['color']) ? $currentBusinessProcessStatus['color'] : '' ?>;">
        <div class="col-sm-3">
            Статус: <b><?= isset($currentBusinessProcessStatus['name']) ? $currentBusinessProcessStatus['name'] : '...' ?></b>
            <a href="#" class="status-block-toggle">
                <img class="icon" src="/images/icons/monitoring.gif" alt="Посмотреть" />
            </a>
        </div>
        <div class="col-sm-9">

            <?php if ($account->lkWizardState) : ?>
                <b style="color: green;"> Wizard
                    включен (<?= $account->lkWizardState->type ?>) </b>, шаг: <?= $account->lkWizardState->step ?> (<?= $account->lkWizardState->stepName ?>)
                <small>
                    [
                    <?= Html::a('выключить', ['/account/change-wizard-state', 'id' => $account->id, 'state' => 'off']) ?>

                    <?php if ($account->lkWizardState->step == 3) : ?>

                        <? if ($account->lkWizardState->step != LkWizardState::STATE_REJECTED) : ?>
                            | <?= Html::a('отклонить', ['/account/change-wizard-state', 'id' => $account->id, 'state' => LkWizardState::STATE_REJECTED]) ?>
                        <?php endif; ?>

                        <? if ($account->lkWizardState->step != LkWizardState::STATE_APPROVE) : ?>
                            | <?= Html::a('одобрить', ['/account/change-wizard-state', 'id' => $account->id, 'state' => LkWizardState::STATE_APPROVE]) ?>
                        <?php endif; ?>

                        <? if ($account->lkWizardState->step != LkWizardState::STATE_REVIEW) : ?>
                            | <?= Html::a('рассмотрение', ['/account/change-wizard-state', 'id' => $account->id, 'state' => LkWizardState::STATE_REVIEW]) ?>
                        <?php endif; ?>

                    <?php endif; ?>

                    <? if ($account->lkWizardState->step != 1): ?>
                        | <?= Html::a('*первый шаг*', ['/account/change-wizard-state', 'id' => $account->id, 'state' => 'first']) ?>
                    <?php endif; ?>

                    <? if ($account->lkWizardState->step != 3): ?>
                        | <?= Html::a('*след шаг*', ['/account/change-wizard-state', 'id' => $account->id, 'state' => 'next']) ?>
                    <?php endif; ?>
                    ]
                </small>
            <?php else: ?>
                <?php if (LkWizardState::isBPStatusAllow($account->contract->business_process_status_id, $account->contract->id)): ?>
                    <b style="color: gray;"> Wizard выключен</b>
                    [<?= Html::a('включить', ['/account/change-wizard-state', 'id' => $account->id, 'state' => 'on' ]) ?>]
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
    <div class="row" id="statuses">
        <div class="col-sm-12">
            <?php foreach ($account->contract->comments as $comment): ?>
                <div class="col-sm-12">
                    <input type="checkbox"
                           name="ContractEditForm[public_comment][<?= $comment->id ?>]" <?= $comment->is_publish ? 'checked' : '' ?> />
                    <b><?= $comment->user ?> <?= DateTimeZoneHelper::getDateTime($comment->ts) ?>: </b><?= $comment->comment ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="row">
        <?php
        echo Form::widget([
            'model' => $contractForm,
            'form' => $f,
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-6 statuses'],
                'type' => Form::INPUT_TEXT
            ],
            'options' => [
                'class' => 'col-sm-6'
            ],
            'attributes' => [
                'business_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => \app\models\Business::getList(),
                    'options' => ['disabled' => !Yii::$app->user->can('clients.client_type_change')]
                ],
                'business_process_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => \app\models\BusinessProcess::getList(),
                    'options' => ['disabled' => !Yii::$app->user->can('clients.restatus')]
                ],
                'business_process_status_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => BusinessProcessStatus::getList(),
                    'options' => ['disabled' => !Yii::$app->user->can('clients.restatus')]
                ],
            ],
        ]);
        ?>
        <?php
        echo Form::widget([
            'model' => $contractForm,
            'form' => $f,
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-6 statuses'],
                'type' => Form::INPUT_TEXT
            ],
            'options' => [
                'class' => 'col-sm-6'
            ],
            'attributes' => [
                'comment' => ['type' => Form::INPUT_TEXTAREA, 'options' => ['style' => 'height:108px;'], 'container' => ['class' => 'col-sm-12']],
            ],
        ]);
        ?>

        <div class="col-sm-12">
            <div class="col-sm-12 form-group">
                <?= Html::hiddenInput('ContractEditForm[save_comment_stage]', true); ?>
                <?= Html::submitButton('Изменить', ['class' => 'btn btn-primary', 'id' => 'buttonSave', 'style' => 'float:right;']); ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>