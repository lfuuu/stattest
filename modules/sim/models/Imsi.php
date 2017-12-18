<?php

namespace app\modules\sim\models;

use app\classes\model\ActiveRecord;
use app\models\Number;
use yii\db\ActiveQuery;

/**
 * Профиль абонента на SIM-карте
 *
 * @property int $imsi IMSI = International Mobile Subscriber Identity
 * @property int $iccid Родительская SIM-карта. ICCID = Integrated Circuit Card Id
 * @property int $msisdn MSISDN = Mobile Subscriber Integrated Services Digital Number
 * @property int $did DID = Direct Inward Dialing
 * @property int $is_anti_cli Анти-АОН. АОН = CLI = Calling Line Identification
 * @property int $is_roaming
 * @property int $is_active
 * @property int $status_id
 * @property string $actual_from
 *
 * @property-read Card $card
 * @property-read Number $number
 * @property-read ImsiStatus $status
 *
 * @method static Imsi findOne($condition)
 * @method static Imsi[] findAll($condition)
 */
class Imsi extends ActiveRecord
{
    // Перевод названий полей модели
    use \app\classes\traits\AttributeLabelsTraits;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'sim_imsi';
    }

    /**
     * @return string[]
     */
    public static function primaryKey()
    {
        return ['imsi'];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['imsi', 'iccid'], 'required'],
            [['imsi', 'iccid', 'msisdn', 'did', 'is_active', 'is_anti_cli', 'is_roaming', 'status_id'], 'integer'],
            [['actual_from'], 'date', 'format' => 'php:Y-m-d'],
            ['did', 'default', 'value' => null], // иначе пустая строка получается, ибо в БД это поле varchar
            ['did', 'exist', 'skipOnError' => true, 'targetClass' => Number::className(), 'targetAttribute' => ['did' => 'number']],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                \app\classes\behaviors\HistoryChanges::className(),
            ]
        );
    }

    /**
     * @return ActiveQuery
     */
    public function getCard()
    {
        return $this->hasOne(Card::className(), ['iccid' => 'iccid']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNumber()
    {
        return $this->hasOne(Number::className(), ['number' => 'did']);
    }

    /**
     * @return ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(ImsiStatus::className(), ['id' => 'status_id']);
    }

    /**
     * Вернуть ID родителя
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->iccid;
    }

    /**
     * Установить ID родителя
     *
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->iccid = $parentId;
    }

    /**
     * Подготовка полей для исторических данных
     *
     * @param string $field
     * @param string $value
     * @return string
     */
    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {

            case 'did':
                if ($number = Number::findOne(['number' => $value])) {
                    return $number->getLink();
                }
                break;
        }

        return $value;
    }
}
