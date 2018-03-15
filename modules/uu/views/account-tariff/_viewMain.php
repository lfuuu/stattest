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
use app\models\billing\Trunk;
use app\models\Datacenter;
use app\models\mtt_raw\MttRaw;
use app\modules\nnp\models\NdcType;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\helpers\Url;
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

    'is_unzipped',

    'prev_usage_id',

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
                $accountTariff->voip_number,
        ];
        break;

    case ServiceType::ID_ONE_TIME:
        $accountLogResources = $accountTariff->accountLogResources;
        $attributes[] = [
            'label' => 'Стоимость',
            'format' => 'html',
            'value' => count($accountLogResources) ? reset($accountLogResources)->price : null,
        ];
        break;

    case ServiceType::ID_INFRASTRUCTURE:

        $infrastructureProjectList = AccountTariff::getInfrastructureProjectList($isWithEmpty = false);
        $attributes[] = [
            'attribute' => 'infrastructure_project',
            'value' => Html::encode($accountTariff->infrastructure_project ? $infrastructureProjectList[$accountTariff->infrastructure_project] : Yii::t('common', '(not set)')),
        ];

        $infrastructureLevelList = AccountTariff::getInfrastructureLevelList($isWithEmpty = false);
        $attributes[] = [
            'attribute' => 'infrastructure_level',
            'value' => Html::encode($accountTariff->infrastructure_level ? $infrastructureLevelList[$accountTariff->infrastructure_level] : Yii::t('common', '(not set)')),
        ];

        $datacenterList = Datacenter::getList($isWithEmpty = false);
        $attributes[] = [
            'attribute' => 'datacenter_id',
            'value' => Html::encode($accountTariff->datacenter_id ? $datacenterList[$accountTariff->datacenter_id] : Yii::t('common', '(not set)')),
        ];

        $attributes[] = [
            'attribute' => 'price',
        ];

        break;

    case ServiceType::ID_TRUNK:

        $trunkTypeList = AccountTariff::getTrunkTypeList($isWithEmpty = false);
        $attributes[] = [
            'attribute' => 'trunk_type_id',
            'value' => Html::encode($accountTariff->trunk_type_id ? $trunkTypeList[$accountTariff->trunk_type_id] : Yii::t('common', '(not set)')),
        ];

        $accountTariffTrunkId = (!$accountTariff->isNewRecord && $accountTariff->usageTrunk) ? $accountTariff->usageTrunk->trunk_id : '';
        $trunkList = Trunk::dao()->getList(['serverIds' => $accountTariff->region_id], $isWithEmpty = false);
        $attributes[] = [
            'label' => 'Транк',
            'value' => Html::encode($accountTariffTrunkId ? $trunkList[$accountTariffTrunkId] : Yii::t('common', '(not set)')),
        ];

        $attributes[] = [
            'label' => 'Маршрутизация',
            'format' => 'raw',
            'value' => Html::a('<span class="glyphicon glyphicon-random" aria-hidden="true"></span> Маршрутизация', ['/usage/trunk/edit', 'id' => $accountTariff->id]),
        ];

        break;
}

if ($formModel->serviceTypeId === ServiceType::ID_VOIP && $formModel->ndcTypeId === NdcType::ID_MOBILE) {
    $attributes[] = [
        'label' => 'Статистика MTT',
        'format' => 'html',
        'value' => function (AccountTariff $accountTariff) {
            $urlSms = $this->render('//layouts/_buttonLink', [
                'url' => Url::toRoute([
                    '/uu/mtt/',
                    'MttRawFilter[number_service_id]' => $accountTariff->id,
                    'MttRawFilter[serviceid][0]' => MttRaw::SERVICE_ID_SMS_IN_HOMENETWORK,
                    'MttRawFilter[serviceid][1]' => MttRaw::SERVICE_ID_SMS_IN_ROAMING,
                ]),
                'text' => 'SMS',
            ]);

            $urlInet = $this->render('//layouts/_buttonLink', [
                'url' => Url::toRoute([
                    '/uu/mtt/',
                    'MttRawFilter[number_service_id]' => $accountTariff->id,
                    'MttRawFilter[serviceid][0]' => MttRaw::SERVICE_ID_INET_IN_HOMENETWORK,
                    'MttRawFilter[serviceid][1]' => MttRaw::SERVICE_ID_INET_IN_ROAMING,
                ]),
                'text' => 'Интернет',
            ]);

            return "$urlSms $urlInet";
        }
    ];
}

?>

<?= DetailView::widget([
    'model' => $accountTariff,
    'attributes' => $attributes,
]) ?>

<div class="well">
    <?= $this->render('//layouts/_showHistory', ['model' => $accountTariff]) ?>
</div>
