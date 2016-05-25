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
        return 'phone';
    }

    public function getServiceClass()
    {
        return UsageVoip::className();
    }

    public function getServiceIdField()
    {
        return 'E164';
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