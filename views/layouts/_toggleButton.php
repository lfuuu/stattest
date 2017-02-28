<?php
/**
 * Показать/скрыть любой контент
 *
 * @var app\classes\BaseView $this
 * @var string $divSelector
 */

use app\classes\Html;

?>

<span class="toggleButtonDiv" onclick="toggleButton(this, '<?= $divSelector ?>')">
    <?= Html::button('∨', ['class' => 'btn btn-default toggleButton']); ?>
</span>