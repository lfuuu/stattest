<?php
/**
 * Вывети alert
 * @link http://getbootstrap.com/components/#alerts
 *
 * @var string $type
 * @var string|string[] $message
 */
?>

<div class="alert alert-<?= $type ?>">
    <?= is_array($message) ? implode('<br />', $message) : $message ?>
</div>