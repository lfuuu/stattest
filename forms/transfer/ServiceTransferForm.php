<?php

namespace app\forms\transfer;

use app\classes\Assert;
use app\classes\Form;
use app\models\ClientAccount;
use app\models\Emails;
use app\models\UsageExtra;
use app\models\UsageIpPorts;
use app\models\UsageSms;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageWelltime;

class ServiceTransferForm extends Form
{

    public $target_account_id;
    public $target_account_custom;
    public $source_service_ids;
    public $actual_from;
    public $actual_custom;

    public $datesVariants = [
        'first day of next month midnight',
        'first day of next month +1 month midnight',
        'first day of next month +2 month midnight'
    ];

    public $servicesErrors = [];
    public $servicesSuccess = [];

    public $targetAccount = null;

    public function rules()
    {
        return [
            [['target_account_id', 'source_service_ids'], 'required', 'message' => 'Необходимо заполнить'],
            ['target_account_custom', 'required', 'when' => function($model) { return !(int) $model->target_account_id;  }, 'message' => 'Необходимо заполнить'],
            ['actual_from', 'required', 'when' => function($model) { return $model->actual_from != 'custom'; }, 'message' => 'Необходимо заполнить'],
            ['actual_custom', 'required', 'when' => function($model) { return $model->actual_from == 'custom'; }, 'message' => 'Необходимо заполнить'],
            ['actual_custom', 'validateActualCustom'],
            ['target_account_id', 'validateTargetAccountId']
        ];
    }

    public function validateActualCustom($attribute, $params)
    {
        if (
            !preg_match('#([0-9]{2})\.([0-9]{2})\.([0-9]{4})#', $this->actual_custom)
                &&
            !preg_match('#([0-9]{4})\-([0-9]{2})\-([0-9]{2})#', $this->actual_custom)
        )
            $this->addError('actual_custom', 'Неверный формат даты переноса');
    }

    public function validateTargetAccountId($attribute, $params)
    {
        try {
            $this->targetAccount = ClientAccount::findOne($this->target_account_id);
            Assert::isObject($this->targetAccount);
        }
        catch(\Exception $e) {
            $this->addError('target-account-not-found', 'Выбранный клиент не найден');
            return false;
        }
    }

    public function attributeLabels()
    {
        return [
            'target_account_id' => '', // Лицевой счет
            'target_account_custom' => '', // Лицевой счет
            'source_service_ids' => '', // Услуги
            'actual_from' => '', // Дата переноса
            'actual_custom' => '' // Дата переноса
        ];
    }

    public function process()
    {
        foreach ($this->source_service_ids as $serviceType => $servicesByType) {
            foreach ($servicesByType as $serviceId) {
                $service = null;
                switch ($serviceType) {
                    case 'emails':
                        $service = Emails::findOne($serviceId);
                        Assert::isObject($service);
                        break;
                    case 'usage_sms':
                        $service = UsageSms::findOne($serviceId);
                        Assert::isObject($service);
                        break;
                    case 'usage_extra':
                        $service = UsageExtra::findOne($serviceId);
                        Assert::isObject($service);
                        break;
                    case 'usage_ip_ports':
                        $service = UsageIpPorts::findOne($serviceId);
                        Assert::isObject($service);
                        break;
                        break;
                    case 'usage_welltime':
                        $service = UsageWelltime::findOne($serviceId);
                        Assert::isObject($service);
                        break;
                }

                if (!is_null($service)) {
                    $serviceTransfer = $service->getTransferHelper();
                    $serviceTransfer
                        ->setActivationDate($this->actual_from == 'custom' ? $this->actual_custom : $this->actual_from);

                    if ($service->actual_to < date('Y-m-d', $serviceTransfer->getActivationDate()))
                        $this->servicesErrors[$service->id][] = 'Услуга не может быть перенеса на указанную дату';
                    else {
                        try {
                            $this->servicesSuccess[] = $serviceTransfer->process($this->targetAccount);
                        } catch (\Exception $e) {
                            $this->servicesErrors[$service->id][] = $e->getMessage();
                        }
                    }
                }
            }
        }

        if (sizeof($this->servicesErrors)) {
            $this->addError('services-got-errors', 'Некоторые услуги не могут быть перенесены');
            return false;
        }

        return true;
    }

    public function getClientAccounts(ClientAccount $client)
    {
        return
            ClientAccount::find()
                ->andWhere(['super_id' => $client->super_id])
                ->andWhere('id != :id', [':id' => $client->id])
                ->all();
    }

    public function getPossibleServices(ClientAccount $client)
    {
        $now = new \DateTime();
        $services = $result = [];

        foreach (self::listOfServices() as $type) {
            $services = array_merge(
                $services,
                $type
                    ->andWhere(['client' => $client->client])
                    ->andWhere('actual_from <= :date', [':date' => $now->format('Y-m-d')])
                    ->andWhere(['dst_usage_id' => 0])
                    ->all()
            );
        }

        if (sizeof($services))
            foreach ($services as $service)
                $result[$service->getServiceType()][] = $service;

        return $result;
    }

    public function getActualDateVariants() {
        return $this->datesVariants;
    }

    private static function listOfServices()
    {
        return [
            Emails::find(),
            //UsageExtra::find(),
            UsageIpPorts::find(),
            UsageSms::find(),
            //UsageVirtpbx::find(),
            //UsageVoip::find(),
            UsageWelltime::find()
        ];
    }
}