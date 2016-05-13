<?php
/**
 * Вывести submit-кнопку "Сохранить"
 */
use app\classes\Html;

?>

<?= Html::submitButton(
    Html::tag('i', '', [
        'class' => 'glyphicon glyphicon-save',
        'aria-hidden' => 'true',
    ]) . ' ' .
    Yii::t('common', 'Save'),
    [
        'class' => 'btn btn-primary',
    ]
) ?>