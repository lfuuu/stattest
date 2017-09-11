<?php
/**
 * Просмотр универсальной услуги
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\AccountTariffForm $formModel
 * @var bool $isReadOnly
 */

use app\classes\DateTimeWithUserTimezone;
use app\classes\Html;
use app\modules\uu\models\ServiceType;
use yii\widgets\DetailView;

$accountTariff = $formModel->accountTariff;

$attributes = [
    [
        'attribute' => 'client_account_id',
        'format' => 'html',
        'value' => $accountTariff->clientAccount->getLink(),
    ],

    [
        'attribute' => 'region_id',
        'value' => Html::encode($accountTariff->region_id ? $accountTariff->region->name : Yii::t('common', '(not set)')),
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
        'attribute' => 'next_account_tariff_id',
        'label' => Yii::t('tariff', 'Packages'),
        'format' => 'html',
        'value' => $accountTariff->getNextAccountTariffsAsString(),
    ],

    [
        'attribute' => 'insert_user_id',
        'format' => 'html',
        'value' => $accountTariff->insertUser ?
            $accountTariff->insertUser->name :
            Yii::t('common', '(not set)'),
    ],

    [
        'attribute' => 'insert_time',
        'format' => 'html',
        'value' => ($accountTariff->insert_time && is_string($accountTariff->insert_time) && $accountTariff->insert_time[0] != '0') ?
            (new DateTimeWithUserTimezone($accountTariff->insert_time))->getDateTime() :
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
        'value' => ($accountTariff->update_time && is_string($accountTariff->update_time) && $accountTariff->update_time[0] != '0') ?
            (new DateTimeWithUserTimezone($accountTariff->update_time))->getDateTime() :
            Yii::t('common', '(not set)'),
    ],

];

switch ($formModel->serviceTypeId) {
    case ServiceType::ID_VOIP:
        $attributes[] = [
            'attribute' => 'city_id',
            'value' => Html::encode($accountTariff->city_id ? $accountTariff->city->name : Yii::t('common', '(not set)')),
        ];
        $attributes[] = [
            'attribute' => 'voip_number',
            'format' => 'html',
            'value' => ($number = $accountTariff->number) ?
                Html::a($accountTariff->voip_number, $number->getUrl()) :
                $accountTariff->voip_number
        ];
        break;
}
?>

<?= DetailView::widget([
    'model' => $accountTariff,
    'attributes' => $attributes,
]) ?>

<div class="well">
    <?= $this->render('//layouts/_showHistory', ['model' => $accountTariff]) ?>
</div>
