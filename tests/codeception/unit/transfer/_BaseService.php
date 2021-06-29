<?php

namespace tests\codeception\unit\transfer;

use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\DidGroup;
use app\models\LogTarif;
use app\models\TariffVoipPackage;
use app\models\usages\UsageInterface;
use app\modules\nnp\models\NdcType;
use app\modules\transfer\components\services\PreProcessor;
use app\modules\transfer\components\services\Processor;
use app\modules\transfer\components\services\regular\RegularTransfer;
use app\modules\transfer\components\services\universal\UniversalTransfer;
use app\modules\transfer\forms\services\BaseForm;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\ServiceType;
use app\modules\uu\models\TariffPeriod;
use app\tests\codeception\fixtures\DestinationFixture;
use app\tests\codeception\fixtures\EntryPointFixture;
use app\tests\codeception\fixtures\NumberFixture;
use app\tests\codeception\fixtures\TariffVoipPackageFixture;
use DateTime;
use DateTimeZone;
use tests\codeception\unit\models\_ClientAccount;
use tests\codeception\unit\models\UbillerTest;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;
use yii\db\ActiveRecord;

abstract class _BaseService extends \yii\codeception\TestCase
{

    /** @var ClientAccount */
    public $regularClientAccountFirst;
    /** @var ClientAccount */
    public $regularClientAccountSecond;

    /** @var ClientAccount */
    public $universalClientAccountFirst;
    /** @var ClientAccount */
    public $universalClientAccountSecond;

    /** @var string */
    public $possibleDate;

    /** @var bool */
    protected $isRegularLogTariff;

    /**
     * @var string
     */
    protected $entryPointCode = 'RU5';

    /**
     * @return string
     */
    abstract protected function getServiceTypeCode();

    /**
     * @param ClientAccount $clientAccount
     * @return UsageInterface
     */
    abstract protected function createRegularService(ClientAccount $clientAccount);

    /**
     * @param ClientAccount $clientAccount
     * @return AccountTariff
     */
    abstract protected function createUniversalService(ClientAccount $clientAccount);

    /**
     * @param UsageInterface|null $service
     * @return int
     */
    abstract protected function getTariffForUniversalService($service = null);

    /**
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected function setUp()
    {
        parent::setUp();

        $this->unload();
        $this->load();
    }

    /**
     * Load fixtures
     *
     * @throws ModelValidationException
     * @throws \Exception
     * @throws \Throwable
     */
    protected function load()
    {
        // Loading fixtures regular fixtures
        (new DestinationFixture)->load();
        (new EntryPointFixture)->load();
        (new NumberFixture)->load();
        (new TariffVoipPackageFixture)->load();

        // Loading fixtures universal fixtures
        UbillerTest::loadUu();

        // Create regular client accounts
        $this->regularClientAccountFirst = $this->createClientAccount();
        $this->regularClientAccountSecond = $this->createClientAccount();

        // Create universal client accounts
        $this->universalClientAccountFirst = $this->createClientAccount($this->entryPointCode, 10000);
        $this->universalClientAccountSecond = $this->createClientAccount($this->entryPointCode, 10000);

        // Setting possible date for transfer
        $this->possibleDate = (new DateTime(BaseForm::NEAREST_POSSIBLE_DATE, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
            ->format(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * Unload fixtures
     */
    protected function unload()
    {
        // Unload regular data
        TariffVoipPackage::deleteAll();

        // Unload universal data
        UbillerTest::unloadUu();

        (new EntryPointFixture)->unload();
        (new NumberFixture)->unload();
        (new DestinationFixture)->unload();
    }

    /**
     * Test transfer between two regular accounts
     *
     * @throws ModelValidationException
     * @throws InvalidCallException
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function testRegular2Regular()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $sourceService = $this->createRegularService($this->regularClientAccountFirst);
        $processor = new RegularTransfer;
        $preProcessor = (new PreProcessor)
            ->setProcessor($processor)
            ->setServiceType($this->getServiceTypeCode())
            ->setService($processor->getHandler($this->getServiceTypeCode()), $sourceService->id)
            ->setProcessedFromDate($this->possibleDate)
            ->setSourceClientAccount($sourceService->clientAccount->id)
            ->setTargetClientAccount($this->regularClientAccountSecond->id);

        $transferResult = $this->runProcessor($processor, $preProcessor);

        /** @var UsageInterface $sourceService */
        $sourceService = $transferResult->sourceServiceHandler->getService();
        /** @var UsageInterface $resultService */
        $resultService = $transferResult->targetServiceHandler->getService();

        $this->assertEquals(
            (new DateTime($sourceService->actual_to))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            (new DateTime($resultService->actual_from))
                ->modify('-1 day')
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            'Actual working date is equals'
        );

        $this->assertEquals($sourceService->id, $resultService->prev_usage_id, 'Source service marked as transferred correctly');
        $this->assertEquals($sourceService->next_usage_id, $resultService->id, 'Result service marked as transferred correctly');

        if ($this->isRegularLogTariff) {
            $this->assertNotNull(
                $resultService->getLogTariff(UsageInterface::MIDDLE_DATE),
                'Result service has correct tariff'
            );
        }

        $transaction->rollBack();
    }

    /**
     * Test transfer between regular and universal accounts
     *
     * @throws ModelValidationException
     * @throws InvalidCallException
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws \yii\db\Exception
     * @throws \Exception
     *
     */
    public function testRegular2Universal()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $sourceService = $this->createRegularService($this->regularClientAccountFirst);
        $processor = new RegularTransfer;

        // Get universal tariff ID
        $tariffId = $this->getTariffForUniversalService($sourceService);

        $preProcessor = (new PreProcessor)
            ->setProcessor($processor)
            ->setServiceType($this->getServiceTypeCode())
            ->setService($processor->getHandler($this->getServiceTypeCode()), $sourceService->id)
            ->setProcessedFromDate($this->possibleDate)
            ->setSourceClientAccount($sourceService->clientAccount->id)
            ->setTargetClientAccount($this->universalClientAccountFirst->id)
            ->setTariff($tariffId);

        $transferResult = $this->runProcessor($processor, $preProcessor);

        $sourceService = $transferResult->sourceServiceHandler->getService();
        $resultService = $transferResult->targetServiceHandler->getService();

        $accountTariffLog = array_shift($resultService->accountTariffLogs);

        $this->assertEquals(
            (new DateTime($sourceService->actual_to))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            (new DateTime($accountTariffLog->actual_from_utc))
                ->format(DateTimeZoneHelper::DATE_FORMAT),
            'Actual working date is equals'
        );

        $transaction->rollBack();
    }

    /**
     * Test transfer between two universal accounts
     *
     * @throws ModelValidationException
     * @throws InvalidCallException
     * @throws InvalidParamException
     * @throws InvalidValueException
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function testUniversal2Universal()
    {
        $transaction = Yii::$app->db->beginTransaction();

        $sourceService = $this->createUniversalService($this->universalClientAccountFirst);
        $processor = new UniversalTransfer;

        $preProcessor = (new PreProcessor)
            ->setProcessor($processor)
            ->setServiceType($this->getServiceTypeCode())
            ->setService($processor->getHandler($this->getServiceTypeCode()), $sourceService->id)
            ->setProcessedFromDate($this->possibleDate)
            ->setSourceClientAccount($sourceService->clientAccount->id)
            ->setTargetClientAccount($this->universalClientAccountSecond->id)
            ->setTariff($sourceService->tariff_period_id);

        $transferResult = $this->runProcessor($processor, $preProcessor);

        $sourceService = $transferResult->sourceServiceHandler->getService();
        $resultService = $transferResult->targetServiceHandler->getService();

        $sourceAccountTariffLog = array_shift($sourceService->accountTariffLogs);
        $resultAccountTariffLog = array_shift($resultService->accountTariffLogs);

        $this->assertEquals($resultService->prev_usage_id, $sourceService->id, 'Result service marked as transferred correctly');
        $this->assertNull($sourceAccountTariffLog->tariff_period_id, 'Source service tariff is closed');
        $this->assertNotNull($resultAccountTariffLog->tariff_period_id, 'Result service tariff is opened');

        $transaction->rollBack();
    }

    /**
     * @param int $serviceTypeId
     * @param int|null $cityId
     * @param int $didGroup
     * @param int $ndcTypeId
     * @return int
     */
    protected function getUniversalTariff($serviceTypeId, $cityId = null, $didGroup = null, $ndcTypeId = NdcType::ID_GEOGRAPHIC)
    {
        $universalClientAccountFirst = $this->universalClientAccountFirst;
        $statusId = null;

        if ($serviceTypeId === ServiceType::ID_VOIP) {
            // Get service DID group
            $didGroup = DidGroup::findOne(['id' => $didGroup]);
            // Get price level
            $priceLevel = $universalClientAccountFirst ?
                $universalClientAccountFirst->price_level :
                ClientAccount::DEFAULT_PRICE_LEVEL;
            // Get tariff status
            $statusId = $didGroup->getTariffStatusMain($priceLevel);
        }

        // Get tariffs
        $possibleTariffs = TariffPeriod::getList(
            $defaultTariffPeriodId,
            $serviceTypeId,
            $universalClientAccountFirst->currency,
            $universalClientAccountFirst->country_id,
            $voipCountryIdTmp = null,
            $cityId,
            $isWithEmpty = false,
            $isWithNullAndNotNull = false,
            $statusId,
            $universalClientAccountFirst->is_postpaid,
            $universalClientAccountFirst->is_voip_with_tax,
            $universalClientAccountFirst->contract->organization_id,
            $ndcTypeId
        );

        if (!count($possibleTariffs)) {
            $this->fail('Available universal tariff not found');
        }

        return key(array_pop($possibleTariffs));
    }

    /**
     * @param string $date
     * @return string
     * @throws \Exception
     */
    protected function getActivationDate($date = '-1 week')
    {
        return (new DateTime($date, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
            ->format(DateTimeZoneHelper::DATE_FORMAT);
    }

    /**
     * @return string
     */
    protected function getExpireDate()
    {
        return UsageInterface::MAX_POSSIBLE_DATE;
    }

    /**
     * @param bool|false $isUniversal
     * @param null $credit
     * @return ClientAccount
     * @throws ModelValidationException
     * @throws \Exception
     */
    protected function createClientAccount($isUniversal = false, $credit = null)
    {
        $clientAccount = _ClientAccount::createOne($isUniversal ? $this->entryPointCode : null);
        $clientAccount->is_voip_with_tax = 1;

        // Set credit value for universal services
        if ($credit !== null) {
            $clientAccount->credit = 10000;
            if (!$clientAccount->save()) {
                throw new ModelValidationException($clientAccount);
            }
        }

        $this->assertNotNull($clientAccount, 'ClientAccount is object');
        $this->assertEquals(
            $isUniversal ? ClientAccount::VERSION_BILLER_UNIVERSAL : ClientAccount::VERSION_BILLER_USAGE,
            $clientAccount->account_version,
            'ClientAccount got valid account version'
        );

        return $clientAccount;
    }

    /**
     * @param ClientAccount $clientAccount
     * @param int $serviceTypeId
     * @param int $tariffId
     * @param array $attributes
     * @return AccountTariff
     * @throws ModelValidationException
     */
    protected function createUniversalServiceSimple(ClientAccount $clientAccount, $serviceTypeId, $tariffId, $attributes = [])
    {
        // Create service
        $accountTariff = new AccountTariff;
        $accountTariff->scenario = 'serviceTransfer';
        $accountTariff->setAttributes([
                'client_account_id' => $clientAccount->id,
                'service_type_id' => $serviceTypeId,
                'tariff_period_id' => $tariffId,
            ] + $attributes, false);

        if (!$accountTariff->save()) {
            throw new ModelValidationException($accountTariff);
        }

        $this->setUniversalTariff($accountTariff, $tariffId);

        return $accountTariff;
    }

    /**
     * @param UsageInterface|ActiveRecord $service
     * @param $tariffId
     * @throws ModelValidationException
     */
    protected function setRegularTariff(UsageInterface $service, $tariffId)
    {
        // Create tariff
        $logTariff = new LogTarif;
        $logTariff->service = $service::tableName();
        $logTariff->id_service = $service->id;
        $logTariff->id_tarif = $tariffId;
        $logTariff->date_activation = $service->actual_from;

        if (!$logTariff->save()) {
            throw new ModelValidationException($logTariff);
        }

        $this->isRegularLogTariff = true;
    }

    /**
     * @param AccountTariff $accountTariff
     * @param $tariffId
     * @throws ModelValidationException
     */
    protected function setUniversalTariff(AccountTariff $accountTariff, $tariffId)
    {
        // Create account tariff log
        $accountTariffLog = new AccountTariffLog;
        $accountTariffLog->setAttributes([
            'account_tariff_id' => $accountTariff->primaryKey,
            'tariff_period_id' => $tariffId,
            'actual_from' => $this->getActivationDate('now'),
        ]);

        if (!$accountTariffLog->save()) {
            throw new ModelValidationException($accountTariffLog);
        }
    }

    /**
     * @param Processor $processor
     * @param PreProcessor $preProcessor
     * @return PreProcessor|null
     */
    protected function runProcessor(Processor $processor, PreProcessor $preProcessor)
    {
        /** @var PreProcessor|null $transferResult */
        $transferResult = null;

        try {
            $transferResult = $processor->run($preProcessor);
        } catch (ModelValidationException $e) {
            $model = $e->getModel();
            $this->fail(get_class($model) . ' (' . $e->getLine() . '): ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->fail($e->getFile() . '(' . $e->getLine() . '): ' . $e->getMessage());
        }

        $this->assertNotNull($transferResult->targetServiceHandler->getService(), 'See result service after transfer');

        return $transferResult;
    }

}