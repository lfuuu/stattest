<?php
/**
 * Иконка со ссылкой на счета
 *
 * @var \yii\web\View $this
 * @var int $clientAccountId
 */

use app\classes\Html;
use yii\helpers\Url;

?>

<?= Html::a(
    '',
    Url::to(['/uu/bill/', 'clientAccountId' => $clientAccountId]),
    [
        'class' => 'btn btn-default glyphicon glyphicon-th-large',
        'aria-hidden' => 'true',
        'title' => Yii::t('tariff', 'Bills'),
    ]
) ?>