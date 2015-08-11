<?php
/** @var $user \app\models\User */
global $module;
/** @var \app\classes\NavigationBlock[] $blocks */
$blocks = $this->context->getNavigationBlocks();
?>

<div style="text-align: right">
    <a href="#" onclick="openAllNavigationBlocks(); $('.btn-toogle-nav-blocks').toggle(); return false;" class="btn-toogle-nav-blocks" style="display: block">Открыть все блоки</a>
    <a href="#" onclick="closeAllNavigationBlocks(); $('.btn-toogle-nav-blocks').toggle(); return false;" class="btn-toogle-nav-blocks" style="display: none">Закрыть все блоки</a>
</div>

<?php foreach ($blocks as $block): ?>

    <?php if (empty($block->items)) continue; ?>

    <div id="navigation-block-<?=$block->id?>" class="menupanel">
        <div class="title" onclick="toggleNavigationBlock('navigation-block-<?=$block->id?>')">
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
    <?php if (false && $block->id == $module):?>
        <script>openNavigationBlock('navigation-block-<?=$block->id?>')</script>
    <?php endif; ?>
<?php endforeach; ?>
<script>initNavigationBlocks()</script>
