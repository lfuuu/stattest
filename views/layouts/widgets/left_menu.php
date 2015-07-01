<?php
/** @var $user \app\models\User */
global $module;
/** @var \app\classes\NavigationBlock[] $blocks */
$blocks = $this->context->getNavigationBlocks();
?>

<div>
    <a href="#" onclick="openAllNavigationBlocks(); return false;">Открыть все блоки</a>
</div>

<?php foreach ($blocks as $block): ?>
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
