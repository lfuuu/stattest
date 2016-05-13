<?php
/**
 * Вывести ссылку-кнопку "Создать"
 *
 * @var string $url
 */
use app\classes\Html;

?>

<?= Html::a(
    Html::tag('i', '', [
        'class' => 'glyphicon glyphicon-plus',
        'aria-hidden' => 'true',
    ]) . ' ' .
    Yii::t('common', 'Create'),
    $url,
    ['class' => 'btn btn-success']
) ?>