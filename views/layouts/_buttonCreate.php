<?php
/**
 * Вывести ссылку-кнопку "Создать"
 *
 * @var app\classes\BaseView $this
 * @var string $url
 * @var string $name
 */
?>

<?= $this->render('//layouts/_link', [
    'url' => $url,
    'text' => (isset($name) ? $name : Yii::t('common', 'Create')),
    'glyphicon' => 'glyphicon-plus',
    'params' => [
        'class' => 'btn btn-success',
    ],
]) ?>