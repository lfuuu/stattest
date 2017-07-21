<?php

namespace app\modules\uu\models_light;

use Yii;
use yii\base\Component;
use app\models\OrganizationSettlementAccount;

class InvoiceBankLight extends Component implements InvoiceLightInterface
{

    public
        $title,
        $account,
        $address,
        $correspondent_account,
        $bik;

    /**
     * @param OrganizationSettlementAccount|null $settlementAccount
     * @param string $clientAccountCurrency
     */
    public function __construct($settlementAccount, $clientAccountCurrency)
    {
        parent::__construct();

        if (!is_null($settlementAccount)) {
            $this->title = $settlementAccount->bank_name . ' ' . $settlementAccount->bank_address;
            $this->account = (string)$settlementAccount->getProperty('bank_account_' . $clientAccountCurrency);
            $this->address = $settlementAccount->bank_address;
            $this->correspondent_account = $settlementAccount->bank_correspondent_account;
            $this->bik = $settlementAccount->bank_bik;
        }
    }

    /**
     * @return string
     */
    public static function getKey()
    {
        return 'bank';
    }

    /**
     * @return string
     */
    public static function getTitle()
    {
        return 'Данные о платежных реквизитах';
    }

    /**
     * @return array
     */
    public static function attributeLabels()
    {
        return [
            'title' => 'Название банка',
            'address' => 'Адрес банка',
            'account' => 'RU => Расчетный счет, Swift => Номер счета, IBAN => IBAN',
            'correspondent_account' => 'Кор. счет',
            'bik' => 'БИК',
        ];
    }

}