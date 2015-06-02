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
    public $target_account_id_custom;
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
            ['target_account_id_custom', 'required', 'when' => function($model) { return !(int) $model->target_account_id;  }, 'message' => 'Необходимо заполнить'],
            ['actual_from', 'required', 'when' => function($model) { return $model->actual_from != 'custom'; }, 'message' => 'Необходимо заполнить'],
            ['actual_custom', 'required', 'when' => function($model) { return $model->actual_from == 'custom'; }, 'message' => 'Необходимо заполнить'],
            ['actual_custom', 'date', 'format' => 'php:d.m.Y', 'message' => 'Неверный формат даты переноса'],
            ['target_account_id', 'validateTargetAccountId']
        ];
    }

    public function validateTargetAccountId($attribute, $params)
    {
        try {
            $this->targetAccount = ClientAccount::findOne(
                (int) (
                    $this->target_account_id == 'custom'
                        ? $this->target_account_id_custom
                        : $this->target_account_id
                )
            );
            Assert::isObject($this->targetAccount);
        }
        catch(\Exception $e) {
            $this->addError('target_account_not_found', 'Выбранный клиент не найден: ');
        }
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
            $this->addError('services_got_errors', 'Некоторые услуги не могут быть перенесены');
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
                ->orderBy('contragent_id ASC, id ASC')
                ->all();
    }

    public function getPossibleServices(ClientAccount $client)
    {
        $now = new \DateTime();
        $services = $result = [];

        foreach (self::listOfServices() as $service) {
            $services = array_merge(
                $services,
                $service->getPossibleToTransfer($client)
            );
        }

        $total = 0;
        if (sizeof($services))
            foreach ($services as $service) {
                $result[$service->getServiceType()][] = $service;
                $total++;
            }

        return array(
            'total' => $total,
            'items' => $result
        );
    }

    public function getActualDateVariants() {
        return $this->datesVariants;
    }

    private static function listOfServices()
    {
        return [
            Emails::dao(),
            UsageExtra::dao(),
            UsageIpPorts::dao(),
            UsageSms::dao(),
            //UsageVirtpbx::find(),
            //UsageVoip::find(),
            UsageWelltime::dao()
        ];
    }
}