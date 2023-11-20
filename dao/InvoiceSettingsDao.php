<?php

namespace app\dao;

use app\models\ClientContragent;
use app\models\InvoiceSettings;

class InvoiceSettingsDao extends \app\classes\Singleton
{
    const AT_ACCOUNT_CODE = 0;
    const TAX_REASON = 1;

    private ?array $_cache = null;
    private string $_lastErrorStr = '';

    private function initCache()
    {
        if ($this->_cache !== null) {
            return;
        }

        $this->_cache = [];

        /** @var InvoiceSettings $settings */
        foreach (InvoiceSettings::find()->all() as $settings) {
            $this->_cache[$settings->doer_organization_id][$settings->customer_country_code ?: 'any'][$settings->vat_apply_scheme] = [$settings->at_account_code, $settings->tax_reason];
        }
    }

    /**
     * @return array
     */
    public function getAtAccountCode($organizationId, $countryId, $contragentTaxRegime)
    {
        return $this->getValue($organizationId, $countryId, $contragentTaxRegime, self::AT_ACCOUNT_CODE);
    }

    /**
     * @return array
     */
    public function getTaxReason($organizationId, $countryId, $contragentTaxRegime)
    {
        return $this->getValue($organizationId, $countryId, $contragentTaxRegime, self::TAX_REASON);
    }

    private function getValue($organizationId, $countryId, $contragentTaxRegime, $field)
    {
        self::initCache();

        $this->_lastErrorStr = '';

        if (isset($this->_cache[$organizationId][$countryId])) { // настройки компания+страна
            $countrySettings = $this->_cache[$organizationId][$countryId];
        } elseif (isset($this->_cache[$organizationId]['any'])) { // настройки компания+любая страна
            $countrySettings = $this->_cache[$organizationId]['any'];
        } else {
            $countrySettings = null;
        }

        $value = null;
        if ($countrySettings) {
            if ($contragentTaxRegime == ClientContragent::TAX_REGTIME_YCH_VAT0 && isset($countrySettings[InvoiceSettings::VAT_SCHEME_NONVAT])) {
                $value = $countrySettings[InvoiceSettings::VAT_SCHEME_NONVAT];
            } elseif ($contragentTaxRegime == ClientContragent::TAX_REGTIME_OCH_VAT18 && isset($countrySettings[InvoiceSettings::VAT_SCHEME_VAT])) {
                $value = $countrySettings[InvoiceSettings::VAT_SCHEME_VAT];
            } elseif (isset($countrySettings[InvoiceSettings::VAT_SCHEME_ANY])) {
                $value = $countrySettings[InvoiceSettings::VAT_SCHEME_ANY];
            }
        }

        if ($value === null) {
            $this->_lastErrorStr = ($countrySettings ? 'Tax settings not found (' . $contragentTaxRegime . ')' : 'relation organization->country not found "' . $organizationId . '"->"' . $countryId . '"');
            return $value;
        }

        $valueF = $value[$field];

        if (!$valueF) {
            $this->_lastErrorStr = 'no value';
        }

        return $valueF;
    }

    public function getLastErrorString(): string
    {
        return $this->_lastErrorStr;
    }
}