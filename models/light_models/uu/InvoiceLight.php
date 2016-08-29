<?php

namespace app\models\light_models\uu;

use Yii;
use DateTime;
use yii\base\Model;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountTariff;
use app\classes\DateFunction;
use app\classes\Smarty;
use app\classes\Wordifier;
use app\forms\templates\uu\InvoiceForm;
use app\models\Organization;
use app\models\ClientAccount;
use app\models\Language;
use app\models\Currency;
use app\models\InvoiceSettings;
use app\models\OrganizationSettlementAccount;
use app\models\Person;

class InvoiceLight extends Model
{

    private
        $seller,
        $buyer,
        $items = [],
        $bill,

        $clientAccount = null,
        $language = Language::LANGUAGE_DEFAULT,
        $date = null;

    public static $variables = [
        'seller.name' => 'Название организации продавца',
        'seller.legal_address' => 'Адрес организации продавца',
        'seller.tax_registration_id' => 'ИНН организации продавца',
        'seller.tax_registration_reason' => 'КПП организации продавца',
        'seller.director.name_nominative' => 'ФИО руководителя организации продавца',
        'seller.accountant.name_nominative' => 'ФИО главного бухгалтера организации продавца',

    ];

    /**
     * @param ClientAccount $clientAccount
     * @param string $langCode
     */
    public function __construct(ClientAccount $clientAccount)
    {
        parent::__construct();

        $this->clientAccount = $clientAccount;
        $this->date =
            (new DateTime)
                ->modify('first day of previous month')
                ->format('Y-m-d');;
    }

    /**
     * @param string $langCode
     * @return $this
     */
    public function setLanguage($langCode)
    {
        $this->language = $langCode;
        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function prepare()
    {
        /** @var InvoiceSettings $invoiceSetting */
        $invoiceSetting = InvoiceSettings::findOne([
            'customer_country_code' => $this->clientAccount->contract->contragent->country_id,
            'doer_country_code' => $this->clientAccount->organization->country_id,
        ]);

        // Данные организации продавца
        /** @var Organization $seller */
        $seller = $this->clientAccount->contract->getOrganization($this->date)->setLanguage($this->language);
        /** @var Person $director */
        $director = $seller->director->setLanguage($this->language);
        /** @var Person $accountant */
        $accountant = $seller->accountant->setLanguage($this->language);
        /** @var OrganizationSettlementAccount $settlementAccount */
        $settlementAccount =
            !is_null($invoiceSetting)
                ? $seller->getSettlementAccount($invoiceSetting->settlement_account_type_id)
                : $seller->settlementAccount;

        $sellerBank = [];
        if (!is_null($settlementAccount)) {
            $sellerBank = [
                'name' => $settlementAccount->bank_name . ' ' . $settlementAccount->bank_address,
                'account' => $settlementAccount->bank_account,
                'correspondent_account' => $settlementAccount->bank_correspondent_account,
                'bik' => $settlementAccount->bank_bik,
                'swift' => $settlementAccount->bank_swift,
                'iban' => $settlementAccount->bank_iban,
            ];
        }

        $this->seller = [
            'name' => $seller->name,
            'legal_address' => $seller->legal_address,
            'post_address' => $seller->post_address,
            'country' => $seller->country->name,
            'tax_registration_id' => $seller->tax_registration_id,
            'tax_registration_reason' => $seller->tax_registration_reason,
            'contact_email' => $seller->contact_email,
            'contact_phone' => $seller->contact_phone,
            'contact_fax' => $seller->contact_fax,
            'director' => [
                'name' => $director->name_nominative,
                'post' => $director->post_nominative,
            ],
            'accountant' => [
                'name' => $accountant->name_nominative,
                'post' => $accountant->post_nominative,
            ],
            'bank' => $sellerBank,
        ];

        // Данные организации покупдателя
        /** @var ClientAccount $buyer */
        $buyer = $this->clientAccount;
        $this->buyer = [
            'name' => ($buyer->head_company ?: $buyer->company_full),
            'address' => ($buyer->head_company_address_jur ?: $buyer->address_jur),
            'tax_registration_id' => $buyer->inn,
            'tax_registration_reason' => $buyer->kpp,
            'consignee' => ($buyer->is_with_consignee && $buyer->consignee) ? $buyer->consignee : '------',
        ];

        // Вернуть проводки клиента за предыдущий календарный месяц для счета-фактуры
        $accountEntryTableName = AccountEntry::tableName();
        $accountTariffTableName = AccountTariff::tableName();

        $items = AccountEntry::find()
            ->joinWith('accountTariff')
            ->where([$accountTariffTableName . '.client_account_id' => $this->clientAccount->id])
            ->orderBy([
                'account_tariff_id' => SORT_ASC,
                'type_id' => SORT_ASC,
            ])
            ->andWhere(['>', $accountEntryTableName . '.vat', 0])
            ->andWhere([$accountEntryTableName . '.date' => $this->date])
            ->all();

        if (count($items)) {
            $this->bill = [
                'id' => $this->items[0]->bill_id,
                'date' => DateFunction::mdate($this->items[0]->date, 'd.m.Y'),
                'summary' => [
                    'without_vat' => 0,
                    'vat' => 0,
                    'with_vat' => 0,
                ],
            ];

            /** @var AccountEntry $item */
            foreach ($items as $item) {
                /** Пересчет НДС если используется отличный от оригинального */
                if (!is_null($invoiceSetting) && $invoiceSetting->vat_rate != $item->vat && $invoiceSetting->vat_rate > 0) {
                    $item->vat_rate = $invoiceSetting->vat_rate;
                    $item->price_with_vat = $item->price_without_vat * (100 + $item->vat_rate) / 100;
                    $item->vat = $item->price_without_vat * $item->vat_rate / 100;
                }

                /** Подсчет сумму счета */
                $this->bill['summary']['without_vat'] += $item->price_without_vat;
                $this->bill['summary']['vat'] += $item->vat;
                $this->bill['summary']['with_vat'] += $item->price_with_vat;

                $this->items[] = [
                    'title' => $item->getTypeName($this->language),
                    'price_without_vat' => sprintf('%.2f', $item->price_without_vat),
                    'vat_rate' => ($item->vat_rate == 0 ? 'без НДС' : sprintf('%.2f', $item->vat_rate) . '%'),
                    'vat' => sprintf('%.2f', $item->vat),
                    'price_with_vat' => sprintf('%.2f', $item->price_with_vat),
                ];
            }

            $this->bill['summary']['without_vat'] = sprintf('%.2f', $this->bill['summary']['without_vat']);
            $this->bill['summary']['vat'] = sprintf('%.2f', $this->bill['summary']['vat']);
            $this->bill['summary']['with_vat'] = sprintf('%.2f', $this->bill['summary']['with_vat']);

            $this->bill['in_total'] = Wordifier::Make($this->bill['summary']['with_vat'], Currency::RUB);
        }
    }

    /**
     * @return []
     */
    public function get()
    {
        $this->prepare();

        return [
            'seller' => $this->seller,
            'buyer' => $this->buyer,
            'items' => $this->items,
            'bill' => $this->bill,
        ];
    }

    /**
     * @return string
     */
    public function render()
    {
        $smarty = Smarty::init();
        $smarty->assign($this->get());

        $invoiceTemplate = new InvoiceForm($this->language);

        if ($invoiceTemplate->fileExists()) {
            $smarty->fetch(Yii::getAlias(InvoiceForm::getPath() . $this->language . '.' . InvoiceForm::TEMPLATE_EXTENSION));
        } else {
            Yii::$app->session->addFlash('error', 'Шаблон счета-фактура для языка "' . $this->language . '" не найден');
        }
    }

}