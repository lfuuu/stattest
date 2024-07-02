<?php
/**
 * Импорт курсов валют с сайта ЦБ РФ
 *
 * @link http://www.cbr.ru/scripts/Root.asp?PrtId=SXML
 */

namespace app\commands;

use app\classes\HandlerLogger;
use app\helpers\DateTimeZoneHelper;
use app\models\Bik;
use app\models\Currency;
use app\models\CurrencyRate;
use app\models\EventQueue;
use DateTime;
use Exception;
use Yii;
use yii\console\Controller;

class CurrencyController extends Controller
{
    public function actionImport()
    {
        Yii::info('CurrencyImport: start');

        $now = (new DateTime());
        $dateTimeFrom = (new DateTime())->modify('-1 month'); // совсем уж старое не надо скачивать
        $dateTimeTomorrow = (new DateTime())->modify('+1 day');

        // уже установленные курсы
        $currencyRates = [];
        $currencyRateQuery = CurrencyRate::find()
            ->where(['>', 'date', $dateTimeFrom->format(DateTimeZoneHelper::DATE_FORMAT)]);

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

            $date = $dateTimeFrom->format(DateTimeZoneHelper::DATE_FORMAT);

            $currenciesCopy = $currencies;
            if (isset($currencyRates[$date])) {
                // что-то уже скачано. Это скачивать не надо
                foreach ($currencyRates[$date] as $currency => $rate) {
                    unset($currenciesCopy[$currency]);
                }
            }

            if (!count($currenciesCopy)) {
                // за этот день все скачано
                continue;
            }

            // скачать
            // иногда курс на завтра уже известен, тогда скачаем. Иначе подождем до завтра (третий параметр - strict)

            $isStrict = $dateTimeFrom == $dateTimeTomorrow && $now->format('G') < 15; // UTC
            Yii::info('CurrencyImport: ' . $date . ' ' . ($isStrict ? 'strict' : 'not_strict'));
            $this->importByDate($currenciesCopy, $dateTimeFrom, $isStrict);
        }
    }

    /**
     * Импортировать все курсы на заданную дату
     *
     * @param array $currencies валюты, которые импортировать. Код валюты - в ключе
     * @param DateTime $dateTime
     * @param bool $isStrictDate
     * @return bool
     * @throws \RuntimeException
     */
    protected function importByDate(array $currencies, DateTime $dateTime, $isStrictDate = false)
    {
        try {
            $fileName = sprintf(Yii::$app->params['currencyDownloadUrl'], $dateTime->format('d/m/Y'));
            $simpleXml = simplexml_load_file($fileName);
            if ($simpleXml === false) {
                throw new \RuntimeException('loading ' . $fileName);
            }
            Yii::debug('CurrencyImport: ' . print_r($simpleXml, true));

            // дата курса
            $date = (string)$simpleXml['Date'];
            if (!$date) {
                throw new \RuntimeException('parsing date ' . $fileName);
            }

            $dateTimeXml = new DateTime($date);
            Yii::info('CurrencyImport: ' . print_r($currencies, true));

            $date1 = $dateTimeXml->format(DateTimeZoneHelper::DATE_FORMAT);
            $date2 = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
            if ($isStrictDate && $date1 !== $date2) {
                Yii::info('CurrencyImport: strict ' . $date1 . ' != ' . $date2);
                return false;
            }

            // по всем валютам
            foreach ($simpleXml->Valute as $valute) {
                $currencyCode = (string)$valute->CharCode;
                if (!isset($currencies[$currencyCode])) {
                    // только для указанных валют
                    continue;
                }

                $currencyValue = (string)$valute->Value;
                $currencyValueFloat = (float)str_replace(',', '.', $currencyValue);
                $currencyValueFloat /= (string)$valute->Nominal;
                Yii::info('CurrencyImport: ' . $currencyCode . ' ' . $currencyValue . ' ' . $currencyValueFloat);
                if ($currencyValueFloat <= 0) {
                    Yii::error('CurrencyImport: wrong rate ' . $dateTime->format(DateTimeZoneHelper::DATE_FORMAT) . ' ' . $currencyCode . ' ' . $currencyValue . ' ' . $currencyValueFloat);
                    continue;
                }

                try {
                    $currencyRate = new CurrencyRate();
                    $currencyRate->date = $dateTime->format(DateTimeZoneHelper::DATE_FORMAT);
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

    public function actionBik($replace = 0)
    {
        $isReplace = (bool)$replace;

        //проверка ссылки
        $url = "https://cbr.ru/s/newbik";
        if (!$file = file_get_contents($url)) {
            throw new \RuntimeException("link is broken " . $url);
        }

        //достаем файл из ЦБ и скачиваем zip в /tmp
        $tempZip = tempnam(sys_get_temp_dir(), "newbik.zip");
        if (!is_writable($tempZip)) {
            throw new \RuntimeException("can't write to file " . $tempZip);
        }
        file_put_contents($tempZip, $file);

        //разархивируем  и достаем файл
        $zip = new \ZipArchive;
        $zip->open($tempZip);
        $xmlName = $zip->getNameIndex(0);
        $path = 'zip://' . $tempZip . '#' . $xmlName;
        $xml = file_get_contents($path);
        $xmlOpen = simplexml_load_string($xml);


        //вытаскиваем информацию из xml файла
        foreach ($xmlOpen as $node) {
            $newAccount = '';
            if ($newAccount == '') {
                $newAccount = $node->Accounts['Account'];
            }

            //2 массива со старыми данными из БД и новыми данными из XML 
            $newInfo = [
                'bik' => (string)$node['BIC'],
                'corr_acc' => (string)$newAccount,
                'bank_city' => (string)$node->ParticipantInfo['Nnp'],
                'bank_address' => $node->ParticipantInfo['Adr'],
                'bank_name' => $node->ParticipantInfo['NameP']
            ];
            $oldInfo = Yii::$app->db->createCommand("select * from bik where bik = :bik", [':bik' => $newInfo['bik']])->queryOne();

            //смотрим если запись отсутствует
            if (!$oldInfo) {
                echo PHP_EOL . "BIK " . $newInfo['bik'] . ": (+)";

                //добавляем новую запись в базу
                Yii::$app->db->createCommand()->insert('bik', [
                    'bik' => $newInfo['bik'],
                    'corr_acc' => $newInfo['corr_acc'],
                    'bank_name' => $newInfo['bank_name'],
                    'bank_city' => $newInfo['bank_city'],
                    'bank_address' => $newInfo['bank_address'],
                ])->execute();

                EventQueue::go(EventQueue::DADATA_BIK, $newInfo['bik']);

            } else { // если запись имеется
                $changeArray = [];
                foreach (['bik', 'corr_acc', 'bank_name', 'bank_city', 'bank_address'] as $param) {
                    if ($oldInfo[$param] != $newInfo[$param]) {
                        echo PHP_EOL . "BIK " . $oldInfo['bik'] . ": (*) " . $param . ' ' . $oldInfo[$param] . '=>' . $newInfo[$param];
                        $changeArray[$param] = $newInfo[$param];
                    }
                }

                //вносим изменения
                if ($changeArray && $isReplace) {
                    Yii::$app->db->createCommand()
                        ->update('bik', $changeArray, 'bik = :bik')
                        ->bindValue(':bik', $newInfo['bik'])
                        ->execute();
                }
            }
        }

        return true;
    }

    public function actionBikUpd()
    {
        $query = <<<SQL
with used_bics as (
    select distinct payer_bik as bik
    from nispd.newpayment_info
    where payer_bik
    union
    select distinct getter_bik as bik
    from nispd.newpayment_info
    where payer_bik
)

select b.bik from bik b
join used_bics using(bik)
where b.dadata is null
SQL;

        $biks = \Yii::$app->db->createCommand($query)->queryColumn();
        foreach ($biks as $bik) {
            echo PHP_EOL . $bik;

            Bik::updateDadata($bik);
            echo implode(PHP_EOL, HandlerLogger::me()->get());
            HandlerLogger::me()->clear();
        }
    }
}