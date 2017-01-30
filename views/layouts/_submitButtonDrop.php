<?php
/**
 * Вывести submit-кнопку "Удалить"
 *
 * @var app\classes\BaseView $this
 */
use app\classes\Html;

?>

<?= $this->render('//layouts/_submitButton', [
    'text' => Yii::t('common', 'Drop'),
    'glyphicon' => 'glyphicon-trash',
    'params' => [
        'name' => 'dropButton',
        'value' => 1,
        'class' => 'btn btn-danger pull-left',
        'aria-hidden' => 'true',
        'onClick' => sprintf('return confirm("%s");', Yii::t('common', "Are you sure? It's irreversibly.")),
    ],
]) ?>