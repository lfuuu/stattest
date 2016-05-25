<?php
/**
 * Вывести ссылку-кнопку "Отменить"
 *
 * @var app\classes\BaseView $this
 * @var string $url
 */
?>

<?= $this->render('//layouts/_link', [
    'url' => $url,
    'text' => Yii::t('common', 'Cancel'),
    'glyphicon' => 'glyphicon-level-up',
    'params' => [
        'class' => 'btn btn-link btn-cancel',
    ],
]) ?>