<?php
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountLogMin;
use app\modules\uu\models\AccountLogPeriod;
use app\modules\uu\models\AccountLogResource;
use app\modules\uu\models\AccountLogSetup;
use app\modules\uu\models\AccountTariffResourceLog;
use app\modules\uu\models\Bill;
use app\modules\uu\models\Resource;
use app\modules\uu\models\TariffPeriod;
use app\modules\uu\models\TariffResource;

/**
 * Class m170721_124439_change_uu_float_to_numeric
 */
class m170721_124439_change_uu_float_to_decimal extends \app\classes\Migration
{
    const TYPE_OLD = 'float'; // старый тип

    const TYPE_AMOUNT = 'decimal(13,6)'; // тип для значений и коэффициентов
    const TYPE_PRICE = 'decimal(13,4)'; // тип для цен

    /**
     * Up
     */
    public function safeUp()
    {
        $tableName = AccountEntry::tableName();
        $this->alterColumn($tableName, 'price', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'price_without_vat', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'vat', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'price_with_vat', self::TYPE_PRICE);

        $tableName = AccountLogMin::tableName();
        $this->alterColumn($tableName, 'period_price', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'coefficient', self::TYPE_AMOUNT);
        $this->alterColumn($tableName, 'price_with_coefficient', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'price_resource', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'price', self::TYPE_PRICE);

        $tableName = AccountLogPeriod::tableName();
        $this->alterColumn($tableName, 'period_price', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'coefficient', self::TYPE_AMOUNT);
        $this->alterColumn($tableName, 'price', self::TYPE_PRICE);

        $tableName = AccountLogResource::tableName();
        $this->alterColumn($tableName, 'amount_use', self::TYPE_AMOUNT);
        $this->alterColumn($tableName, 'amount_free', self::TYPE_AMOUNT);
        $this->alterColumn($tableName, 'amount_overhead', self::TYPE_AMOUNT);
        $this->alterColumn($tableName, 'price_per_unit', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'price', self::TYPE_PRICE);

        $tableName = AccountLogSetup::tableName();
        $this->alterColumn($tableName, 'price_setup', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'price_number', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'price', self::TYPE_PRICE);

        $tableName = AccountTariffResourceLog::tableName();
        $this->alterColumn($tableName, 'amount', self::TYPE_AMOUNT);

        $tableName = Bill::tableName();
        $this->alterColumn($tableName, 'price', self::TYPE_PRICE);

        $tableName = Resource::tableName();
        $this->alterColumn($tableName, 'min_value', self::TYPE_AMOUNT);
        $this->alterColumn($tableName, 'max_value', self::TYPE_AMOUNT);

        $tableName = TariffPeriod::tableName();
        $this->alterColumn($tableName, 'price_per_period', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'price_setup', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'price_min', self::TYPE_PRICE);

        $tableName = TariffResource::tableName();
        $this->alterColumn($tableName, 'amount', self::TYPE_AMOUNT);
        $this->alterColumn($tableName, 'price_per_unit', self::TYPE_PRICE);
        $this->alterColumn($tableName, 'price_min', self::TYPE_PRICE);
    }

    /**
     * Down
     */
    public function safeDown()
    {
        $tableName = AccountEntry::tableName();
        $this->alterColumn($tableName, 'price', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price_without_vat', self::TYPE_OLD);
        $this->alterColumn($tableName, 'vat', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price_with_vat', self::TYPE_OLD);

        $tableName = AccountLogMin::tableName();
        $this->alterColumn($tableName, 'period_price', self::TYPE_OLD);
        $this->alterColumn($tableName, 'coefficient', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price_with_coefficient', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price_resource', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price', self::TYPE_OLD);

        $tableName = AccountLogPeriod::tableName();
        $this->alterColumn($tableName, 'period_price', self::TYPE_OLD);
        $this->alterColumn($tableName, 'coefficient', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price', self::TYPE_OLD);

        $tableName = AccountLogResource::tableName();
        $this->alterColumn($tableName, 'amount_use', self::TYPE_OLD);
        $this->alterColumn($tableName, 'amount_free', self::TYPE_OLD);
        $this->alterColumn($tableName, 'amount_overhead', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price_per_unit', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price', self::TYPE_OLD);

        $tableName = AccountLogSetup::tableName();
        $this->alterColumn($tableName, 'price_setup', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price_number', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price', self::TYPE_OLD);

        $tableName = AccountTariffResourceLog::tableName();
        $this->alterColumn($tableName, 'amount', self::TYPE_OLD);

        $tableName = Bill::tableName();
        $this->alterColumn($tableName, 'price', self::TYPE_OLD);

        $tableName = Resource::tableName();
        $this->alterColumn($tableName, 'min_value', self::TYPE_OLD);
        $this->alterColumn($tableName, 'max_value', self::TYPE_OLD);

        $tableName = TariffPeriod::tableName();
        $this->alterColumn($tableName, 'price_per_period', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price_setup', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price_min', self::TYPE_OLD);

        $tableName = TariffResource::tableName();
        $this->alterColumn($tableName, 'amount', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price_per_unit', self::TYPE_OLD);
        $this->alterColumn($tableName, 'price_min', self::TYPE_OLD);
    }
}
