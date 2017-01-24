<?php

namespace app\models\light_models\uu;

use Yii;
use DateTime;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\db\Expression;
use yii\db\Query;
use app\classes\Assert;
use app\classes\Smarty;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountTariff;
use app\forms\templates\uu\InvoiceForm;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\Language;
use app\models\InvoiceSettings;
use app\classes\uu\model\Bill;

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

        /** @var InvoiceSettings $invoiceSetting */
        // Настройки счета-фактуры
        $invoiceSetting = InvoiceSettings::findOne([
            'doer_organization_id' => $sellerOrganization->organization_id,
            'customer_country_code' => $this->_clientAccount->contract->contragent->country_id,
        ]);

        $this->_seller = new InvoiceSellerLight(
            $this->_language,
            $sellerOrganization->setLanguage($dataLanguage),
            $invoiceSetting,
            $this->_clientAccount
        );

        // Данные организации покупателя
        $this->_buyer = new InvoiceBuyerLight($this->_clientAccount);

        // Вернуть проводки клиента за предыдущий календарный месяц для счета-фактуры
        $accountTariffTableName = AccountTariff::tableName();
        $accountEntryTableName = AccountEntry::tableName();

        $items = AccountEntry::find()
            ->joinWith('accountTariff')
            ->where([$accountTariffTableName . '.client_account_id' => $this->_clientAccount->id])
            ->andWhere(['>', $accountEntryTableName . '.vat', 0])
            ->andWhere(['bill_id' => $this->_bill->id])
            ->orderBy([
                'account_tariff_id' => SORT_ASC,
                'type_id' => SORT_ASC,
            ])
            ->all();

        if (count($items)) {
            // Данные о счете
            $this->_bill = new InvoiceBillLight($this->_bill->id, $this->_bill->date, $dataLanguage);
            // Данные проводках
            $this->_items = (new InvoiceItemsLight($this->_clientAccount, $this->_bill, $items, $invoiceSetting, $dataLanguage))->getAll();
        }
    }

    /**
     * @return array
     */
    public function getBills()
    {
        // Получить -1 месяц от даты счета
        $monthAgo = (new DateTime($this->_date))
            ->modify('-1 month')
            ->format('Y-m');

        return (new Query())
            ->select([
                'bill.*',
                'entries' => new Expression('COUNT(entry.id)'),
            ])
            ->from(['bill' => Bill::tableName()])
            ->leftJoin(['entry' => AccountEntry::tableName()], 'entry.bill_id = bill.id')
            ->where(['bill.client_account_id' => $this->_clientAccount->id])
            ->andWhere([
                'OR',
                [
                    'AND',
                    new Expression('DATE_FORMAT(bill.date, "%Y-%m") = :thisMonth', ['thisMonth' => $this->_date]),
                    ['bill.is_default' => 0]
                ],
                [
                    'AND',
                    new Expression('DATE_FORMAT(bill.date, "%Y-%m") = :prevMonth', ['prevMonth' => $monthAgo]),
                    ['bill.is_default' => 1]
                ]
            ])
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
            return $smarty->fetch(Yii::getAlias(InvoiceForm::getPath() . $this->_language . '.' . InvoiceForm::TEMPLATE_EXTENSION));
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