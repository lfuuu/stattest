<?php
/**
 * Иконка со ссылкой на счет-фактуру
 *
 * @var \app\classes\BaseView $this
 * @var int $clientAccountId
 */

use app\classes\Html;
use yii\helpers\Url;

?>

<?= Html::a(
    '',
    Url::to(['/uu/invoice/view/', 'clientAccountId' => $clientAccountId]),
    [
        'class' => 'btn btn-default glyphicon glyphicon-th',
        'aria-hidden' => 'true',
        'title' => Yii::t('tariff', 'Invoice'),
    ]
) ?>