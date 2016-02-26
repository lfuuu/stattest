<?php
namespace app\forms\tariff\call_chat;

use app\models\TariffCallChat;
use yii\db\Query;

class CallChatListForm extends CallChatForm
{

    public function rules()
    {
        return [
            [['status', 'currency_id'], 'string'],
            [['price_include_vat'], 'boolean']
        ];
    }

    /**
     * @return Query
     */
    public function spawnQuery()
    {
        return TariffCallChat::find()->orderBy('description asc');
    }

    public function applyFilter(Query $query)
    {
        if ($this->status) {
            $query->andWhere(['status' => $this->status]);
        }

        if ($this->currency_id) {
            $query->andWhere(['tarifs_voip_package.currency_id' => $this->currency_id]);
        }

        if ($this->price_include_vat) {
            $query->andWhere(['price_include_vat' => $this->price_include_vat]);
        }
    }

}