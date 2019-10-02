<?php

namespace app\models\dictionary;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property int $form_url
 * @property-readonly FormFieldInfo $info
 */
class FormInfo extends ActiveRecord
{
    public static function tableName()
    {
        return 'form_info';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['form_url'], 'string'],
            [['form_url',], 'required'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'form_url' => 'URL',
        ];
    }

    public function getInfo()
    {
        return $this->hasMany(FormInfoData::class, ['form_id' => 'id']);
    }
}