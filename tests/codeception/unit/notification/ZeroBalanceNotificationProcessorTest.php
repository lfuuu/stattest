<?php

namespace tests\codeception\unit\notification;

use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\LkClientSettings;
use app\models\LkNotificationLog;
use tests\codeception\unit\models\_ClientAccount;
use Yii;


class ZeroBalanceNotificationProcessorTest extends \yii\codeception\TestCase
{
    /** @var \yii\db\Transaction */
    private $transaction = null;

    /** @var \app\models\ClientAccount */
    private $account = null;

    private $event = '';

    private function init()
    {
        $this->event = ImportantEventsNames::IMPORTANT_EVENT_ZERO_BALANCE;

        $this->transaction = Yii::$app->getDb()->beginTransaction();

        $account = _ClientAccount::createOne();

        $c = new ClientContact;
        $c->client_id = $account->id;
        $c->type = 'email';
        $c->data = 'test' . $account->id . '@mcn.ru.loc';
        $c->setActiveAndOfficial();
        $c->user_id = 0;
        $c->save();


        $account = ClientAccount::findOne(['id' => $account->id]);
        $this->assertNotNull($account);

        $this->account = $account;
    }

    private function end()
    {
        $this->transaction->commit();
    }

    public function testNotSetBalanceGreatCredit()
    {
        $this->init();

        $mockObj = $this->getMock('\app\classes\notification\processors\ZeroBalanceNotificationProcessor', [
            'getValue',
            'getLimit',
            'createImportantEventSet',
            'oldSetupSendAndSaveLog',
            'oldUnsetSaveLog',
            'oldAddLogRaw'
        ]);

        $mockObj->expects($this->any())->method('getValue')->will($this->returnValue(1000));
        $mockObj->expects($this->any())->method('getLimit')->will($this->returnValue(0));
        $mockObj->expects($this->never())->method('oldSetupSendAndSaveLog')->willReturn(null);
        $mockObj->expects($this->never())->method('oldUnsetSaveLog')->willReturn(null);
        $mockObj->expects($this->never())->method('oldAddLogRaw')->will($this->returnValue(null));

        /** @var \app\classes\notification\processors\ZeroBalanceNotificationProcessor $mockObj */
        //$mockObj = new ZeroBalanceNotificationProcessor;
        $mockObj->compareAndNotificationClient($this->account);

        $this->end();
    }


    public function testNotSetBalanceLessCredit()
    {
        $this->init();

        $mockObj = $this->getMock('\app\classes\notification\processors\ZeroBalanceNotificationProcessor', [
            'getValue',
            'getLimit'
        ]);
        $mockObj->expects($this->any())->method('getValue')->will($this->returnValue(-1000));
        $mockObj->expects($this->any())->method('getLimit')->will($this->returnValue(0));


        /** @var \app\classes\notification\processors\ZeroBalanceNotificationProcessor $mockObj */
        //$mockObj = new ZeroBalanceNotificationProcessor;
        $mockObj->compareAndNotificationClient($this->account);

        $this->assertEquals($mockObj->getValue(), -1000);
        $this->assertEquals($mockObj->getLimit(), 0);

        $this->account->refresh();

        $this->assertNotNull($this->account->lkClientSettings);
        $this->assertEquals($this->account->lkClientSettings->is_zero_balance_sent, 1);

        $this->assertEquals($mockObj->getSetEvent(), $this->event);

        $event = ImportantEvents::findOne(['client_id' => $this->account->id, 'event' => $mockObj->getSetEvent()]);
        $this->assertNotNull($event);

        /** @var \app\models\LkNotificationLog $lkNoticeLog */
        $lkNoticeLog = LkNotificationLog::findOne([
            'client_id' => $this->account->id,
            'event' => $mockObj->getSetEvent(),
            'is_set' => 1
        ]);
        $this->assertNotNull($lkNoticeLog);
        $this->assertGreaterThan(0, $lkNoticeLog->contact_id);

        $this->assertNotNull(ClientContact::findOne([
            'client_id' => $this->account->id,
            'id' => $lkNoticeLog->contact_id
        ]));

        $this->end();
    }


    public function testIsSetBalanceGreatCredit()
    {
        $this->init();

        $this->assertNotNull($this->account);
        $this->assertTrue($this->account instanceof ClientAccount);

        LkClientSettings::saveState($this->account, $this->event, true);

        $this->assertNotNull($this->account->lkClientSettings);
        $this->assertEquals($this->account->lkClientSettings->is_zero_balance_sent, 1);


        $mockObj = $this->getMock('\app\classes\notification\processors\ZeroBalanceNotificationProcessor', [
            'getValue',
            'getLimit'
        ]);
        $mockObj->expects($this->any())->method('getValue')->will($this->returnValue(1000));
        $mockObj->expects($this->any())->method('getLimit')->will($this->returnValue(0));


        /** @var \app\classes\notification\processors\ZeroBalanceNotificationProcessor $mockObj */
        //$mockObj = new ZeroBalanceNotificationProcessor;
        $mockObj->compareAndNotificationClient($this->account);

        $this->assertEquals($mockObj->getValue(), 1000);
        $this->assertEquals($mockObj->getLimit(), 0);

        $this->account->refresh();

        $this->assertNotNull($this->account->lkClientSettings);
        $this->assertEquals($this->account->lkClientSettings->is_zero_balance_sent, 0);

        $this->assertEquals($mockObj->getSetEvent(), $this->event);

        /** @var \app\models\LkNotificationLog $lkNoticeLog */
        $lkNoticeLog = LkNotificationLog::findOne([
            'client_id' => $this->account->id,
            'event' => $mockObj->getSetEvent(),
            'is_set' => 0
        ]);
        $this->assertNotNull($lkNoticeLog);
        $this->assertEquals($lkNoticeLog->contact_id, 0);

        $this->end();
    }

}
