<?php

namespace app\modules\uu\behaviors;

use app\classes\helpers\DependecyHelper;
use app\exceptions\ModelValidationException;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffHeap;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffStatus;
use yii\base\Behavior;
use yii\base\Event;
use app\classes\model\ActiveRecord;
use yii\caching\TagDependency;
use yii\db\Expression;

class AccountTariffLogTimeHistory extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
        ];
    }

    /**
     * @param Event $event
     */
    public function beforeInsert(Event $event)
    {
        /** @var AccountTariffLog $accountTariffLog */
        $accountTariffLog = $event->sender;
        $transaction = AccountTariffHeap::getDb()->beginTransaction();
        try {
            $accountTariff = $accountTariffLog->accountTariff;
            $tariffPeriod = $accountTariffLog->tariffPeriod;
            // Если еще не существует данных в таблице AccountTariffHeap, то необходимо создать
            $accountTariffHeap = AccountTariffHeap::findOne([
                'account_tariff_id' => $accountTariff->id
            ]);
            if (!$accountTariffHeap) {
                $accountTariffHeap = new AccountTariffHeap;
                $accountTariffHeap->account_tariff_id = $accountTariff->id;
                $accountTariffHeap->start_date = new Expression('NOW()');
            }
            // Установка временных значений
            if (!$tariffPeriod) {
                // Отключение услуги
                $accountTariffHeap->disconnect_date = $accountTariffLog->actual_from_utc;
            } else if ($tariffPeriod->tariff->isTest) {
                // Услуга является тестовой
                $accountTariffHeap->test_connect_date = $accountTariffLog->actual_from_utc;
            } else {

                $cacheKey = 'date_sale_by_account_id=' . $accountTariff->client_account_id;

                // при массовых добавлениях пакетов данный запрос:
                // а) выполняется долго (относительно) на крупных клиентах;
                // б) всегда один и тот же результат, если уже есть данные;
                if (\Yii::$app->cache->exists($cacheKey)) {
                    $dateSale = \Yii::$app->cache->get($cacheKey);
                } else {
                    // Получение даты продажи
                    $dateSale = AccountTariffLog::find()
                        ->select([
                            'actual_from_utc' => new Expression('MIN(uatl.actual_from_utc)'),
                        ])
                        ->alias('uatl')
                        ->innerJoin(['uat' => AccountTariff::tableName()], 'uatl.account_tariff_id = uat.id')
                        ->leftJoin(['utp' => TariffPeriod::tableName()], 'uatl.tariff_period_id = utp.id')
                        ->leftJoin(['ut' => Tariff::tableName()], 'utp.tariff_id = ut.id')
                        ->where([
                            'uat.client_account_id' => $accountTariff->client_account_id,
                            'uat.prev_account_tariff_id' => null,
                        ])
                        ->andWhere(['AND',
                            ['NOT', ['uatl.tariff_period_id' => null]],
                            ['NOT', ['ut.tariff_status_id' => TariffStatus::TEST_LIST]],
                        ])
                        ->scalar();

                    if($dateSale) {
                        \Yii::$app->cache->set($cacheKey, $dateSale, DependecyHelper::DEFAULT_TIMELIFE, (new TagDependency(['tags' => [DependecyHelper::TAG_UU_SERVICE_LIST]])));
                    }
                }

                $dateSaleTime = $dateSale ? strtotime($dateSale) : null;
                $accountTariffLogTime = strtotime($accountTariffLog->actual_from_utc);
                // Если дата продажи по всем услугам клиента отсутствует или дата продажи + 2 недели содержит текущую дату лога
                if (!$dateSaleTime || strtotime('+2 weeks' ,$dateSaleTime) >= $accountTariffLogTime) {
                    $accountTariffHeap->date_sale = $accountTariffLog->actual_from_utc;
                }
                // Получение даты допродажи
                $dateBeforeSale = AccountTariff::find()
                    ->select([
                        'actual_from_utc' => new Expression('MIN(uatl.actual_from_utc) ')
                    ])
                    ->alias('uat')
                    ->leftJoin(['uatl' => AccountTariffLog::tableName()], 'uat.id = uatl.account_tariff_id')
                    ->leftJoin(['utp' => TariffPeriod::tableName()], 'uatl.tariff_period_id = utp.id')
                    ->leftJoin(['ut' => Tariff::tableName()], 'utp.tariff_id = ut.id')
                    ->where(['AND',
                        ['IS NOT', 'uatl.tariff_period_id', null],
                        ['NOT IN', 'ut.tariff_status_id', TariffStatus::TEST_LIST],
                        ['IS', 'uat.prev_account_tariff_id', null],
                        ['=', 'uat.id', $accountTariff->id],
                    ])
                    ->scalar();
                // Если дата продажи существует, дата допродажи не существует и дата продажи + 2 недели меньше текущей даты (допродажи)
                if ($dateSaleTime && !$dateBeforeSale && strtotime('+2 weeks', $dateSaleTime) < $accountTariffLogTime) {
                    $accountTariffHeap->date_before_sale = $accountTariffLog->actual_from_utc;
                }
            }
            // Если есть измененные поля, то происходит попытка сохранения
            if ($accountTariffHeap->getDirtyAttributes() && !$accountTariffHeap->save()) {
                throw new ModelValidationException($accountTariffHeap);
            }
            $transaction->commit();
        } catch (ModelValidationException $e) {
            $transaction->rollBack();
        }
    }
}