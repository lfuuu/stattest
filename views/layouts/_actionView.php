<?php
/**
 * Вывести ссылку "Просмотр" в виде иконки "действия" грида
 *
 * @var app\classes\BaseView $this
 * @var string $url
 */
use app\classes\Html;

echo Html::a(
    Html::tag('i', '', ['class' => 'glyphicon glyphicon-eye-open']),
    $url,
    [
        'title' => 'Просмотр',
        'class' => 'btn btn-link btn-xs'
    ]
);