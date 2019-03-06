<?php
namespace app\models;
use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property integer $number
 * @property string $fields
 */
class RoistatNumberFields extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'roistat_number_fields';
    }


    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['number', 'integer'],
            ['fields', 'string'],
            ['number', 'required']
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'number' => 'Номер',
            'fields' => 'Аттрибуты'
        ];
    }
}
