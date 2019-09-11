<?php

namespace app\classes\monitoring;

use app\classes\api\ApiVpbx;
use app\models\UsageVoip;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\helpers\Html;

class SyncErrorsUsageVoip extends SyncErrorsUsageBase
{
    private static $brokenAccountIds = [34523];  //Телемир, массово подключает номера 7800

    /**
     * @return string
     */
    public function getKey()
    {
        return 'sync_errors_usage_voip';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Ошибки синхронизации. Телефония';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Ошибки синхронизации услуг телефонии платформой. (Без "коротких" номеров и "Телемир"а)';
    }

    /**
     * @return array
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidCallException
     */
    public function getServiceData()
    {
        return ApiVpbx::me()->getPhoneServices();
    }

    /**
     * @return string
     */
    public function getStatData()
    {
        return UsageVoip::find()
            ->alias('uv')
            ->actual()
            ->joinWith('clientAccount c')
            ->select('c.id')
            ->indexBy('E164')
            ->asArray()
            ->column() +
        AccountTariff::find()
            ->where(['NOT', ['tariff_period_id' => null]])
            ->andWhere(['service_type_id' => ServiceType::ID_VOIP])
            ->select('client_account_id')
            ->indexBy('voip_number')
            ->asArray()
            ->column();
    }

    /**
     * Предварительный фильтр результат запроса
     *
     * @param array $data
     * @return null
     */
    public function filterResult($data)
    {
        $result = [];
        foreach ($data as $phone => $clientId) {
            if (in_array($clientId, self::$brokenAccountIds)) {
                continue;
            }

            if (strlen($phone) >= 10) {
                $result[$phone] = $clientId;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [
            [
                'label' => 'Id услуги',
                'format' => 'html',
                'value' => function ($model) {

                    $usage =
                        AccountTariff::find()
                            ->where([
                                'voip_number' => $model['usage_id'],
                                'service_type_id' => ServiceType::ID_VOIP
                            ])
                            ->andWhere(['NOT', ['tariff_period_id' => null]])
                            ->one()
                        ?: UsageVoip::find()
                            ->where([
                                'E164' => $model['usage_id']
                            ])
                            ->orderBy(['id' => SORT_DESC])
                            ->one();

                    return ($usage ? Html::a(' ' . $usage->id . ' ', $usage->url) : '');
                }
            ],
            [
                'label' => 'Номер',
                'attribute' => 'usage_id',
            ],

            [
                'attribute' => 'ЛС',
                'format' => 'html',
                'value' => function ($model) {
                    return Html::a(' ' . $model['account_id'] . ' ', ['/client/view', 'id' => $model['account_id']]);
                }
            ],
            [
                'label' => 'Статус',
                'format' => 'html',
                'value' => function ($model) {
                    return Html::beginTag('span', ['class' => self::$statusClasses[$model['status']]]) .
                        self::$statusNames[$model['status']] . ($model['status'] == self::STATUS_ACCOUNT_DIFF ? ' (ЛС: ' . Html::a(' ' . $model['account_id2'] . ' ', ['/client/view', 'id' => $model['account_id2']]) . ')' : '') .
                        Html::endTag('span');
                }
            ],

        ];
    }
}