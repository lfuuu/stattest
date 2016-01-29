<?php

namespace app\controllers;

use app\classes\BaseController;
use app\classes\monitoring\MonitorFactory;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\models\UsageWelltime;
use app\models\UsageSms;
use app\models\UsageTechCpe;
use app\models\UsageIpPorts;
use app\models\UsageExtra;
use app\models\UsageEmails;
use app\dao\MonitoringDao;

class MonitoringController extends BaseController
{

    public function actionIndex($monitor = 'usages_lost_tariffs')
    {
        return $this->render('default', [
            'monitors' => MonitorFactory::me()->getAll(),
            'current' => MonitorFactory::me()->getOne($monitor),
        ]);
    }

    public function actionTransferedUsages()
    {
        $usages = [
            (new UsageVoip)->helper->title      => MonitoringDao::transferedUsages(UsageVoip::className()),
            (new UsageVirtpbx)->helper->title   => MonitoringDao::transferedUsages(UsageVirtpbx::className()),
            (new UsageWelltime)->helper->title  => MonitoringDao::transferedUsages(UsageWelltime::className()),
            (new UsageSms)->helper->title       => MonitoringDao::transferedUsages(UsageSms::className()),
            (new UsageTechCpe)->helper->title   => MonitoringDao::transferedUsages(UsageTechCpe::className()),
            (new UsageIpPorts)->helper->title   => MonitoringDao::transferedUsages(UsageIpPorts::className()),
            (new UsageExtra)->helper->title     => MonitoringDao::transferedUsages(UsageExtra::className()),
            (new UsageEmails)->helper->title    => MonitoringDao::transferedUsages(UsageEmails::className()),
        ];

        return $this->render('transfer', [
            'result' => $usages,
        ]);
    }

}