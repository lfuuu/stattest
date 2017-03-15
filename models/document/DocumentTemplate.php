<?php
namespace app\models\document;

use app\classes\Smarty;
use app\models\ClientDocument;
use Yii;
use yii\db\ActiveRecord;

/**
 * @property int id
 * @property string name
 * @property int folder_id
 * @property string content
 * @property string type
 * @property int sort
 */
class DocumentTemplate extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait;

    const ZAKAZ_USLUG = 13;
    const DC_TELEFONIA = 41;

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
            [['name', 'folder_id',], 'required'],
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
     * @return bool
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

        return parent::save();
    }
}