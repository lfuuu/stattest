<?php
/** @var $user \app\models\User */
global $module;
/** @var \app\classes\NavigationBlock[] $blocks */
$blocks = $this->context->getNavigationBlocks();
?>

<div class="text-right">
    <a href="#" class="btn-toogle-nav-blocks" data-action="open">
        Открыть все блоки
    </a>
    <a href="#" class="btn-toogle-nav-blocks collapse" data-action="close">
        Закрыть все блоки
    </a>
</div>

<?php foreach ($blocks as $block): ?>

    <?php if (!count($block->items)) {
        continue;
    } ?>

    <div id="navigation-block-<?=$block->id?>" class="menupanel navigation-block">
        <div class="title">
            <span class="title">
                <?=$block->title?>
                <span class="arrow-open">&nbsp;»&nbsp;</span>
                <span class="arrow-close">&nbsp;«&nbsp;</span>
            </span>
        </div>
        <div class="group">
            <?php foreach ($block->items as $item): ?>
                <?php if ($item['title']) { ?>
                    <a class="item" href="<?=$item['url']?>"><?=$item['title']?></a>
                <?php } else { ?>
                    <div>&nbsp;</div>
                <?php } ?>
            <?php endforeach; ?>
        </div>
    </div>

<?php endforeach; ?>
