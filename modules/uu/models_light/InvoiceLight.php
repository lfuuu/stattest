<?php

namespace app\modules\uu\models_light;

use app\classes\Assert;
use app\classes\Html2Pdf;
use app\classes\Smarty;
use app\forms\templates\uu\InvoiceForm;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\Invoice;
use app\models\Language;
use app\modules\uu\models\AccountEntry;
use app\modules\uu\models\AccountTariff;
use app\modules\uu\models\Bill as uuBill;
use DateTime;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\db\Expression;
use yii\db\Query;

class InvoiceLight extends Component
{

    private
        $_seller,
        $_buyer,
        $_items,
        $_bill,
        $_invoice,
        $_invoiceProformaBill,

        $_clientAccount,
        $_language = Language::LANGUAGE_DEFAULT,
        $_date;

    /**
     * @param ClientAccount $clientAccount
     */
    public function __construct(ClientAccount $clientAccount)
    {
        parent::__construct();

        $this->_clientAccount = $clientAccount;
        $this->_date = (new DateTime)
            ->modify('first day of previous month')
            ->format('Y-m');
    }

    /**
     * @param Bill|\app\modules\uu\models\Bill $bill
     * @return $this
     */
    public function setBill($bill)
    {
        $this->_bill = $bill;
        return $this;
    }

    /**
     * @param Invoice $invoice
     * @return $this
     * @internal param Bill $bill
     */
    public function setInvoice(Invoice $invoice)
    {
        $this->_invoice = $invoice;
        return $this;
    }

    /**
     * @param string $langCode
     * @return $this
     */
    public function setLanguage($langCode)
    {
        $this->_language = $langCode;
        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->_date = $date;
        return $this;
    }

    /**
     * @param Bill $invoiceProformaBill
     * @return $this
     */
    public function setInvoiceProformaBill($invoiceProformaBill)
    {
        $this->_invoiceProformaBill = $invoiceProformaBill;
        return $this;
    }

    /**
     * @throws InvalidParamException
     */
    public function prepare()
    {
        Assert::isObject($this->_bill, 'Данные о счете не найдены');

        // Устанавливаем язык для универсального шаблона
        $dataLanguage = $this->_language === InvoiceForm::UNIVERSAL_INVOICE_KEY ?
            Language::LANGUAGE_ENGLISH :
            $this->_language;

        // Данные организации продавца
        $dateForOrganization = (new DateTime)->format(DateTimeZoneHelper::DATE_FORMAT);

        $sellerOrganization = $this->_clientAccount->contract->getOrganization($dateForOrganization);
        Assert::isObject($sellerOrganization, 'Данные об организации за дату "' . $dateForOrganization . '" не найдены');

        $this->_seller = new InvoiceSellerLight(
            $this->_language,
            $sellerOrganization->setLanguage($dataLanguage),
            $this->_clientAccount
        );

        // Данные организации покупателя
        $this->_buyer = new InvoiceBuyerLight($this->_clientAccount);

        // Вернуть проводки клиента за предыдущий календарный месяц для счета-фактуры
        $accountTariffTableName = AccountTariff::tableName();
        $accountEntryTableName = AccountEntry::tableName();

        $items = [];
        if ($this->_bill instanceof uuBill) {
            $items = AccountEntry::find()
                ->joinWith('accountTariff')
                ->where([
                    $accountTariffTableName . '.client_account_id' => $this->_clientAccount->id,
                    $accountEntryTableName . '.bill_id' => $this->_bill->id,
                ])
                ->andWhere(['>', $accountEntryTableName . '.price_with_vat', 0])
                ->orderBy([
                    $accountEntryTableName . '.account_tariff_id' => SORT_ASC,
                    $accountEntryTableName . '.type_id' => SORT_ASC,
                ])
                ->all();

            if ($this->_bill->is_converted) {

                $sBill = Bill::findOne(['uu_bill_id' => $this->_bill->id]);

                if (!$sBill) {
                    throw new InvalidParamException('Счет №' . $this->_bill->id . ' не найден');
                }

                $additionItems = BillLine::find()
                    ->alias('bl')
                    ->joinWith('bill b')
                    ->where([
                        'b.client_id' => $sBill->client_id,
                        'b.bill_date' => $sBill->bill_date,
                        'b.is_to_uu_invoice' => 1,
                    ])
                    ->andWhere(['>', 'bl.sum', 0])
                    ->all();

                $additionItems && $items = array_merge($items, $additionItems);
            }

        } elseif ($this->_invoice) {
            $this->_bill = $this->_invoice->bill;
            $items = $this->_invoice->lines ?: $this->_bill->getLinesByTypeId($this->_invoice->type_id);
        } elseif ($this->_bill instanceof Bill) {
            $items = $this->_bill->lines;

        }

        if (count($items)) {
            // Данные о счете
            $this->_bill = new InvoiceBillLight($this->_bill, $this->_invoice, $dataLanguage);
            // Данные проводках
            $this->_items = (new InvoiceItemsLight($this->_clientAccount, $this->_bill, $items, $dataLanguage))->getAll();
        }
    }

    /**
     * @return array
     */
    public function getBills()
    {
        return (new Query())
            ->select([
                'bill.*',
                'entries' => new Expression('COUNT(entry.id)'),
            ])
            ->from(['bill' => uuBill::tableName()])
            ->leftJoin(['entry' => AccountEntry::tableName()], 'entry.bill_id = bill.id')
            ->where(['bill.client_account_id' => $this->_clientAccount->id])
            ->andWhere(['bill.date' => $this->_date . '-01'])
            ->groupBy('bill.id')
            ->having('entries > 0')
            ->all();
    }

    /**
     * @return array
     * @throws InvalidParamException
     */
    public function getProperties()
    {
        $this->prepare();

        return [
            InvoiceSellerLight::getKey() => (array)$this->_seller,
            InvoiceBuyerLight::getKey() => (array)$this->_buyer,
            InvoiceItemsLight::getBlockKey() => (array)$this->_items,
            InvoiceBillLight::getKey() => (array)$this->_bill,
        ];
    }

    /**
     * @param bool $isPdf
     * @return string
     */
    public function render($isPdf = false)
    {
        $smarty = Smarty::init();
        $smarty->assign($this->getProperties());

        $invoiceTemplate = new InvoiceForm($this->_language, $this->_invoice, $this->_invoiceProformaBill);

        if ($invoiceTemplate->fileExists()) {
            $content = $smarty->fetch(Yii::getAlias($invoiceTemplate->getFileName()));

            if ($isPdf) {
                $generator = new Html2Pdf();
                $generator->html = $content;
                $content = $generator->pdf;
            }

            return $content;
        } else {
            $msg = 'Шаблон счета-фактуры для языка "' . $this->_language . '" не найден';
            if (Yii::$app instanceof \app\classes\WebApplication) {
                Yii::$app->session->setFlash('error', $msg);
            } else {
                throw new \InvalidArgumentException($msg);
            }
        }

        return false;
    }



    /**
     * @return array
     */
    public static function getHelp()
    {
        return [
            InvoiceSellerLight::class,
            InvoiceBuyerLight::class,
            InvoiceBillLight::class,
            InvoiceItemsLight::class,
        ];
    }

}
