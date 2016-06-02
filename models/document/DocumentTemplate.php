<?php
namespace app\models\document;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\classes\Smarty;
use app\models\ClientDocument;

class DocumentTemplate extends ActiveRecord
{
    const ZAKAZ_USLUG = 13;
    const DC_telefonia = 41;

    const DEFAULT_WIZARD_MCN = 102;
    const DEFAULT_WIZARD_EURO_LEGAL = 133;
    const DEFAULT_WIZARD_EURO_PERSON = 148;

    const DOCUMENT_FOLDER = 'glyphicon glyphicon-folder-close';
    const DOCUMENT_ICON = 'glyphicon glyphicon-file';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'document_template';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'content'], 'string'],
            ['folder_id', 'integer'],
            [['name', 'folder_id', ], 'required'],
            ['type', 'in', 'range' => array_keys(ClientDocument::$types)],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'type' => 'Тип документа',
            'folder_id' => 'Раздел',
            'id' => 'Шаблон',
            'name' => 'Название',
            'content' => 'Содержание',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFolder()
    {
        return $this->hasOne(DocumentFolder::className(), ['id' => 'folder_id']);
    }

    /**
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(
            self::find()->select(["id", "name"])->orderBy("name")->asArray()->all(),
            'id',
            'name'
        );
    }

    /**
     * @param bool|true $runValidation
     * @param null $attributeNames
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        $this->content = preg_replace_callback(
            '#\{[^\}]+\}#',
            function ($matches) {
                return preg_replace('#&[^;]+;#', '', strip_tags($matches[0]));
            },
            $this->content
        );

        try {
            $smarty = Smarty::init();
            $smarty->fetch('string:' . $this->content);
        } catch (\SmartyException $e) {
            Yii::$app->session->setFlash('error', 'Ошибка преобразования шаблона<br />' . $e->getMessage());
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Ошибка преобразования шаблона<br />' . $e->getMessage());
        }

        parent::save();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
    }

}
