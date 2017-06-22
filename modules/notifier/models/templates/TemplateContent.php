<?php

namespace app\modules\notifier\models\templates;

use app\exceptions\ModelValidationException;
use Yii;
use yii\db\ActiveRecord;
use app\models\Language;
use app\models\Country;
use app\modules\notifier\components\templates\ContentMedia;

/**
 * @property int $country_id
 * @property int $template_id
 * @property int $lang_code
 * @property int $type
 * @property string $title
 * @property string $file
 *
 * @property-read ContentMedia $mediaManager
 */
class TemplateContent extends ActiveRecord
{

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['country_id', 'template_id',], 'integer'],
            ['lang_code', 'default', 'value' => Language::LANGUAGE_DEFAULT],
            ['lang_code', 'in', 'range' => array_keys(Language::getList())],
            ['type', 'in', 'range' => array_keys(Template::$types)],
            [['country_id', 'template_id', 'lang_code', 'type'], 'required'],
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
        return ['template_id', 'type', 'lang_code', 'country_id'];
    }

    /**
     * @return string
     */
    public function formName()
    {
        return parent::formName() . '[' . $this->formNameKey() . ']';
    }

    /**
     * @return string
     */
    public function formNameKey()
    {
        return implode(':', [$this->country_id, $this->template_id, $this->lang_code, $this->type]);
    }

    /**
     * @return ContentMedia
     */
    public function getMediaManager()
    {
        return new ContentMedia($this);
    }

    /**
     * @param boolean|true $runValidation
     * @param null|array $attributeNames
     * @return bool
     * @throws ModelValidationException
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $countries = Country::getList();
        $templateContentTypes = Template::$types;

        $filenameKey = $this->formNameKey() . '_filename';
        if (isset($_FILES[$filenameKey]) && ($fileContent = $this->mediaManager->addFile($_FILES[$filenameKey]))) {
            $this->filename = $fileContent->filename;
        }

        $saveResult = parent::save($runValidation, $attributeNames);

        if (!$saveResult) {
            Yii::$app->session->setFlash('error',
                    'Не сохранились данные (
                    Страна: ' . $countries[$this->country_id] . ',
                    Тип сообщения: ' . (
                        isset($templateContentTypes[$this->type])
                            ? $templateContentTypes[$this->type]['title']
                            : 'не определено'
                    ) . ',
                    Язык: ' . $this->lang_code . ')'
            );
        }

        return $saveResult;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        switch ($this->type) {
            case Template::CLIENT_CONTACT_TYPE_EMAIL: {
                return !$this->getMediaManager()->getFile($this->filename);
            }
            case Template::CLIENT_CONTACT_TYPE_PHONE: {
                return empty(trim($this->content));
            }
            case Template::CLIENT_CONTACT_TYPE_EMAIL_INNER: {
                return empty(trim(strip_tags($this->content)));
            }
        }

        return true;
    }

}
