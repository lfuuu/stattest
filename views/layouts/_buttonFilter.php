<?php
/**
 * Вывести кнопку "Фильтровать"
 */
use app\classes\Html;

?>

<?= Html::button(
    Html::tag('i', '', [
        'class' => 'glyphicon glyphicon-filter',
        'aria-hidden' => 'true',
    ]) . ' ' .
    Yii::t('common', 'Filter'),
    [
        'id' => 'submitButtonFilter',
        'class' => 'btn btn-primary',
    ]
) ?>