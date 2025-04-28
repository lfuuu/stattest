<?php

namespace app\classes\documents;

use app\models\BillLine;
use app\models\ClientAccount;
use app\models\Currency;
use Yii;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use app\classes\BillQRCode;
use app\controllers\SiteController;
use app\models\Organization;
use app\models\Bill;

/**
 * @property Organization $organization
 */
abstract class DocumentReport extends BaseObject
{

    const TEMPLATE_PATH = '@app/views/documents/';

    const DOC_TYPE_BILL = 'bill';
    const DOC_TYPE_BILL_OPERATOR = 'bill_operator';
    const DOC_TYPE_INVOICE = 'invoice';
    const DOC_TYPE_PROFORMA = 'proforma';
    const DOC_TYPE_CREDIT_NOTE = 'credit_note';
    const DOC_TYPE_CURRENT_STATEMENT = 'current_statement';

    /**
     * @var Bill
     */
    public $bill;
    /**
     * @var ClientAccount
     */
    public $clientAccount;
    public $sendEmail;
    public $lines = [];

    public $isAllLanguages = false;
    public $isMultiCurrencyDocument = false;

    public
        $sum,
        $sum_without_tax,
        $sum_with_tax,
        $sum_discount = 0;

    /**
     * @return ActiveRecord
     */
    public function getOrganization()
    {
        return $this->bill->clientAccount->getOrganization($this->bill->bill_date)->setLanguage($this->getLanguage());
    }

    /**
     * @return ClientAccount
     */
    public function getPayer()
    {
        return
            $this->bill->clientAccount->loadVersionOnDate($this->bill->bill_date);
    }

    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return self::TEMPLATE_PATH .
            $this->getLanguage() . '/' .
            $this->getDocTypeFileName() .
            ($this->isMultiCurrencyDocument ? '' : '_' . mb_strtolower($this->getCurrency(), 'UTF-8'));
    }

    public function getDocTypeFileName()
    {
        return $this->getDocType();
    }

    /**
     * @return string
     */
    public function getHeaderTemplate()
    {
        return self::TEMPLATE_PATH . $this->getLanguage() . '/header_base';
    }

    /**
     * @return array
     */
    public function getQrCode()
    {
        $result = BillQRCode::getNo($this->bill->bill_no);
        return $result['bill'];
    }

    /**
     * @return $this
     */
    public function setBill(Bill $bill = null)
    {
        $this->bill = $bill;
        return $this;
    }

    /**
     * @param ClientAccount $clientAccount
     * @return $this
     */
    public function setClientAccount(ClientAccount $clientAccount)
    {
        $this->clientAccount = $clientAccount;

        $this->bill = new Bill();
        $this->bill->bill_date = date('Y-m-d');
        $this->bill->client_id = $clientAccount->id;
        $this->bill->currency = $clientAccount->currency;
        $this->bill->price_include_vat = $clientAccount->price_include_vat;

        return $this;
    }

    /**
     * @return $this
     */
    public function setSendEmail($sendEmail)
    {
        $this->sendEmail = $sendEmail;
        return $this;
    }

    /**
     * @return $this
     */
    public function prepare()
    {
        return $this
            ->fetchLines()
            ->filterLines()
            ->postFilterLines()
            ->calculateSummary();
    }

    /**
     * @param bool|true $inline_img
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @throws InvalidParamException
     */
    public function render($inline_img = true)
    {
        /** @var SiteController $siteController */
        $siteController = Yii::$app->createControllerByID('site');
        $siteController->layout = 'empty';
        return $siteController->render($this->getTemplateFile() . '.php', [
            'document' => $this,
            'inline_img' => $inline_img,
        ]);
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function renderAsPDF()
    {
        /** @var SiteController $siteController */
        $siteController = Yii::$app->createControllerByID('site');
        $siteController->layout = 'empty';
        return $siteController->renderAsPDF($this->getTemplateFile() . '.php', [
            'document' => $this,
            'inline_img' => true,
        ]);
    }

    /**
     * @return $this
     */
    protected function fetchLines()
    {
        $tax_rate = $this->bill->clientAccount->getTaxRate();

        $this->lines =
            Yii::$app->db->createCommand('
                select
                    l.*,
                    if(g.nds is null, ' . $tax_rate . ', g.nds) as nds,
                    g.art as art,
                    g.num_id as num_id,
                    g.store as in_store,
                    if(l.service="usage_extra",
                      (
                        select
                          okvd_code
                        from
                          usage_extra u, tarifs_extra t
                        where
                            u.id = l.id_service and
                            t.id = tarif_id
                      ),
                      if (l.type = "good",
                        (
                          select
                            okei
                          from
                            g_unit
                          where
                            id = g.unit_id
                        ), "")
                    ) okvd_code
                from newbill_lines l
                        left join g_goods g on (l.item_id = g.id)
                            left join g_unit as gu ON g.unit_id = gu.id
                where l.bill_no=:billNo
                order by sort
            ', [
                ':billNo' => $this->bill->bill_no,
            ])->queryAll();

        return $this;
    }

    /**
     * @return $this
     */
    protected function filterLines()
    {
        $filtered_lines = [];

        foreach ($this->lines as $line) {
            if (!$line['sum']) {
                continue;
            }

            $filtered_lines[] = $line;
        }

        $this->lines = $filtered_lines;

        return $this;
    }

    /**
     * @return $this
     */
    protected function postFilterLines()
    {
        $type = $langCode = $priceIncludeVat = null;

        if ($this->clientAccount && $this->clientAccount instanceof ClientAccount) {
            $type = $this->clientAccount->type_of_bill;
            $priceIncludeVat = $this->clientAccount->price_include_vat;
            $langCode = $this->clientAccount->contragent->lang_code;
        } elseif ($this->bill && $this->bill instanceof Bill) {
            $type = $this->bill->clientAccount->type_of_bill;
            $priceIncludeVat = $this->bill->price_include_vat;
            $langCode = $this->bill->clientAccount->contragent->lang_code;
        }

        if ($type !== null && $type == ClientAccount::TYPE_OF_BILL_SIMPLE) {
            $this->lines = BillLine::compactLines($this->lines, $langCode, $priceIncludeVat);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function calculateSummary()
    {
        $this->sum =
        $this->sum_without_tax =
        $this->sum_with_tax =
        $this->sum_discount = 0;

        foreach ($this->lines as $line) {
            $this->sum += $line['sum'];
            $this->sum_without_tax += $line['sum_without_tax'];
            $this->sum_with_tax += $line['sum_tax'];
            $this->sum_discount += $line['discount_auto'] + $line['discount_set'];
        }

        return $this;
    }

    /**
     * @throws NotSupportedException
     */
    public function getLanguage()
    {
        // Если в наследуемом объекте стоит $isAllLanguages = true, то данный метод не будет вызван.
        throw new NotSupportedException('Метод не переопределен');
    }


    /**
     * @throws NotSupportedException
     */
    public function getCurrency()
    {
        // Если в наследуемом объекте стоит $isMultiCurrencyDocument = true, то данный метод не будет вызван.
        throw new NotSupportedException('Метод не переопределен');
    }


    abstract public function getDocType();

    abstract public function getName();
}