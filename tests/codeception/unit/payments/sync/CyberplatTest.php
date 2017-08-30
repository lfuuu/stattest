<?php

namespace tests\codeception\unit\payments\sync;


use app\classes\payments\cyberplat\CyberplatProcessor;
use app\classes\payments\cyberplat\exceptions\AnswerErrorStatus;
use app\classes\payments\cyberplat\exceptions\AnswerOk;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Payment;
use tests\codeception\unit\_TestCase;


class CyberPlatProcessorTest extends _TestCase
{
    public $receipt = "1009999999999";

    private $_transaction = null;

    function setUp()
    {
        parent::setUp();

        $this->_transaction = \Yii::$app->db->begintransaction();
        Payment::deleteAll();
    }

    function tearDown()
    {
        parent::tearDown();
        $this->_transaction->rollBack();
    }

    public function testAddPaymnet()
    {
        $account = ClientAccount::find()->orderBy(['id' => SORT_ASC])->one();

        $data = [
            'number' => $account->id,
            'amount' => '500.00',
            'type' => '1',
            'sign' => 'XXXXXXXXX',
            'receipt' => $this->receipt,
            'date' => '2017-03-06T03:59:21',
            'mes' => '',
            'additional' => ''
        ];

        $processor = new CyberplatProcessor();
        $processor->setNoCheckSign();
        $processor->setData($data);

        $processor->proccessRequest('payment');

        $code = $processor->getAnswerCode();
        $answer = $processor->getAnswerData();

        $this->assertEquals($code, 0);
        $this->assertNotEmpty($answer);

        $this->assertArraySubset(['authcode' => Payment::find()->one()->id], $answer);
    }


    public function testStatusNotFound()
    {
        $account = ClientAccount::find()->orderBy(['id' => SORT_ASC])->one();

        $data = [
            'number' => $account->id,
            'amount' => '500.00',
            'type' => '1',
            'sign' => 'XXXXXXXXX',
            'receipt' => $this->receipt,
            'date' => '2017-03-06T03:59:21',
            'mes' => '',
            'additional' => ''
        ];

        $processor = new CyberplatProcessor();
        $processor->setNoCheckSign();
        $processor->setData($data);

        $processor->proccessRequest('status');

        $code = $processor->getAnswerCode();
        $answer = $processor->getAnswerData();

        $this->assertEquals($code, (new AnswerErrorStatus())->code);
        $this->assertEmpty($answer);
    }


    public function testStatusFound()
    {
        $account = ClientAccount::find()->orderBy(['id' => SORT_ASC])->one();

        $payment = new Payment();
        $payment->payment_no = $this->receipt;
        $payment->sum = 500;
        $payment->client_id = $account->id;
        $payment->payment_date = (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $this->assertTrue($payment->save());
        $this->assertTrue($payment->refresh());


        $data = [
            'number' => $account->id,
            'amount' => '500.00',
            'type' => '1',
            'sign' => 'XXXXXXXXX',
            'receipt' => $this->receipt,
            'date' => '2017-03-06T03:59:21',
            'mes' => '',
            'additional' => ''
        ];

        $processor = new CyberplatProcessor();
        $processor->setNoCheckSign();
        $processor->setData($data);

        $processor->proccessRequest('status');

        $code = $processor->getAnswerCode();
        $answer = $processor->getAnswerData();

        $this->assertEquals($code, (new AnswerOk())->code);
        $this->assertArraySubset(['authcode' => $payment->id], $answer);
    }


    public function testAddAlreadyPaymnet()
    {
        $account = ClientAccount::find()->orderBy(['id' => SORT_ASC])->one();

        $payment = new Payment();
        $payment->payment_no = $this->receipt;
        $payment->sum = 500;
        $payment->client_id = $account->id;
        $payment->payment_date = (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $this->assertTrue($payment->save());
        $this->assertTrue($payment->refresh());

        $data = [
            'number' => $account->id,
            'amount' => '500.00',
            'type' => '1',
            'sign' => 'XXXXXXXXX',
            'receipt' => $this->receipt,
            'date' => '2017-03-06T03:59:21',
            'mes' => '',
            'additional' => ''
        ];

        $processor = new CyberplatProcessor();
        $processor->setNoCheckSign();
        $processor->setData($data);

        $processor->proccessRequest('payment');

        $code = $processor->getAnswerCode();
        $answer = $processor->getAnswerData();

        $this->assertEquals($code, 0);
        $this->assertNotEmpty($answer);

        $this->assertArraySubset(['authcode' => Payment::find()->one()->id], $answer);
    }

}