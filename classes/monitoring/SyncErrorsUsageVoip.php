<?php

namespace app\classes\monitoring;

use app\models\UsageVoip;
use Yii;
use yii\helpers\Html;

class SyncErrorsUsageVoip extends SyncErrorsUsageBase
{

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
        return 'Ошибки синхронизации услуг телефонии платформой';
    }

    public function getServiceType()
    {
        return 'voip';
    }

    public function getServiceClass()
    {
        return UsageVoip::className();
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
                'value' => function($model) {
                    $usage = UsageVoip::findOne(['id' => $model['usage_id']]);
                    return ($usage ? Html::a(' ' . $model['usage_id'] . ' ', $usage->helper->editLink) : $model['usage_id']);
                }
            ],
            [
                'label' => 'Номер',
                'value' => function($model) {
                    $usage = UsageVoip::findOne(['id' => $model['usage_id']]);
                    return ($usage ? $usage->E164 : '');
                }
            ],

            [
                'attribute' => 'ЛС',
                'format' => 'html',
                'value' => function($model) {
                    return Html::a(' ' . $model['account_id'] . ' ', ['/client/view', 'id' => $model['account_id']]) ;
                }
            ],
            [
                'label' => 'Статус',
                'format' => 'html',
                'value' => function($model) {
                    return Html::beginTag('span', ['class' => self::$statusClasses[$model['status']]]) .
                        self::$statusNames[$model['status']] . ($model['status'] == self::STATUS_ACCOUNT_DIFF ? ' (ЛС: ' . Html::a(' ' . $model['account_id2'] . ' ', ['/client/view', 'id' => $model['account_id2']]) . ')' : '') .
                        Html::endTag('span');
                }
            ],

        ];
    }
}