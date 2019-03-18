<?php

namespace tests\codeception\web\api\uu;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Tariff;
use \_WebTester as _WebTester;
use tests\codeception\web\api\ApiExternalTest;

class getAccountTariffsWithPackagesCept extends ApiExternalTest
{
    protected $apiUrl = '/api/internal/uu/get-account-tariffs-with-packages';
    protected $title = 'Get account tariffs with Packages';

    protected $clientAccount;
    protected $serviceType;

    /** @var AccountTariff */
    protected $checkAccountTariff;
    /** @var Tariff */
    protected $checkTariff;

    /**
     * Запрос по 2м параметрам: service_type_id, client_account_id
     */
    public function getByServiceTypeIdAndClientId()
    {
        if (!$this->clientAccount || !$this->serviceType) {
            return;
        }

        $params = [
            'service_type_id'   => $this->serviceType->id,
            'client_account_id' => $this->clientAccount->id,
        ];

        //$this->server->wantTo('External API Test');
        $this->server->wantTo(__FUNCTION__);
        $this->server->sendGET($this->apiUrl, $params);
        $this->server->dontSee('ERROR');
        $this->server->dontSee('Exception');
        $this->server->see('status');
        $this->server->seeResponseContainsJson(['status' => 'OK']);

        // custom checks
        if ($this->checkAccountTariff) {
            $this->server->seeResponseContainsJson(['result' => [0 => ['id' => $this->checkAccountTariff->id]]]);
        }
        if ($this->checkTariff) {
            $this->server->seeResponseContainsJson(['result' => [0 => ['tariff' => ['id' => $this->checkTariff->id]]]]);
        }
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()
            ->where('client_account_id IS NOT NULL')
            ->andWhere('service_type_id IS NOT NULL')
            ->andWhere('prev_account_tariff_id IS NULL')
            ->orderBy(['id' => SORT_ASC])
            ->one();

        if ($accountTariff) {
            $this->clientAccount = $accountTariff->clientAccount;
            $this->serviceType = $accountTariff->serviceType;

            $this->checkAccountTariff = $accountTariff;
            $this->checkTariff = $accountTariff->tariffPeriod->tariff;
        }
    }
}

$context = new getAccountTariffsWithPackagesCept(new _WebTester($scenario));
$context->getByServiceTypeIdAndClientId();
