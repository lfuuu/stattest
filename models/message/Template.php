<?php
namespace app\models\message;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\models\important_events\ImportantEventsNames;
use app\classes\behaviors\message\MessageTemplateEvent;

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

    public $event;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            [['name',], 'required'],
            ['event', 'in', 'range' => ArrayHelper::getColumn(ImportantEventsNames::find()->select('code')->asArray()->each(), 'code')]
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'event' => 'Событие',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'Template' => MessageTemplateEvent::className(),
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

    /**
     * @return \yii\db\ActiveQuery|null
     */
    public function getEvent()
    {
        return TemplateEvents::findOne(['template_id' => $this->id]);
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