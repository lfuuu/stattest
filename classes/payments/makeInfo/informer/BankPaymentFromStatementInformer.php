<?php

namespace app\classes\payments\makeInfo\informer;


use app\helpers\DateTimeZoneHelper;
use app\classes\payments\makeInfo\informer\CaseClassInformerShortInfo as case_class_InformerShortInfo;

class BankPaymentFromStatementInformer extends Informer
{
    public function detectCb(): bool
    {
        $json = $this->json;

        if (!$json || !is_array($json) || !isset($json['paymentsFromBankStatement'])) {
            return false;
        }

        return true;
    }

    protected function getShortText(): ?case_class_InformerShortInfo
    {
        if (!$this->info) {
            return null;
        }

        $operDate = date(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED, strtotime($this->json['payment_date']));
        $s = "Платеж №{$this->json['payment_no']} от {$operDate} bank_account={$this->json['payment_data']['payer_account']} bank_name={$this->json['payment_data']['payer_bank']}";

        return new case_class_InformerShortInfo('Банковский перевод', $s);
    }
}