<?php
namespace app\dao;

use app\classes\Singleton;

/**
 * @method static ClientContactDao me($args = null)
 */
class ClientContactDao extends Singleton
{
    /**
     * Преобразовать номер телефона в стандарт E164
     *
     * @param string $phone
     * @param string $ndcDefault
     * @return \string[] $phones Возможно, что получилось несколько телефонов
     */
    public function getE164($phone, $ndcDefault = '')
    {
        $phone = trim($phone);
        $phone = str_replace('-', '', $phone); // удалить тире. Как между цифрами, так и между телефоном и комментом
        $phone = preg_replace('/ +(\d)/', '$1', $phone); // удалить пробелы перед цифрой. Другие пробелы оставить!
        $e164Phones = [];

        // найти NDC на случай следующего телефона без NDC
        if (preg_match('/\((\d{3,4})\)/', $phone, $matches)) {
            $ndc = $matches[1];
        } else {
            $ndc = $ndcDefault;
        }

        $phone = str_replace(['(', ')'], '', $phone); // удалить скобки

        // поискать телефоны в E164
        if (preg_match_all(
            '/(?:^|\b)' . // начало слова или начало слова
            '(\+\d{10,12})' . // телефон. В России 1+10 символов, но в других странах может быть по-другому
            '(?:\b|$)/', // конец слова или конец строки
            $phone, $matches, $flags = PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $phone = str_replace($match[1], '', $phone); // убрать найденный телефон из строки
                $match[1] = str_replace('+70', '+74', $match[1]); // российские коды давно заменили начальный "0" на "4"
                $e164Phones[] = $match[1]; // вероятность 100%
            }
        }

        // поискать телефоны без префикса
        if (preg_match_all(
            '/(?:^|\b)' .  // начало слова или начало слова
            '(\d{10,11})' . // телефон
            '(?:\b|$)/', // конец слова или конец строки
            $phone, $matches, $flags = PREG_SET_ORDER)) {
            foreach ($matches as $match) {

                if (strlen($match[1]) >= 11) {

                    switch ($match[1][0]) {
                        case '7':
                            // E164, но без "+"
                            $phone = str_replace($match[1], '', $phone); // убрать найденный телефон из строки
                            ($match[1][1] == '0') && ($match[1][1] = '4'); // российские коды давно заменили начальный "0" на "4"
                            $e164Phones[] = '+' . $match[1];  // Вероятность 80%
                            break;
                        case '8':
                            // через "восьмерку". Заменить на "+7"
                            $phone = str_replace($match[1], '', $phone); // убрать найденный телефон из строки
                            $match[1][0] = '7';
                            ($match[1][1] == '0') && ($match[1][1] = '4'); // российские коды давно заменили начальный "0" на "4"
                            $e164Phones[] = '+' . $match[1];  // Вероятность 90%
                            break;
                        default:
                            // неизвестный формат
                            break;
                    }
                } else {

                    // ndc + номер
                    $phone = str_replace($match[1], '', $phone); // убрать найденный телефон из строки
                    ($match[1][0] == '0') && ($match[1][0] = '4'); // российские коды давно заменили начальный "0" на "4"
                    $e164Phones[] = '+7' . $match[1];  // Вероятность 90%
                }
            }
        }

        // поискать телефоны без префикса и без NDC (иногда пишут несколько номеров, но NDC указывают только у первого)
        if ($ndc && preg_match_all(
                '/(?:^|\b)' .  // начало слова или начало слова
                '(\d{6,7})' .  // телефон
                '(?:\b|$)/', // конец слова или конец строки
                $phone, $matches, $flags = PREG_SET_ORDER)
        ) {
            foreach ($matches as $match) {
                $phone = str_replace($match[1], '', $phone); // убрать найденный телефон из строки
                $e164Phones[] = '+7' . $ndc . $match[1]; // вероятность 70%
            }
        }

        return $e164Phones;
    }
}
