<?php

namespace app\classes\payments\recognition\processors;

/**
 * [paymentPurpose] => Пополнение по операции СБП 2539446810. Терминал MCN Telecom
 */
class RusSbpRecognitionProcessor extends RecognitionProcessor
{
    public static function detect($infoJson): bool
    {
        if (
            !$infoJson
            || !is_array($infoJson)
            || !isset($infoJson['paymentPurpose'])
            || strpos($infoJson['paymentPurpose'], ' по операции СБП ') === false
            || strpos($infoJson['paymentPurpose'], 'Терминал') === false
        ) {
            return false;
        }

        return true;
    }

    protected function yetWho(): int
    {
        $this->logger->add("Найден СБП платеж, переносим в Л/С " . self::SPB_ACCOUNT_ID);
        return self::SPB_ACCOUNT_ID;
    }
}