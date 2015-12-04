<?php

namespace tests\codeception\unit\custom;

use Yii;
use DateTime;
use DateTimeZone;
use app\models\ClientAccount;
use app\models\usages\UsageInterface;

trait _TransferTrait
{

    private
        $fromClientAccount,
        $toClientAccount;

    private $transaction;

    /**
     * @param $usageType - Класс услуги
     */
    protected function checkTransfer($usageType,  $extendsData)
    {
        // Создание услуги для переноса
        $fromUsage = $usageType::findOne(static::createSingleUsage($this->fromClientAccount, $usageType));
        $this->assertNotNull($fromUsage, 'See object "fromUsage"');

        if ($extendsData instanceof \Closure) {
            $extendsData($fromUsage);
        }

        // Подготовка переноса услуги
        try {
            $serviceTransfer =
                $fromUsage::getTransferHelper($fromUsage)
                    ->setTargetAccount($this->toClientAccount)
                    ->setActivationDate((new DateTime('first day of next month midnight', new DateTimeZone('UTC')))->format('Y-m-d'));
        }
        catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        // Запуск переноса с возвратом результата
        $toUsage = $usageType::findOne($serviceTransfer->process()->id);
        $this->assertNotNull($toUsage, 'See object "toUsage" after transfer');

        $this->checkUsagesAfter($fromUsage, $toUsage);

        return [$fromUsage, $toUsage];
    }

    /**
     * @param $from - Экземпляр услуги
     * @param $to - Экземпляр услуги
     */
    protected function checkUsagesAfter($from, $to)
    {
        // Проверка результата переноса
        $this->assertEquals(
            (new DateTime($from->actual_to))->format('Y-m-d'), (new DateTime($to->actual_from))->modify('-1 day')->format('Y-m-d'),
            'ActualFrom equals'
        );
        $this->assertEquals($from->id, $to->prev_usage_id, 'UsagePrevId equals');
        $this->assertEquals($from->next_usage_id, $to->id, 'UsageNextId equals');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->transaction = Yii::$app->db->beginTransaction();

        // Создание аккаунта с которого будет перенос услуги
        $this->fromClientAccount = ClientAccount::findOne($this->createSingleClientAccount());
        $this->assertNotNull($this->fromClientAccount, 'See object "fromClientAccount"');

        // Создание аккаунта на который будет перенос услуги
        $this->toClientAccount = ClientAccount::findOne($this->createSingleClientAccount());
        $this->assertNotNull($this->toClientAccount, 'See object "toClientAccount"');
    }

    protected function tearDown()
    {
        $this->transaction->rollBack();

        parent::tearDown();
    }

    /**
     * Создание болванки аккаунта для переноса
     * @return int
     */
    private function createSingleClientAccount() {
        $client = new ClientAccount;
        $client->is_active = 0;
        $client->validate();
        $client->save();
        $client->client = 'id' . $client->id;
        $client->save();
        return $client->id;
    }

    /**
     * Создание болванки услуги для переноса
     * @param ClientAccount $client
     * @return int
     */
    private static function createSingleUsage(ClientAccount $client, $usageClass)
    {
        $actualFrom = (new DateTime('-1 week', new DateTimeZone('UTC')))->format('Y-m-d');
        $actualTo = UsageInterface::MAX_POSSIBLE_DATE;
        $client = 'id' . $client->id;

        $usage = new $usageClass;
        $usage->actual_from  = $actualFrom;
        $usage->actual_to  = $actualTo;
        $usage->client = $client;
        $usage->save();

        return $usage->id;
    }

}