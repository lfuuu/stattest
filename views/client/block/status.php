<?php

use Yii;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use \yii\helpers\Url;

$currentBusinessProcessStatus = \app\models\BusinessProcessStatus::findOne($contractForm->business_process_status_id);;
?>
<div class="status-block">
    <?php
    $f = ActiveForm::begin([
        'action' => Url::toRoute([
            'contract/edit',
            'id' => $contractForm->id,
            'childId' => $account->id,
            'returnTo' => Url::toRoute(['client/view', 'id'=>$account->id, 'childId'=>$contractForm->id]),
        ])
    ]);
    ?>
    <div class="row" style="background: <?= isset($currentBusinessProcessStatus['color']) ? $currentBusinessProcessStatus['color'] : '' ?>;">
        <div class="col-sm-3">
            Статус: <b><?= isset($currentBusinessProcessStatus['name']) ? $currentBusinessProcessStatus['name'] : '...' ?></b>
            <a href="#" class="status-block-toggle">
                <img class="icon" src="/images/icons/monitoring.gif" alt="Посмотреть">
            </a>
        </div>
        <div class="col-sm-9">

            <?php if ($account->lkWizardState) : ?>
                <b style="color: green;"> Wizard
                    включен</b>, шаг: <?= $account->lkWizardState->step ?> (<?= $account->lkWizardState->stepName ?>)
                <small>
                    [
                    <a href="/account/change-wizard-state/?id=<?= $account->id ?>&state=off">выключить</a>

                    <?php if ($account->lkWizardState->step == 4): ?>

                        <? if ($account->lkWizardState->step != 'rejected'): ?>
                            | <a
                                href="/account/change-wizard-state/?id=<?= $account->id ?>&state=rejected">отклонить</a>
                        <?php endif; ?>

                        <? if ($account->lkWizardState->step != 'approve'): ?>
                            | <a href="/account/change-wizard-state/?id=<?= $account->id ?>&state=approve">одобрить</a>
                        <?php endif; ?>

                        <? if ($account->lkWizardState->step != 'review'): ?>
                            | <a
                                href="/account/change-wizard-state/?id=<?= $account->id ?>&state=review">рассмотрение</a>
                        <?php endif; ?>

                    <?php endif; ?>

                    <? if ($account->lkWizardState->step != 1): ?>
                        | <a href="/account/change-wizard-state/?id=<?= $account->id ?>&state=first">*первый шаг*</a>
                    <?php endif; ?>

                    <? if ($account->lkWizardState->step != 4): ?>
                        | <a href="/account/change-wizard-state/?id=<?= $account->id ?>&state=next">*след шаг*</a>
                    <?php endif; ?>

                    ]
                </small>
            <?php else: ?>
                <?php if (\app\models\LkWizardState::isBPStatusAllow($account->contract->business_process_status_id, $account->contract->id)): ?>
                    <b style="color: gray;"> Wizard выключен</b>
                    [<a href="/account/change-wizard-state/?id=<?= $account->id ?>&state=on">включить</a>]
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
    <div class="row" id="statuses">
        <div class="col-sm-12">
            <?php foreach ($account->contract->comments as $comment): ?>
                <div class="col-sm-12">
                    <input type="checkbox"
                           name="ContractEditForm[public_comment][<?= $comment->id ?>]" <?= $comment->is_publish ? 'checked' : '' ?>>
                    <b><?= $comment->user ?> <?= $comment->ts ?>: </b><?= $comment->comment ?>
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
                    'items' => \app\models\BusinessProcessStatus::getList(),
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
<script>
    $('.status-block-toggle').on('click', function () {
        $('#statuses').toggle();
        $('#w1 .row').slice(0, 2).toggle();
        return false;
    })

    $(function () {
        document.cookie = "openedBlock=;";
        <?php if($_COOKIE['openedBlock']!='statuses'):?>
        $('.status-block-toggle').click();
        <?php endif; ?>

        var statuses = <?= json_encode(\app\models\BusinessProcessStatus::getTree()) ?>;
        var s1 = $('#contracteditform-business_id');
        var s2 = $('#contracteditform-business_process_id');
        var s3 = $('#contracteditform-business_process_status_id');

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

        s1.on('change', function () {
            s2.empty();
            $(statuses.processes).each(function (k, v) {
                if (s1.val() == v['up_id'])
                    s2.append('<option value="' + v['id'] + '">' + v['name'] + '</option>');
            });

            s3.empty();
            $(statuses.statuses).each(function (k, v) {
                if (s2.val() == v['up_id'])
                    s3.append('<option value="' + v['id'] + '">' + v['name'] + '</option>');
            });

        });

        s2.on('change', function () {
            s3.empty();
            $(statuses.statuses).each(function (k, v) {
                if (s2.val() == v['up_id'])
                    s3.append('<option value="' + v['id'] + '">' + v['name'] + '</option>');
            });
        });

        $('#buttonSave').closest('form').on('submit', function () {
            document.cookie = "openedBlock=statuses";
            return true;
        });


    });
</script>
