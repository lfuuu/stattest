<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\AccountEntry;

/**
 * Тип расчётной операции / документа
 *
 * @property integer $id
 * @property string $key
 * @property string $name
 * @property integer $is_convertible
 *
 * @property AccountEntry[] $accountEntries
 * @property Bill[] $bills
 * @property \app\modules\uu\models\Bill[] $uuBills
 */
class OperationType extends ActiveRecord
{
    const ID_PRICE = 1; // Доходный
    const ID_COST = 2; // Расходный
    const ID_CORRECTION = 3; // Корректировочный

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operation_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'name', 'is_convertible'], 'required'],
            [['key'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 255],
            [['is_convertible'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Текстовый ключ',
            'name' => 'Название',
            'is_convertible' => 'Конвертируется ли в счёт-фактуру',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBills()
    {
        return $this->hasMany(Bill::class, ['operation_type_id' => 'id']);
    }

    /**
     * @return bool
     */
    public static function isCorrection($id)
    {
        return $id == self::ID_CORRECTION;
    }
}
