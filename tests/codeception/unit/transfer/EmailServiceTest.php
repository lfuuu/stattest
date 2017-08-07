<?php

namespace tests\codeception\unit\transfer;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\UsageEmails;
use app\models\UsageExtra;
use app\modules\transfer\components\services\Processor;
use yii\base\InvalidValueException;

class EmailServiceTest extends _BaseService
{

    /**
     * Test transfer between regular and universal accounts
     *
     * @return bool
     */
    public function testRegular2Universal()
    {
        try {
            parent::testRegular2Universal();
        } catch (InvalidValueException $e) {
            return true;
        }

        return false;
    }

    /**
     * Test transfer between two universal accounts
     *
     * @return bool
     */
    public function testUniversal2Universal()
    {
        try {
            parent::testUniversal2Universal();
        } catch (InvalidValueException $e) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    protected function getServiceTypeCode()
    {
        return Processor::SERVICE_EMAIL;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return UsageExtra
     * @throws ModelValidationException
     */
    protected function createRegularService(ClientAccount $clientAccount)
    {
        // Creating service (Can't use setAttributes method. Model has missing rules)
        $service = new UsageEmails;
        $service->actual_from = $this->getActivationDate();
        $service->actual_to = $this->getExpireDate();
        $service->client = $clientAccount->client;
        $service->local_part = 'email_service_test';
        $service->domain = 'mcn.ru';
        $service->password = '1234';

        if (!$service->save()) {
            throw new ModelValidationException($service);
        }

        return $service;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return bool
     */
    protected function createUniversalService(ClientAccount $clientAccount)
    {
        return false;
    }

    /**
     * @param null $service
     * @return int
     */
    protected function getTariffForUniversalService($service = null)
    {
        return 0;
    }

}