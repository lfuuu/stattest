<?php

namespace tests\codeception\unit\notification;

use app\models\ClientAccount;
use app\models\ClientContact;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\LkClientSettings;
use app\models\LkNoticeSetting;
use app\models\LkNotificationLog;
use tests\codeception\unit\models\_ClientAccount;
use Yii;
use yii\db\Expression;


class MinDayLimitNotificationProcessorTest extends \yii\codeception\TestCase
{
    /** @var \yii\db\Transaction */
    private $transaction = null;

    /** @var \app\models\ClientAccount */
    private $account = null;

    private $event = '';

    private function init($isSet = false)
    {
        $this->event = ImportantEventsNames::IMPORTANT_EVENT_MIN_DAY_LIMIT;

        $this->transaction = Yii::$app->getDb()->beginTransaction();

        $account = _ClientAccount::createOne();

        $c = new ClientContact;
        $c->client_id = $account->id;
        $c->type = 'email';
        $c->data = 'test' . $account->id . '@mcn.ru.loc';
        $c->is_official = 1;
        $c->user_id = 0;
        $c->save();


        $account = ClientAccount::findOne(['id' => $account->id]);
        $this->assertNotNull($account);
        $this->assertTrue($account instanceof ClientAccount);

        $row = new LkNoticeSetting();
        $row->client_id = $account->id;
        $row->client_contact_id = $c->id;
        $row->min_day_limit = 1;
        $row->status = LkNoticeSetting::STATUS_WORK;
        $row->activate_code = '';
        $row->save();

        $row = new LkClientSettings();
        $row->client_id = $account->id;
        $row->{ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE} = LkClientSettings::DEFAULT_MIN_BALANCE;
        $row->{ImportantEventsNames::IMPORTANT_EVENT_MIN_DAY_LIMIT} = LkClientSettings::DEFAULT_MIN_DAY_LIMIT;

        $row->{$this->event . '_sent'} = new Expression('NOW()');
        $row->{'is_' . $this->event . '_sent'} = ($isSet ? 1 : 0);
        $row->save();
        $account->refresh();

        $this->account = $account;
    }

    private function end()
    {
        $this->transaction->commit();
    }

    public function testNotSetDaySumLessLimit()
    {
        $this->init(false);

        $mockObj = $this->getMock('\app\classes\notification\processors\MinDayLimitNotificationProcessor', [
            'getValue',
            'getLimit',
            'createImportantEventSet',
            'oldSetupSendAndSaveLog',
            'oldUnsetSaveLog',
            'oldAddLogRaw'
        ]);

        $mockObj->expects($this->any())->method('getValue')->will($this->returnValue(100));
        $mockObj->expects($this->any())->method('getLimit')->will($this->returnValue(1000));
        $mockObj->expects($this->never())->method('oldSetupSendAndSaveLog')->willReturn(null);
        $mockObj->expects($this->never())->method('oldUnsetSaveLog')->willReturn(null);
        $mockObj->expects($this->never())->method('oldAddLogRaw')->will($this->returnValue(null));

        /** @var \app\classes\notification\processors\MinDayLimitNotificationProcessor $mockObj */
        //$mockObj = new DayLimitNotificationProcessor;
        $mockObj->compareAndNotificationClient($this->account);

        $this->end();
    }


    public function testNotSetDaySumGreatLimit()
    {
        $this->init(false);

        $this->assertNotNull($this->account->lkClientSettings);
        $this->assertEquals($this->account->lkClientSettings->is_day_limit_sent, 0); //not set

        $mockObj = $this->getMock('\app\classes\notification\processors\MinDayLimitNotificationProcessor', [
            'getValue',
            'getLimit'
        ]);
        $mockObj->expects($this->any())->method('getValue')->will($this->returnValue(1200));
        $mockObj->expects($this->any())->method('getLimit')->will($this->returnValue(1000));


        /** @var \app\classes\notification\processors\MinDayLimitNotificationProcessor $mockObj */
        //$mockObj = new DayLimitNotificationProcessor;
        $mockObj->compareAndNotificationClient($this->account);

        $this->assertEquals($mockObj->getValue(), 1200);
        $this->assertEquals($mockObj->getLimit(), 1000);

        $this->account->refresh();

        $this->assertNotNull($this->account->lkClientSettings);
        $this->assertEquals($this->account->lkClientSettings->is_min_day_limit_sent, 1);

        $this->assertEquals($mockObj->getEnterEvent(), $this->event);

        /** @var \app\models\important_events\ImportantEvents $event */
        $event = ImportantEvents::findOne(['client_id' => $this->account->id, 'event' => $mockObj->getEnterEvent()]);
        $this->assertNotNull($event);
        $this->assertNotNull($event->id);

        if ($mockObj->isOldNotification()) {
            /** @var \app\models\LkNotificationLog $lkNoticeLog */
            $lkNoticeLog = LkNotificationLog::findOne([
                'client_id' => $this->account->id,
                'event' => $mockObj->getEnterEvent(),
                'is_set' => 1
            ]);
            $this->assertNotNull($lkNoticeLog);
            $this->assertGreaterThan(0, $lkNoticeLog->contact_id);

            /** @var \app\models\ClientContact $contact */
            $contact = ClientContact::findOne(['client_id' => $this->account->id, 'id' => $lkNoticeLog->contact_id]);
            $this->assertNotNull($contact);
            $this->assertNotNull($contact->id);
        }

        $this->end();
    }

    public function testSetDaySumGreatLimit()
    {
        $this->init(true);

        $this->assertNotNull($this->account->lkClientSettings);
        $this->assertEquals($this->account->lkClientSettings->is_min_day_limit_sent, 1); //set

        $mockObj = $this->getMock('\app\classes\notification\processors\MinDayLimitNotificationProcessor', [
            'getValue',
            'getLimit'
        ]);
        $mockObj->expects($this->any())->method('getValue')->will($this->returnValue(1300));
        $mockObj->expects($this->any())->method('getLimit')->will($this->returnValue(1000));


        /** @var \app\classes\notification\processors\MinDayLimitNotificationProcessor $mockObj */
        //$mockObj = new DayLimitNotificationProcessor;
        $mockObj->compareAndNotificationClient($this->account);

        $this->assertEquals($mockObj->getValue(), 1300);
        $this->assertEquals($mockObj->getLimit(), 1000);

        $this->account->refresh();

        $this->assertNotNull($this->account->lkClientSettings);
        $this->assertEquals($this->account->lkClientSettings->is_min_day_limit_sent, 1);

        $this->assertEquals($mockObj->getEnterEvent(), $this->event);

        $event = ImportantEvents::findOne(['client_id' => $this->account->id, 'event' => $mockObj->getEnterEvent()]);
        $this->assertNull($event);

        if ($mockObj->isOldNotification()) {
            /** @var \app\models\LkNotificationLog $lkNoticeLog */
            $lkNoticeLog = LkNotificationLog::findOne([
                'client_id' => $this->account->id,
                'event' => $mockObj->getEnterEvent(),
                'is_set' => 1
            ]);
            $this->assertNull($lkNoticeLog);
        }

        $this->end();
    }

    public function testSetDaySumLessLimit()
    {
        $this->init(true);


        $this->assertNotNull($this->account->lkClientSettings);
        $this->assertEquals($this->account->lkClientSettings->is_min_day_limit_sent, 1);


        $mockObj = $this->getMock('\app\classes\notification\processors\MinDayLimitNotificationProcessor', [
            'getValue',
            'getLimit'
        ]);
        $mockObj->expects($this->any())->method('getValue')->will($this->returnValue(300));
        $mockObj->expects($this->any())->method('getLimit')->will($this->returnValue(1000));


        /** @var \app\classes\notification\processors\MinDayLimitNotificationProcessor $mockObj */
        //$mockObj = new DayLimitNotificationProcessor;
        $mockObj->compareAndNotificationClient($this->account);

        $this->assertEquals($mockObj->getValue(), 300);
        $this->assertEquals($mockObj->getLimit(), 1000);

        $this->account->refresh();

        $this->assertNotNull($this->account->lkClientSettings);
        $this->assertEquals($this->account->lkClientSettings->is_min_day_limit_sent, 0);

        $this->assertEquals($mockObj->getEnterEvent(), $this->event);

        /** @var \app\models\important_events\ImportantEvents $event */
        $event = ImportantEvents::findOne(['client_id' => $this->account->id, 'event' => 'unset_' . $mockObj->getEnterEvent()]);
        $this->assertNotNull($event);

        /** @var array $eventProperty */
        $eventProperty = $event->properties;
        $this->assertNotNull($eventProperty);
        $isFind = false;
        $findObj = null;
        foreach ($eventProperty as $propertyName => $propertyValue) {
            if ($propertyName == 'is_set') {
                $findObj = $propertyName;
                $isFind = true;
                break;
            }
        }

        $this->assertTrue($isFind);
        $this->assertNotNull($findObj);
        $this->assertEquals($findObj->value, 0);

        if ($mockObj->isOldNotification()) {
            /** @var \app\models\LkNotificationLog $lkNoticeLog */
            $lkNoticeLog = LkNotificationLog::findOne([
                'client_id' => $this->account->id,
                'event' => $mockObj->getEnterEvent(),
                'is_set' => 0
            ]);
            $this->assertNotNull($lkNoticeLog);
            $this->assertEquals($lkNoticeLog->contact_id, 0);
        }

        $this->end();
    }

}
