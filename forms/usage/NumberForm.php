<?php
namespace app\forms\usage;

use app\classes\Assert;
use app\classes\Form;
use app\models\ClientAccount;
use app\models\Number;

class NumberForm extends Form
{
    public $did;
    public $client_account_id;
    public $hold_month;

    public function rules()
    {
        return [
            [['did'], 'string'],
            [['client_account_id'], 'string'],
            [['did'], 'required', 'on' => ['default', 'startReserve','stopReserve','startHold','stopHold','startNotSell','stopNotSell']],
            [['hold_month'], 'default', 'value' => 6],
            [['hold_month'], 'in', 'range' => [6, 3, 1]],
            [['scenario'], 'safe'],
        ];
    }

    public function process()
    {
        $number = Number::findOne($this->did);
        Assert::isObject($number);

        $clientAccount = $this->client_account_id ? ClientAccount::findOne($this->client_account_id) : null;

        if ($this->scenario == 'startHold') {
            $holdTo = new \DateTime('now', new \DateTimeZone('UTC'));
            $holdTo->modify("+" . $this->hold_month . " month");

            Number::dao()->startHold($number, $holdTo);
        } elseif ($this->scenario == 'stopHold') {
            Number::dao()->stopHold($number);
        } elseif ($this->scenario == 'startReserve') {
            Number::dao()->startReserve($number, $clientAccount);
        } elseif ($this->scenario == 'stopReserve') {
            Number::dao()->stopReserve($number);
        } elseif ($this->scenario == 'startNotSell') {
            Number::dao()->startNotSell($number);
        } elseif ($this->scenario == 'stopNotSell') {
            Number::dao()->stopNotSell($number);
        }

        return true;
    }

}
