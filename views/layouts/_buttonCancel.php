<?php
/**
 * Вывести ссылку-кнопку "Отменить"
 *
 * @var string $url
 */
use app\classes\Html;

?>

<?= Html::a(
    Html::tag('i', '', [
        'class' => 'glyphicon glyphicon-level-up',
        'aria-hidden' => 'true',
    ]) . ' ' .
    Yii::t('common', 'Cancel'),
    $url,
    [
        'class' => 'btn btn-link btn-cancel',
    ]
) ?>