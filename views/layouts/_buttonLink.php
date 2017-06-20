<?php
/**
 * Вывести ссылку-кнопку
 *
 * @var app\classes\BaseView $this
 * @var string $url
 * @var string $text
 * @var string $class
 * @var string $title
 * @var string $glyphicon
 */

$params = [
    'class' => 'btn ' . (isset($class) ? $class : ''),
];
isset($title) && $params['title'] = $title;

echo $this->render('//layouts/_link', [
    'url' => $url,
    'text' => $text,
    'glyphicon' => isset($glyphicon) ? $glyphicon : '',
    'params' => $params,
]);