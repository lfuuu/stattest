<?php
/**
 * Вывести <input type=submit>
 *
 * @var app\classes\BaseView $this
 * @var string $text
 * @var string $glyphicon
 * @var string $params
 */
use app\classes\Html;

!isset($params) && $params = [];
?>

<?= Html::submitButton(
    Html::tag('i', '', [
        'class' => 'glyphicon ' . $glyphicon,
        'aria-hidden' => 'true',
    ]) . ' ' .
    $text,
    $params
) ?>