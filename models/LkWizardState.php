<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\models\ClientBPStatuses;
use app\models\Saldo;
use app\models\ClientAccount;


/**
 * @property int $account_id
 * @property int $step
 * @property string $state
 */
class LkWizardState extends ActiveRecord
{
    public static function tableName()
    {
        return 'lk_wizard_state';
    }

    public function getTrouble()
    {
        return $this->hasOne(Trouble::className(), ["id" => "trouble_id"]);
    }

    public static function create($accountId, $troubleId = 0)
    {
        $wizard = new self();
        $wizard->account_id = $accountId;
        $wizard->step = 1;
        $wizard->state = "process";
        $wizard->trouble_id = $troubleId;

        return $wizard->save();
    }

    public function getStepName()
    {
        switch($this->step)
        {
            case 1: return "Заполнение реквизитов"; break;
            case 2: return "Скачивание договора"; break;
            case 3: return "Загрузка договора"; break;
            case 4: $s = "Ожидание проверки"; 
            switch ($this->state)
            {
                case 'approve': $s = "Документы проверенны"; break;
                case 'rejected': $s = "Проверка не пройдена"; break;
            }
            return $s;
        }
    }

    public static function isBPStatusAllow($bpsId, $accountId = 0)
    {
        return in_array($bpsId, [
            ClientBPStatuses::TELEKOM__SUPPORT__ORDER_OF_SERVICES
            ]) || $accountId == 9130;
    }

    public function add100Rub()
    {
        $saldo = Saldo::findOne(["client_id" => $this->account_id]);

        if (!$saldo)
        {
            $saldo = new Saldo;
            $saldo->client_id = $this->account_id;
            $saldo->ts = (new \DateTime('now', new \DateTimeZone('UTC')))->format("Y-m-d");
            $saldo->currency = "RUB";
            $saldo->edit_user = User::SYSTEM_USER_ID;
            $saldo->edit_time = (new \DateTime('now', new \DateTimeZone('UTC')))->format(\DateTime::ATOM);
        }

        $saldo->saldo = -100;
        $saldo->save();

        ClientAccount::dao()->updateBalance($this->account_id);

        return true;
    }
}
