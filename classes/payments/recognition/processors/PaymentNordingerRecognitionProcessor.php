<?php

namespace app\classes\payments\recognition\processors;

use app\models\ClientAccount;

class PaymentNordingerRecognitionProcessor extends RecognitionProcessor
{
    public static function detect($infoJson): bool
    {
        if (
            !$infoJson
            || !is_array($infoJson)
            || !(isset($infoJson['debtorName']) || isset($infoJson['creditorName']))
        ) {
            return false;
        }

        return true;
    }

    public function yetWho(): int
    {
        $info = $this->infoJson;

        $iban = $info['debtorAccount']['iban'] ?? false;

        if ($iban) {
            $accountId = $this->findByIban($iban);

            if ($accountId) {
                return $accountId;
            }
        }

        return $this->findByName($info['debtorName'] ?? $info['creditorName'] ?? '');
    }

    public function getBankAccount(): ?string {
        return $this->infoJson['debtorAccount']['iban'] ?? null;
    }

    private function findByIban($iban)
    {
        $accountIds = $this->getAccountIdsByIban($iban);

        if (!$accountIds) {
            $accountIds = $this->getAccountIdsByIban($iban, false);
        }

        if (!$accountIds) {
            $this->logger->add("Нет Л/С с IBAN {$iban}");
            return 0;
        }

        if (count($accountIds) == 1) {
            $accountId = reset($accountIds);
            $this->logger->add("Найден единственный Л/С {$accountId}");
            $this->isIdentificationPayment = true;
            return $accountId;
        }
        // count($accountIds) > 1
        $this->logger->add("Найдено несколько Л/С с IBAN платежа (" . $this->getAccountListToString($accountIds) . ")");
        $this->logger->add("Требуется проверка");
        return 0;
    }

    private function getAccountIdsByIban($iban, $isActive = true): array
    {
        $query = ClientAccount::find()
            ->alias('c')
            ->where([
                'c.pay_acc' => $iban,
            ])->select('c.id')
            ->limit(100);

        if ($isActive) {
            $query->andWhere([
                'c.is_active' => 1,
            ]);
        }

        return $query->column();
    }

    /**
     * @throws \yii\db\Exception
     */
    private function findByName($name): int
    {
        if (!$name) {
            return 0;
        }

        $accountId = $this->getAccountIdByName($name, 43);

        if (!$accountId) {
            $this->logger->add('не найден контрагент по компании: ' . $name);
            return 0;
        }

        $this->logger->add('Найден ЛС по названию компании (' . $name . '): ' . $accountId);
        return $accountId;
    }


}
