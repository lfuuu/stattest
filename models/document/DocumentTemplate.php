<?php
namespace app\models\document;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\classes\Smarty;
use app\models\ClientDocument;

/**
 * @property int id
 * @property string name
 * @property int folder_id
 * @property string content
 * @property string type
 * @property int sort
 *
 * Class DocumentTemplate
 * @package app\models\document
 */
class DocumentTemplate extends ActiveRecord
{
    const ZAKAZ_USLUG = 13;
    const DC_telefonia = 41;

    const DEFAULT_WIZARD_MCN = 102;
    const DEFAULT_WIZARD_EURO_LEGAL = 133;
    const DEFAULT_WIZARD_EURO_PERSON = 148;

    const DEFAULT_WIZARD_MCN_LEGAL_LEGAL = 158;
    const DEFAULT_WIZARD_MCN_LEGAL_PERSON = 159;

    const DOCUMENT_ICON_FOLDER = 'glyphicon glyphicon-folder-close';
    const DOCUMENT_ICON_CONTRACT = 'glyphicon glyphicon-book';
    const DOCUMENT_ICON_AGREEMENT = 'glyphicon glyphicon-duplicate';
    const DOCUMENT_ICON_BLANK = 'glyphicon glyphicon-file';

    public static $documentIcons = [
        ClientDocument::DOCUMENT_CONTRACT_TYPE => self::DOCUMENT_ICON_CONTRACT,
        ClientDocument::DOCUMENT_AGREEMENT_TYPE => self::DOCUMENT_ICON_AGREEMENT,
        ClientDocument::DOCUMENT_BLANK_TYPE => self::DOCUMENT_ICON_BLANK,
    ];

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
            [['folder_id', 'sort',], 'integer'],
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
            'sort' => 'Приоритет',
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
     * @return string|false
     */
    public function getIcon()
    {
        if (array_key_exists($this->type, self::$documentIcons)) {
            return self::$documentIcons[$this->type];
        }
        return false;
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