<?php

namespace app\classes\payments\recognition\processors;

use app\classes\helpers\LoggerSimpleInternal;
use app\classes\Utils;
use app\models\ClientAccount;
use app\models\PaymentInfo;

abstract class RecognitionProcessor
{
    const SPB_ACCOUNT_ID = 133674;
    const UNRECOGNIZED_PAYMENTS_ACCOUNT_ID = 132778;

    const SPECIAL_ACCOUNT_IDS = [self::SPB_ACCOUNT_ID, self::UNRECOGNIZED_PAYMENTS_ACCOUNT_ID];

    protected ?LoggerSimpleInternal $logger = null;
    public bool $isIdentificationPayment = false;
    public array $listInnAccountIds = [];

    protected ?array $infoJson = null;

    abstract public static function detect($infoJson): bool;

    public function __construct($infoJson) {
        $this->infoJson = $infoJson;

        $this->logger = new LoggerSimpleInternal();
    }

    final public function who(): int
    {
        $this->check();

        return $this->yetWho();
    }

    abstract protected function yetWho(): int;
    
    public function getBankBik(): ?string {
        return null;
    }

    public function getBankAccount(): ?string {
        return null;
    }

    final protected function check()
    {
        if (!static::detect($this->infoJson)) {
            $this->logger->add('Пустой info_json');
            throw new \InvalidArgumentException('Empty data');
        }
    }

    final public function getLog():string
    {
        return $this->logger->get();
    }

    protected function getAccountListToString($array): string
    {
        return implode(", ", array_splice($array, 0, 10)) . (count($array) > 10 ? '...' : '');
    }

    /**
     * @param string $name
     * @param int $factorLimit
     * @return int
     * @throws \yii\db\Exception
     */
    protected function getAccountIdByName(string $name, int $factorLimit = 20): int
    {
        $name = Utils::fixMysqlFulltextSearch($name);
        $sql = <<<SQL
        SELECT `client`.id
  FROM `clients` `client`
         INNER JOIN `client_contract` `contract` ON contract.id = client.contract_id
         INNER JOIN `client_contragent` `contragent` ON contragent.id = contract.contragent_id
WHERE match(name_full) against('*{$name}*' IN BOOLEAN MODE) > {$factorLimit}
ORDER BY match(name_full) against('*{$name}*' IN BOOLEAN MODE) desc, client.is_active desc, client.id desc
LIMIT 1
SQL;

        return ClientAccount::getDb()->createCommand($sql)->queryScalar() ?: 0;
    }
}