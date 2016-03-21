<?php
namespace app\models\message;

use yii\db\ActiveRecord;
use yii\db\Query;
use app\classes\Language;
use app\models\Language as LanguageModel;

class Template extends ActiveRecord
{

    const TYPE_EMAIL = 'email';
    const TYPE_EMAIL_INNER = 'email_inner';
    const TYPE_SMS = 'sms';

    public static $types = [
        self::TYPE_EMAIL => [
            'title' => 'Клиенту',
            'format' => 'file',
            'icon' => 'envelope',
        ],
        self::TYPE_SMS => [
            'title' => 'SMS',
            'format' => 'plain',
            'icon' => 'phone',
        ],
        self::TYPE_EMAIL_INNER => [
            'title' => 'Внутренний',
            'format' => 'html',
            'icon' => 'envelope',
        ],
    ];

    public static $languages = [
        'ru-RU' => 'Русский',
        'en-EN' => 'Английский',
        'hu-HU' => 'Венгерский',
    ];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['name', 'required'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Название',
        ];
    }

    public static function tableName()
    {
        return 'message_template';
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return Template::find();
    }

    public function getLanguage()
    {
        return $this->hasOne(LanguageModel::className(), ['code' => 'lang_code']);
    }

    /**
     * @throws \Exception
     */
    public function delete()
    {
        TemplateContent::deleteAll(['template_id' => $this->id]);
        parent::delete();
    }

    /**
     * @param $languageCode
     * @param $type
     * @return TemplateContent|null
     */
    public function getTemplateContent($languageCode, $type)
    {
        if ($templateContent =
                TemplateContent::findOne([
                    'template_id' => $this->id,
                    'lang_code' => $languageCode,
                    'type' => $type,
                ])
        ) {
            return $templateContent;
        }

        $templateContent = new TemplateContent;
        $templateContent->template_id = $this->id;

        return $templateContent;
    }

}