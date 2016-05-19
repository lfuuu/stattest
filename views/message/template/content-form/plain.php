<?php

/**
 * @var \app\models\message\TemplateContent $model
 */
?>

<div class="container col-xs-12" style="float: none;">

    <div class="form-group">
        <label><?= $model->getAttributeLabel('content'); ?></label>
        <textarea name="<?= $model->formName(); ?>[content]" rows="20" class="form-control"><?= $model->content; ?></textarea>
    </div>

</div>