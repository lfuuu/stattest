<?php

namespace app\classes\voip\forms\NumberCommandForm;

use app\modules\nnp\models\Operator;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\ServiceType;
use yii\base\BaseObject;

class ActualizeData extends BaseObject
{
    protected array $operators = [];
    protected array $accountTariffs = [];

    public array $updates = [];
    public array $allNumbers = [];
    public array $allOperatorIds = [];
    public array $eventsFrom = [];
    public array $eventsTo = [];
    public array $portedNumbers = [];

    /**
     * Имя оператора
     *
     * @param int $operatorId
     * @return string
     */
    public function getOperatorNameById($operatorId) {
        if (!$this->operators && $this->allOperatorIds) {
            $this->operators = Operator::find()
                ->andWhere(['id' => $this->allOperatorIds])
                ->indexBy('id')
                ->all();
        }

        return !empty($this->operators[$operatorId]) ? $this->operators[$operatorId]->name : '';
    }

    /**
     * Id клиента
     *
     * @param string $number
     * @return string|null
     */
    public function getClientIdByNumber($number) {
        if (!$this->accountTariffs && $this->allNumbers) {
            $this->accountTariffs = AccountTariff::find()
                ->from(AccountTariff::tableName() . ' u')
                ->andWhere([
                    'u.voip_number' => $this->allNumbers,
                    'u.service_type_id' => ServiceType::ID_VOIP,
                ])
                ->andWhere('id = (SELECT MAX(id) FROM `uu_account_tariff` ua where ua.voip_number = u.voip_number)')
                ->indexBy('voip_number')
                ->all();
        }

        return !empty($this->accountTariffs[$number]) ? $this->accountTariffs[$number]->client_account_id : null;
    }
}