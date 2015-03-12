<?php
namespace app\forms\buh;

use Yii;
use app\classes\Assert;
use app\models\ClientAccount;
use app\models\Payment;

class PaymentAddForm extends PaymentForm
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['client_id', 'payment_date', 'oper_date', 'bill_no','original_currency','original_sum','sum','payment_rate','type'], 'required'];
        $rules[] = [
            ['payment_no','bank'],
            'required',
            'when' => function ($model) { return $model->type == 'bank'; },
            'whenClient' => 'function (attribute, value) { return $("#payment_type").val() == "bank"; }'
        ];
        $rules[] = [
            ['ecash_operator'],
            'required',
            'when' => function ($model) { return $model->type == 'ecash'; },
            'whenClient' => 'function (attribute, value) { return $("#payment_type").val() == "ecash"; }'
        ];
        return $rules;
    }

    public function save()
    {
        $client = ClientAccount::findOne($this->client_id);
        Assert::isObject($client);

        $item = new Payment();
        $item->client_id = $this->client_id;
        $item->payment_date = $this->payment_date;
        $item->payment_no = $this->payment_no;
        $item->oper_date = $this->oper_date;
        $item->bill_no = $this->bill_no;
        $item->bill_vis_no = $this->bill_no;
        $item->original_currency = $this->original_currency;
        $item->currency = $client->currency;
        $item->original_sum = round($this->original_sum, 2);
        $item->sum = round($this->sum, 2);
        $item->payment_rate = round($item->original_sum / $item->sum, 8);
        $item->type = $this->type;
        $item->bank = $item->type == 'bank' ? $this->bank : 'mos';
        $item->ecash_operator = $item->type == 'ecash' ? $this->ecash_operator : null;
        $item->comment = $this->comment;
        $item->add_date = (new \DateTime())->format(\DateTime::ATOM);
        $item->add_user = \Yii::$app->user->getId();

        $result = $this->saveModel($item);
        if ($result) {
            ClientAccount::dao()->updateBalance($client->id);
        }
        return $result;
    }
}
