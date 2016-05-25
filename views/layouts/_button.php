<?php
/**
 * Вывести <button>
 *
 * @var app\classes\BaseView $this
 * @var string $text
 * @var string $glyphicon
 * @var string $params
 */
use app\classes\Html;

!isset($params) && $params = [];
?>

<?= Html::button(
    Html::tag('i', '', [
        'class' => 'glyphicon ' . $glyphicon,
        'aria-hidden' => 'true',
    ]) . ' ' .
    $text,
    $params
) ?>