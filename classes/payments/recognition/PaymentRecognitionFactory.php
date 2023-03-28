<?php

namespace app\classes\payments\recognition;

use app\classes\payments\recognition\processors\DummyRecognitionProcessor;
use app\classes\payments\recognition\processors\PaymentNordingerRecognitionProcessor;
use app\classes\payments\recognition\processors\PaymentTinkoffRecognitionProcessor;
use app\classes\payments\recognition\processors\RecognitionProcessor;
use app\classes\payments\recognition\processors\RusSbpRecognitionProcessor;
use app\classes\Singleton;

class PaymentRecognitionFactory extends Singleton
{
    private function getProcessors(): array
    {
        return [
            RusSbpRecognitionProcessor::class,

            PaymentTinkoffRecognitionProcessor::class,
            PaymentNordingerRecognitionProcessor::class,

            DummyRecognitionProcessor::class,
        ];
    }

    /**
     * @param $infoJson
     * @return RecognitionProcessor
     */
    public function getProcessor($infoJson): RecognitionProcessor
    {
        foreach ($this->getProcessors() as $processorClass) {
            /** @var $processorClass RecognitionProcessor */
            if ($processorClass::detect($infoJson)) {
                return new $processorClass($infoJson);
            }
        }

        throw new \LogicException('Processor not found');
    }
}