<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Class ClientContactPersonal
 * @package app\models
 *
 * @property int $id
 * @property string $date
 * @property string $type_id
 * @property string $contract_id
 * @property string $contact
 * @property-read ClientContactType $type
 */
class ClientContactPersonal extends ActiveRecord
{

    public static function tableName()
    {
        return 'client_contact_personal';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'create_time',
                'updatedAtAttribute' => 'create_time',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            ['contract_id', 'required'],
            ['contract_id', 'integer'],
            ['type_id', 'in', 'range' => ClientContactType::find()->select('id')->column()],
            ['contact', 'required'],
        ];
    }

    public function getType()
    {
        return $this->hasOne(ClientContactType::className(), ['id' => 'type_id']);
    }
}
