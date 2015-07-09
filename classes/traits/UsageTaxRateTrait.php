<?php

namespace app\classes\traits;

trait UsageTaxRateTrait
{

    public function getTaxRate()
    {
        if ($this->currentTariff->price_include_vat && $this->clientAccount->price_include_vat) {
            $organization_tax_rate = $this->clientAccount->getTaxRate();
            return $organization_tax_rate ? (1 + $organization_tax_rate) : 1;
        }
        return 1;
    }

}