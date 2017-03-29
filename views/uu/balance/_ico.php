<?php
/**
 * Иконка со ссылкой на бухгалтерский баланс
 *
 * @var \app\classes\BaseView $this
 * @var int $clientAccountId
 */

use app\classes\Html;
use yii\helpers\Url;

?>
<?= Html::a(
    '',
    Url::to(['/uu/balance/view/', 'clientAccountId' => $clientAccountId]),
    [
        'class' => 'btn btn-default glyphicon glyphicon-th-list',
        'aria-hidden' => 'true',
        'title' => Yii::t('tariff', 'Balance'),
    ]
) ?>