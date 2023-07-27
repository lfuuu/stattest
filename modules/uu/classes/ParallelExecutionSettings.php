<?php

namespace app\modules\uu\classes;

use app\models\Param;

class ParallelExecutionSettings
{
    private array $serverArgv = [];
    private int $fromId = 0;
    private int $toId = 0;

    public function __construct($serverArgv)
    {
        $this->serverArgv = $serverArgv;
    }

    public function isParallel(): bool
    {
        return $this->checkIncomingParameters() && ($this->checkDirect() || $this->checkPart());
    }

    public function getFromId()
    {
        $this->setParam();
        $this->postSetParam();

        return $this->fromId;
    }

    public function getToId()
    {
        $this->setParam();
        $this->postSetParam();

        return $this->toId;
    }

    private function setParam(): void
    {
        if ($this->fromId) {
            return; // already
        }

        $this->checkDirect() || $this->checkPart();
    }

    private function postSetParam(): void
    {
        if (!$this->fromId || !$this->toId || $this->fromId > $this->toId) {
            throw new \InvalidArgumentException('Неверные аргументы');
        }
    }

    private function checkIncomingParameters(): bool
    {
        if (!$this->serverArgv || !is_array($this->serverArgv) || count($this->serverArgv) < 3) {
            return false;
        }

        if (!in_array($this->serverArgv[1], ['ubiller/resource', 'ubiller/period'])) {
            return false;
        }

        return true;
    }

    private function checkDirect(): bool
    {
        if (count($this->serverArgv) != 4) {
            return false;
        }

        $this->fromId = (int)$this->serverArgv[2];
        $this->toId = (int)$this->serverArgv[3];

        return true;
    }

    private function checkPart(): bool
    {
        $sa = $this->serverArgv;

        if (count($sa) != 3 || $sa[2][0] != 'p') {
            return false;
        }

        $part = substr($sa[2], 1);
        $partsDataStr = Param::getParam(Param::RESOURCE_PARTS, []);

        if ($partsDataStr) {
            $partsData = json_decode($partsDataStr, true);
            if (isset($partsData[$part - 1])) {
                $partData = $partsData[$part - 1];
                $this->fromId = (int)$partData['min'];
                $this->toId = (int)$partData['max'];

                return true;
            }
        }

        return false;
    }
}