<?php
namespace app\models\message;

use yii\db\ActiveRecord;
use app\classes\Language;
use app\models\Language as LanguageModel;

class TemplateContent extends ActiveRecord
{

    const TYPE_EMAIL = 'email';
    const TYPE_SMS = 'sms';

    public static $types = [
        self::TYPE_EMAIL => [
            'title' => 'E-mail',
            'format' => 'html',
        ],
        self::TYPE_SMS => [
            'title' => 'SMS',
            'format' => 'plain',
        ],
    ];

    public function rules()
    {
        return [
            [['template_id'], 'required'],
            [['lang_code', 'title', 'content'], 'string'],
            ['lang_code', 'default', 'value' => Language::DEFAULT_LANGUAGE],
            ['lang_code', 'in', 'range' => array_keys(LanguageModel::getList())],
            ['type', 'in', 'range' => array_keys(self::$types)],
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => 'Тип',
            'lang_code' => 'Язык',
            'title' => 'Тема',
            'content' => 'Содержание',
        ];
    }

    public static function tableName()
    {
        return 'message_template_content';
    }

}