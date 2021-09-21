<?php

namespace app\classes\monitoring;

use app\classes\DBROQuery;
use app\classes\helpers\DependecyHelper;
use app\models\ClientAccount;
use app\models\ClientSuper;
use app\models\usages\UsageInterface;
use Yii;
use yii\base\Component;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;
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

    public function getServiceData()
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
                'attribute' => 'Клиент Id',
                'format' => 'html',
                'value' => function($model) {
                    return Html::a($model['client_super_id'], [
                        'account/super-client-edit',
                        'id' => $model['client_super_id'],
                        'childId' => $model['account_id']
                    ]) ;
                }
            ],
            [
                'attribute' => 'Название клиента',
                'format' => 'html',
                'value' => function ($model) {
                    return Html::a($model['name'] ?: '???', [
                        'client/view',
                        'id' => $model['account_id']
                    ]);
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

        if (Yii::$app->request->get('page') && Yii::$app->cache->exists($cacheId)) {
            $result = Yii::$app->cache->get($cacheId);
        } else {

            $query = (new Query())
                ->select([
                    'id' => 's.id',
                    'name' => 's.name',
                    'account_id' => (new Expression('MIN(c.id)'))
                ])
                ->from([
                    'c' => ClientAccount::tableName(),
                    's' => ClientSuper::tableName(),
                ])
                ->where([
                    'c.is_active' => 1,
                    's.is_lk_exists' => 0
                ])
                ->andWhere('s.id  = c.super_id')
                ->groupBy('s.id');


            foreach ($query->each() as $client) {
                $result[$client['id']] = [
                    'client_super_id' => $client['id'],
                    'account_id' => $client['account_id'],
                    'name' => $client['name'],
                    'status' => self::STATUS_IN_STAT
                ];
            }

            ksort($result);
            Yii::$app->cache->set($cacheId, $result, DependecyHelper::TIMELIFE_HOUR);
        }

        return new ArrayDataProvider([
            'allModels' => $result,
        ]);
    }

}