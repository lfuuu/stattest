<?php
/* @var $user \app\models\User */
global $module;
?>
<?php foreach ($this->context->getPanelsData() as $panel): ?>
    <div class="panel">
        <div class="title">
            <a href="?module=<?=$panel['module']?>"><?=$panel['title']?></a>
            <a href='javascript:toggle(document.getElementById("panel_id<?=$panel['module']?>"),link<?=$panel['module']?>,"<?=$panel['module']?>");' id='link<?=$panel['module']?>'>
                <?=$module == $panel['module'] || $user->isPanelVisible($panel['module']) ? '&laquo;' : '&raquo;'?>
            </a>
        </div>
        <div class="group" id='panel_id<?=$panel['module']?>' style='display: <?=$module == $panel['module'] || $user->isPanelVisible($panel['module']) ? 'inline-block' : 'none'?>' >
            <?php foreach ($panel['items'] as $item): ?>
                <div class="item">
                    <?php if ($item[0]) { ?>
                        <a href="?<?=$item[1]?>"><?=$item[0]?></a> <?=isset($item[3]) ? $item[3] : ''?>
                    <?php } else { ?>
                        &nbsp;
                    <?php } ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>