<?php

namespace app\classes\monitoring;

use app\classes\api\ApiVpbx;
use app\models\UsageVoip;
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
    public function getServiceClass()
    {
        return UsageVoip::class;
    }

    /**
     * @return string
     */
    public function getServiceIdField()
    {
        return 'E164';
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
                    $usage = UsageVoip::find()->where(['E164' => $model['usage_id']])->orderBy(['id' => SORT_DESC])->one();
                    return ($usage ? Html::a(' ' . $usage->id . ' ', $usage->helper->editLink) : '');
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