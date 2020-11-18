<?php

namespace app\modules\nnp2\filters;

use app\helpers\DateTimeZoneHelper;
use app\modules\nnp\models\Country;
use app\modules\nnp2\models\NumberRange;

class NumberRangeImport extends NumberRange
{
    public static $nowString = null;

    public $ndc = null;
    public $country_prefix = null;

    public static function resetNowString()
    {
        self::$nowString = null;
    }

    protected function getNowString()
    {
        if (!self::$nowString) {
            self::$nowString = (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        }

        return self::$nowString;
    }

    /**
     * Код (префикс) страны. Непустое. Целое число. Например, 7
     *
     * @param string|int|null $value
     * @param Country $country
     * @return bool
     */
    public function setCountryPrefix($value, Country $country)
    {
        $this->country_code = $country->code;

        $errorMessage = 'Не числовое значение';
        if (
            $this->checkNatural($value, $isEmptyAllowed = false)
            && ($prefixes = $country->getPrefixes())
        ) {
            $errorMessage = 'Неизвестное значение';
            foreach ($prefixes as $prefix) {
                if (strpos($prefix, (string)$value) === 0) {
                    $this->country_prefix = $value;
                    return true;
                }
            }
        }

        $this->addError('country_prefix', $errorMessage);
        return false;
    }

    /**
     * ID местоположения. Непустое.
     *
     * @param $ndc string
     * @param $region string
     * @param $city string
     * @param string[] $geoPlacesList
     * @return bool
     */
    public function setGeoPlaceId($ndc, $region, $city, $geoPlacesList)
    {
        $errorMessage = 'Местоположение не найдено';
        if (
            isset($geoPlacesList[$ndc][$region][$city])
        ) {
            $this->geo_place_id = $geoPlacesList[$ndc][$region][$city];
            $this->ndc = $ndc;
            return true;
        }

        $this->addError('geo_place_id', $errorMessage);
        return false;
    }

    /**
     * ID типа NDC. Непустое. Целое число. 1 - geo, 2 - mobile, 3 - nomadic, 4 - freephone, 5 - premium, 6 - short code и пр.
     *
     * @param string $value
     * @param string[] $ndcTypeList
     * @return bool
     */
    public function setNdcTypeId($value, $ndcTypeList)
    {
        $errorMessage = 'Неизвестный тип NDC';
        if (
            $this->checkString($value)
            && isset($ndcTypeList[$value])
        ) {
            $this->ndc_type_id = $ndcTypeList[$value];
            return true;
        }

        $this->addError('ndc_type_id', $errorMessage);
        return false;
    }

    /**
     * ID оператора. Можно пустое
     *
     * @param string $value
     * @param string[] $operatorList
     * @return bool
     */
    public function setOperatorId($value, $operatorList)
    {
        if (
            $this->checkString($value)
            && isset($operatorList[$value])
        ) {
            $this->operator_id = $operatorList[$value];
            return true;
        }

        $this->addError('operator_id');
        return false;
    }

    /**
     * Диапазон с. Непустое. Строка (не число, чтобы не потерять ведущие нули!). Не должно быть букв, пробелов или другого форматирования разрядов. Например, 0000000
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setNumberFrom($value)
    {
        $errorMessage = 'Не числовое значение';
        if (
            $this->checkNatural($value, $isEmptyAllowed = false, $isConvertToInt = false)
            && strlen($value) >= 2
        ) {
            $this->number_from = $value;
            return true;
        }

        $this->addError('number_from', $errorMessage);
        return false;
    }

    /**
     * Диапазон по. Непустое. Строка (не число, чтобы не потерять ведущие нули!). Не должно быть букв, пробелов или другого форматирования разрядов. Кол-во цифр должно быть таким же, как у предыдущего поля. Например, 0009999
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setNumberTo($value)
    {
        $errorMessage = 'Не числовое значение';
        if (
            $this->checkNatural($value, $isEmptyAllowed = false, $isConvertToInt = false)
            && strlen($this->number_from) === strlen($value)
        ) {
            $this->number_to = $value;
            return true;
        }

        $this->addError('number_to', $errorMessage);
        return false;
    }

    /**
     * Дата принятия решения о выделении диапазона. Можно пустое. Можно этот и последующие столбцы вообще не указывать. Сохраняется, но не используется. Если указано, то должна быть дата в любом из форматов: ГГГГ.ММ.ДД (Excel-формат), ГГГГ-ММ-ДД (SQL-формат), ММ/ДД/ГГГГ (американский формат), ДД-ММ-ГГГГ (европейский формат). Например, 2016.12.31
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setAllocationDateStart($value)
    {
        $value = trim($value);
        if (!$value) {
            $this->allocation_date_start = null;
            return true;
        }

        $value = str_replace('.', '-', $value); // ГГГГ.ММ.ДД преобразовать в ГГГГ-ММ-ДД. Остальные форматы strtotime распознает сам
        $dateTime = strtotime($value);
        if (!$dateTime) {
            $errorMessage = 'Несуществующая дата';
            $this->addError('allocation_date_start', $errorMessage);
            return false;
        }

        $this->allocation_date_start = date('Y-m-d', $dateTime);
        return true;
    }

    /**
     * Комментарий или номер решения о выделении диапазона. Можно пустое. Можно этот и последующие столбцы вообще не указывать. Сохраняется, но не используется. Например, Приказ №12345/6
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setAllocationReason($value)
    {
        $errorMessage = 'Не текстовое значение';
        if ($this->checkString($value)) {
            $this->allocation_reason = $value;
            return true;
        }

        $this->addError('allocation_reason', $errorMessage);
        return false;
    }

    /**
     * Статус номера. Можно пустое. Можно этот и последующие столбцы вообще не указывать.
     * Сохраняется, но не используется. Например, Зарезервировано для спецслужб
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setComment($value)
    {
        $errorMessage = 'Не текстовое значение';
        if ($this->checkString($value)) {
            $this->comment = $value;
            return true;
        }

        $this->addError('comment', $errorMessage);
        return false;
    }

    /**
     * Проверить, что значение является натуральным числом
     *
     * @param string|int|null $value Пустое привести к null, непустое к int
     * @param bool $isEmptyAllowed Что возвращать для пустых
     * @param bool $isConvertToInt
     * @return bool
     */
    protected function checkNatural(&$value, $isEmptyAllowed, $isConvertToInt = true)
    {
        $value = trim($value);
        if (!$value) {
            $value = null;
            return $isEmptyAllowed;
        }

        if (!preg_match('/^\d+$/', $value)) {
            return false;
        }

        if ($isConvertToInt) {
            $value = (int)$value;
        }

        return true;
    }

    /**
     * Проверить, что значение является строкой. Можно пустой
     *
     * @param string $value
     * @return bool
     */
    protected function checkString(&$value)
    {
        $value = trim($value);
        return $value === '' || !is_numeric($value);
    }

    /**
     * @return array
     */
    public function getSqlData()
    {
        return [
            $this->country_code,
            $this->geo_place_id,
            $this->ndc_type_id,
            $this->operator_id,
            $this->number_from,
            $this->number_to,
            $this->country_prefix . $this->ndc . $this->number_from, // full_number_from
            $this->country_prefix . $this->ndc . $this->number_to, // full_number_to
            min(max($this->number_to - $this->number_from + 1,1), 499999999),
            true, //is_valid
            $this->allocation_reason,
            $this->allocation_date_start,
            $this->comment,
            $this->getNowString(),
        ];

    }
}
