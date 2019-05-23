<?php

namespace app\classes\helpers;


use app\models\Param;
use app\models\Trouble;
use app\models\TroubleType;
use InvalidArgumentException;

class TTCounterHelper
{
    /**
     * Возвращает pk в бинарном виде. Длина строки зависит от максимального числа, один pk - один бит, расположение бита - сдвиг справа на величину равную pk.
     * Такая структура нужна для использования бинарной маски при отслеживании различных типов заявок, которые необходимо обновить.
     *
     * @param $pk
     * @return string
     */
    private static function _getPkBin($pk)
    {
        $pkMax = TroubleType::find()->select('max(pk)')->scalar();
        if (!is_numeric($pk) || $pk > $pkMax) {
            throw new InvalidArgumentException('Неправильный pk');
        }
        $pkBin = str_repeat('0', $pkMax);
        $pkBin[$pkMax - $pk] = 1;
        return $pkBin;
    }

    /**
     * @return Param
     */
    public static function getIsNeedRecalc()
    {
        return Param::findOne(['param' => Param::IS_NEED_RECALC_TT_COUNT]);
    }

    /**
     * Получить массив для работы со счетчиками
     *
     * @param array $select
     * @param null $troubleTypes
     * @return array
     */
    public static function getTroubleTypeData($select = ['pk', 'code'], $troubleTypes = null)
    {
        if (!$troubleTypes) {
            $troubleTypes = array_keys(Trouble::$types);
        }
        return TroubleType::find()
            ->select($select)
            ->where(['code' => $troubleTypes])
            ->asArray()
            ->all();
    }

    /**
     * @param $pk
     * @throws \app\exceptions\ModelValidationException
     */
    public static function addPkTroubleTypeToRecalc($pk)
    {
        $pkBin = static::_getPkBin($pk);
        $param = static::getIsNeedRecalc();
        $newValue = ($param && $param->value) ? (strval($param->value) | $pkBin) : $pkBin;
        Param::setParam(Param::IS_NEED_RECALC_TT_COUNT, $newValue, true);
    }

    /**
     * @param array $data
     * @param string $val
     * @return array
     */
    public static function filterTroubleData($data, $val)
    {
        if (!$data) {
            $data = static::getTroubleTypeData();
        }
        if (!isset(reset($data)['pk'])) {
            throw new InvalidArgumentException('pk not found');
        }
        return array_filter($data, function ($item) use ($val) {
            $pkBin = static::_getPkBin($item['pk']);
            return ($pkBin & ((string)$val)) === $pkBin;
        });
    }
}