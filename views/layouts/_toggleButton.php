<?php
/**
 * Показать/скрыть любой контент
 *
 * @var app\classes\BaseView $this
 * @var string $divSelector
 * @var string $title
 */

use app\classes\Html;

?>

<span class="toggleButtonDiv" onclick="toggleButton(this, '<?= $divSelector ?>')">
    <?= Html::button('∨', ['class' => 'btn btn-default toggleButton']) ?>
    <a><?= isset($title) ? $title : '' ?></a>
</span>