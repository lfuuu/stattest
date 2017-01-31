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
     */
    public function actionIndex($monitor = 'usages_lost_tariffs')
    {
        return $this->render('default', [
            'monitors' => MonitorFactory::me()->getAll(),
            'current' => MonitorFactory::me()->getOne($monitor),
        ]);
    }

    /**
     * Перенос услуг
     *
     * @param bool $isCurrentOnly
     * @return string
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

        return $this->render('transfer', [
            'result' => $listing,
            'clientAccount' => $clientAccount,
        ]);
    }

    /**
     * Очередь событий
     *
     * @return string
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

        return $this->render('event-queue/index', [
            'filterModel' => $filterModel,
        ]);
    }

    /**
     * Включение оповещений
     */
    public function actionNotificationOn()
    {
        $switchOffParam = Param::findOne(Param::NOTIFICATIONS_SWITCH_OFF_DATE);

        if ($switchOffParam) {
            if (!$switchOffParam->delete()) {
                throw new ModelValidationException($switchOffParam);
            }
        }

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }

    /**
     * Отключение оповещений
     */
    public function actionNotificationOff()
    {
        $switchOffParam = Param::findOne(Param::NOTIFICATIONS_SWITCH_OFF_DATE);

        if (!$switchOffParam) {
            $switchOffParam = new Param();
            $switchOffParam->param = Param::NOTIFICATIONS_SWITCH_OFF_DATE;
        }

        $switchOffParam->value = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        if (!$switchOffParam->save()) {
            throw new ModelValidationException($switchOffParam);
        }

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }
}