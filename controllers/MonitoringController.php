<?php

namespace app\controllers;

use app\classes\BaseController;
use app\classes\monitoring\MonitorFactory;
use app\classes\traits\AddClientAccountFilterTraits;
use app\dao\MonitoringDao;
use app\exceptions\ModelValidationException;
use app\forms\transfer\ServiceTransferForm;
use app\helpers\DateTimeZoneHelper;
use app\models\EventQueue;
use app\models\filter\EventQueueFilter;
use app\models\Param;
use app\modules\transfer\components\services\regular\BasicServiceTransfer as RegularBasicServiceTransfer;
use app\modules\transfer\components\services\regular\RegularTransfer;
use app\modules\transfer\components\services\universal\UniversalTransfer;
use kartik\base\Config;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidValueException;
use yii\db\Expression;

class MonitoringController extends BaseController
{

    use AddClientAccountFilterTraits;

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
     * @throws InvalidCallException
     * @throws InvalidValueException
     */
    public function actionTransferredServices($isCurrentOnly = true)
    {
        $clientAccount = null;

        if ($isCurrentOnly) {
            $clientAccount = $this->_getCurrentClientAccount();
            if ($clientAccount === null) {
                return $this->redirect('/');
            }
        }

        $regularProcessor = new RegularTransfer;
        $universalProcessor = new UniversalTransfer;

        $knownRegularServices = $regularProcessor->getServices();
        $regularServices = [];
        foreach ($knownRegularServices as $serviceCode => $serviceHandler) {
            /** @var RegularBasicServiceTransfer $serviceHandler */
            $serviceHandler = $regularProcessor->getHandler($serviceCode);

            if(($service = $serviceHandler->getServiceModelName()) === '') {
                continue;
            }

            $regularServices += MonitoringDao::transferredRegularServices($service, $clientAccount);
        }

        $knownUniversalServices = $universalProcessor->getServices();
        $universalServices = [];
        foreach ($knownUniversalServices as $serviceCode => $serviceHandler) {
            /** @var RegularBasicServiceTransfer $serviceHandler */
            $serviceHandler = $regularProcessor->getHandler($serviceCode);

            if(!$serviceHandler->getServiceTypeId()) {
                continue;
            }

            $universalServices += MonitoringDao::transferredUniversalServices($serviceHandler->getServiceTypeId(), $clientAccount);
        }

        return $this->render('transferred_services', [
            'regularServices' => $regularServices,
            'universalServices' => $universalServices,
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
            Param::NOTIFICATIONS_SWITCH_ON_DATE,
        ]]);

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }

    /**
     * Отключение оповещений
     *
     * @throws ModelValidationException
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
     *
     * @throws ModelValidationException
     */
    public function actionRecalculationBalanceWhenBillEditOff()
    {
        Param::setParam(Param::DISABLING_RECALCULATION_BALANCE_WHEN_EDIT_BILL, 1);

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }

    /**
     * Включение пересчета баланса при редактировании счета
     *
     * @throws ModelValidationException
     * @throws \yii\db\StaleObjectException
     * @throws \Exception
     */
    public function actionRecalculationBalanceWhenBillEditOn()
    {
        /** @var Param $param */
        $param = Param::findOne(Param::DISABLING_RECALCULATION_BALANCE_WHEN_EDIT_BILL);

        if ($param) {
            if (!$param->delete()) {
                throw new ModelValidationException($param);
            }
        }

        return $this->redirect(\Yii::$app->request->referrer ?: "/");
    }
}