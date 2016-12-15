<?php
namespace app\models\billing;

use Yii;
use yii\db\ActiveRecord;

class DisconnectCause extends ActiveRecord
{
    // Определяет getList (список для selectbox) и __toString
    use \app\classes\traits\GetListTrait;

    const UNALLOCATED = 1;
    const NO_ROUTE_TRANSIT_NET = 2;
    const NO_ROUTE_DESTINATION = 3;
    const CHANNEL_UNACCEPTABLE = 6;
    const CALL_AWARDED_DELIVERED = 7;
    const NORMAL_CLEARING = 16;
    const USER_BUSY = 17;
    const NO_USER_RESPONSE = 18;
    const NO_ANSWER = 19;
    const SUBSCRIBER_ABSENT = 21;
    const CALL_REJECTED = 22;
    const DESTINATION_OUT_OF_ORDER = 27;
    const INVALID_NUMBER_FORMAT = 28;
    const FACILITY_REJECTED = 29;
    const RESPONSE_TO_STATUS_ENQUIRY = 30;
    const NORMAL_UNSPECIFIED = 31;
    const NORMAL_CIRCUIT_CONGESTION = 34;
    const NETWORK_OUT_OF_ORDER = 38;
    const NORMAL_TEMPORARY_FAILURE = 41;
    const SWITCH_CONGESTION = 42;
    const ACCESS_INFO_DISCARDED = 43;
    const REQUESTED_CHAN_UNAVAIL = 44;
    const PRE_EMPTED = 45;
    const FACILITY_NOT_SUBSCRIBED = 50;
    const OUTGOING_CALL_BARRED = 52;
    const INCOMING_CALL_BARRED = 54;
    const BEARERCAPABILITY_NOTAUTH = 57;
    const BEARERCAPABILITY_NOTAVAIL = 58;
    const BEARERCAPABILITY_NOTIMPL = 65;
    const CHAN_NOT_IMPLEMENTED = 66;
    const FACILITY_NOT_IMPLEMENTED = 69;
    const INVALID_CALL_REFERENCE = 81;
    const INCOMPATIBLE_DESTINATION = 88;
    const INVALID_MSG_UNSPECIFIED = 95;
    const MANDATORY_IE_MISSING = 96;
    const MESSAGE_TYPE_NONEXIST = 97;
    const WRONG_MESSAGE = 98;
    const IE_NONEXIST = 99;
    const INVALID_IE_CONTENTS = 100;
    const WRONG_CALL_STATE = 101;
    const RECOVERY_ON_TIMER_EXPIRE = 102;
    const MANDATORY_IE_LENGTH_ERROR = 103;
    const PROTOCOL_ERROR = 111;
    const INTERWORKING = 127;

    public static $successCodes = [
        self::NORMAL_CLEARING,
        self::USER_BUSY,
        self::NO_USER_RESPONSE,
        self::NO_ANSWER,
        self::SUBSCRIBER_ABSENT,
        self::NORMAL_UNSPECIFIED
    ];

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.disconnect_cause';
    }

    /**
     * @return []
     */
    public static function getDb()
    {
        return Yii::$app->dbPgSlave;
    }

    /**
     * Вернуть список всех доступных моделей
     * @param bool $isWithEmpty
     * @param bool $isWithNullAndNotNull
     * @return self[]
     */
    public static function getList($isWithEmpty = false, $isWithNullAndNotNull = false)
    {
        $list = self::find()
            ->orderBy(self::getListOrderBy())
            ->indexBy('cause_id')
            ->all();

        return self::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $list;
    }

    /**
     * По какому полю сортировать для getList()
     * @return []
     */
    public static function getListOrderBy()
    {
        return ['cause_id' => SORT_ASC];
    }

    /**
     * Преобразовать объект в строку
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

}