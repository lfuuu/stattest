<?php

namespace app\classes\transfer;

use app\classes\Assert;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\usages\UsageInterface;
use DateTime;
use DateTimeZone;
use LogicException;
use Yii;
use yii\base\Component;
use yii\base\InvalidValueException;
use yii\db\ActiveRecord;

abstract class ServiceTransfer extends Component
{

    protected
        /** @var ClientAccount $targetAccount */
        $targetAccount,
        /** @var DateTime $activationDate */
        $activationDate;

    /** @var UsageInterface|ActiveRecord */
    public $service;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
        ];
    }

    /**
     * @param UsageInterface|ActiveRecord $service
     */
    public function __construct($service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Устанавливает лицевой счет на который предполагается перенос
     *
     * @param ClientAccount $targetAccount
     * @return $this
     */
    public function setTargetAccount(ClientAccount $targetAccount)
    {
        $this->targetAccount = $targetAccount;
        return $this;
    }

    /**
     * Устанавливает дату активации переносимых услуг
     *
     * @param string $date
     * @return $this
     */
    public function setActivationDate($date)
    {
        $this->activationDate = new DateTime($date, $this->targetAccount->timezone);
        return $this;
    }

    /**
     * Процесс переноса услуги
     *
     * @return ActiveRecord
     * @throws \Exception
     */
    public function process()
    {
        if (!($this->targetAccount instanceof ClientAccount)) {
            throw new InvalidValueException('Необходимо указать лицевой счет на который совершается перенос');
        }

        if (!$this->activationDate) {
            throw new InvalidValueException('Необходимо указать дату переноса');
        }

        if ((int)$this->service->next_usage_id) {
            throw new InvalidValueException('Услуга уже перенесена');
        }

        if ($this->service->actual_to < $this->getActualDate()) {
            throw new InvalidValueException('Услуга не может быть перенесена на указанную дату');
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            /** @var ActiveRecord $targetService */
            $targetService = new $this->service;
            $targetService->setAttributes($this->service->getAttributes(), false);

            unset($targetService->id);
            $targetService->activation_dt = $this->getActivationDatetime();
            $targetService->actual_from = $this->getActualDate();
            $targetService->prev_usage_id = $this->service->id;
            $targetService->client = $this->targetAccount->client;

            $targetService->save();

            $this->service->expire_dt = $this->getExpireDatetime();
            $this->service->actual_to = $this->getExpireDate();
            $this->service->next_usage_id = $targetService->id;

            $this->service->save();

            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }

        return $targetService;
    }

    /**
     * Процесс отмены переноса услуги
     *
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function fallback()
    {
        if (!(int)$this->service->next_usage_id) {
            throw new InvalidValueException('Услуга не была подготовлена к переносу');
        }

        $dbTransaction = Yii::$app->db->beginTransaction();
        try {
            /** @var ActiveRecord $movedService */
            $movedService = new $this->service;
            $movedService = $movedService->find()
                ->andWhere(['id' => $this->service->next_usage_id])
                ->andWhere(['>', 'actual_from', date(DateTimeZoneHelper::DATE_FORMAT)])
                ->one();
            Assert::isObject($movedService);

            $this->service->next_usage_id = 0;
            $this->service->expire_dt = $movedService->expire_dt;
            $this->service->actual_to = $movedService->actual_to;
            if (!$this->service->save()) {
                throw new LogicException(implode(' ', $this->service->getFirstErrors()));
            }

            $movedService->delete();
            $dbTransaction->commit();
        } catch (\Exception $e) {
            $dbTransaction->rollBack();
            throw $e;
        }
    }

    /**
     * @return string
     */
    public function getActivationDatetime()
    {
        /** @var DateTime $activationDatetime */
        $activationDatetime = clone $this->activationDate;
        return
            $activationDatetime
                ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);
    }

    /**
     * @return string
     */
    public function getActualDate()
    {
        return $this->activationDate->format(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * @return string
     */
    public function getExpireDatetime()
    {
        /** @var DateTime $expireDatetime */
        $expireDatetime = clone $this->activationDate;
        return
            $expireDatetime
                ->modify('-1 day')
                ->setTime(23, 59, 59)
                ->setTimezone(new DateTimeZone('UTC'))
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);
    }

    /**
     * @return string
     */
    public function getExpireDate()
    {
        /** @var DateTime $expireDate */
        $expireDate = clone $this->activationDate;
        return
            $expireDate
                ->modify('-1 day')
                ->format(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * @param ClientAccount $client
     * @return UsageInterface[]
     */
    abstract public function getPossibleToTransfer(ClientAccount $client);

}