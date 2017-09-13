<?php

namespace tests\codeception\unit\usage\sync;


use app\classes\ActaulizerVoipNumbers;
use app\classes\Event;
use app\models\ClientAccount;
use app\models\EntryPoint;
use app\models\EventQueue;
use app\models\Number;
use app\modules\uu\models\Tariff;
use app\modules\uu\models\TariffOrganization;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffVoipCity;
use app\modules\uu\models\TariffVoipNdcType;
use app\tests\codeception\fixtures\NumberFixture;
use app\tests\codeception\fixtures\uu\TariffFixture;
use app\tests\codeception\fixtures\uu\TariffOrganizationFixture;
use app\tests\codeception\fixtures\uu\TariffPeriodFixture;
use app\tests\codeception\fixtures\uu\TariffVoipCityFixture;
use app\tests\codeception\fixtures\uu\TariffVoipNdcTypeFixture;
use tests\codeception\unit\_TestCase;
use tests\codeception\unit\models\_AccountTariff;
use tests\codeception\unit\models\_ClientAccount;
use tests\codeception\unit\models\_UsageVoip;

class Voip extends _TestCase
{
    private $_accountUsage = null;
    private $_accountUniversal = null;

    private $_transaction = null;

    public function setUp()
    {
        parent::setUp();

        $this->_transaction = \Yii::$app->db->beginTransaction();

        TariffPeriod::deleteAll();
        TariffVoipCity::deleteAll();
        TariffOrganization::deleteAll();
        TariffVoipNdcType::deleteAll();
        Tariff::deleteAll();

        (new TariffFixture())->load();
        (new TariffOrganizationFixture())->load();
        (new TariffVoipCityFixture())->load();
        (new TariffVoipNdcTypeFixture)->load();
        (new TariffPeriodFixture)->load();

        $this->_accountUsage = _ClientAccount::createOne(EntryPoint::RU1);
        $this->_accountUniversal = _ClientAccount::createOne(EntryPoint::RU5);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->_transaction->rollBack();
    }

    public function testVoipUsageAddAndDel()
    {
        $usage = _UsageVoip::createUsage(
            $this,
            $this->_accountUsage,
            _UsageVoip::getFreeNumber()
        );

        $this->_testSync($usage->E164, 'add', $this->_accountUsage);
        $usage->switchOff($this);
        $this->_testSync($usage->E164, 'del', $this->_accountUsage);
    }

    public function testVoipUniversalAddAndDel()
    {
        $accountTariff = _AccountTariff::createVoip(
            $this,
            $this->_accountUniversal->id,
            _UsageVoip::getFreeNumber()->number
        );

        $this->_testSync($accountTariff->voip_number, 'add', $this->_accountUniversal);
        $accountTariff->switchOff($this);
        $this->_testSync($accountTariff->voip_number, 'del', $this->_accountUniversal);
    }

    public function testVoipSyncTransferUsageToAccountTariff()
    {
        /** @var Number $number */
        $number = _UsageVoip::getFreeNumber();
        $usage = _UsageVoip::createUsage($this, $this->_accountUsage, $number);

        $actualizer = ActaulizerVoipNumbers::me();
        $actualizer->actualizeByNumber($number->number);

        $this->_testSync($number->number, 'add', $this->_accountUsage);

        $usage->switchOff($this);

        $accountTariff = _AccountTariff::createVoip($this, $this->_accountUniversal->id, $number->number);
        $accountTariff->prev_usage_id = $usage->id;

        if (!$accountTariff->validate()) {
            $this->failOnValidationModel($accountTariff);
        }
        $this->assertTrue($accountTariff->save());

        $this->_testSync($number->number, 'update', $this->_accountUsage, $this->_accountUniversal);
    }

    public function testVoipSyncTransferAccountTariffToUsage()
    {
        /** @var Number $number */
        $number = _UsageVoip::getFreeNumber();

        $accountTariff = _AccountTariff::createVoip($this, $this->_accountUniversal->id, $number->number);

        $actualizer = ActaulizerVoipNumbers::me();
        $actualizer->actualizeByNumber($number->number);

        $this->_testSync($number->number, 'add', $this->_accountUniversal);

        $accountTariff->switchOff($this);

        $usage = _UsageVoip::createUsage($this, $this->_accountUsage, $number);
        $usage->prev_usage_id = $accountTariff->id;
        if (!$usage->validate()) {
            $this->failOnValidationModel($usage);
        }
        $this->assertTrue($usage->save());

        $this->_testSync($number->number, 'update', $this->_accountUniversal, $this->_accountUsage);
    }

    public function testVoipSyncTransferUsageToAccountTariffWithError()
    {
        /** @var Number $number */
        $number = _UsageVoip::getFreeNumber();
        $usage = _UsageVoip::createUsage($this, $this->_accountUsage, $number);

        $actualizer = ActaulizerVoipNumbers::me();
        $actualizer->actualizeByNumber($number->number);

        $this->_testSync($number->number, 'add', $this->_accountUsage);

        $usage->switchOff($this);

        $accountTariff = _AccountTariff::createVoip($this, $this->_accountUniversal->id, $number->number);
        $accountTariff->prev_usage_id = $usage->id;

        $accountTariff->switchOff($this);

        $this->_testSync($number->number, 'del', $this->_accountUsage, null, \LogicException::class);
    }

    public function testVoipSyncTransferAccountTariffToUsageWithError()
    {
        /** @var Number $number */
        $number = _UsageVoip::getFreeNumber();

        $accountTariff = _AccountTariff::createVoip($this, $this->_accountUniversal->id, $number->number);

        ActaulizerVoipNumbers::me()->actualizeByNumber($number->number);

        $this->_testSync($number->number, 'add', $this->_accountUniversal);

        $accountTariff->switchOff($this);

        $usage = _UsageVoip::createUsage($this, $this->_accountUsage, $number);
        $usage->prev_usage_id = $accountTariff->id;
        $usage->switchOff($this);

        $this->_testSync($number->number, 'del', $this->_accountUniversal, null, \LogicException::class);
    }

    private function _cleanEventQueue()
    {
        EventQueue::deleteAll();
    }

    private function _testSync(
        $number,
        $actionEvent,
        ClientAccount $account1,
        ClientAccount $account2 = null,
        $syncException = null)
    {
        switch ($actionEvent) {
            case 'add':
                $subset = [
                    'client_id' => (int)$account1->id,
                    'did' => (string)$number
                ];
                $syncEvent = 'add_did';
                break;

            case 'del':
                $subset = [
                    'client_id' => (int)$account1->id,
                    'did' => (string)$number
                ];
                $syncEvent = 'disable_did';
                break;

            case 'update':
                $subset = [
                    'old_client_id' => (int)$account1->id,
                    'new_client_id' => (int)$account2->id,
                    'did' => (string)$number
                ];
                $syncEvent = 'edit_client_id';
                break;

            default:
                $this->fail('Неизвестное событие');
        }


        $this->_cleanEventQueue();

        $actualizer = ActaulizerVoipNumbers::me();
        $actualizer->actualizeByNumber($number);

        $event = EventQueue::find()->one();

        $this->assertNotNull($event);
        $this->assertInstanceOf(EventQueue::className(), $event);

        $this->assertEquals($event->event, Event::ATS3__SYNC);

        $eventData = json_decode($event->param, true);

        $this->assertNotEmpty($eventData);
        $this->assertTrue(is_array($eventData));

        $this->assertArrayHasKey('action', $eventData);
        $this->assertEquals($eventData['action'], $actionEvent);

        $this->assertArrayHasKey('client_id', $eventData);
        $this->assertEquals($eventData['client_id'], $account2 ? $account2->id : $account1->id);

        $this->assertArrayHasKey('number', $eventData);
        $this->assertEquals($eventData['number'], $number);

        // check sync
        $this->_cleanEventQueue();

        /** @var _ApiPhone $phoneApiTest */
        $phoneApiTest = _ApiPhone::me();
        $phoneApiTest->clearStack();
        $actualizer->setPhoneApi($phoneApiTest);
        try {
            $actualizer->sync($number);
        } catch(\Exception $e) {
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