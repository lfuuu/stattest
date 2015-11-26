<?php
namespace app\models\message;

use yii\db\ActiveRecord;
use yii\db\Query;
use app\classes\Language;
use app\models\Language as LanguageModel;

class Template extends ActiveRecord
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
            ['id', 'integer'],
            ['name', 'required'],
        ];
    }

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

    public function delete()
    {
        TemplateContent::deleteAll(['template_id' => $this->id]);
        parent::delete();
    }

}