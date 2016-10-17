<?php

namespace app\models\light_models\uu;

use Yii;
use yii\base\Component;
use app\classes\uu\model\AccountEntry;
use app\models\ClientAccount;
use app\models\InvoiceSettings;

class InvoiceItemsLight extends Component implements InvoiceLightInterface
{

    public $items = [];

    private
        $invoiceSetting,
        $clientContragentEuroINN = false;

    /**
     * @param ClientAccount $clientAccount
     * @param AccountEntry[] $items
     * @param InvoiceBillLight $bill
     * @param $invoiceSetting
     */
    public function __construct(ClientAccount $clientAccount, InvoiceBillLight $bill, $items, $invoiceSetting)
    {
        parent::__construct();

        $this->invoiceSetting = $invoiceSetting;
        // Взять EU Vat ID у контрагента
        $this->clientContragentEuroINN = $clientAccount->contragent->inn_euro;

        foreach ($items as $item) {
            // Пересчет НДС если необходимо
            $this->relalcVat($item);

            // Подсчет суммы счета
            $bill
                ->setSummaryVat($item->vat)
                ->setSummaryWithoutVat($item->price_without_vat)
                ->setSummaryWithVat($item->price_with_vat);

            $this->items[] = [
                'title' => $item->getTypeName($bill->getLanguage()),
                'price_without_vat' => $item->price_without_vat,
                'price_with_vat' => $item->price_with_vat,
                'vat_rate' => $item->vat_rate,
                'vat' => $item->vat,
            ];
        }
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->items;
    }

    /**
     * @param $item
     */
    public function relalcVat(&$item)
    {
        $applyVatRate = false;
        $vatRate = $item->vat;

        if (!is_null($this->invoiceSetting)) {
            $vatRate = $this->invoiceSetting->vat_rate;

            // Применение схемы начисления НДС
            switch ($this->invoiceSetting->vat_apply_scheme) {

                // Схема #1 применение НДС из настроек as is если есть отличия
                case InvoiceSettings::VAT_SCHEME_FIRST:
                    if ($this->invoiceSetting->vat_rate != $item->vat_rate) {
                        $vatRate = $this->invoiceSetting->vat_rate;
                        $applyVatRate = true;
                    }
                    break;

                // Схема #3 + 0 НДС (Международная)
                case InvoiceSettings::VAT_SCHEME_THIRD:

                // Схема #2 упрощенная система налогооблажения
                case InvoiceSettings::VAT_SCHEME_SECOND:
                    $vatRate = 0;
                    $applyVatRate = true;
                    break;

                // Схема #4 + 0 НДС + EU Vat ID
                case InvoiceSettings::VAT_SCHEME_FOURTH:
                    if (!empty($this->clientContragentEuroINN)) {
                        $vatRate = 0;
                        $applyVatRate = true;
                    } else if($this->invoiceSetting->vat_rate != $item->vat_rate && is_numeric($this->invoiceSetting->vat_rate)) {
                        $vatRate = $this->invoiceSetting->vat_rate;
                        $applyVatRate = true;
                    }
                    break;
            }
        }

        if ($applyVatRate === true) {
            $item->vat_rate = $vatRate;
            $item->price_with_vat = $item->price_without_vat * (100 + $item->vat_rate) / 100;
            $item->vat = $item->price_without_vat * $item->vat_rate / 100;
        }
    }

    /**
     * @return string
     */
    public static function getKey()
    {
        return 'item';
    }

    /**
     * @return string
     */
    public static function getBlockKey()
    {
        return 'items';
    }

    /**
     * @return string
     */
    public static function getTitle()
    {
        return 'Данные о проводке (используется в цикле $items)';
    }

    /**
     * @return array
     */
    public static function attributeLabels()
    {
        return [
            'title' => 'Название услуги',
            'price_without_vat' => 'Цена без НДС',
            'price_with_vat' => 'Цена с НДС',
            'vat' => 'НДС',
            'vat_rate' => 'Процент НДС',
        ];
    }

}