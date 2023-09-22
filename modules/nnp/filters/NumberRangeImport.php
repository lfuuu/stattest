<?php

namespace app\modules\nnp\filters;

use app\modules\nnp\models\Country;
use app\modules\nnp\models\NumberRange;

class NumberRangeImport extends NumberRange
{
    public $country_prefix = null;

    /**
     * Код (префикс) страны. Непустое. Целое число. Например, 7
     *
     * @param string|int|null $value
     * @param Country $country
     * @return bool
     */
    public function setCountryPrefix($value, Country $country)
    {
        if (
            $this->_checkNatural($value, $isEmptyAllowed = false)
            && ($prefixes = $country->getPrefixes())
        ) {
            foreach ($prefixes as $prefix) {
                if (strpos($prefix, (string)$value) === 0) {
                    $this->country_prefix = $value;
                    return true;
                }
            }
        }

        $this->addError('country_prefix');
        return false;
    }

    /**
     * NDC. Для ABC/DEF непустое, для других типов NDC - можно пустое. Целое число. Например, 495
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setNdc($value)
    {
        $origValue = $value;
        if ($this->_checkNatural($value, $isEmptyAllowed = true, $isConvertToInt = false)) {
            $this->ndc = $value;
            $this->ndc_str = $origValue;
            return true;
        }

        $this->addError('ndc');
        return false;
    }

    /**
     * Исходный тип NDC/Operator/Region
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setNdcTypeSource($value)
    {
        if ($this->_checkString($value)) {
            $this->ndc_type_source = $value;
            return true;
        }

        $this->addError('ndc_type_source');
        return false;
    }

    /**
     * ID типа NDC. Непустое. Целое число. 1 - geo, 2 - mobile, 3 - nomadic, 4 - freephone, 5 - premium, 6 - short code и пр.
     *
     * @param string|int|null $value
     * @param string[] $ndcTypeList
     * @return bool
     */
    public function setNdcTypeId($value, $ndcTypeList)
    {
        $message = 'Не натуральное число';
        if ($this->_checkNatural($value, $isEmptyAllowed = false)) {
            $message = sprintf(
                'Неизвестный id: %s. Возможные значения: %s',
                $value,
                $ndcTypeList ? implode(',', array_keys($ndcTypeList)) : '-'
            );
            if (isset($ndcTypeList[$value])) {
                $this->ndc_type_id = $value;
                return true;
            }
        }

        $this->addError('ndc_type_id', $message);
        return false;
    }

    /**
     * Диапазон с. Непустое. Строка (не число, чтобы не потерять ведущие нули!).
     * Не должно быть букв, пробелов или другого форматирования разрядов. Например, 0000000
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setNumberFrom($value)
    {
        if (
            $this->_checkNatural($value, $isEmptyAllowed = false, $isConvertToInt = false)
            && (strlen($value) >= 2 || (strlen($value) >= 1 && $this->ndc_type_id == 6))
        ) {
            $this->number_from = $value;
            return true;
        }

        $this->addError('number_from');
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
        if (
            $this->_checkNatural($value, $isEmptyAllowed = false, $isConvertToInt = false)
            && strlen((string)$this->number_from) === strlen((string)$value)
        ) {
            $this->number_to = $value;

            if ($this->number_from <= $this->number_to) {
                return true;
            } else {
                $this->addError('number_from');
            }
        }

        $this->addError('number_to');
        return false;
    }

    /**
     * Исходный регион. Можно пустое. Например, Алтайский край
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setRegionSource($value)
    {
        if ($this->_checkString($value)) {
            $this->region_source = $value;
            return true;
        }

        $this->addError('region_source');
        return false;
    }

    /**
     * Исходный город. Можно пустое. Например, Улан-Уде
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setCitySource($value)
    {
        if ($this->_checkString($value)) {
            $this->city_source = $value;
            return true;
        }

        $this->addError('city_source');
        return false;
    }

    /**
     * Исходный оператор. Можно пустое. Например, ПАО Мегафон
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setOperatorSource($value)
    {
        if ($this->_checkString($value)) {
            $this->operator_source = $value;
            return true;
        }

        $this->addError('operator_source', 'Не является строкой: ' . $value);
        return false;
    }

    /**
     * Дата принятия решения о выделении диапазона. Можно пустое. Можно этот и последующие столбцы вообще не указывать. Сохраняется, но не используется. Если указано, то должна быть дата в любом из форматов: ГГГГ.ММ.ДД (Excel-формат), ГГГГ-ММ-ДД (SQL-формат), ММ/ДД/ГГГГ (американский формат), ДД-ММ-ГГГГ (европейский формат). Например, 2016.12.31
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setDateResolution($value)
    {
        $value = trim($value);
        if (!$value) {
            $this->date_resolution = null;
            return true;
        }

        $value = str_replace('.', '-', $value); // ГГГГ.ММ.ДД преобразовать в ГГГГ-ММ-ДД. Остальные форматы strtotime распознает сам
        $dateTime = strtotime($value);
        if (!$dateTime) {
            $this->addError('date_resolution');
            return false;
        }

        $this->date_resolution = date('Y-m-d', $dateTime);
        return true;
    }

    /**
     * Комментарий или номер решения о выделении диапазона. Можно пустое. Можно этот и последующие столбцы вообще не указывать. Сохраняется, но не используется. Например, Приказ №12345/6
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setDetailResolution($value)
    {
        if ($this->_checkString($value)) {
            $this->detail_resolution = $value;
            return true;
        }

        $this->addError('detail_resolution');
        return false;
    }

    /**
     * Статус номера. Можно пустое. Можно этот и последующие столбцы вообще не указывать.
     * Сохраняется, но не используется. Например, Зарезервировано для спецслужб
     *
     * @param string|int|null $value
     * @return bool
     */
    public function setStatusNumber($value)
    {
        if ($this->_checkString($value)) {
            $this->status_number = $value;
            return true;
        }

        $this->addError('status_number');
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
    private function _checkNatural(&$value, $isEmptyAllowed, $isConvertToInt = true)
    {
        $value = trim($value);
        if (!$value && $value !== '0') {
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
    private function _checkString(&$value)
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
            $this->ndc,
            $this->ndc_str,
            $this->number_from,
            $this->number_to,
            $this->ndc_type_id,
            $this->operator_source,
            $this->region_source,
            $this->city_source,
            $this->country_prefix . $this->ndc_str . $this->number_from, // full_number_from
            $this->country_prefix . $this->ndc_str . $this->number_to, // full_number_to
            $this->date_resolution,
            $this->detail_resolution,
            $this->status_number,
            $this->ndc_type_source,
        ];

    }
}
