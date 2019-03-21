<?php
/**
 * Вывести ссылку "Включить" в виде иконки "действия" грида
 *
 * @var app\classes\BaseView $this
 * @var string $url
 */
use app\classes\Html;

?>

<?= Html::a(
    Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']),
    $url,
    [
        'title' => Yii::t('common', 'Enable'),
        'class' => 'btn btn-link btn-xs'
    ]
) ?>