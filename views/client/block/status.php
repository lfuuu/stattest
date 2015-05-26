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
    <div class="row">
        <div class="col-sm-3">
            Статус: <b>Внутренний офис</b>
            <a href="#" onclick="$('#statuses').toggle(); return false;"><img class="icon" src="/images/icons/monitoring.gif" alt="Посмотреть"></a>
        </div>
        <div class="col-sm-9">Wizard</div>
    </div>
    <div class="row" id="statuses" style="display: none;">
        <div class="col-sm-12">
            <?php foreach($client->contract->comments as $comment): ?>
                <div class="col-sm-12">
                    <input type="checkbox" name="ContractEditForm[public_comment][<?= $comment->id ?>]" <?= $comment->is_publish?'checked':'' ?>>
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
                bl = 'false';
            } else {
                $(this).addClass('btn-danger');
                w.removeClass('btn-success');
                bl = 'true';
            }
            $.get('/?module=clients&action=rpc_setBlocked&account_id=' + acId + '&is_blocked=' + bl);
        });


        $('.statuses').on('change', 'select', function () {
            //https://stat.mcn.ru/?module=clients&action=rpc_loadBPStatuses
            var t = $(this);
            $.get('/?module=clients&action=rpc_loadBPStatuses', function (vall) {
                    var ol = [];
                    var s1 = $('#contracteditform-contract_type_id');
                    var s2 = $('#contracteditform-business_process_id');
                    var s3 = $('#contracteditform-business_process_status_id');

                    if(t.attr('id') == s1.attr('id')){
                        s2.empty();
                        $(vall.processes).each(function(k, v){
                            if(s1.val() == v['up_id'])
                                s2.append('<option value="'+v['id']+'">'+v['name']+'</option>');
                        });
                    }

                s3.empty();
                $(vall.statuses).each(function(k, v){
                    if(s2.val() == v['up_id'])
                        s3.append('<option value="'+v['id']+'">'+v['name']+'</option>');
                });
            }, 'json');
        });


    });
</script>