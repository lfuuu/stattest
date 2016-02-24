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
    public function actionImport()
    {
        Yii::info('CurrencyImport: start');

        $dateTimeFrom = (new DateTime())->modify('-1 month'); // совсем уж старое не надо скачивать
        $dateTimeTomorrow = (new DateTime())->modify('+1 day');

        // уже установленные курсы
        $currencyRates = [];
        $currencyRateQuery = CurrencyRate::find()
            ->where(['>', 'date', $dateTimeFrom->format('Y-m-d')]);
        /** @var CurrencyRate $currencyRate */
        foreach ($currencyRateQuery->each() as $currencyRate) {
            $currencyRates[$currencyRate->date][$currencyRate->currency] = true;
        }

        // все валюты, кроме рубля (ибо странно считать курс рубля к рублю)
        $currencies = Currency::find()
            ->where(['!=', 'id', Currency::RUB])
            ->indexBy('id')
            ->all();
        $currencies = array_keys($currencies);
        $currencies = array_combine($currencies, $currencies);

        // по всем нескачанным дням
        while ($dateTimeFrom->modify('+1 day') <= $dateTimeTomorrow) {

            $date = $dateTimeFrom->format('Y-m-d');
            Yii::info('CurrencyImport: ' . $date);

            $currenciesCopy = $currencies;
            if (isset($currencyRates[$date])) {
                // что-то уже скачано. Это скачивать не надо
                foreach ($currencyRates[$date] as $currency => $rate) {
                    unset($currenciesCopy[$currency]);
                }
            }

            if (!count($currenciesCopy)) {
                // за этот день все скачано
                Yii::info('CurrencyImport: already');
                continue;
            }

            // скачать
            // иногда курс на завтра уже известен, тогда скачаем. Иначе подождем до завтра (третий параметр - strict)
            $this->importByDate($currenciesCopy, $dateTimeFrom, $dateTimeFrom == $dateTimeTomorrow);
        }

        Yii::info('CurrencyImport: finish');
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
            $fileName = sprintf(Yii::$app->params['currencyDownloadUrl'], $dateTime->format('d/m/Y'));
            $simplexml = simplexml_load_file($fileName);
            if ($simplexml === false) {
                throw new ParseError('loading ' . $fileName);
            }
            Yii::trace('CurrencyImport: ' . print_r($simplexml, true));

            // дата курса
            $date = (string)$simplexml['Date'];
            if (!$date) {
                throw new ParseError('parsing date ' . $fileName);
            }
            $dateTimeXml = new DateTime($date);
            Yii::info('CurrencyImport: ' . print_r($currencies, true));
            if ($isStrictDate && $dateTimeXml->format('Y-m-d') !== $dateTime->format('Y-m-d')) {
                Yii::info('CurrencyImport: strict');
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
                Yii::info('CurrencyImport: ' . $currencyCode . ' ' . $currencyValue . ' ' . $currencyValueFloat);
                if ($currencyValueFloat <= 0) {
                    Yii::error('CurrencyImport: wrong rate ' . $dateTime->format('Y-m-d') . ' ' . $currencyCode . ' ' . $currencyValue . ' ' . $currencyValueFloat);
                    continue;
                }

                try {
                    $currencyRate = new CurrencyRate();
                    $currencyRate->date = $dateTime->format('Y-m-d');
                    $currencyRate->currency = $currencyCode;
                    $currencyRate->rate = $currencyValueFloat;
                    $currencyRate->save();
                } catch (Exception $e) {
                    Yii::error('CurrencyImport: error ' . $e->getMessage());
                }
            }

            return true;

        } catch (Exception $e) {
            Yii::error('CurrencyImport: error ' . $e->getMessage());
        }

        return false;
    }

}