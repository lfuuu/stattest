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
$iTagParams['aria-hidden'] = 'true';
if (isset($glyphicon)) {
    $iTagParams['class'] = 'glyphicon ' . $glyphicon;
}
?>

<?= Html::submitButton(
    Html::tag('i', '', $iTagParams) . ' ' .
    $text,
    $params
) ?>