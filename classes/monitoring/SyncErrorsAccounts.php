<?php

namespace app\classes\monitoring;

use app\classes\DBROQuery;
use app\models\ClientAccount;
use app\models\usages\UsageInterface;
use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class SyncErrorsAccounts extends SyncErrorsUsageBase
{


    /**
     * @return string
     */
    public function getKey()
    {
        return 'sync_errors_account';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Ошибки синхронизации ЛС';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Ошибки синхронизации лицевых счетов с платформой';
    }

    public function getServiceType()
    {
        //abstract interface
    }


    public function getServiceClass()
    {
        //abstract interface
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return [

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

    /**
     * @return ArrayDataProvider
     */
    public function getResult()
    {
        $cacheId = 'monitor_' . $this->getKey();

        $result = [];

        if (Yii::$app->request->get('page') && Yii::$app->cache->exists($cacheId))
        {
            $result = Yii::$app->cache->get($cacheId);
        } else {

            $dbroResult = ArrayHelper::map((new DBROQuery())
                ->select(["account_id"])
                ->from('services_available')
                ->where([
                    'enabled' => 't'
                ])
                ->group('account_id')
                ->all(),
                "account_id",
                "account_id"
            );

            $statResult = ArrayHelper::map(
                Yii::$app->db->createCommand(
                    "select distinct c.id from view_platforma_services_ro v, clients c where c.client = v.client"
                )->queryAll(),
                'id',
                'id'
            );

            $dbroKeys = array_keys($dbroResult);
            $statKeys = array_keys($statResult);

            foreach (array_diff($dbroKeys, $statKeys) as $accountId) {
                $result[$accountId] = [
                    'account_id' => $dbroResult[$accountId],
                    'status' => self::STATUS_IN_PLATFORM
                ];
            }

            foreach (array_diff($statKeys, $dbroKeys) as $accountId) {
                $result[$accountId] = [
                    'account_id' => $statResult[$accountId],
                    'status' => self::STATUS_IN_STAT
                ];
            }

            ksort($result);
            Yii::$app->cache->set($cacheId, $result);
        }

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}