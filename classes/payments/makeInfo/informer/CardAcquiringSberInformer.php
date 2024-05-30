<?php

namespace app\classes\payments\makeInfo\informer;

use app\classes\payments\makeInfo\informer\CaseClassInformerShortInfo as case_class_InformerShortInfo;

class CardAcquiringSberInformer extends Informer
{
    public function detectCb(): bool
    {
        $json = $this->json;

        if ($json && isset($json['cardAuthInfo']) && isset($json['cardAuthInfo']['maskedPan'])) {
            return true;
        }

        return false;
    }

    protected function getShortText(): ?case_class_InformerShortInfo
    {
        $bank = $this->json['bankInfo']['bankName'] ?? '';
        if($bank)  $bank = ' (' . $this->json['bankInfo']['bankName'] . ')';

        $cardHolder = $this->json['cardAuthInfo']['cardholderName'] ?? '';
        $cardHolder = ($cardHolder && $cardHolder != 'CARDHOLDER NAME') ? " /{$cardHolder}/" : '';
        $s = "Оплата банковской картой {$this->json['cardAuthInfo']['maskedPan']}{$bank}{$cardHolder} (orderNumber: {$this->json['orderNumber']})";

        return new case_class_InformerShortInfo('Оплата банковской картой (Эквайринг). Оператор: Сбербанк.', $s);
    }

}