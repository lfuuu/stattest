<?php

namespace app\models;

use app\classes\model\ActiveRecord;

class TroubleState extends ActiveRecord
{
    public static $closedStates = [2, 7, 8, 20, 21, 39, 40, 48];

    const CONNECT__INCOME = 41;
    const CONNECT__NEGOTIATION = 42;
    const CONNECT__VERIFICATION_OF_DOCUMENTS = 49;

    public static function tableName()
    {
        return 'tt_states';
    }

    /**
     * Закрывающая стадия?
     *
     * @param integer $stateId
     * @return bool
     */
    public static function isClose($stateId)
    {
        return in_array($stateId, self::$closedStates);
    }
}

