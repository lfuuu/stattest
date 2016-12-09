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
        $this->prepareTransfer(UsageExtra::className());
    }

    /**
     * Тест переноса услуг Welltime
     */
    public function testUsageWelltime()
    {
        $this->prepareTransfer(UsageWelltime::className());
    }

    /**
     * Тест переноса услуг SMS
     */
    public function testUsageSms()
    {
        $this->prepareTransfer(UsageSms::className());
    }

    /**
     * Тест переноса услуг E-mail
     */
    public function testUsageEmail()
    {
        $this->prepareTransfer(UsageEmails::className());
    }

    /**
     * Тест переноса устройств
     */
    public function testUsageTechCpe()
    {
        $this->prepareTransfer(UsageTechCpe::className());
    }

}