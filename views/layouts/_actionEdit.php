<?php
/**
 * Вывести ссылку "Редактировать" в виде иконки "действия" грида
 *
 * @var app\classes\BaseView $this
 * @var string $url
 */
use app\classes\Html;

?>

<?= Html::a(
    Html::tag('i', '', ['class' => 'glyphicon glyphicon-pencil']),
    $url,
    [
        'title' => Yii::t('common', 'Edit'),
        'class' => 'btn btn-link btn-xs'
    ]
) ?>