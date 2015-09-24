<?php
namespace app\models\document;

use app\models\ClientDocument;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class DocumentTemplate extends ActiveRecord
{

    public static function tableName()
    {
        return 'document_template';
    }

    public function rules()
    {
        return [
            [['name', 'content'], 'string'],
            ['folder_id', 'integer'],
            ['type', 'in', 'range' => array_keys(ClientDocument::$types)],
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => 'Тип документа',
            'folder_id' => 'Папка',
            'id' => 'Шаблон',
            'name' => 'Название',
            'content' => 'Контент',
        ];
    }

    public function getFolder()
    {
        return $this->hasOne(DocumentFolder::className(), ['id' => 'folder_id']);
    }

    public static function getList()
    {
        return ArrayHelper::map(self::find()->select("id", "name")->orderBy("name")->all(), 'id', 'name');
    }
}
