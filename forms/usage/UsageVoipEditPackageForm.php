<?php
namespace app\forms\usage;

use app\classes\Assert;
use app\classes\Form;
use app\helpers\DateTimeZoneHelper;
use app\models\usages\UsageInterface;
use app\models\UsageVoipPackage;
use DateTime;
use Yii;

class UsageVoipEditPackageForm extends Form
{
    public $id;
    public $connecting_date;
    public $disconnecting_date;
    public $status;

    public $tariff = "";

    public $is_package_active = false;
    public $is_package_in_future = false;

    /** @var UsageVoipPackage */
    public $package = null;

    public function rules()
    {
        $rules = [];
        $rules[] = ['id', 'integer'];
        $rules[] = ['disconnecting_date', 'default', 'value' => UsageInterface::MAX_POSSIBLE_DATE];
        $rules[] = [['connecting_date', 'disconnecting_date'], 'string'];
        $rules[] = [['connecting_date'], 'date', 'format' => DateTimeZoneHelper::DATE_FORMAT];
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
        $this->is_package_active = $package->actual_from <= $now->format(DateTimeZoneHelper::DATE_FORMAT) && $package->actual_to >= $now->format(DateTimeZoneHelper::DATE_FORMAT);
        $this->is_package_in_future = $package->actual_from > $now->format(DateTimeZoneHelper::DATE_FORMAT) && $package->actual_to > $now->format(DateTimeZoneHelper::DATE_FORMAT);
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

    public function validateConnectingDate()
    {
        $connectDate = new DateTime($this->connecting_date, $this->package->clientAccount->timezone);
        Assert::isObject($connectDate);

        $disconnectDate = new DateTime($this->disconnecting_date, $this->package->clientAccount->timezone);
        Assert::isObject($disconnectDate);

        if ($connectDate > $disconnectDate) {
            $this->addError('connecting_date', 'Дата завершения пакета должна быть позже начала');
        }

        $usageConnectDate = new DateTime($this->package->actual_from, $this->package->clientAccount->timezone);
        Assert::isObject($usageConnectDate);

        $usageDisconnectDate = new DateTime($this->package->actual_to, $this->package->clientAccount->timezone);
        Assert::isObject($usageDisconnectDate);

        if ($usageConnectDate > $connectDate || $disconnectDate > $usageDisconnectDate) {
            $this->addError('connecting_date', 'Время действия пакета должно быть во время действия услуги');
        }

    }

    public function validateDisconnectingDate()
    {
        if (!$this->is_package_active && !$this->is_package_in_future) {
            $this->addError('disconnecting_date', 'Пакет отключен');
            return;
        }

        $now = new DateTime('now', $this->package->clientAccount->timezone);

        $disconnect_date = new DateTime($this->disconnecting_date, $this->package->clientAccount->timezone);
        Assert::isObject($disconnect_date);

        if ($disconnect_date < $now) {
            $this->addError('disconnecting_date', 'Отключить пакет можно только в будущем');
        }
    }

    public function save()
    {
        if ($this->is_package_in_future) {
            $this->package->actual_from = $this->connecting_date;
        }

        if ($this->is_package_active || $this->is_package_in_future) {
            $this->package->actual_to = $this->disconnecting_date;
            $this->package->status = $this->status;
        }

        if ($this->is_package_active || $this->is_package_in_future) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $this->package->save();
                $transaction->commit();

                return true;
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return null;
    }
}
