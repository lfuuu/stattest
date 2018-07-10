<?php

namespace app\modules\uu\models_light;

use app\classes\Assert;
use app\classes\Smarty;
use app\forms\templates\uu\InvoiceForm;
use app\helpers\DateTimeZoneHelper;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
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
     * @param Bill $bill
     * @return $this
     */
    public function setBill($bill)
    {
        $this->_bill = $bill;
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
                        'b.bill_date' => $sBill->bill_date,
                        'b.is_to_uu_invoice' => 1,
                    ])
                    ->andWhere(['>', 'bl.sum', 0])
                    ->all();

                $additionItems && $items = array_merge($items, $additionItems);
            }

        } elseif ($this->_bill instanceof Bill) {
            $items = $this->_bill->lines;
        }

        if (count($items)) {
            // Данные о счете
            $this->_bill = new InvoiceBillLight($this->_bill, $dataLanguage);
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
     * @return string
     * @throws \Exception
     * @throws \SmartyException
     */
    public function render()
    {
        $smarty = Smarty::init();
        $smarty->assign($this->getProperties());

        $invoiceTemplate = new InvoiceForm($this->_language);

        if ($invoiceTemplate->fileExists()) {
            return $smarty->fetch(Yii::getAlias($invoiceTemplate->getFileName()));
        } else {
            Yii::$app->session->setFlash('error', 'Шаблон счета-фактуры для языка "' . $this->_language . '" не найден');
        }

        return false;
    }

    /**
     * @return array
     */
    public static function getHelp()
    {
        return [
            InvoiceSellerLight::className(),
            InvoiceBuyerLight::className(),
            InvoiceBillLight::className(),
            InvoiceItemsLight::className(),
        ];
    }

}