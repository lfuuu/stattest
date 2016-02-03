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
    public $status;

    public $tariff = "";

    public $is_package_active = false;
    public $is_package_in_future = false;
    public $package = null;

    public function rules()
    {
        $rules = [];
        $rules[] = ['id', 'integer'];
        $rules[] = ['disconnecting_date', 'default', 'value' => UsageInterface::MAX_POSSIBLE_DATE];
        $rules[] = [['connecting_date', 'disconnecting_date'], 'string'];
        $rules[] = [['connecting_date'], 'date', 'format' => 'Y-m-d'];
        $rules[] = ['connecting_date', 'validateConnectingDate'];
        $rules[] = ['disconnecting_date', 'validateDisconnectingDate'];
        $rules[] = ['status', 'default', 'value' => 'connecting'];

        return $rules;
    }

    public function initModel(UsageVoipPackage $package)
    {
        $now = new DateTime('now', $package->clientAccount->timezone);

        $this->id = $package->id;
        $this->connecting_date = $package->actual_from;
        $this->disconnecting_date = $package->actual_to == UsageInterface::MAX_POSSIBLE_DATE ? '' : $package->actual_to;
        $this->is_package_active =    $package->actual_from <= $now->format('Y-m-d') && $package->actual_to >= $now->format('Y-m-d');
        $this->is_package_in_future = $package->actual_from >  $now->format('Y-m-d') && $package->actual_to >  $now->format('Y-m-d');
        $this->tariff = $package->tariff->name;
        $this->status = $package->status;

        $this->package = $package;
    }

    public function attributeLabels()
    {
        return [
            'connecting_date' => 'Дата подключения',
            'disconnecting_date' => 'Дата отключения',
            'tariff' => 'Тариф',
            'status' => 'Статус'
        ];
    }

    public function validateConnectingDate($attr, $params)
    {
        $now = new DateTime('now', $package->clientAccount->timezone);

        $connect_date = new DateTime($this->connecting_date, $package->clientAccount->timezone);
        Assert::isObject($connect_date);

        $disconnect_date = new DateTime($this->disconnecting_date, $package->clientAccount->timezone);
        Assert::isObject($disconnect_date);

        if ($connect_date >= $disconnect_date) {
            $this->addError('connecting_date', 'Прверьте даты');
        }
    }

    public function validateDisconnectingDate($attr, $params)
    {
        if (!$this->is_package_active && !$this->is_package_in_future) {
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
        $timezone = $this->package->clientAccount->timezone;

        if ($this->is_package_in_future) {
            $connecting_date = new DateTime($this->connecting_date, $timezone);

            $this->package->actual_from = $this->connecting_date;
            $this->package->activation_dt = DateTimeZoneHelper::getActivationDateTime($this->connecting_date, $timezone);
        }

        if ($this->is_package_active || $this->is_package_in_future) {
            $disconnect_date = new DateTime($this->disconnecting_date, $timezone);

            $this->package->actual_to = $this->disconnecting_date;
            $this->package->expire_dt = DateTimeZoneHelper::getExpireDateTime($this->disconnecting_date, $timezone);

            $this->package->status = $this->status;
        }


        if ($this->is_package_active || $this->is_package_in_future) {
            try {

                $transaction = Yii::$app->db->beginTransaction();

                $this->package->save();

                $transaction->commit();

                return 'Данные по пакету сохранены';
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return '';
    }
}
