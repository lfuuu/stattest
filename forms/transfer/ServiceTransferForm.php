<?php

namespace app\forms\transfer;

use app\classes\Assert;
use app\classes\Form;
use app\models\ClientAccount;
use app\models\Emails;
use app\models\UsageExtra;
use app\models\UsageSms;
use app\models\UsageWelltime;
use app\models\UsageIpPorts;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageTrunk;

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
            ['target_account_id_custom', 'required', 'when' => function ($model) {
                return !(int)$model->target_account_id;
            }, 'message' => 'Необходимо заполнить'],
            ['actual_from', 'required', 'when' => function ($model) {
                return $model->actual_from != 'custom';
            }, 'message' => 'Необходимо заполнить'],
            ['actual_custom', 'required', 'when' => function ($model) {
                return $model->actual_from == 'custom';
            }, 'message' => 'Необходимо заполнить'],
            ['actual_custom', 'date', 'format' => 'php:d.m.Y', 'message' => 'Неверный формат даты переноса'],
            ['target_account_id', 'validateTargetAccountId']
        ];
    }

    public function validateTargetAccountId()
    {
        try {
            $this->targetAccount = ClientAccount::findOne(
                (int)(
                $this->target_account_id == 'custom'
                    ? $this->target_account_id_custom
                    : $this->target_account_id
                )
            );
            Assert::isObject($this->targetAccount);
        } catch (\Exception $e) {
            $this->addError('target_account_not_found', 'Выбранный клиент не найден');
        }
    }

    /**
     * Процесс переноса услуг
     */
    public function process()
    {
        $services = $this->getServicesByIDs((array) $this->source_service_ids);

        foreach ($services as $serviceId => $service) {
            $serviceTransfer = $service['object']->getTransferHelper();
            $serviceTransfer->setActivationDate(
                $this->actual_from == 'custom'
                    ? $this->actual_custom
                    : $this->actual_from
            );

            if ($service['object']->actual_to < date('Y-m-d', $serviceTransfer->getActivationDate()))
                $this->servicesErrors[ $serviceId ][] = 'Услуга не может быть перенеса на указанную дату';
            else {
                try {
                    $this->servicesSuccess[ $service['type'] ][] = $serviceTransfer->process($this->targetAccount)->id;
                } catch (\Exception $e) {
                    $this->servicesErrors[ $serviceId ][] = $e->getMessage();
                }
            }
        }

        if (sizeof($this->servicesErrors)) {
            $this->addError('services_got_errors', 'Некоторые услуги не могут быть перенесены');
            return false;
        }

        return true;
    }

    /**
     * Список услуг по группам
     * @return array
     */
    public function getServicesGroups()
    {
        return [
            'emails' => [
                'title' => 'E-mail',
                'service' => Emails::dao()
            ],
            'usage_extra' => [
                'title' => 'Доп. услуги',
                'service' => UsageExtra::dao()
            ],
            'usage_sms' => [
                'title' => 'SMS',
                'service' => UsageSms::dao()
            ],
            'usage_welltime' => [
                'title' => 'Welltime',
                'service' => UsageWelltime::dao()
            ],
            'usage_voip' => [
                'title' => 'Телефония номера',
                'service' => UsageVoip::dao()
            ],
            'usage_trunk' => [
                'title' => 'Телефония транки',
                'service' => UsageTrunk::dao()
            ],
            'usage_ip_ports' => [
                'title' => 'Интернет',
                'service' => UsageIpPorts::dao()
            ],
            'usage_virtpbx' => [
                'title' => 'Виртуальная АТС',
                'service' => UsageVirtpbx::dao()
            ]
        ];
    }

    /**
     * Получение всех лицевых счетов
     * @param ClientAccount $client - клиент для которого получаем всех лицевых счетов
     * @return \app\models\ClientAccount[]
     */
    public function getClientAccounts(ClientAccount $client)
    {
        return
            ClientAccount::find()
                ->andWhere(['super_id' => $client->super_id])
                ->andWhere('id != :id', [':id' => $client->id])
                ->orderBy('contragent_id ASC, id ASC')
                ->all();
    }

    /**
     * Получение доступных для переноса услуг
     * @param ClientAccount $client - клиент для которого получаем список услуг
     * @return array
     */
    public function getPossibleServices(ClientAccount $client)
    {
        $services = $result = [];

        foreach ($this->getServicesGroups() as $groupKey => $groupData) {
            $services = array_merge(
                $services,
                $groupData['service']->getPossibleToTransfer($client)
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

    /**
     * Получение списка услуг по ID услуги и типу
     * @param array $servicesList - Список услуг разделенных по группам
     * @return array
     */
    public function getServicesByIDs(array $servicesList)
    {
        $result = [];

        foreach ($servicesList as $serviceType => $services) {
            foreach ($services as $serviceId) {
                $service = null;
                switch ($serviceType) {
                    case 'emails':
                        $service = Emails::findOne($serviceId);
                        break;
                    case 'usage_sms':
                        $service = UsageSms::findOne($serviceId);
                        break;
                    case 'usage_extra':
                        $service = UsageExtra::findOne($serviceId);
                        break;
                    case 'usage_ip_ports':
                        $service = UsageIpPorts::findOne($serviceId);
                        break;
                    case 'usage_welltime':
                        $service = UsageWelltime::findOne($serviceId);
                        break;
                    case 'usage_voip':
                        $service = UsageVoip::findOne($serviceId);
                        break;
                    case 'usage_virtpbx':
                        $service = UsageVirtpbx::findOne($serviceId);
                        break;
                    case 'usage_trunk':
                        $service = UsageTrunk::findOne($serviceId);
                        break;
                }
                Assert::isObject($service);

                $result[$service->id] = [
                    'type' => $serviceType,
                    'object' => $service
                ];
            }
        }

        return $result;
    }

    /**
     * Получение списка доступных для переноса дат
     * @return array
     */
    public function getActualDateVariants() {
        return $this->datesVariants;
    }

}