<?php

namespace app\models\dictionary;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property int $form_id
 * @property int $key
 * @property int $url
 */
class FormInfoData extends ActiveRecord
{
    public static function tableName()
    {
        return 'form_info_data';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['form_id', 'key', 'url',], 'required'],
            [['key', 'url'], 'string'],
            [['form_id'], 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'url' => 'URL',
        ];
    }
}