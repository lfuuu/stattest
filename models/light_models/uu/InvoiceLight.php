<?php

namespace app\models\light_models\uu;

use Yii;
use DateTime;
use yii\base\Component;
use app\classes\Assert;
use app\classes\Smarty;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\AccountTariff;
use app\models\ClientAccount;
use app\models\Language;
use app\models\InvoiceSettings;
use app\forms\templates\uu\InvoiceForm;
use app\helpers\DateTimeZoneHelper;

class InvoiceLight extends Component
{

    private
        $seller,
        $buyer,
        $items,
        $bill,

        $clientAccount = null,
        $language = Language::LANGUAGE_DEFAULT,
        $date = null;

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
                ->format(DateTimeZoneHelper::DATE_FORMAT);
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

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        // Устанавливаем язык для универсального шаблона
        $dataLanguage =
            $this->language == InvoiceForm::UNIVERSAL_INVOICE_KEY
                ? Language::LANGUAGE_ENGLISH
                : $this->language;

        // Данные организации продавца
        $dateForOrganization = (new DateTime)->format(DateTimeZoneHelper::DATE_FORMAT);

        $sellerOrganization = $this->clientAccount->contract->getOrganization($dateForOrganization);
        Assert::isObject($sellerOrganization, 'Данные об организации за дату "' . $dateForOrganization . '" не найдены');

        /** @var InvoiceSettings $invoiceSetting */
        // Настройки счета-фактуры
        $invoiceSetting = InvoiceSettings::findOne([
            'doer_organization_id' => $sellerOrganization->organization_id,
            'customer_country_code' => $this->clientAccount->contract->contragent->country_id,
        ]);

        $this->seller = new InvoiceSellerLight(
            $this->language,
            $sellerOrganization->setLanguage($dataLanguage),
            $invoiceSetting,
            $this->clientAccount
        );

        // Данные организации покупателя
        $this->buyer = new InvoiceBuyerLight($this->clientAccount);

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
            // Первая проводка
            $firstAccountEntry = reset($items);
            // Данные о счете
            $this->bill = new InvoiceBillLight($firstAccountEntry->bill_id, $firstAccountEntry->date, $dataLanguage);
            // Данные проводках
            $this->items = (new InvoiceItemsLight($this->clientAccount, $this->bill, $items, $invoiceSetting))->getAll();
        }
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        $this->prepare();

        return [
            InvoiceSellerLight::getKey() => (array)$this->seller,
            InvoiceBuyerLight::getKey() => (array)$this->buyer,
            InvoiceItemsLight::getBlockKey() => (array)$this->items,
            InvoiceBillLight::getKey() => (array)$this->bill,
        ];
    }

    /**
     * @return string
     */
    public function render()
    {
        $smarty = Smarty::init();
        $smarty->assign($this->getProperties());

        $invoiceTemplate = new InvoiceForm($this->language);

        if ($invoiceTemplate->fileExists()) {
            return $smarty->fetch(Yii::getAlias(InvoiceForm::getPath() . $this->language . '.' . InvoiceForm::TEMPLATE_EXTENSION));
        } else {
            Yii::$app->session->addFlash('error', 'Шаблон счета-фактуры для языка "' . $this->language . '" не найден');
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