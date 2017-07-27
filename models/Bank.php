<?php

namespace app\models;

use app\classes\model\ActiveRecord;

class Bank extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'bik';
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @return string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false
    ) {
        return self::getListTrait(
            $isWithEmpty,
            $isWithNullAndNotNull,
            $indexBy = 'bik',
            $select = 'CONCAT(bik, " (", bank_name, ")")',
            $orderBy = ['bik' => SORT_ASC],
            $where = []
        );
    }
}