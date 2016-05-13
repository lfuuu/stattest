<?php
/**
 * Вывести submit-кнопку "Создать"
 */
use app\classes\Html;

?>

<?= Html::submitButton(
    Html::tag('i', '', [
        'class' => 'glyphicon glyphicon-save',
        'aria-hidden' => 'true',
    ]) . ' ' .
    Yii::t('common', 'Create'),
    [
        'class' => 'btn btn-primary',
    ]
) ?>