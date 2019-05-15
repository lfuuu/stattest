<?php

use app\models\Lead;
use app\models\SaleChannel;
use app\models\TroubleState;

?>
<div class="well well-sm message-buttons" data-message-id="<?= $messageId ?>">
    <div class="row">
        <div class="col-sm-12">
            <div>Откуда вы о нас узнали: </div><?= \app\classes\Html::radioList('sale_channel', '', SaleChannel::getList())?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <button name="to_lead" value=1 class="btn btn-sm btn-success btn-block">Лид</button>
        </div>
        <div class="col-sm-4">
            <button name="make_client" value=1 class="btn btn-sm btn-info btn-block">Клиент</button>
        </div>
        <div class="col-sm-4">
            <span onclick="$('#trash-form').slideToggle();" class="btn btn-sm btn-default btn-block" id="btn_trash">Мусор</span>
        </div>
    </div>
    <form class="row" id="trash-form" style="margin-top: 10px; display: none;" onsubmit="return false;">
        <div class="col-sm-4"></div>
        <div class="col-sm-4"></div>
        <div class="col-sm-4">
            <div class="row" style="margin-bottom: 10px;">
                <div class="col-md-8">
                    <select class="form-control" name="trash-type" id="trash-selector">
                        <?php
                        $trashTypes = Lead::getTrashTypes();
                        foreach ($trashTypes as $trashType) {
                            echo '<option>' . $trashType . '</option>';
                        } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button name="to_trash" value=1 id="submit-trash" class="btn btn-success" style="width: 100%;" >Отправить</button>
                </div>
            </div>
            <textarea name="trash-comment" class="form-control" style="display: none;" id="trash-textarea"></textarea>
        </div>
    </form>
    <?php
    if ($clientAccount) : ?>
        <hr/>
    <?php endif; ?>
    <div class="row">
        <div class="col-sm-12">
            <?php
            if ($clientAccount) {
                /** @var TroubleState $state */
                foreach ($states as $state) : ?>
                    <button name="set_state" value="<?= $state->id ?>"
                            class="btn btn-xs btn-<?= ($state->is_final ? 'warning' : 'info') ?> btn-block"><?= $state->name ?></button>
                <?php endforeach;
            } ?>
        </div>
    </div>
</div>

<script>
  statLeadNotifier.initLead('<?= $messageId ?>');
</script>
