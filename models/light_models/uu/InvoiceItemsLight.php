<?php

namespace app\models\light_models\uu;

use Yii;
use yii\base\Component;
use app\classes\uu\model\AccountEntry;

class InvoiceItemsLight extends Component implements InvoiceLightInterface
{

    public $items = [];

    private $invoiceSetting;

    /**
     * @param AccountEntry[] $items
     * @param InvoiceBillLight $bill
     * @param $invoiceSetting
     */
    public function __construct(InvoiceBillLight $bill, $items, $invoiceSetting)
    {
        parent::__construct();

        $this->invoiceSetting = $invoiceSetting;

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
     * @return []
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
        /** Пересчет НДС если используется отличный от оригинального */
        if (!is_null($this->invoiceSetting) && $this->invoiceSetting->vat_rate != $item->vat && $this->invoiceSetting->vat_rate > 0) {
            $item->vat_rate = $this->invoiceSetting->vat_rate;
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
     * @return []
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