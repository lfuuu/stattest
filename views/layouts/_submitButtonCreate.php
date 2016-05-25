<?php
/**
 * Вывести submit-кнопку "Создать"
 *
 * @var app\classes\BaseView $this
 */
?>

<?= $this->render('//layouts/_submitButton', [
    'text' => Yii::t('common', 'Create'),
    'glyphicon' => 'glyphicon-save',
    'params' => [
        'class' => 'btn btn-primary',
    ],
]) ?>