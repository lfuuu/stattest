<?php

namespace app\modules\sim\classes;

use app\classes\Singleton;
use app\classes\traits\GetListTrait;
use app\models\Number;
use yii\db\ActiveQuery;


class VoipHlr extends Singleton
{
    const ID_MTT = 1;
    const ID_TELE2 = 2;

    const NAMES = [
        self::ID_MTT => 'МТТ',
        self::ID_TELE2 => 'TELE2',
    ];

    const QUERY = [
        self::ID_MTT => ['7958', '7931'],
        self::ID_TELE2 => ['7995'],
    ];

    public static function getList($isWithEmpty = false, $isWithNullAndNotNull = false)
    {
        return GetListTrait::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + self::NAMES;
    }

    /**
     * @param ActiveQuery $query
     * @param $hlrId
     */
    public function addAndWhereInQuery(ActiveQuery $query, $hlrId)
    {
        $mapQuery = $this->_mapQuery($hlrId);

        if (!$query) {
            return;
        }

        $query->andWhere($mapQuery);

    }

    private function _mapQuery($hlrId)
    {
        if (self::QUERY[$hlrId] === null) {
            return false;
        }

        $query = [];

        foreach (self::QUERY[$hlrId] as $mask) {
            $query[] = ['like', Number::tableName() . '.number', $mask . '%', false];
        }

        if (count($query) == 1) {
            $query = reset($query);
        } else {
            array_unshift($query, 'OR');
        }

        return $query;
    }

    /**
     * Проверяем принадлежность телефонного номера к HLR
     *
     * @param int $number
     * @param int $hlrId
     * @return bool
     */
    public function isNumberBelongHlr($number, $hlrId)
    {
        if (self::QUERY[$hlrId] === null) {
            return false;
        }

        foreach (self::QUERY[$hlrId] as $mask) {
            if (strpos($number, $mask) === 0) {
                return true;
            }
        }

        return false;
    }

}
