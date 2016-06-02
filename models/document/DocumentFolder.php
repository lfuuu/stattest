<?php
namespace app\models\document;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class DocumentFolder extends ActiveRecord
{

    public
        $parentIdField = 'parent_id',
        $textField = 'name',
        $orderBy = [
            'parent_id' => SORT_DESC,
            'name' => SORT_ASC,
            'sort' => SORT_DESC,
        ];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name',], 'string'],
            [['name',], 'required'],
            [['parent_id', 'sort'], 'integer'],
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
            'sort' => 'Позиция в ветке',
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
     * @return array
     */
    public static function getList()
    {
        return ArrayHelper::map(self::find()->orderBy('sort')->all(), 'id', 'name');
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
        return $this->hasMany(DocumentTemplate::className(), ['folder_id' => 'id']);
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
     */
    public function populateTreeForWidget($data = null, $withDocuments = true)
    {
        if (!is_array($data)) {
            $data = static::find()
                ->where([$this->parentIdField => 0])
                ->orderBy($this->orderBy)
                ->all();
        }

        $result = [];

        foreach ($data as $row) {
            $resultRow = [
                'label' => (string) $row,
                'id' => $row->id,
                'children' => [],
            ];

            if ($row instanceof DocumentTemplate) {
                $resultRow['icon'] = DocumentTemplate::DOCUMENT_ICON;
                $resultRow['href'] = Url::toRoute(['/document/template/edit', 'id' => $row->id]);
            }

            if ($row instanceof self) {
                $resultRow['icon'] = DocumentTemplate::DOCUMENT_FOLDER;
                $resultRow['href'] = Url::toRoute(['/document/folder/edit', 'id' => $row->id]);

                if ($withDocuments === true && count($row->documents)) {
                    $resultRow['children'] = array_merge($resultRow['children'], (array) $this->populateTreeForWidget($row->documents, $withDocuments));
                }

                if (count($row->childs)) {
                    $resultRow['children'] = array_merge($resultRow['children'], (array) $this->populateTreeForWidget($row->childs, $withDocuments));
                }
            }

            $result[] = $resultRow;
        }

        return $result;
    }

}
