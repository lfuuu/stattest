<?php

namespace tests\codeception\unit\custom;

use Yii;
use DateTime;
use DateTimeZone;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\usages\UsageInterface;

trait _TransferTrait
{

    private
        $fromClientAccount,
        $toClientAccount;

    private $transaction;

    protected
        $fromUsage,
        $toUsage;

    /**
     * @param string $usageClass - Класс услуги
     * @param null|\Closure $extendsAction - Вызов дополнительного метода, после создания услуги
     * @return array
     */
    protected function prepareTransfer($usageClass, \Closure $extendsAction = null)
    {
        // Создание услуги для переноса
        $this->fromUsage = $usageClass::findOne(['id' => static::createSingleUsage($this->fromClientAccount, $usageClass)]);
        $this->assertNotNull($this->fromUsage, 'See object "fromUsage"');

        if (!is_null($extendsAction) && $extendsAction instanceof \Closure) {
            $extendsAction($this->fromUsage);
        }

        // Подготовка переноса услуги
        try {
            $usage = $this->fromUsage;

            $serviceTransfer =
                $usage::getTransferHelper($usage)
                    ->setTargetAccount($this->toClientAccount)
                    ->setActivationDate(
                        (new DateTime(
                            'first day of next month midnight',
                            new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)
                        ))
                            ->format('Y-m-d')
                    );
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }

        // Запуск переноса с возвратом результата
        $this->toUsage = $usageClass::findOne($serviceTransfer->process()->id);
        $this->assertNotNull($this->toUsage, 'See object "toUsage" after transfer');

        $this->checkUsagesAfter($this->fromUsage, $this->toUsage);
    }

    /**
     * @param $from - Экземпляр услуги
     * @param $to - Экземпляр услуги
     */
    protected function checkUsagesAfter($from, $to)
    {
        // Проверка результата переноса
        $this->assertEquals(
            (new DateTime($from->actual_to))->format('Y-m-d'),
            (new DateTime($to->actual_from))->modify('-1 day')->format('Y-m-d'),
            'ActualFrom equals'
        );
        $this->assertEquals($from->id, $to->prev_usage_id, 'UsagePrevId equals');
        $this->assertEquals($from->next_usage_id, $to->id, 'UsageNextId equals');
    }

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->transaction = Yii::$app->db->beginTransaction();

        // Создание ЛС с которого будет перенос услуги
        $this->fromClientAccount = $this->createSingleClientAccount();
        $this->assertNotNull($this->fromClientAccount, 'See object "fromClientAccount"');

        // Создание ЛС на который будет перенос услуги
        $this->toClientAccount = $this->createSingleClientAccount();
        $this->assertNotNull($this->toClientAccount, 'See object "toClientAccount"');

        // Example: display debug info
        //\Codeception\Util\Debug::debug($this->toClientAccount->getAttributes(['id', 'client', 'timezone_name']));
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->transaction->rollBack();

        parent::tearDown();
    }

    /**
     * Создание болванки ЛС для переноса
     * @return int
     */
    private function createSingleClientAccount()
    {
        $client = new ClientAccount;
        $client->sale_channel = 0;
        $client->consignee = '';
        $client->is_active = 0;
        $client->client = 'id' . mt_rand(0, 1000);
        $client->timezone_name = DateTimeZoneHelper::TIMEZONE_MOSCOW;
        if ($client->validate() && $client->save()) {
            return $client;
        }
        $this->fail('Cant create client account');
    }

    /**
     * Создание болванки услуги для переноса
     * @param ClientAccount $clientAccount
     * @param string $usageClass
     * @return int
     */
    private static function createSingleUsage(ClientAccount $clientAccount, $usageClass)
    {
        $usage = new $usageClass;
        $usage->actual_from =
            (new DateTime('-1 week', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
                ->format('Y-m-d');
        $usage->actual_to = UsageInterface::MAX_POSSIBLE_DATE;
        $usage->client = $clientAccount->client;
        if ($usage->save()) {
            return $usage->id;
        }
        self::fail('Cant create usage "' . $usageClass . '"');
    }

}