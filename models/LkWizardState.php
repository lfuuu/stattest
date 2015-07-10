<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\models\ClientBPStatuses;
use app\models\Bill;
use app\models\BillLine;
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

    public static function create($accountId, $type = "mcn", $troubleId = 0)
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
        /** @var ClientAccount $clientAccount */
        $clientAccount = ClientAccount::findOne($this->account_id);
        $tax_rate = $clientAccount->getTaxRate(true);

        $sum = -100;

        $bill = new Bill();
        $bill->client_id = $clientAccount->id;
        $bill->currency = $clientAccount->currency;
        $bill->nal = $clientAccount->nal;
        $bill->is_lk_show = 1;
        $bill->is_user_prepay = 0;
        $bill->is_approved = 1;
        $bill->bill_date = date('Y-m-d');
        $bill->bill_no = Bill::dao()->spawnBillNumber(date('Y-m-d'));
        $bill->price_include_vat = $clientAccount->price_include_vat;
        $bill->save();

        $line = new BillLine(["bill_no" => $bill->bill_no]);
        $line->item = "Услуга \"Бонус\"";
        $line->date_from = date("Y-m-d", strtotime("first day of this month"));
        $line->date_to = date("Y-m-d", strtotime("last day of this month"));
        $line->type = 'service';
        $line->amount = 1;
        $line->price = $sum;
        $line->tax_rate = $tax_rate;
        $line->calculateSum($bill->price_include_vat);
        $line->sum = $sum;
        $line->save();

        Bill::dao()->recalcBill($bill);
        ClientAccount::dao()->updateBalance($clientAccount->id);

        return true;
    }
}
