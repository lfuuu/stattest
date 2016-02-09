<?php
namespace app\forms\support;

use app\classes\enum\TicketStatusEnum;
use app\classes\ListForm;
use app\classes\validators\AccountIdValidator;
use app\classes\validators\ArrayValidator;
use app\classes\validators\EnumValidator;
use app\models\support\Ticket;
use yii\db\ActiveQuery;
use yii\db\Query;

class TicketListForm extends ListForm
{
    public $client_account_id;
    public $status;

    public function rules()
    {
        return [
            ['client_account_id', AccountIdValidator::className()],
            ['status', ArrayValidator::className(), 'validator' => ['class' => EnumValidator::className(), 'enum' => TicketStatusEnum::className()]],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function spawnQuery()
    {
        return Ticket::find()->orderBy('created_at desc');
    }

    public function applyFilter(Query $query)
    {
        $query->andWhere(['client_account_id' => $this->client_account_id]);

        if ($this->status) {
            $query->andWhere(['status' => $this->status]);
        }
    }

}