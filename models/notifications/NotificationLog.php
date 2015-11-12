<?php
namespace app\models\notifications;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use app\models\ClientContact;

class NotificationLog extends ActiveRecord
{

    public function rules()
    {
        return [
            [['client_id'], 'integer', 'integerOnly' => true],
            ['date', 'date', 'format' => 'yyyy-MM-dd - yyyy-MM-dd'],
            ['event', 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'client_id' => 'Клиент',
            'date' => 'Когда произошло',
            'event' => 'Событие',
            'balance' => 'Баланс',
            'limit' => 'Лимит',
            'value' => 'Значение',
        ];
    }


    public static function tableName()
    {
        return 'notification_log';
    }

    public function behaviors()
    {
        return [
            'UpdateContactsList' => \app\classes\behaviors\NotificationLog::className(),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactLog()
    {
        return $this->hasMany(NotificationContactLog::className(), ['notification_id' => 'id']);
    }

    /**
     * @return static
     */
    public function getContacts()
    {
        return $this->hasMany(ClientContact::className(), ['id' => 'contact_id'])
            ->viaTable('notification_contact_log', ['notification_id' => 'id']);
    }

    /**
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find()->with('contacts')->orderBy('date DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->sort = false;

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'client_id' => $this->client_id,
            'event' => $this->event,
        ]);

        $query->andFilterWhere(array_merge(['between', 'date'], preg_split('#\s\-\s#', $this->date)));

        return $dataProvider;
    }

}