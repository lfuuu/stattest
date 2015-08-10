<?php

namespace app\forms\transfer;

use app\classes\Assert;
use app\classes\Form;
use app\models\ClientAccount;
use app\models\ClientContragent;
use app\models\Emails;
use app\models\Usage;
use app\models\UsageExtra;
use app\models\UsageSms;
use app\models\UsageWelltime;
use app\models\UsageIpPorts;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use app\models\UsageTrunk;
use app\models\TechCpe;

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

    /**
     * Список возможных услуг
     * @return array
     */
    public function getServicesGroups()
    {
        return [
            Emails::dao(),
            UsageExtra::dao(),
            UsageSms::dao(),
            UsageWelltime::dao(),
            UsageVoip::dao(),
            UsageTrunk::dao(),
            UsageIpPorts::dao(),
            //UsageVirtpbx::dao(),
            TechCpe::dao(),
        ];
    }

    public function rules()
    {
        return [
            [['target_account_id', 'source_service_ids'], 'required', 'message' => 'Необходимо заполнить'],
            ['target_account_id_custom', 'required', 'when' => function ($model) { return !(int)$model->target_account_id; }, 'message' => 'Необходимо заполнить'],
            ['actual_from', 'required', 'when' => function ($model) { return $model->actual_from != 'custom'; }, 'message' => 'Необходимо заполнить'],
            ['actual_custom', 'required', 'when' => function ($model) { return $model->actual_from == 'custom'; }, 'message' => 'Необходимо заполнить'],
            ['actual_custom', 'date', 'format' => 'php:Y-m-d', 'when' => function ($model) { return $model->actual_from == 'custom'; }, 'message' => 'Неверный формат даты переноса'],
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

        foreach ($services as $service) {
            $serviceTransfer =
                $service
                    ->getTransferHelper()
                        ->setTargetAccount($this->targetAccount)
                        ->setActivationDate(
                            $this->actual_from == 'custom'
                                ? $this->actual_custom
                                : $this->actual_from
                        );

            try {
                try {
                    $this->servicesSuccess[get_class($service)][] = $serviceTransfer->process()->id;
                } catch (\yii\base\InvalidValueException $e) {
                    $this->servicesErrors[ $service->id ][] = $e->getMessage();
                }
            }
            catch (\Exception $e) {
                \Yii::error($e);
            }
        }

        if (sizeof($this->servicesErrors)) {
            $this->addError('services_got_errors', 'Некоторые услуги не могут быть перенесены');
            return false;
        }

        return true;
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
                ->orderBy('contract_id ASC, id ASC')
                ->all();
    }

    /**
     * Получение доступных для переноса услуг
     * @param ClientAccount $client - клиент для которого получаем список услуг
     * @param array $usages - список услуг которые возможны для переноса
     * @return array
     */
    public function getPossibleServices(ClientAccount $client, $usages = [])
    {
        /** @var Usage[] $services */
        $services = [];
        foreach ($this->getServicesGroups() as $serviceDao) {
            $modelName = str_replace('Dao', '', (new \ReflectionClass($serviceDao))->getShortName());
            if (count($usages) && !in_array($modelName, $usages))
                continue;

            $services = array_merge(
                $services,
                $serviceDao->getPossibleToTransfer($client)
            );
        }

        $total = 0;
        $result = [];
        if (sizeof($services))
            foreach ($services as $service) {
                $result[ get_class($service) ][] = $service;
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
     * @return Usage[]
     */
    public function getServicesByIDs(array $servicesList)
    {
        $result = [];

        foreach ($servicesList as $serviceClass => $services) {
            foreach ($services as $serviceId) {
                $result[] = $this->getService($serviceClass, $serviceId);
            }
        }

        return $result;
    }

    public function getService($className, $id)
    {
        $service = $className::findOne($id);
        if (!$service) {
            Assert::isUnreachable();
        }
        return $service;
    }

    /**
     * Получение списка доступных для переноса дат
     * @return array
     */
    public function getActualDateVariants() {
        return $this->datesVariants;
    }

}
