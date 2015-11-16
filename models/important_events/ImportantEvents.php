<?php

namespace app\models\important_events;

use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;

class ImportantEvents extends ActiveRecord
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

    /**
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find()->orderBy('date DESC');

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