<?php
/**
 * Вывести help иконкой
 *
 * @link http://getbootstrap.com/css/#type-abbreviations
 * @var string $message
 * @var string $extraClass не обязательно
 */
?>

<abbr title="<?= $message ?>" class="<?= isset($extraClass) ? $extraClass : '' ?>">
    <span class="glyphicon glyphicon-question-sign"></span>
</abbr>
