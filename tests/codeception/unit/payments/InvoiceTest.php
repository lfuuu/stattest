<?php

namespace tests\codeception\unit\payments;


use app\classes\payments\cyberplat\CyberplatProcessor;
use app\classes\payments\cyberplat\exceptions\AnswerErrorStatus;
use app\classes\payments\cyberplat\exceptions\AnswerOk;
use app\forms\client\ClientCreateExternalForm;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\Invoice;
use app\models\Organization;
use app\models\Payment;
use tests\codeception\unit\_TestCase;
use tests\codeception\unit\models\_ClientAccount;
use yii\base\InvalidCallException;
use yii\db\ActiveRecord;


class InvoiceTest extends _TestCase
{
    private $_transaction = null;

    /** @var ClientAccount */
    public static $account = null;

    function setUp()
    {
        parent::setUp();

        $this->_transaction = \Yii::$app->db->begintransaction();
        Invoice::deleteAll();
    }

    function tearDown()
    {
        parent::tearDown();
        $this->_transaction->rollBack();
    }

    public function makeClient()
    {
        $clientForm = new ClientCreateExternalForm();
        $clientForm->email = 'invoice' . rand(10000, 99999) . '@test.mcn.ru';
        if (!$clientForm->validate() || !$clientForm->create()) {
            throw new \BadMethodCallException('Невозможно создать клиента');
        }

        self::$account = ClientAccount::findOne(['id' => $clientForm->account_id]);
    }

    private function makeBill(ClientAccount $account, $start = 'now')
    {
        $firstDayOfThisMonth = (new \DateTimeImmutable($start))->setTime(0, 0, 0)->modify('first day of this month');
        $lastDayOfThisMonth = $firstDayOfThisMonth->modify('last day of this month');
        $bill = Bill::dao()->createBill($account);
        $bill->bill_date = $firstDayOfThisMonth->format(DateTimeZoneHelper::DATE_FORMAT);
        $this->assertTrue($bill->save());

        $bill->addLine('test item', 1, 1000, BillLine::LINE_TYPE_SERVICE, $firstDayOfThisMonth, $lastDayOfThisMonth);
        Bill::dao()->recalcBill($bill);

        return $bill;
    }

    /**
     * @param Bill $bill
     * @param bool $isCheckNotEmpty
     * @return Invoice
     */
    private function getInvoice(Bill $bill, $isCheckNotEmpty = true)
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::find()
            ->where(['bill_no' => $bill->bill_no, 'type_id' => Invoice::TYPE_1])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($isCheckNotEmpty) {
            $this->assertNotEmpty($invoice);
            $this->assertInstanceOf(Invoice::class, $invoice);
        }

        return $invoice;
    }

    public function testBasic()
    {
        $account = _ClientAccount::createOne();

        // генерация новой с/ф
        $bill = $this->makeBill($account);
        $bill->generateInvoices();

        $invoice = $this->getInvoice($bill);
        $this->assertNotEmpty($invoice->number);


        // генерация номера
        $bill = $this->makeBill($account);
        Invoice::dao()->actionDraft($bill, Invoice::TYPE_1);

        $invoice = $this->getInvoice($bill);
        $this->assertEmpty($invoice->number);

        $bill->generateInvoices();

        $invoice = $this->getInvoice($bill);
        $this->assertNotEmpty($invoice->number);


        // создали сторно
        Invoice::dao()->actionStorno($bill, Invoice::TYPE_1);
        $invoice1 = $this->getInvoice($bill);
        $this->assertEquals($invoice1->is_reversal, 1);

        $bill->generateInvoices();

        $invoice2 = $this->getInvoice($bill);
        $this->assertEquals($invoice2->is_reversal, 1);

        // ничего не меняется
        $this->assertEquals($invoice1, $invoice2);
    }

    public function testDraftAndDel()
    {
        $account = _ClientAccount::createOne();
        $bill = $this->makeBill($account);

        Invoice::dao()->actionDraft($bill, Invoice::TYPE_1);

        $invoice = $this->getInvoice($bill);
        $this->assertEmpty($invoice->number);

        // нельзя сторнировать
        $isExceptionCaught = false;
        try {
            Invoice::dao()->actionStorno($bill, Invoice::TYPE_1);
        } catch (\Exception $e) {
            $isExceptionCaught = true;
            $this->assertInstanceOf(\LogicException::class, $e);
        }
        $this->assertTrue($isExceptionCaught);


        //удаляем
        Invoice::dao()->actionDelete($bill, Invoice::TYPE_1);

        $invoice = $this->getInvoice($bill, false);
        $this->assertEmpty($invoice);

    }

    public function testDraftRegisterAndStorno()
    {
        $account = _ClientAccount::createOne();
        $bill = $this->makeBill($account);

        Invoice::dao()->actionDraft($bill, Invoice::TYPE_1);

        $invoice = $this->getInvoice($bill);
        $this->assertEmpty($invoice->number);

        Invoice::dao()->actionRegister($bill, Invoice::TYPE_1);
        $invoice = $this->getInvoice($bill);
        $this->assertNotEmpty($invoice->number);

        // нельзя удалить
        $isExceptionCaught = false;
        try {
            Invoice::dao()->actionDelete($bill, Invoice::TYPE_1);
        } catch (\Exception $e) {
            $isExceptionCaught = true;
            $this->assertInstanceOf(\LogicException::class, $e);
        }
        $this->assertTrue($isExceptionCaught);


        // нельзя повторно зарегистрировать
        $isExceptionCaught = false;
        try {
            Invoice::dao()->actionRegister($bill, Invoice::TYPE_1);
        } catch (\Exception $e) {
            $isExceptionCaught = true;
            $this->assertInstanceOf(\LogicException::class, $e);
        }
        $this->assertTrue($isExceptionCaught);


        Invoice::dao()->actionStorno($bill, Invoice::TYPE_1);
        $invoice = $this->getInvoice($bill);
        $this->assertNotEmpty($invoice->number);
        $this->assertEquals($invoice->is_reversal, 1);


        // нельзя удалить
        $isExceptionCaught = false;
        try {
            Invoice::dao()->actionDelete($bill, Invoice::TYPE_1);
        } catch (\Exception $e) {
            $isExceptionCaught = true;
            $this->assertInstanceOf(\LogicException::class, $e);
        }
        $this->assertTrue($isExceptionCaught);
    }

    public function testStornoNumbering()
    {
        $account = _ClientAccount::createOne();
        $bill = $this->makeBill($account);

        for ($i = 1; $i <= 5; $i++) {
            Invoice::dao()->actionDraft($bill, Invoice::TYPE_1);

            $invoice = $this->getInvoice($bill);
            $this->assertEmpty($invoice->number);

            Invoice::dao()->actionRegister($bill, Invoice::TYPE_1);
            $invoice = $this->getInvoice($bill);
            $this->assertNotEmpty($invoice->number);

            Invoice::dao()->actionStorno($bill, Invoice::TYPE_1);
            $invoice = $this->getInvoice($bill);
            $this->assertNotEmpty($invoice->number);
            $this->assertEquals($invoice->is_reversal, 1);

            if ($i != 1) {
                $this->assertEquals($invoice->correction_idx, $i - 1);
            }
        }
    }

    public function testRussianNumbering()
    {
        $account = _ClientAccount::createOne();

        foreach (['-3 month', '-2 month', '-1 month', 'now'] as $dateModificator) {
            $bill = $this->makeBill($account, $dateModificator);
            $bill->generateInvoices();

            $invoice = $this->getInvoice($bill);
            $this->assertNotEmpty($invoice->number);
            $this->assertNotEmpty($invoice->idx);
            $this->assertEquals($invoice->idx, 1);
        }
    }

    public function testNoRussianNumbering()
    {
        $account = _ClientAccount::createOne();

        Organization::updateAll(['invoice_counter_range_id' => Organization::INVOICE_COUNTER_RANGE_ID_YEAR]);

        $counter = 0;
        foreach (['-3 month', '-2 month', '-1 month', 'now'] as $dateModificator) {
            $bill = $this->makeBill($account, $dateModificator);
            $bill->generateInvoices();

            $invoice = $this->getInvoice($bill);
            $this->assertNotEmpty($invoice->number);
            $this->assertNotEmpty($invoice->idx);
            $this->assertEquals($invoice->idx, ++$counter);
        }
    }

    public function testException()
    {
        $invoice = new Invoice();
        $isExceptionCaught = false;
        try {
            $invoice->save();
        } catch (\Exception $e) {
            $isExceptionCaught = true;
            $this->assertInstanceOf(\BadFunctionCallException::class, $e);
        }
        $this->assertTrue($isExceptionCaught);
    }

}