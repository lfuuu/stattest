<?php

namespace app\models\document;

use app\classes\model\ActiveRecord;
use app\models\ClientDocument;
use yii\base\InvalidParamException;
use yii\helpers\Url;

class DocumentFolder extends ActiveRecord
{

    const WIZARD_CONTRACT_FOLDER_ID = 12; // Папка с договорами для wizard'a

    public
        $parentIdField = 'parent_id',
        $textField = 'name';

    public static
        $orderBy = [
        'parent_id' => SORT_DESC,
        'sort' => SORT_DESC,
        'name' => SORT_ASC,
    ];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name',], 'string'],
            [['name',], 'required'],
            [['parent_id', 'default_for_business_id', 'sort'], 'integer'],
            [['parent_id', 'sort',], 'default', 'value' => 0],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'parent_id' => 'Раздел',
            'default_for_business_id' => 'Бизнес-процесс по-умолчанию',
            'sort' => 'Приоритет',
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'document_folder';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChilds()
    {
        return static::findAll([$this->parentIdField => $this->id]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(DocumentTemplate::class, ['folder_id' => 'id'])->orderBy([
            'sort' => SORT_DESC,
            'name' => SORT_ASC,
        ]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->{$this->textField};
    }

    /**
     * @param null|array $data
     * @param bool|true $withDocuments
     * @return array
     * @throws InvalidParamException
     */
    public function populateTreeForWidget($data = null, $withDocuments = true)
    {
        if (!is_array($data)) {
            $data = static::find()
                ->where([$this->parentIdField => 0])
                ->orderBy(self::$orderBy)
                ->all();
        }

        $result = [];

        foreach ($data as $row) {
            $resultRow = [
                'label' => (string)$row,
                'id' => $row->id,
                'children' => [],
            ];

            if ($row instanceof DocumentTemplate) {
                $resultRow['icon'] = $row->icon;
                $resultRow['iconTitle'] = ClientDocument::$types[$row->type];
                $resultRow['href'] = Url::toRoute(['/templates/document/template/edit', 'id' => $row->id]);
            }

            if ($row instanceof self) {
                $resultRow['icon'] = DocumentTemplate::DOCUMENT_ICON_FOLDER;
                $resultRow['href'] = Url::toRoute(['/templates/document/folder/edit', 'id' => $row->id]);

                if ($withDocuments === true && count($row->documents)) {
                    $resultRow['children'] = array_merge($resultRow['children'], (array)$this->populateTreeForWidget($row->documents, $withDocuments));
                }

                if (count($row->childs)) {
                    $resultRow['children'] = array_merge($resultRow['children'], (array)$this->populateTreeForWidget($row->childs, $withDocuments));
                }
            }

            $result[] = $resultRow;
        }

        return $result;
    }

}
