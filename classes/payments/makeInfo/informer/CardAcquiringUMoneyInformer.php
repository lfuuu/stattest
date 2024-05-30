<?php

namespace app\classes\payments\makeInfo\informer;

use app\classes\payments\makeInfo\informer\CaseClassInformerShortInfo as case_class_InformerShortInfo;

class CardAcquiringUMoneyInformer extends Informer
{
    public function detectCb(): bool
    {
        $json = $this->json;

        if (
            $json
            && isset($json['payment_method'])
            && isset($json['payment_method']['card'])
            && isset($json['payment_method']['card']['first6'])
        ) {
            return true;
        }

        return false;
    }

    protected function getShortText(): case_class_InformerShortInfo
    {
        $pm = $this->json['payment_method']['card'];

        /*
         *     "card": {
         *      "last4": "6542",
         *      "first6": "220028",
         *      "card_type": "Mir",
         *      "expiry_year": "2027",
         *      "issuer_name": "MTS-Bank",
         *      "card_product": {
         *        "code": "PPB",
         *        "name": "MIR Privilege Business"
         *      },
         *      "expiry_month": "07",
         *      "issuer_country": "RU"
         *    },
         */

        $bank = $pm['issuer_name'] ?? '';
        if ($bank) $bank = ' (' . $bank . ')';

        $card = $pm['first6'] . 'XXXXXX' . $pm['last4'];
        $s = "Оплата банковской картой {$card}{$bank} (id: {$this->json['id']})";

        return new case_class_InformerShortInfo('Оплата банковской картой (Эквайринг). Оператор: ЮMoney.', $s);
    }
}