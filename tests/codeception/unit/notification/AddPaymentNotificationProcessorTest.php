<?php

namespace tests\codeception\unit\notification;

use app\classes\notification\processors\AddPaymentNotificationProcessor;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientContact;
use app\models\Currency;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;
use app\models\LkNoticeSetting;
use app\models\LkNotificationLog;
use app\models\Payment;
use tests\codeception\unit\models\_ClientAccount;
use Yii;


class AddPaymentNotificationProcessorTest extends \yii\codeception\TestCase
{
    /** @var \yii\db\Transaction */
    private $transaction = null;

    /** @var \app\models\ClientAccount */
    private $account = null;

    /** @var \app\models\Payment */
    private $payment = null;

    private $event = '';

    private function init()
    {

        $this->event = ImportantEventsNames::IMPORTANT_EVENT_ADD_PAY_NOTIF;

        $this->transaction = Yii::$app->getDb()->beginTransaction();

        $account = _ClientAccount::createOne();

        $c = new ClientContact;
        $c->client_id = $account->id;
        $c->type = 'email';
        $c->data = 'test' . $account->id . '@mcn.ru';
        $c->is_official = 1;
        $c->user_id = 0;
        if (!$c->save()) {
            $this->fail(implode('', $c->getFirstErrors()));
        }

        $row = new LkNoticeSetting();
        $row->client_id = $account->id;
        $row->client_contact_id = $c->id;
        $row->add_pay_notif = 1;
        $row->status = LkNoticeSetting::STATUS_WORK;
        $row->activate_code = '';
        if (!$row->save()) {
            $this->fail(implode('', $row->getFirstErrors()));
        }

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $payment = new Payment();
        $payment->client_id = $account->id;
        $payment->sum = 120;
        $payment->currency = Currency::RUB;
        $payment->payment_date = $now->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $payment->comment = '';
        if (!$payment->save()) {
            $this->fail(implode('', $payment->getFirstErrors()));
        }

        $account->refresh();

        $this->account = $account;
        $this->payment = $payment;
    }

    private function end()
    {
        $this->transaction->commit();
    }

    public function testAddPayNotification()
    {
        $this->init();

        /** @var \app\classes\notification\processors\AddPaymentNotificationProcessor $mockObj */
        $processor = (new AddPaymentNotificationProcessor($this->account->id, $this->payment->id));
        $processor->makeSingleClientNotification();

        $this->assertEquals($processor->getValue(), $this->payment->sum);
        $this->assertEquals($processor->getLimit(), 0);
        $this->assertEquals($processor->getEnterEvent(), $this->event);


        /** @var \app\models\important_events\ImportantEvents $event */
        $event = ImportantEvents::findOne(['client_id' => $this->account->id, 'event' => $processor->getEnterEvent()]);
        $this->assertNotNull($event);
        $this->assertNotNull($event->id);

        /** @var \app\models\LkNotificationLog $lkNoticeLog */
        $lkNoticeLog = LkNotificationLog::findOne([
            'client_id' => $this->account->id,
            'event' => $processor->getEnterEvent(),
            'is_set' => 1
        ]);
        $this->assertNotNull($lkNoticeLog);
        $this->assertGreaterThan(0, $lkNoticeLog->contact_id);

        /** @var \app\models\ClientContact $contact */
        $contact = ClientContact::findOne(['client_id' => $this->account->id, 'id' => $lkNoticeLog->contact_id]);
        $this->assertNotNull($contact);
        $this->assertNotNull($contact->id);

        $this->end();
    }
}
