<?php

namespace app\classes\helpers;

class CalculateGrowthRate
{
    private ?int $previousCounter = null;
    private ?float $previousTime = null;

    public function calculate($currentCounter): float
    {
        $currentTime = microtime(true);

        if ($this->previousCounter === null || $this->previousTime === null) {
            // Первый вызов: сохраняем значения и возвращаем 0
            $this->previousCounter = $currentCounter;
            $this->previousTime = $currentTime;
            return 0.0;
        }

        $deltaCounter = $currentCounter - $this->previousCounter;
        $deltaTime = $currentTime - $this->previousTime;

        // Обработка случая, когда время между вызовами слишком мало
        if ($deltaTime <= 0) {
            $rate = 0.0;
        } else {
            $rate = $deltaCounter / $deltaTime;
        }

        // Обновляем предыдущие значения
        $this->previousCounter = $currentCounter;
        $this->previousTime = $currentTime;

        return (float)$rate;
    }

}