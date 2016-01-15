<?php
namespace app\forms\usage;

use Yii;
use DateTime;
use app\classes\Form;
use app\models\UsageVoipPackage;
use app\models\usages\UsageInterface;
use app\helpers\DateTimeZoneHelper;
use yii\validators\DateValidator;
use app\classes\Assert;

class UsageVoipEditPackageForm extends Form
{
    public $id;
    public $connecting_date;
    public $disconnecting_date;

    public $tariff = "";

    public $is_package_active = false;
    public $package = null;

    public function rules()
    {
        $rules = [];
        $rules[] = ['id', 'integer'];
        $rules[] = ['disconnecting_date', 'default', 'value' => UsageInterface::MAX_POSSIBLE_DATE];
        $rules[] = [['connecting_date', 'disconnecting_date'], 'string'];
        $rules[] = [['connecting_date'], 'date', 'format' => 'Y-m-d'];
        $rules[] = ['disconnecting_date', 'validateDisconnectDate'];

        return $rules;
    }

    public function initModel(UsageVoipPackage $package)
    {
        $now = new DateTime('now', $package->clientAccount->timezone);

        $this->id = $package->id;
        $this->connecting_date = $package->actual_from;
        $this->disconnecting_date = $package->actual_to == UsageInterface::MAX_POSSIBLE_DATE ? '' : $package->actual_to;
        $this->is_package_active = $package->actual_from <= $now->format('Y-m-d') && $package->actual_to >= $now->format('Y-m-d');
        $this->tariff = $package->tariff->name;

        $this->package = $package;
    }

    public function attributeLabels()
    {
        return [
            'connecting_date' => 'Дата подключения',
            'disconnecting_date' => 'Дата отключения',
            'tariff' => 'Тариф'
        ];
    }

    public function validateDisconnectDate($attr, $params)
    {
        if (!$this->is_package_active) {
            $this->addError('disconnecting_date', 'Пакет отключен');
            return;
        }

        $now = new DateTime('now', $package->clientAccount->timezone);

        $disconnect_date = new DateTime($this->disconnecting_date, $package->clientAccount->timezone);
        Assert::isObject($disconnect_date);

        if ($disconnect_date < $now) {
            $this->addError('disconnecting_date', 'Отключить пакет можно только в будущем');
        }
    }

    public function save()
    {
        $disconnect_date = new DateTime($this->disconnecting_date, $this->package->clientAccount->timezone);
        $disconnect_date->setTime(0, 0, 0);

        $this->package->actual_to = $this->disconnecting_date;
        $this->package->expire_dt = DateTimeZoneHelper::getExpireDateTime($this->disconnecting_date, $this->package->clientAccount->timezone);


        try {
            $transaction = Yii::$app->db->beginTransaction();

            $this->package->save();

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return false;
    }
}
