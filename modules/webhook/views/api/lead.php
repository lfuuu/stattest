<?php

use app\models\TroubleState;

?>
<div class="well message-buttons" data-message-id="<?= $messageId ?>">
    <div class="row">
        <div class="col-sm-4">
            <button name="to_lead" value=1 class="btn btn-sm btn-success btn-block">Лид</button>
        </div>
        <div class="col-sm-4">
            <button name="make_client" value=1 class="btn btn-sm btn-info btn-block">Клиент</button>
        </div>
        <div class="col-sm-4">
            <button name="to_trash" value=1 class="btn btn-sm btn-default btn-block" id="btn_trash">Мусор</button>
        </div>
    </div>
    <hr>
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
