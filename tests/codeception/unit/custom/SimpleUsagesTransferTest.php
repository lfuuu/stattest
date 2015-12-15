<?php

namespace tests\codeception\unit\custom;

use Yii;
use app\models\UsageExtra;
use app\models\UsageWelltime;
use app\models\UsageSms;
use app\models\UsageEmails;
use app\models\UsageTechCpe;

class SimpleUsagesTransferTest extends \yii\codeception\TestCase
{

    use _TransferTrait;

    /**
     * Тест переноса доп. услуг
     */
    public function testUsageExtra()
    {
        $this->checkTransfer(UsageExtra::className());
    }

    /**
     * Тест переноса услуг Welltime
     */
    public function testUsageWelltime()
    {
        $this->checkTransfer(UsageWelltime::className());
    }

    /**
     * Тест переноса услуг SMS
     */
    public function testUsageSms()
    {
        $this->checkTransfer(UsageSms::className());
    }

    /**
     * Тест переноса услуг E-mail
     */
    public function testUsageEmail()
    {
        $this->checkTransfer(UsageEmails::className());
    }

    /**
     * Тест переноса устройств
     */
    public function testUsageTechCpe()
    {
        $this->checkTransfer(UsageTechCpe::className());
    }

}