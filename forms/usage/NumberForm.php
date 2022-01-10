<?php
namespace app\forms\usage;

use app\classes\Assert;
use app\classes\Form;
use app\models\ClientAccount;
use app\models\Number;
use app\modules\nnp\models\NdcType;

class NumberForm extends Form
{
    public $did;
    public $client_account_id;
    public $hold_month;
    public $number_tech;

    public function rules()
    {
        return [
            [['did'], 'string'],
            [['client_account_id'], 'string'],
            [
                ['did'],
                'required',
                'on' => [
                    'default',
                    'startReserve',
                    'stopReserve',
                    'startHold',
                    'stopHold',
                    'startNotSell',
                    'stopNotSell',
                    'toRelease',
                    'toReleaseAndPort',
                    'unRelease',
                    'setTechNumber'
                ]
            ],
            [['hold_month'], 'default', 'value' => 6],
            [['hold_month'], 'in', 'range' => [6, 3, 1]],
            ['number_tech', 'default', 'value' => ''],
            ['number_tech', 'trim',],
            ['number_tech', 'number',],
            [['scenario'], 'safe'],
        ];
    }

    public function process()
    {
        $number = Number::findOne($this->did);
        Assert::isObject($number);

        $clientAccount = $this->client_account_id ? ClientAccount::findOne($this->client_account_id) : null;

        try {

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
            } elseif ($this->scenario == 'toRelease') {
                Number::dao()->toRelease($number);
            } elseif ($this->scenario == 'unRelease') {
                Number::dao()->unRelease($number);
            } elseif ($this->scenario == 'setTechNumber' && $number->ndc_type_id == NdcType::ID_FREEPHONE) {
                $number->number_tech = $this->number_tech ?: null;
                $number->save();
                \Yii::$app->session->addFlash('success', 'Технический номер сохранен');
            }
        } catch (\Exception $e) {
            \Yii::$app->session->addFlash('error', 'Ошибка сохранения номера: ' . $e->getMessage());
            return false;
        }


        return true;
    }

}
