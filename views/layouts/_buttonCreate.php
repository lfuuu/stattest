<?php
/**
 * Вывести ссылку-кнопку "Создать"
 *
 * @var app\classes\BaseView $this
 * @var string $url
 */
?>

<?= $this->render('//layouts/_link', [
    'url' => $url,
    'text' => Yii::t('common', 'Create'),
    'glyphicon' => 'glyphicon-plus',
    'params' => [
        'class' => 'btn btn-success',
    ],
]) ?>