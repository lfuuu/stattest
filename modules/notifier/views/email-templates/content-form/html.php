<?php

/**
 * @var \app\modules\notifier\models\templates\TemplateContent $model
 */
?>

<div class="container col-sm-12" style="float: none;">

    <div class="form-group">
        <label><?= $model->getAttributeLabel('title') ?></label>
        <input type="text" name="<?= $model->formName(); ?>[title]" class="form-control" value="<?= $model->title; ?>" />
    </div>

    <div class="form-group">
        <label><?= $model->getAttributeLabel('content'); ?></label>
        <textarea name="<?= $model->formName(); ?>[content]" rows="20" class="form-control editor"><?= $model->content; ?></textarea>
    </div>

</div>
