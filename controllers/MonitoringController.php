<?php

namespace app\controllers;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\Param;
use Yii;
use yii\db\Expression;
use app\classes\BaseController;
use app\classes\monitoring\MonitorFactory;
use app\models\EventQueue;
use app\models\ClientAccount;
use app\models\filter\EventQueueFilter;
use app\dao\MonitoringDao;
use app\forms\transfer\ServiceTransferForm;

class MonitoringController extends BaseController
{

    /**
     * Index
     *
     * @param string $monitor
     * @return string
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidParamException
     */
    public function actionIndex($monitor = 'usages_lost_tariffs')
    {
        return $this->render('index', [
            'monitors' => MonitorFactory::me()->getAll(),
            'current' => MonitorFactory::me()->getOne($monitor),
        ]);
    }

    /**
     * Перенос услуг
     *
     * @param bool $isCurrentOnly
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\Exception
     */
    public function actionTransferedUsages($isCurrentOnly = true)
    {
        global $fixclient_data;

        $services = ServiceTransferForm::getServicesGroups();
        $clientAccount = ($isCurrentOnly && $fixclient_data instanceof ClientAccount ? $fixclient_data : null);

        $listing = [];
        foreach ($services as $serviceKey => $serviceClass) {
            $listing[(new $serviceClass)->helper->title] = MonitoringDao::transferedUsages($serviceClass, $clientAccount);
        }

        return $this->render('transferUsages', [
            'result' => $listing,
            'clientAccount' => $clientAccount,
        ]);
    }

    /**
     * Очередь событий
     *
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public function actionEventQueue()
    {
        $get = Yii::$app->request->get();

        if (isset($get['submitButtonRepeatStopped'])) {
            EventQueue::updateAll(
                [
                    'status' => EventQueue::STATUS_PLAN,
                    'iteration' => EventQueue::ITERATION_MAX_VALUE - 1,
                    'next_start' => new Expression('NOW()'),
                ],
                [
                    'status' => EventQueue::STATUS_STOP,
                ]
            );
        }

        $filterModel = new EventQueueFilter();
        $filterModel->load($get);

        return $this->render('eventQueue', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Включение оповещений
     */
    public function actionNotificationOn()
    {
        Param::deleteAll(['param' => [
            Param::NOTIFICATIONS_SWITCH_OFF_DATE,
            Param::NOTIFICATIONS_SWITCH_ON_DATE
        ]]);

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }

    /**
     * Отключение оповещений
     */
    public function actionNotificationOff()
    {
        $now = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)));
        Param::setParam(
            Param::NOTIFICATIONS_SWITCH_OFF_DATE,
            $now
                ->format(DateTimeZoneHelper::DATETIME_FORMAT),
            $isRawValue = true
        );

        Param::setParam(
            Param::NOTIFICATIONS_SWITCH_ON_DATE,
            $now
                ->modify(Param::NOTIFICATIONS_PERIOD_OFF_MODIFY)
                ->format(DateTimeZoneHelper::DATETIME_FORMAT),
            $isRawValue = true
        );

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }

    /**
     * Отключение пересчета баланса при редактировании счета
     */
    public function actionRecalculationBalanceWhenBillEditOff()
    {
        Param::setParam(Param::DISABLING_RECALCULATION_BALANCE_WHEN_EDIT_BILL, 1);

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }

    /**
     * Включение пересчета баланса при редактировании счета
     */
    public function actionRecalculationBalanceWhenBillEditOn()
    {
        $param = Param::findOne(Param::DISABLING_RECALCULATION_BALANCE_WHEN_EDIT_BILL);

        if ($param) {
            if (!$param->delete()) {
                throw new ModelValidationException($param);
            }
        }

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }
}