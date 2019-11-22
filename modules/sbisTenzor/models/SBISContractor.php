<?php

namespace app\modules\sbisTenzor\models;

use app\classes\model\ActiveRecord;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Данные по контрагенту в системе СБИС
 *
 * @property integer $id
 * @property string $tin     Идентификационный номер налогоплательщика (ИНН) (для ЮЛ)
 * @property string $itn     Идентификационный номер налогоплательщика (ИНН) (для ФЛ и ИП)
 * @property string $iec     Код причины постановки (КПП)
 * @property string $full_name
 * @property string $email
 * @property string $phone
 * @property string $exchange_id
 * @property string $exchange_id_is
 * @property string $exchange_id_spp
 * @property integer $country_code
 * @property integer $is_private
 * @property string $inila   Страховой Номер Индивидуального Лицевого Счёта (СНИЛС)
 * @property string $last_name
 * @property string $first_name
 * @property string $middle_name
 *
 * @property string $created_at
 * @property string $updated_at
 */
class SBISContractor extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sbis_contractor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['full_name', 'is_private'], 'required'],
            [['country_code'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['tin'], 'string', 'max' => 10],
            [['itn'], 'string', 'max' => 12],
            [['iec'], 'string', 'max' => 36],
            [['full_name', 'email', 'phone', 'exchange_id_is', 'exchange_id_spp'], 'string', 'max' => 255],
            [['exchange_id'], 'string', 'max' => 46],
            [['inila'], 'string', 'max' => 15],
            [['last_name', 'first_name', 'middle_name'], 'string', 'max' => 60],
            [['tin', 'itn', 'iec', 'inila'], 'unique', 'targetAttribute' => ['tin', 'itn', 'iec', 'inila'], 'message' => 'The combination of Tin, Itn, Iec and Inila has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->db;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tin' => 'ИНН для ЮЛ',
            'itn' => 'ИНН для ФЛ и ИП',
            'iec' => 'КПП',
            'full_name' => 'Название',
            'email' => 'Email',
            'phone' => 'Телефон',
            'exchange_id' => 'Идентификатор',
            'exchange_id_is' => 'ИдентификаторИС',
            'exchange_id_spp' => 'ИдентификаторСПП',
            'country_code' => 'Код страны',
            'is_private' => 'Частное лицо',
            'inila' => 'СНИЛС',
            'last_name' => 'Фамилия',
            'first_name' => 'Имя',
            'middle_name' => 'Отчество',
            'created_at' => 'Добавлен',
            'updated_at' => 'Обновлён',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                // Установить "когда создал" и "когда обновил"
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression("UTC_TIMESTAMP()"), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
        ];
    }
}