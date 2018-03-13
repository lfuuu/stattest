<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class TroubleState
 *
 * @property integer id
 * @property integer pk
 * @property string name
 * @property integer order
 * @property integer time_delta
 * @property integer folder
 * @property integer deny
 * @property string state_1c
 * @property integer oso
 * @property integer omo
 * @property integer is_final
 * @property integer is_in_popup
 */
class TroubleState extends ActiveRecord
{
    const CONNECT__INCOME = 41;
    const CONNECT__NEGOTIATION = 42;
    const CONNECT__VERIFICATION_OF_DOCUMENTS = 49;

    const CONNECT__TRASH = 47;

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
        return in_array($stateId, Trouble::dao()->getClosedStatesId());
    }

    /**
     * Проводится ли сумма счета на этой стадии
     *
     * @param integer $stateId
     * @return bool
     */
    public static function isStateWithApprovedSum($stateId)
    {
        $state = self::findOne(['id' => $stateId]);

        if (!$state) {
            return true;
        }

        return in_array($state->state_1c, ['Отгружен', 'КОтгрузке', 'Закрыт']);
    }
}

