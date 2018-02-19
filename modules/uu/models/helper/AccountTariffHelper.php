<?php

namespace app\modules\uu\models\helper;

use app\helpers\usages\UsageHelperInterface;
use app\modules\uu\models\AccountTariff;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\db\ActiveRecord;

class AccountTariffHelper extends Object implements UsageHelperInterface
{
    /** @var AccountTariff */
    private $_accountTariff;

    /**
     * @param AccountTariff $usage
     */
    public function __construct(AccountTariff $usage)
    {
        $this->_accountTariff = $usage;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_accountTariff->getName($isWithAccount = false);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->getTitle();
    }

    /**
     * @return array
     */
    public function getExtendsData()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getDescription()
    {
        $value = $this->getValue();
        $description = '';
        $checkboxOptions = [];

        return [$value, $description, $checkboxOptions];
    }

    /**
     * @return string
     * @throws InvalidParamException
     */
    public function getEditLink()
    {
        return $this->_accountTariff->getUrl();
    }

    /**
     * @return ActiveRecord
     */
    public function getTransferedFrom()
    {
        return null;
    }

    /**
     * Получение полей для связи с лицевым счетом
     * Поле в услуге => Поле в лицевом счете
     *
     * @return array
     */
    public function getFieldsForClientAccountLink()
    {
        // Поле в услуге, Поле в лицевом счете
        return ['client_account_id', 'client'];
    }

    /**
     * Получение информации о тарифном плане услуги
     *
     * @return string|null
     */
    public function getTariffDescription()
    {
        return '';
    }

    /**
     * Дата создания услуги
     *
     * @return string|null
     */
    public function getActivationDt()
    {
        return $this->_accountTariff->insert_time;
    }
}