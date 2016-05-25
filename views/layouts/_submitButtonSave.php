<?php
/**
 * Вывести submit-кнопку "Сохранить"
 *
 * @var app\classes\BaseView $this
 * @var string $class
 */

!isset($class) && $class = '';
?>

<?= $this->render('//layouts/_submitButton', [
    'text' => Yii::t('common', 'Create'),
    'glyphicon' => 'glyphicon-save',
    'params' => [
        'class' => 'btn btn-primary ' . $class,
    ],
]) ?>