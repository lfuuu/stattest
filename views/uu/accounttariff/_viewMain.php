<?php
/**
 * Просмотр универсальной услуги
 *
 * @var \yii\web\View $this
 * @var \app\classes\uu\forms\AccountTariffForm $formModel
 * @var bool $isReadOnly
 */

use app\classes\Html;
use yii\widgets\DetailView;

$accountTariff = $formModel->accountTariff;

?>
<?= DetailView::widget([
    'model' => $accountTariff,
    'attributes' => [
        [
            'attribute' => 'client_account_id',
            'format' => 'html',
            'value' => Html::a(
                Html::encode($accountTariff->clientAccount->client),
                ['/client/view', 'id' => $accountTariff->client_account_id]
            )
        ],

        [
            'attribute' => 'region_id',
            'value' => Html::encode($accountTariff->region->name),
        ],

        'comment:ntext',

        [
            'attribute' => 'prev_account_tariff_id',
            'format' => 'html',
            'value' => $accountTariff->prevAccountTariff ?
                Html::a(
                    Html::encode($accountTariff->prevAccountTariff->getName()),
                    $accountTariff->prevAccountTariff->getUrl()
                ) :
                Yii::t('common', '(not set)'),
        ],

        [
            'attribute' => 'update_user_id',
            'format' => 'html',
            'value' => $accountTariff->updateUser ?
                $accountTariff->updateUser->name :
                Yii::t('common', '(not set)'),
        ],

        [
            'attribute' => 'update_time',
            'format' => 'html',
            'value' => $accountTariff->update_time ?: Yii::t('common', '(not set)'),
        ],
    ],
]) ?>
