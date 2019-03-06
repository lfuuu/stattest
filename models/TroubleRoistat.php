<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use yii\base\InvalidParamException;

/**
 * Class TroubleRoistat
 *
 * @property int $id
 * @property int $trouble_id
 * @property string $roistat_visit
 * @property float $roistat_price
 * @property int $roistat_channel_id
 * @property string $roistat_fields
 *
 * @property-read Trouble $trouble
 */
class TroubleRoistat extends ActiveRecord
{
    const CHANNEL_LK = 5;
    const CHANNEL_PHONE = 6;

    const CHANNELS = [
        0 => '-- Не заполненно -- ',
        1 => 'Сарафанное радио',
        2 => 'Чатофон',
        3 => 'E-mail',
        4 => 'Исходящий звонок',
        5 => 'LK',
        6 => 'Прямой звонок',
    ];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['trouble_id'], 'integer'],
            ['roistat_visit', 'string'],
            [['roistat_price',], 'double'],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tt_troubles_roistat';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrouble()
    {
        return $this->hasOne(Trouble::class, ['id' => 'trouble_id']);
    }

    /**
     * Выдаем имя канала по ID
     *
     * @param integer $id
     * @param string $addition
     * @return string
     */
    public static function getChannelNameById($id)
    {
        if (!array_key_exists($id, self::CHANNELS)) {
            throw new \InvalidArgumentException('неизвестный канал: ' . $id);
        }

        return self::CHANNELS[$id];
    }
}
