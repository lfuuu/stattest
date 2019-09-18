<?php

namespace app\modules\sbisTenzor\commands;

use app\modules\sbisTenzor\classes\SBISProcessor;
use yii\console\Controller;

class ProcessorController extends Controller
{
    const WAITING_TIMEOUT_2_TIMES = 26;
    const WAITING_TIMEOUT_3_TIMES = 17;

    /**
     * Подписать пакеты документов
     */
    public function actionSign()
    {
        $processor = SBISProcessor::createProcessor(SBISProcessor::TYPE_SIGNER);
        $this->smartProcess($processor);
    }

    /**
     * Отправить пакеты документов
     */
    public function actionSend()
    {
        $processor = SBISProcessor::createProcessor(SBISProcessor::TYPE_SENDER);
        $this->smartProcess($processor);
    }

    /**
     * Проверить статус пакетов документов
     */
    public function actionCheck()
    {
        $processor = SBISProcessor::createProcessor(SBISProcessor::TYPE_FETCHER);
        $this->smartProcess($processor, self::WAITING_TIMEOUT_2_TIMES);
    }

    /**
     * Умный вызов обработчика
     *
     * @param SBISProcessor $processor
     * @param int $waitingTimeout
     */
    protected function smartProcess(SBISProcessor $processor, $waitingTimeout = self::WAITING_TIMEOUT_3_TIMES)
    {
        $start = time();
        for ($i = 0; ($i <= 4) && (time() - $start < 50); $i++) {
            $processed = $processor->run();

            if ($processed) {
                // идет обработка
                sleep(2);
            } else {
                // ожидание
                sleep($waitingTimeout);
            }
        }
    }
}
