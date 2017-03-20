<?php
namespace app\models\media;

use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use app\models\User;
use app\models\ClientContract;
use app\classes\validators\ArrayValidator;
use app\classes\media\ClientMedia;
use app\classes\traits\TagsTrait;

/**
 * @property int $id
 * @property int $contract_id
 * @property int $user_id
 * @property string $ts
 * @property string $comment
 * @property string $name
 */
class ClientFiles extends ActiveRecord
{

    use TagsTrait;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_files';
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'filename' => 'Файл',
            'name' => 'Имя файла',
            'user' => 'Кто загрузил',
            'comment' => 'Комментарий',
            'ts' => 'Дата загрузки',
            'tags_filter' => 'Метки',
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['tags_filter', ArrayValidator::className(), 'on' => 'default'],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContract()
    {
        return $this->hasOne(ClientContract::className(), ['id' => 'contract_id']);
    }

    /**
     * @return ClientMedia
     */
    public function getMediaManager()
    {
        $contract = ClientContract::findOne(['id' => $this->contract_id]);
        return new ClientMedia($contract);
    }

    /**
     * @param string $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $this->_applyTags();
        }
    }

    /**
     * @param int $contractId
     * @return ActiveDataProvider
     */
    public function search($contractId)
    {
        $query = self::find()
            ->where(['contract_id' => $contractId]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => false,
        ]);

        if (!($this->load(\Yii::$app->request->get()) && $this->validate())) {
            return $dataProvider;
        }

        $this->setTagsFilter($query);

        return $dataProvider;
    }

}
