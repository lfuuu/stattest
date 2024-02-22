<?php

namespace app\classes;

use app\helpers\DateTimeZoneHelper;
use Yii;

class DateFunction
{
    /**
     * @param string $string
     * @param int $nMonth
     * @return string
     */
    public static function dateReplaceMonth($string, $nMonth)
    {
        $p = [
            'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сентября',
            'октября',
            'ноября',
            'декабря'
        ];
        $string = str_replace('месяца', $p[$nMonth - 1], $string);

        $p = [
            'январе',
            'феврале',
            'марте',
            'апреле',
            'мае',
            'июне',
            'июле',
            'августе',
            'сентябре',
            'октябре',
            'ноябре',
            'декабре'
        ];
        $string = str_replace('месяце', $p[$nMonth - 1], $string);

        $p = [
            'Январь',
            'Февраль',
            'Март',
            'Апрель',
            'Май',
            'Июнь',
            'Июль',
            'Август',
            'Сентябрь',
            'Октябрь',
            'Ноябрь',
            'Декабрь'
        ];
        $string = str_replace('Месяц', $p[$nMonth - 1], $string);

        $p = [
            'январь',
            'февраль',
            'март',
            'апрель',
            'май',
            'июнь',
            'июль',
            'август',
            'сентябрь',
            'октябрь',
            'ноябрь',
            'декабрь'
        ];
        $string = str_replace('месяц', $p[$nMonth - 1], $string);

        return $string;
    }

    /**
     * @param int|string $ts
     * @param string $format
     * @return string
     */
    public static function mdate($ts, $format)
    {
        if (!is_numeric($ts)) {
            $ts = strtotime($ts);
        }

        if ($ts) {
            $s = date($format, $ts);
        } else {
            $s = date($format);
        }

        if ($ts) {
            $d = getdate($ts);
        } else {
            $d = getdate();
        }

        return self::dateReplaceMonth($s, $d['mon']);
    }

    /**
     * @param integer|string|\DateTime|\DateTimeImmutable $dateFrom
     * @param integer|string|\DateTime|\DateTimeImmutable $dateTo
     * @return string
     * @throws \yii\base\InvalidParamException
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDateRange($dateFrom, $dateTo)
    {
        if (!$dateFrom) {
            return '';
        }

        $dateFrom = self::convertToDateTime($dateFrom);
        $dateTo = self::convertToDateTime($dateTo);

        if ($dateFrom->format('Y') === $dateTo->format('Y')) {
            if ($dateFrom->format('m') === $dateTo->format('m')) {
                if ($dateFrom->format('d') === $dateTo->format('d')) {
                    return Yii::$app->formatter->asDate($dateTo, DateTimeZoneHelper::HUMAN_DATE_FORMAT);
                }

                $format = 'd';
            } else {
                $format = 'd MMM';
            }
        } else {
            $format = DateTimeZoneHelper::HUMAN_DATE_FORMAT;
        }

        return sprintf('%s - %s',
            Yii::$app->formatter->asDate($dateFrom, $format),
            Yii::$app->formatter->asDate($dateTo, DateTimeZoneHelper::HUMAN_DATE_FORMAT)
        );
    }

    /**
     * @param integer|string|\DateTime|\DateTimeImmutable $date
     * @return \DateTime
     */
    public static function convertToDateTime($date)
    {
        if (is_numeric($date)) {
            // unix timestamp
            $dateTime = new \DateTime();
            $dateTime->setTimestamp($date);
            return $dateTime;
        }

        if (is_string($date)) {
            // строка
            return new \DateTime($date);
        }

        return $date;

    }
}
