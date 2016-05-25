<?php
/**
 * Вывести <a>
 *
 * @var app\classes\BaseView $this
 * @var string $url
 * @var string $text
 * @var string $glyphicon
 * @var @var string $params
 */
use app\classes\Html;

!isset($params) && $params = [];
?>

<?= Html::a(
    Html::tag('i', '', [
        'class' => 'glyphicon ' . $glyphicon,
        'aria-hidden' => 'true',
    ]) . ' ' .
    $text,
    $url,
    $params
) ?>