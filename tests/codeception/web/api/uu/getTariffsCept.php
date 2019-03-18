<?php

namespace tests\codeception\web\api\uu;

use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\Tariff;
use \_WebTester as _WebTester;
use tests\codeception\web\api\ApiExternalTest;
use yii\db\ActiveQuery;

class getTariffsCept extends ApiExternalTest
{
    protected $apiUrl = '/api/internal/uu/get-tariffs';
    protected $title = 'Get tariffs';

    /** @var Tariff */
    protected $tariff;

    /** @var ServiceType */
    protected $checkServiceType;
    /** @var Tariff */
    protected $checkTariff;

    /**
     * Запрос по id
     */
    public function getById()
    {
        if (!$this->tariff) {
            return;
        }

        $params = [
            'id' => $this->tariff->id,
        ];

        //$this->server->wantTo('External API Test');
        $this->server->wantTo(__FUNCTION__);
        $this->server->sendGET($this->apiUrl, $params);
        $this->server->dontSee('ERROR');
        $this->server->dontSee('Exception');
        $this->server->see('status');
        $this->server->seeResponseContainsJson(['status' => 'OK']);

        // custom checks
        if ($this->checkTariff) {
            $this->server->seeResponseContainsJson(['result' => [0 => ['id' => $this->checkTariff->id]]]);
        }
        if ($this->checkServiceType) {
            $this->server->seeResponseContainsJson(['result' => [0 => ['service_type' => ['id' => $this->checkServiceType->id]]]]);
        }
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()
            ->joinWith([
                'tariffPeriod.tariff trf' => function (ActiveQuery $query) {
                    $query->where('trf.service_type_id IS NOT NULL');
                },
            ])
            ->where('client_account_id IS NOT NULL')
            ->andWhere('prev_account_tariff_id IS NULL')
            ->orderBy(['id' => SORT_ASC])
            ->one();

        if ($accountTariff) {
            $this->tariff = $this->checkTariff = $accountTariff->tariffPeriod->tariff;
            $this->checkServiceType = $accountTariff->tariffPeriod->tariff->serviceType;
        }
    }
}

$context = new getTariffsCept(new _WebTester($scenario));
$context->getById();
