<?php
/**
 * Вывести ссылку "Выключить" в виде иконки "действия" грида
 *
 * @var app\classes\BaseView $this
 * @var string $url
 */
use app\classes\Html;

?>

<?= Html::a(
    Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']),
    $url,
    [
        'title' => Yii::t('common', 'Disable'),
        'onClick' => sprintf('return confirm("%s");', Yii::t('common', "Are you sure?")),
        'class' => 'btn btn-link btn-xs'
    ]
) ?>