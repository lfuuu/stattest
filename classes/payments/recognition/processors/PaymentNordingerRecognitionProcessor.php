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
            || !isset($infoJson['debtorAccount'])
            || !isset($infoJson['debtorName'])
            || !isset($infoJson['debtorAccount']['iban'])
            || !$infoJson['debtorAccount']['iban']
        ) {
            return false;
        }

        return true;
    }

    public function yetWho(): int
    {
        $info = $this->infoJson;

        $iban = $info['debtorAccount']['iban'];

        $accountId = $this->findByIban($iban);

        if ($accountId) {
            return $accountId;
        }

        return $this->findByName($info['debtorName']);
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
        } else { // count($accountIds) > 1
            $this->logger->add("Найдено несколько Л/С с IBAN платежа (" . $this->getAccountListToString($accountIds) . ")");
            $this->logger->add("Требуется проверка");
            return 0;
        }

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

    private function findByName($name): int
    {
        $accountId = $this->getAccountIdByName($name);

        if (!$accountId) {
            $this->logger->add('не найден контрагент по компании: ' . $name);
            return 0;
        }

        $this->logger->add('Найден ЛС по названию компании (' . $name . '): ' . $accountId);
        return $accountId;
    }

    private function getAccountIdByName($name): int
    {
        $sql = <<<SQL
        SELECT `client`.id
  FROM `clients` `client`
         INNER JOIN `client_contract` `contract` ON contract.id = client.contract_id
         INNER JOIN `client_contragent` `contragent` ON contragent.id = contract.contragent_id
WHERE match(name_full) against('*{$name}*' IN BOOLEAN MODE) > 20
ORDER BY match(name_full) against('*{$name}*' IN BOOLEAN MODE) desc, client.is_active desc, client.id desc
LIMIT 1
SQL;

        return ClientAccount::getDb()->createCommand($sql)->queryScalar() ?: 0;
    }
}
