<?php

namespace app\modules\transfer\components\services;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\important_events\ImportantEventsSources;
use app\models\User;
use app\modules\transfer\forms\services\BaseForm;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;
use yii\base\Model;
use yii\base\UnknownClassException;

/**
 * @property-read BaseForm $form
 * @property-read array $services
 * @property-read $handler
 */
abstract class Processor extends Model
{

    const SERVICE_VOIP = 'usage_voip';
    const SERVICE_VPBX= 'usage_virtpbx';
    const SERVICE_CALL_CHAT = 'usage_call_chat';
    const SERVICE_TRUNK = 'usage_trunk';
    const SERVICE_SMS = 'usage_sms';
    const SERVICE_WELLTIME_SAAS = 'usage_welltime';
    const SERVICE_EXTRA = 'usage_extra';
    const SERVICE_EMAIL = 'usage_email';
    const SERVICE_INTERNET = 'usage_ip_ports';
    const SERVICE_INTERNET_ROUTES = 'usage_ip_routes';
    const SERVICE_INTERNET_DEVICES = 'usage_tech_cpe';

    const SERVICE_PACKAGE = 'service_package';

    /**
     * @param PreProcessor $preProcessor
     * @return PreProcessor
     * @throws ModelValidationException
     * @throws InvalidParamException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function run(PreProcessor $preProcessor)
    {
        if (!$preProcessor->validate()) {
            throw new ModelValidationException($preProcessor);
        }

        $transaction = \Yii::$app->getDb()->beginTransaction();

        try {
            // Set close attributes on source service
            $sourceServiceHandler = $preProcessor->sourceServiceHandler;
            $sourceServiceHandler->closeService($preProcessor);

            // Create service based on source service
            $targetServiceHandler = $preProcessor->targetServiceHandler;
            $targetServiceHandler->openService($preProcessor);

            // Trying to update source service
            if (!$sourceServiceHandler->getService()->save()) {
                throw new ModelValidationException($sourceServiceHandler->getService());
            }

            // Trying to create service
            if (!$targetServiceHandler->getService()->save()) {
                throw new ModelValidationException($targetServiceHandler->getService());
            }

            // Process related services for source service
            $preProcessor->sourceServiceHandler->finalizeClose($preProcessor);

            // Process related services for target service
            $preProcessor->targetServiceHandler->finalizeOpen($preProcessor);

            // Create important event about transfer
            ImportantEvents::create(
                ImportantEventsNames::TRANSFER_USAGE,
                ImportantEventsSources::SOURCE_STAT,
                [
                    'client_id' => $preProcessor->clientAccount->id,
                    'usage' => $preProcessor->serviceType,
                    'usage_id' => $sourceServiceHandler->getService()->getAttribute('id'),
                    'user_id' => \Yii::$app->user->id ?: User::SYSTEM_USER,
                ]
            );

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $preProcessor;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return [];
    }

    /**
     * @param string $serviceType
     * @return ServiceTransfer
     * @throws InvalidValueException
     * @throws InvalidParamException
     */
    public function getHandler($serviceType)
    {
        $services = $this->getServices();

        if (!array_key_exists($serviceType, $services)) {
            throw new InvalidValueException('Unknown service transfer handler "' . $serviceType . '"');
        }

        return new $services[$serviceType];
    }

    /**
     * @param int $serviceTypeId
     * @return string
     * @throws UnknownClassException
     */
    public function getHandlerByTypeId($serviceTypeId)
    {
        foreach ($this->getServices() as $serviceCode => $serviceClass) {
            $serviceHandler = new $serviceClass;
            if ($serviceHandler->getServiceTypeId() !== $serviceTypeId) {
                continue;
            }

            return $serviceHandler;
        }

        throw new UnknownClassException('Service handler not found by type #' . $serviceTypeId);
    }

    /**
     * @param ClientAccount $clientAccount
     * @return BaseForm
     */
    abstract public function getForm(ClientAccount $clientAccount);

}
