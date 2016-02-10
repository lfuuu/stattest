<?php
/**
 * Импорт курсов валют с сайта ЦБ РФ
 *
 * @link http://www.cbr.ru/scripts/Root.asp?PrtId=SXML
 */

namespace app\commands;

use app\models\Currency;
use app\models\CurrencyRate;
use DateTime;
use Exception;
use ParseError;
use Yii;
use yii\console\Controller;

class CurrencyController extends Controller
{
    protected $isDebug = true;

    public function actionImport()
    {
        echo 'CurrencyImport: start' . PHP_EOL;

        $dateTimeFrom = (new DateTime())->modify('-1 month'); // совсем уж старое не надо скачивать
        $dateTimeTomorrow = (new DateTime())->modify('+1 day');

        // уже установленные курсы
        $currencyRates = [];
        $currencyRateQuery = CurrencyRate::find()
            ->where('date > :date', [':date' => $dateTimeFrom->format('Y-m-d')]);
        /** @var CurrencyRate $currencyRate */
        foreach ($currencyRateQuery->each() as $currencyRate) {
            $currencyRates[$currencyRate->date][$currencyRate->currency] = true;
        }

        // все валюты, кроме рубля (ибо странно считать курс рубля к рублю)
        $currencies = Currency::find()
            ->where('id != :id', [':id' => Currency::RUB])
            ->indexBy('id')
            ->all();
        $currencies = array_keys($currencies);
        $currencies = array_combine($currencies, $currencies);

        // по всем нескачанным дням
        while ($dateTimeFrom->modify('+1 day') <= $dateTimeTomorrow) {

            $date = $dateTimeFrom->format('Y-m-d');
            if ($this->isDebug) {
                echo PHP_EOL . $date . PHP_EOL;
            }

            $currenciesCopy = $currencies;
            if (isset($currencyRates[$date])) {
                // что-то уже скачано. Это скачивать не надо
                foreach ($currencyRates[$date] as $currency => $rate) {
                    unset($currenciesCopy[$currency]);
                }
            }

            if (!count($currenciesCopy)) {
                // за этот день все скачано
                if ($this->isDebug) {
                    echo 'Already' . PHP_EOL;
                }
                continue;
            }

            // скачать
            // иногда курс на завтра уже известен, тогда скачаем. Иначе подождем до завтра (третий параметр - strict)
            $this->importByDate($currenciesCopy, $dateTimeFrom, $dateTimeFrom != $dateTimeTomorrow);
        }

        echo 'CurrencyImport: finish' . PHP_EOL;
    }

    /**
     * Импортировать все курсы на заданную дату
     *
     * @param [] $currencies валюты, которые импортировать. Код валюты - в ключе
     * @param DateTime $dateTime
     * @param bool $isStrictDate
     * @throws ParseError
     */
    protected function importByDate(array $currencies, DateTime $dateTime, $isStrictDate = false)
    {
        try {
            $fileName = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=' . $dateTime->format('d/m/Y');
            $simplexml = simplexml_load_file($fileName);
            if ($simplexml === false) {
                throw new ParseError('loading ' . $fileName);
            }
//            if ($this->isDebug) {
//                print_r($simplexml);
//            }

            // дата курса
            $date = (string)$simplexml['Date'];
            if (!$date) {
                throw new ParseError('parsing date ' . $fileName);
            }
            $dateTimeXml = new DateTime($date);
            if ($this->isDebug) {
                echo $dateTimeXml->format('Y-m-d') . PHP_EOL;
            }
            if ($isStrictDate && $dateTimeXml->format('Y-m-d') !== $dateTime->format('Y-m-d')) {
                if ($this->isDebug) {
                    echo 'Strict' . PHP_EOL;
                }
                return false;
            }

            // по всем валютам
            foreach ($simplexml->Valute as $valute) {
                $currencyCode = (string)$valute->CharCode;
                if (!isset($currencies[$currencyCode])) {
                    // только для указанных валют
                    continue;
                }

                $currencyValue = (string)$valute->Value;
                $currencyValueFloat = (float)str_replace(',', '.', $currencyValue);
                if ($this->isDebug) {
                    echo $currencyCode . ' ' . $currencyValue . ' ' . $currencyValueFloat . PHP_EOL;
                }

                try {
                    $currencyRate = new CurrencyRate();
                    $currencyRate->date = $dateTime->format('Y-m-d');
                    $currencyRate->currency = $currencyCode;
                    $currencyRate->rate = $currencyValueFloat;
                    $currencyRate->save();
                } catch (Exception $e) {
                    $message = 'CurrencyImport: error ' . $e->getMessage();
                    echo $message;
                    Yii::error($message);
                }
            }

            return true;

        } catch (Exception $e) {
            $message = 'CurrencyImport: error ' . $e->getMessage();
            echo $message;
            Yii::error($message);
        }

        return false;
    }

}