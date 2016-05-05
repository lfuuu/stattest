<?php
namespace app\classes\test;

/**
 * Класс для тестирования (!) ИНН. Очевидно, автосгенерированные значения нельзя присваивать реальным клиентам
 */
class Inn
{
    /**
     * Вернуть цифровой массив заданной длицы
     * @param int $length
     * @return int[]
     */
    public static function randomNumber($length = 1)
    {
        $number = [];
        for ($i = 0; $i < $length; $i++) {
            $number[] = mt_rand(0, 9);
        }
        return $number;
    }

    /**
     * Сгенерировать и вернуть тестовый (!) ИНН для юрлица
     * @return string
     */
    public static function generateCompanyInn()
    {
        $n = self::randomNumber(9);
        array_unshift($n, 0); //for checksum indexes
        $n[] = ((2 * $n[1] + 4 * $n[2] + 10 * $n[3] + 3 * $n[4] + 5 * $n[5] + 9 * $n[6] + 4 * $n[7] + 6 * $n[8] + 8 * $n[9]) % 11) % 10;
        array_shift($n);
        return implode($n);
    }

    /**
     * Сгенерировать и вернуть тестовый (!) ИНН для физлица
     * @return string
     */
    public static function generatePersonalInn()
    {
        $n = self::randomNumber(10);
        $n[] = (($n[0] * 7 + $n[1] * 2 + $n[2] * 4 + $n[3] * 10 + $n[4] * 3 + $n[5] * 5 + $n[6] * 9 + $n[7] * 4 + $n[8] * 6 + $n[9] * 8) % 11) % 10;
        $n[] = (($n[0] * 3 + $n[1] * 7 + $n[2] * 2 + $n[3] * 4 + $n[4] * 10 + $n[5] * 3 + $n[6] * 5 + $n[7] * 9 + $n[8] * 4 + $n[9] * 6 + $n[10] * 8) % 11) % 10;
        return implode($n);
    }
}
