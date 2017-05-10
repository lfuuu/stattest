<?php

namespace app\modules\notifier\models\templates;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use app\models\important_events\ImportantEventsNames;
use app\modules\notifier\behaviors\templates\TemplateEvent;

/**
 * @property int $id
 * @property string $name
 * @property string $event
 */
class Template extends ActiveRecord
{

    const CLIENT_CONTACT_TYPE_EMAIL = 'email';
    const CLIENT_CONTACT_TYPE_PHONE = 'phone';
    const CLIENT_CONTACT_TYPE_EMAIL_INNER = 'email_inner';

    public static $types = [
        self::CLIENT_CONTACT_TYPE_EMAIL => [
            'title' => 'Клиенту',
            'format' => 'file',
            'icon' => 'envelope',
        ],
        self::CLIENT_CONTACT_TYPE_PHONE => [
            'title' => 'SMS',
            'format' => 'plain',
            'icon' => 'phone',
        ],
        self::CLIENT_CONTACT_TYPE_EMAIL_INNER => [
            'title' => 'Внутренний',
            'format' => 'html',
            'icon' => 'envelope',
        ],
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
            [
                'event',
                'in',
                'range' => ArrayHelper::getColumn(ImportantEventsNames::find()->select('code')->asArray()->all(), 'code')
            ]
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
            'Template' => TemplateEvent::className(),
        ];
    }

    /**
     * @return string
     */
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
     * @param int $countryId
     * @param string $languageCode
     * @param string $contentType
     * @return TemplateContent|null
     */
    public function getTemplateContent($countryId, $languageCode, $contentType)
    {
        if ($templateContent = TemplateContent::findOne([
                'country_id' => $countryId,
                'template_id' => $this->id,
                'lang_code' => $languageCode,
                'type' => $contentType,
            ])
        ) {
            return $templateContent;
        }

        $templateContent = new TemplateContent;
        $templateContent->template_id = $this->id;
        $templateContent->country_id = $countryId;
        $templateContent->lang_code = $languageCode;
        $templateContent->type = $contentType;

        return $templateContent;
    }

}
