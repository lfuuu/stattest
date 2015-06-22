<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use \yii\helpers\Url;

?>
<div class="status-block">
    <?php
    $f = ActiveForm::begin(['action' => Url::toRoute(['contract/edit', 'id' => $contractForm->id, 'childId' => $client->id])]);
    ?>
    <div class="row" style="background: <?= $contractForm->currentBusinessProcessStatus->color ?>;">
        <div class="col-sm-3">
            Статус: <b><?= $contractForm->currentBusinessProcessStatus->name ?></b>
            <a href="#" onclick="$('#statuses').toggle(); return false;"><img class="icon"
                                                                              src="/images/icons/monitoring.gif"
                                                                              alt="Посмотреть"></a>
        </div>
        <div class="col-sm-9">

            <?php if($client->lkWizardState) :?>
            <b style="color: green;"> Wizard включен</b>, шаг: <?= $client->lkWizardState->step ?> (<?= $client->lkWizardState->stepName ?>)
            <small>
                [
                <a href="/account/change-wizard-state/?id=<?= $client->id ?>&state=off">выключить</a>

                <?php if($client->lkWizardState->step == 4): ?>

                    <?if($client->lkWizardState->step!='rejected'):?>
                        | <a href="/account/change-wizard-state/?id=<?= $client->id ?>&state=rejected">отклонить</a>
                    <?php endif; ?>

                    <?if($client->lkWizardState->step!='approve'):?>
                        | <a href="/account/change-wizard-state/?id=<?= $client->id ?>&state=approve">одобрить</a>
                    <?php endif; ?>

                    <?if($client->lkWizardState->step!='review'):?>
                        | <a href="/account/change-wizard-state/?id=<?= $client->id ?>&state=review">рассмотрение</a>
                    <?php endif; ?>

                <?php endif; ?>

                <?if($client->lkWizardState->step!=1):?>
                    | <a href="/account/change-wizard-state/?id=<?= $client->id ?>&state=first">*первый шаг*</a>
                <?php endif; ?>

                <?if($client->lkWizardState->step!=4):?>
                    | <a href="/account/change-wizard-state/?id=<?= $client->id ?>&state=next">*след шаг*</a>
                <?php endif; ?>

                ]</small>
            <?php else: ?>
                <?php  if(\app\models\LkWizardState::isBPStatusAllow($client->contract->business_process_status_id, $client->id)): ?>
                    <b style="color: gray;"> Wizard выключен</b>
                    [<a href="/account/change-wizard-state/?id=<?= $client->id ?>&state=on">включить</a>]
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
    <div class="row" id="statuses" style="display: none;">
        <div class="col-sm-12">
            <?php foreach ($client->contract->comments as $comment): ?>
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
                'contract_type_id' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => $contractForm->contractTypes],
                'business_process_id' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => $contractForm->businessProcessesList],
                'business_process_status_id' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => $contractForm->businessProcessStatusesList],
            ],
        ]);
        ?>

        <?php
        echo Form::widget([
            'model' => $contractForm,
            'form' => $f,
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
            ],
            'options' => [
                'class' => 'col-sm-6'
            ],
            'attributes' => [
                'block' => [
                    'type' => Form::INPUT_RAW,
                    'value' => '<div class="col-sm-6">
                                    <div class="form-group field-contracteditform-status">
                                        <label class="control-label">Блокировка</label>
                                        <div class="btn-group" role="group" aria-label="..." data-account-id="' . $client->id . '">
                                            <button id="block-btn-work" type="button" class="btn btn-default btn-sm ' . ($client->is_blocked ? '' : 'btn-success') . '" style="width: 120px;">Работает</button>
                                            <button id="block-btn-block" type="button" class="btn btn-default btn-sm ' . ($client->is_blocked ? 'btn-danger' : '') . '" style="width: 120px;">Заблокирован</button>
                                        </div>
                                    </div>
                                </div>'
                ],
                'comment' => ['type' => Form::INPUT_TEXTAREA, 'options' => ['style' => 'height:108px;']]
            ],
        ]);
        ?>


        <div class="row" style="clear: both;">
            <div class="col-sm-12">
                <div class="col-sm-12 form-group" style="text-align: center;">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-default', 'id' => 'buttonSave',]); ?>
                </div>
            </div>
        </div>

    </div>
    <?php ActiveForm::end(); ?>
</div>
<script>
    $(function () {
        $('.field-contracteditform-status .btn-group button').on('click', function () {
            var b = $('#block-btn-block'),
                w = $('#block-btn-work'),
                acId = $(this).parent().data('account-id');
            if ($(this).attr('id') == 'block-btn-work') {
                $(this).addClass('btn-success');
                b.removeClass('btn-danger');
            } else {
                $(this).addClass('btn-danger');
                w.removeClass('btn-success');
            }
            $.get('/account/set-block?id=' + acId);
        });

        var statuses = <?= json_encode($client->getBpStatuses()) ?>;
        var s1 = $('#contracteditform-contract_type_id');
        var s2 = $('#contracteditform-business_process_id');
        var s3 = $('#contracteditform-business_process_status_id');

        var vals2 = s2.val();
        s2.empty();
        $(statuses.processes).each(function (k, v) {
            if (s1.val() == v['up_id'])
                s2.append('<option '+(v['id']==vals2 ? 'selected' : '')+' value="' + v['id'] + '">' + v['name'] + '</option>');
        });

        var vals3 = s3.val();
        s3.empty();
        $(statuses.statuses).each(function (k, v) {
            if (s2.val() == v['up_id'])
                s3.append('<option '+(v['id']==vals3 ? 'selected' : '')+' value="' + v['id'] + '">' + v['name'] + '</option>');
        });

        $('.statuses').on('change', 'select', function () {
            var t = $(this);

            if (t.attr('id') == s1.attr('id')) {
                s2.empty();
                $(statuses.processes).each(function (k, v) {
                    if (s1.val() == v['up_id'])
                        s2.append('<option value="' + v['id'] + '">' + v['name'] + '</option>');
                });
            }

            if (t.attr('id') == s2.attr('id')) {
                s3.empty();
                $(statuses.statuses).each(function (k, v) {
                    if (s2.val() == v['up_id'])
                        s3.append('<option value="' + v['id'] + '">' + v['name'] + '</option>');
                });
            }
        });


    });
</script>