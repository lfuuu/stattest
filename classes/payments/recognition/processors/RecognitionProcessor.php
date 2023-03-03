<?php

namespace app\classes\payments\recognition\processors;

use app\classes\helpers\LoggerSimpleInternal;

abstract class RecognitionProcessor
{
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
}