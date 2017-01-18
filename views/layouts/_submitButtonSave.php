<?php
/**
 * Вывести submit-кнопку "Сохранить"
 *
 * @var app\classes\BaseView $this
 * @var string $class
 */

!isset($class) && $class = '';
!isset($style) && $style = '';

echo $this->render('//layouts/_submitButton', [
    'text' => Yii::t('common', 'Save'),
    'glyphicon' => 'glyphicon-save',
    'params' => [
        'class' => 'btn btn-primary ' . $class,
        'style' => $style,
    ],
]);