<?php
/**
 * Вывести кнопку "Фильтровать"
 *
 * @var app\classes\BaseView $this
 */
?>

<?= $this->render('//layouts/_button', [
    'text' => Yii::t('common', 'Filter'),
    'glyphicon' => 'glyphicon-filter',
    'params' => [
        'id' => 'submitButtonFilter',
        'class' => 'btn btn-primary',
    ],
]) ?>