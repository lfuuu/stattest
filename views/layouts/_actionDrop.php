<?php
/**
 * Вывести submit-кнопку "Удалить" в виде иконки "действия" грида
 *
 * @var app\classes\BaseView $this
 * @var string $url
 */
use app\classes\Html;

?>

<?= Html::beginForm($url, 'post', ['class' => 'inline']) ?>

<?= Html::submitButton(
    Html::tag('i', '', ['class' => 'glyphicon glyphicon-trash text-danger']),
    [
        'name' => 'dropButton',
        'value' => 1,
        'title' => Yii::t('common', 'Drop'),
        'onClick' => sprintf('return confirm("%s");', Yii::t('common', "Are you sure? It's irreversibly.")),
        'class' => 'btn btn-link btn-xs'
    ]
) ?>

<?= Html::endForm() ?>