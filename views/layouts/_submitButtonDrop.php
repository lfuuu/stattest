<?php
/**
 * Вывести submit-кнопку "Удалить"
 */
use app\classes\Html;

?>

<?= Html::submitButton(
    Html::tag('i', '', ['class' => 'glyphicon glyphicon-trash']) . ' ' . Yii::t('common', 'Drop'),
    [
        'name' => 'dropButton',
        'value' => 1,
        'class' => 'btn btn-danger pull-right',
        'aria-hidden' => 'true',
        'onClick' => sprintf('return confirm("%s");', Yii::t('common', "Are you sure? It's irreversibly.")),
    ]
) ?>