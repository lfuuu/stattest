<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\models\BusinessProcessStatus;
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

    public static function create($contractId, $troubleId = 0, $type = 'mcn')
    {
        $wizard = new self();
        $wizard->contract_id = $contractId;
        $wizard->step = 1;
        $wizard->state = "process";
        $wizard->trouble_id = $troubleId;
        $wizard->type = $type;
        $wizard->is_on = 1;

        return $wizard->save();
    }

    public function getStepName()
    {
        if ($this->type == "mcn")
        {
            switch ($this->step) {
                case 1:
                    return "Заполнение реквизитов";
                    break;
                case 2:
                    return "Скачивание договора";
                    break;
                case 3:
                    return "Загрузка договора";
                    break;
                case 4:
                    $s = "Ожидание проверки";
                    switch ($this->state) {
                        case 'approve':
                            $s = "Документы проверенны";
                            break;
                        case 'rejected':
                            $s = "Проверка не пройдена";
                            break;
                    }
                    return $s;
            }
        } else { //t2t
            return "Шаг ".$this->step;
        }
    }

    public static function isBPStatusAllow($bpsId, $contractId = 0)
    {
        return in_array($bpsId, [
            BusinessProcessStatus::TELEKOM_MAINTENANCE_ORDER_OF_SERVICES
        ]) || $contractId == 9130;
    }

}
