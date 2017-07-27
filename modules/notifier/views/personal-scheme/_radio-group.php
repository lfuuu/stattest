<?php

/** @var string $inputName */
/** @var bool|null $value */
/** @var bool|null $globalValue */
?>

<div class="personal-scheme-radio-group">
    <div class="status">
        <div class="status__text">Вкл.</div>
        <div class="status__radio">
            <input type="radio" name="<?= $inputName ?>" value="1"<?= ($value === 1 ? ' checked="checked"' : '') ?> />
        </div>
        <div class="status__text"></div>
    </div>
    <div class="status">
        <div class="status__text"></div>
        <div class="status__radio">
            <input type="radio" name="<?= $inputName ?>" value="-1"<?= ($value === null ? ' checked="checked"' : '') ?>" />
        </div>
        <div class="status__text">
            <?php if (!is_null($globalValue)) : ?>
                <span class="glyphicon glyphicon-resize-horizontal"></span>
                <span title="Состояние в глобальной схеме" class="glyphicon <?= ($globalValue ? 'glyphicon-ok text-success' : 'glyphicon-remove text-danger') ?>"></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="status">
        <div class="status__text">Выкл.</div>
        <div class="status__radio">
            <input type="radio" name="<?= $inputName ?>" value="0"<?= ($value === 0 ? ' checked="checked"' : '') ?> />
        </div>
        <div class="status__text"></div>
    </div>
</div>
