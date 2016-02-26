<?php
namespace app\forms\usage;

use app\models\TariffCallChat;
use app\models\UsageCallChat;
use yii\db\Query;

class UsageCallChatListForm extends UsageCallChatForm
{
    public $client = null;

    public function spawnQuery()
    {
        return UsageCallChat::find()
            ->with('tariff')
            ->orderBy(['id' => SORT_ASC]);
    }


    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'client' => 'Клиент',
            'actual_from' => 'Дата подключения',
            'actual_to' => 'Дата отключения',
            'tarif_id' => 'Тариф'
        ];
    }

    public function applyFilter(Query $query)
    {
        if ($this->client) {
            $query->andWhere(['client' => $this->client]);
        }
    }

}