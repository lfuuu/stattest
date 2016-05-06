<?php
namespace app\models\message;

use yii\db\ActiveRecord;
use app\classes\Language;
use app\classes\media\TemplateContentMedia;

class TemplateContent extends ActiveRecord
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['lang_code', 'default', 'value' => Language::DEFAULT_LANGUAGE],
            ['lang_code', 'in', 'range' => array_keys(Template::$languages)],
            ['type', 'in', 'range' => array_keys(Template::$types)],
            [['template_id', 'lang_code', 'type'], 'required'],
            [['title', 'content'], 'string'],
            [['title', 'content'], 'trim'],
            [
                'filename',
                'file',
                'checkExtensionByMimeType' => false,
                'extensions' => 'htm, html',
                'mimeTypes' => ['text/html', 'text/plain']
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'type' => 'Тип',
            'lang_code' => 'Язык',
            'title' => 'Тема',
            'content' => 'Содержание',
            'filename' => 'Файл с содержанием'
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'message_template_content';
    }

    /**
     * @return array
     */
    public static function primaryKey()
    {
        return ['template_id', 'type', 'lang_code'];
    }

    /**
     * @return TemplateContentMedia
     */
    public function getMediaManager()
    {
        return new TemplateContentMedia($this);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        switch ($this->type) {
            case 'email': {
                return !$this->getMediaManager()->getFile($this->filename);
            }
            case 'sms': {
                return empty(trim($this->content));
            }
            case 'email_inner': {
                return empty(trim(strip_tags($this->content)));
            }
        }
        return true;
    }

}