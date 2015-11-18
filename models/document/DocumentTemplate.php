<?php
namespace app\models\document;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\classes\Smarty;
use app\models\ClientDocument;

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

    public function save($runValidation = true, $attributeNames = null)
    {
        $this->content =  preg_replace_callback(
            '#\{[^\}]+\}#',
            function($matches) {
                return preg_replace('#&[^;]+;#', '', strip_tags($matches[0]));
            },
            $this->content
        );

        try {
            $smarty = Smarty::init();
            $smarty->fetch('string:' . $this->content);
        }
        catch (\SmartyException $e) {
            Yii::$app->session->setFlash('error', 'Ошибка преобразования шаблона<br />' . $e->getMessage());
        }
        catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Ошибка преобразования шаблона<br />' . $e->getMessage());
        }

        parent::save();
    }

}
