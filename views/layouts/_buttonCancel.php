<?php
/**
 * Вывести ссылку-кнопку "Отменить"
 *
 * @var app\classes\BaseView $this
 * @var string $url
 * @var string $class
 */
?>

<?= $this->render('//layouts/_link', [
    'url' => $url,
    'text' => Yii::t('common', 'Cancel'),
    'glyphicon' => 'glyphicon-level-up',
    'params' => [
        'class' => 'btn btn-link btn-cancel ' . (isset($class) ? $class : ''),
    ],
]) ?>