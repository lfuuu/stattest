<?php

namespace tests\codeception\unit\usage\sync;


use app\models\Bill;
use app\models\ClientAccount;
use app\models\EntryPoint;
use app\models\EventQueue;
use app\models\TariffVirtpbx;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\AccountTariffLog;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffCountry;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffResource;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipCountry;
use app\modules\uu\models\TariffVoipNdcType;
use app\tests\codeception\fixtures\uu\AccountTariffFixture;
use app\tests\codeception\fixtures\uu\AccountTariffLogFixture;
use app\tests\codeception\fixtures\uu\AccountTariffResourceLogFixture;
use app\tests\codeception\fixtures\uu\TariffCountryFixture;
use app\tests\codeception\fixtures\uu\TariffFixture;
use app\tests\codeception\fixtures\uu\TariffOrganizationFixture;
use app\tests\codeception\fixtures\uu\TariffPeriodFixture;
use app\tests\codeception\fixtures\uu\TariffResourceFixture;
use app\tests\codeception\fixtures\uu\TariffVoipCityFixture;
use app\tests\codeception\fixtures\uu\TariffVoipCountryFixture;
use app\tests\codeception\fixtures\uu\TariffVoipNdcTypeFixture;
use tests\codeception\unit\_TestCase;
use tests\codeception\unit\models\_AccountTariff;
use tests\codeception\unit\models\_ClientAccount;
use tests\codeception\unit\models\_UsageVirtpbx;
use tests\codeception\unit\models\UbillerTest;

class Vpbx extends _TestCase
{
    private $_accountUsage = null;
    private $_accountUniversal = null;

    private $_transaction = null;

    /**
     * @throws \Throwable
     */
    public function setUp()
    {
        parent::setUp();

        $this->_transaction = \Yii::$app->db->beginTransaction();

        UbillerTest::unloadUu();
        UbillerTest::loadUu();

        $this->_accountUsage = _ClientAccount::createOne(EntryPoint::RU1);
        $this->_accountUniversal = _ClientAccount::createOne(EntryPoint::RU5);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->_transaction->rollBack();
    }

    public function testVpbxUsageAddAndDel()
    {
        $usage = _UsageVirtpbx::createUsage($this, $this->_accountUsage);

        $this->_testSync($usage->id, 'add', $this->_accountUsage);
        $usage->switchOff($this);
        $this->_testSync($usage->id, 'del', $this->_accountUsage);
    }

    public function testVpbxUniversalAddAndDel()
    {
        $accountTariff = _AccountTariff::createVpbx(
            $this,
            $this->_accountUniversal->id
        );

        $this->_testSync($accountTariff->id, 'add', $this->_accountUniversal);
        $accountTariff->switchOff($this);
        $this->_testSync($accountTariff->id, 'del', $this->_accountUniversal);
    }

    public function testVpbxUsageChangeTariff()
    {
        $usage = _UsageVirtpbx::createUsage($this, $this->_accountUsage);

        $this->_testSync($usage->id, 'add', $this->_accountUsage);

        $logTarif = $usage->logTariffDirect;
        $logTarif->id_tarif = TariffVirtpbx::PUBLIC_START_TARIFF_ID;
        $this->assertTrue($logTarif->save());

        $this->_testSync($usage->id, 'changed_data', $this->_accountUsage);
    }

    public function testVpbxAccountTariffChangeTariff()
    {
        $accountTariff = _AccountTariff::createVpbx($this, $this->_accountUniversal->id);

        $this->_testSync($accountTariff->id, 'add', $this->_accountUniversal);

        $accountTariff->tariff_period_id = TariffPeriod::START_VPBX_ID;
        $this->assertTrue($accountTariff->save());

        $this->_testSync($accountTariff->id, 'changed_data', $this->_accountUniversal);
    }

    public function testVpbxSyncTransferUsageToAccountTariff()
    {
        $usage = _UsageVirtpbx::createUsage($this, $this->_accountUsage);

        $this->_testSync($usage->id, 'add', $this->_accountUsage);

        $usage->switchOff($this);

        $accountTariff = _AccountTariff::createVpbx($this, $this->_accountUniversal->id);
        $accountTariff->prev_usage_id = $usage->id;
        $accountTariff->validateAndSave($this);

        $this->_testSync($accountTariff->id, 'changed_client', $this->_accountUsage, $this->_accountUniversal);
    }

    public function testVpbxSyncTransferAccountTariffToUsage()
    {
        $accountTariff = _AccountTariff::createVpbx($this, $this->_accountUniversal->id);

        $this->_testSync($accountTariff->id, 'add', $this->_accountUniversal);

        $accountTariff->switchOff($this);

        $usage = _UsageVirtpbx::createUsage($this, $this->_accountUsage);
        $usage->prev_usage_id = $accountTariff->id;
        if (!$usage->validate()) {
            $this->failOnValidationModel($usage);
        }
        $this->assertTrue($usage->save());

        $this->_testSync($usage->id, 'changed_client', $this->_accountUniversal, $this->_accountUsage);
    }

    public function testVpbxSyncTransferUsageToAccountTariffNotStarted()
    {
        $usage = _UsageVirtpbx::createUsage($this, $this->_accountUsage);

        $this->_testSync($usage->id, 'add', $this->_accountUsage);

        $usage->switchOff($this);

        $accountTariff = _AccountTariff::createVpbx($this, $this->_accountUniversal->id);
        $accountTariff->prev_usage_id = $usage->id;

        $accountTariff->switchOff($this);

        $this->_testSync($usage->id, 'del', $this->_accountUsage, null, \LogicException::class);
    }

    public function testVpbxSyncTransferAccountTariffToUsageNotStarted()
    {
        $accountTariff = _AccountTariff::createVpbx($this, $this->_accountUniversal->id);

        $this->_testSync($accountTariff->id, 'add', $this->_accountUniversal);

        $accountTariff->switchOff($this);

        $usage = _UsageVirtpbx::createUsage($this, $this->_accountUsage);
        $usage->prev_usage_id = $accountTariff->id;
        $usage->switchOff($this);

        $this->_testSync($accountTariff->id, 'del', $this->_accountUniversal, null, \LogicException::class);
    }

    public function testVpbxSyncTransferUsageToAccountTariffAllStarted()
    {
        $usage = _UsageVirtpbx::createUsage($this, $this->_accountUsage);

        $this->_testSync($usage->id, 'add', $this->_accountUsage);

        $accountTariff = _AccountTariff::createVpbx($this, $this->_accountUniversal->id);
        $accountTariff->prev_usage_id = $usage->id;
        $accountTariff->validateAndSave($this);

        $this->_testSync($accountTariff->id, 'add', $this->_accountUsage, $this->_accountUniversal, \LogicException::class);
    }

    public function testVpbxSyncTransferAccountTariffToUsageAllStarted()
    {
        $accountTariff = _AccountTariff::createVpbx($this, $this->_accountUniversal->id);

        $this->_testSync($accountTariff->id, 'add', $this->_accountUniversal);

        $usage = _UsageVirtpbx::createUsage($this, $this->_accountUsage);
        $usage->prev_usage_id = $usage->id;
        $usage->validateAndSave($this);

        $this->_testSync($usage->id, 'add', $this->_accountUsage, null, \LogicException::class);
    }

    private function _cleanEventQueue()
    {
        EventQueue::deleteAll();
    }

    private function _testSync(
        $usageId,
        $actionEvent,
        ClientAccount $account1 = null,
        ClientAccount $account2 = null,
        $syncException = null
    ) {
        switch ($actionEvent) {
            case 'add':
                $subset = [
                    'client_id' => (int)$account1->id,
                    'stat_product_id' => (int)$usageId
                ];
                $syncEvent = 'create';
                break;

            case 'del':
                $subset = [
                    'account_id' => (int)$account1->id,
                    'stat_product_id' => (int)$usageId
                ];
                $syncEvent = 'archive_vpbx';
                break;

            case 'changed_client':
                $subset = [
                    'from_account_id' => (int)$account1->id,
                    'to_account_id' => (int)$account2->id,
                ];
                $syncEvent = 'transfer_vpbx_only';
                break;

            case 'changed_data':
                $subset = [
                    'stat_product_id' => $usageId,
                ];
                $syncEvent = 'update';
                break;

            default:
                $this->fail('Неизвестное событие');
        }

        $this->_cleanEventQueue();

        \VirtPbx3::check($usageId);

        $event = EventQueue::find()->one();

        $this->assertNotNull($event);
        $this->assertInstanceOf(EventQueue::class, $event);

        $this->assertEquals($event->event, EventQueue::SYNC__VIRTPBX3);

        $eventData = json_decode($event->param, true);

        $this->assertNotEmpty($eventData);
        $this->assertTrue(is_array($eventData));

        $this->assertArrayHasKey('action', $eventData);
        $this->assertEquals($eventData['action'], $actionEvent);

        $this->assertArrayHasKey('client_id', $eventData);

        $this->assertArrayHasKey('usage_id', $eventData);
        $this->assertEquals($eventData['usage_id'], $usageId);

        // check sync
        $this->_cleanEventQueue();

        /** @var _ApiPhone $phoneApiTest */
        $phoneApiTest = _ApiVpbx::me();
        $phoneApiTest->clearStack();

        \VirtPbx3::setApi($phoneApiTest);

        try {
            \VirtPbx3::sync($usageId);
        } catch (\Exception $e) {
            if ($syncException) {
                $this->assertInstanceOf($syncException, $e);
                return;
            } else {
                throw $e;
            }
        }

        if ($syncException) {
            $this->fail("Exception '{$syncException}' not catched");
        }

        $stack = $phoneApiTest->getCallStack();
        $this->assertNotEmpty($stack);
        $this->assertTrue(is_array($stack));
        $this->assertEquals(count($stack), 1);

        $row = reset($stack);

        $this->assertTrue(is_array($row));
        $this->assertArrayHasKey('action', $row);
        $this->assertArrayHasKey('data', $row);

        $this->assertEquals($syncEvent, $row['action']);
        $data = $row['data'];

        $this->assertArraySubset($subset, $data);
    }
}