<?php

namespace app\classes\payments\makeInfo\informer;


use app\helpers\DateTimeZoneHelper;
use app\models\PaymentInfo;
use app\classes\payments\makeInfo\informer\CaseClassInformerShortInfo as case_class_InformerShortInfo;

class BankPaymentInformer extends Informer
{
    public function detectCb(): bool
    {
        $json = $this->json;

        if (!$json || !is_array($json) || !isset($json['paymentPurpose'])) {
            return false;
        }

        return true;
    }

    public function getPaymentInfo($paymentId): ?PaymentInfo
    {
        $j = $this->json;

        $info = PaymentInfo::findOne(['payment_id' => $paymentId]) ?: (new PaymentInfo());
        $info->payment_id = $paymentId;
        $info->payer = $j['payerName'] ?? '';
        $info->payer_inn = $j['payerInn'] ?? '';
        $info->payer_bik = $j['payerBic'];
        $info->payer_bank = $j['payerBank'];
        $info->payer_account = $j['payerAccount'];
        $info->getter = $j['recipient'];
        $info->getter_inn = $j['recipientInn'];
        $info->getter_bik = $j['recipientBic'];
        $info->getter_bank = $j['recipientBank'];
        $info->getter_account = $j['recipientAccount'];
        $info->comment = $j['paymentPurpose'];

        return $info;
    }

    protected function getShortText(): ?case_class_InformerShortInfo
    {
        if (!$this->info) {
            return null;
        }

        $operDate = date(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED, strtotime($this->json['drawDate']));
        $s = "Платеж №{$this->json['id']} от {$operDate} bank_account={$this->info->payer_account} bank_name={$this->info->payer_bank}";

        return new case_class_InformerShortInfo('Банковский перевод', $s);
    }
}