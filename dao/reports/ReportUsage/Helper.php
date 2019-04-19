<?php

namespace app\dao\reports\ReportUsage;

use Yii;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\billing\CallsRaw;

class Helper
{
    /**
     * Получение коннектора по id
     *
     * @param integer $connectionId
     * @return \yii\db\Connection
     */
    public static function getDbByConnectionId($connectionId)
    {
        if ($connectionId == Config::CONNECTION_MAIN) {
            return CallsRaw::getDb();
        }

        return Yii::$app->dbPgStatistic;
    }

    /**
     * Получение даты разделения статистики по новому расчёту
     *
     * @return DateTimeImmutable
     * @throws \Exception
     */
    public static function getCdrWithMcnCallIdSeparationDate()
    {
        return new DateTimeImmutable('2019-03-12 14:48:00',
            new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)
        );
    }

    /**
     * Получение архивной даты разделения статистики
     *
     * @return DateTime
     * @throws \Exception
     */
    public static function getArchiveSeparationDate()
    {
        $callsRawTable = DataProvider::getCallsRawLastTable();

        if (!$callsRawTable || !preg_match('/calls_raw_(\d{4})(\d{2})/', $callsRawTable, $matches)) {
            throw new \LogicException('Невозможно получить данные с сервера статистики');
        }

        return
            (
                new DateTime(
                    $matches[1] . '-' . $matches[2] . '-01 00:00:00',
                    new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)
                )
            )->modify("+1 month");
    }

    /**
     * Расчет записи
     *
     * @param ClientAccount $account
     * @param array $row
     * @param bool $isWithProfit
     * @param int $decimals
     * @return array
     */
    public static function calcRow($account, array $row = [], $isWithProfit = false)
    {
        if ($isWithProfit) {
            $row['profit'] = $row['price'] - $row['cost_price'];
        }
        else {
            list($price, $priceWithTax) = self::getTaxAmounts($account, $row['price']);
            $row['price'] = $price;
            $row['price_with_tax'] = $priceWithTax;
        }

        return $row;
    }

    /**
     * Формирование записи
     *
     * @param array $row
     * @param bool $isWithProfit
     * @param int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @return array
     */
    public static function formatRow(array $row = [], $isWithProfit = false, $decimals = 2, $decPoint = '.', $thousandsSep = ',')
    {
        $columns = ['price', 'price_with_tax'];
        if ($isWithProfit) {
            $columns[] = 'cost_price';
            $columns[] = 'cost_price_with_tax';
            $columns[] = 'profit';
        }
        foreach ($columns as $column) {
            $row[$column] = number_format($row[$column], $decimals, $decPoint, $thousandsSep);
        }

        return $row;
    }

    /**
     * Получение сумм с НДС и без него
     *
     * @param ClientAccount $account
     * @param float $amount
     * @return array
     */
    public static function getTaxAmounts(ClientAccount $account, $amount)
    {
        $amountWithTax = $amount;

        $taxRate = $account->getTaxRate();
        if ($account->price_include_vat) {
            $amount = $amountWithTax * 100 / (100 + $taxRate);
        } else {
            $amountWithTax = $amount * (100 + $taxRate) / 100;
        }

        return [
            $amount,
            $amountWithTax,
        ];
    }

    /**
     * Получение dateTime с нужной зоной
     *
     * @param DateTime $dateTime
     * @param string $timeZone
     * @return DateTime
     * @throws \Exception
     */
    public static function createDateTimeWithTimeZone(DateTime $dateTime, $timeZone)
    {
        return (new \DateTime('now', new DateTimeZone($timeZone)))
            ->setTimestamp($dateTime->getTimestamp())
            ->setTime($dateTime->format('H'), $dateTime->format('i'), $dateTime->format('s'))
            ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC));
    }
}