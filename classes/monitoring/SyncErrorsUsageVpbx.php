<?php

namespace app\classes\monitoring;

use app\classes\DBROQuery;
use app\models\UsageVirtpbx;
use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use app\classes\Html;
use yii\helpers\ArrayHelper;

class SyncErrorsUsageVpbx extends SyncErrorsUsageBase
{

    /**
     * @return string
     */
    public function getKey()
    {
        return 'sync_errors_usage_vpbx';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Ошибки синхронизации. ВАТС';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Ошибки синхронизации услуг ВАТС платформой';
    }

    public function getServiceType()
    {
        return 'vpbx';
    }

    public function getServiceClass()
    {
        return UsageVirtpbx::className();
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
                    $usage = UsageVirtpbx::findOne(['id' => $model['usage_id']]);
                    return ($usage ? Html::a(' ' . $model['usage_id'] . ' ', $usage->helper->editLink) : $model['usage_id']);
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