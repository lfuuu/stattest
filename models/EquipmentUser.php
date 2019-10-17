<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\classes\validators\FormFieldValidator;
use app\helpers\DateTimeZoneHelper;
use yii\behaviors\TimestampBehavior;

/**
 * @property int $id
 * @property int $client_account_id
 * @property string $full_name
 * @property string $birth_date
 * @property string $passport
 * @property string $passport_ext
 * @property string $created_at
 * @property string $updated_at
 */
class EquipmentUser extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'equipment_user';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => function () {
                    return (new \DateTime())->format(DateTimeZoneHelper::DATETIME_FORMAT);
                }
            ],
            \app\classes\behaviors\HistoryChanges::class,
        ];
    }

    public function rules()
    {
        return [
            [['birth_date', 'full_name', 'passport', 'passport_ext'], 'required'],
            [['birth_date', 'full_name', 'passport', 'passport_ext'], 'string'],
            [['birth_date', 'full_name', 'passport', 'passport_ext'], FormFieldValidator::class],
            ['birth_date', 'date', 'format' => 'Y-m-d']
        ];
    }

    public function getParentId()
    {
        return $this->client_account_id;
    }
}
