<?php

namespace app\controllers;

use app\forms\transfer\ServiceTransferForm;
use app\models\UsageTrunk;
use Yii;
use yii\db\Expression;
use app\classes\BaseController;
use app\classes\Assert;
use app\classes\monitoring\MonitorFactory;
use app\models\filter\EventQueueFilter;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\models\UsageWelltime;
use app\models\UsageSms;
use app\models\UsageTechCpe;
use app\models\UsageIpPorts;
use app\models\UsageExtra;
use app\models\UsageEmails;
use app\dao\MonitoringDao;
use app\models\ClientAccount;
use app\models\EventQueue;

class MonitoringController extends BaseController
{

    /**
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
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionTransferedUsages()
    {
        $services = ServiceTransferForm::getServicesGroups();

        $listing = [];
        foreach ($services as $serviceKey => $serviceClass) {
            $listing[(new $serviceClass)->helper->title] = MonitoringDao::transferedUsages($serviceClass);
        }

        return $this->render('transfer', [
            'result' => $listing,
        ]);
    }

    /**
     * Очередь событий
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

}