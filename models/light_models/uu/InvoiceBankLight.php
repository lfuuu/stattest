<?php

namespace app\models\light_models\uu;

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
     */
    public function __construct($settlementAccount)
    {
        parent::__construct();

        if (!is_null($settlementAccount)) {
            $this->title = $settlementAccount->bank_name . ' ' . $settlementAccount->bank_address;
            $this->account = $settlementAccount->bank_account;
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
     * @return []
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